<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomAvailability;
use App\Services\AvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Room::class);
        $query = Room::whereHas('hotel', fn ($q) => $q->where('vendor_id', auth()->id()));
        if ($request->filled('hotel_id')) {
            $hotel = Hotel::where('id', $request->hotel_id)->where('vendor_id', auth()->id())->firstOrFail();
            $query->where('hotel_id', $hotel->id);
        }
        $rooms = $query->with(['hotel', 'images', 'bannerImage'])->latest()->paginate(15);
        $hotels = Hotel::where('vendor_id', auth()->id())->orderBy('name')->get();
        return view('admin.vendor.rooms.index', compact('rooms', 'hotels'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Room::class);
        $hotels = Hotel::where('vendor_id', auth()->id())->orderBy('name')->get();
        $hotelId = $request->get('hotel_id');
        return view('admin.vendor.rooms.create', compact('hotels', 'hotelId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Room::class);
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'total_rooms' => 'required|integer|min:1',
            'cancellation_policy_preset' => 'nullable|string|in:,none,non_refundable,free_24,free_48,free_168,custom',
            'cancellation_policy_custom' => 'nullable|string',
        ]);
        $validated['cancellation_policy'] = $this->buildCancellationPolicyFromRequest($request);
        $hotel = Hotel::where('id', $validated['hotel_id'])->where('vendor_id', auth()->id())->firstOrFail();
        unset($validated['cancellation_policy_preset'], $validated['cancellation_policy_custom']);
        Room::create($validated);
        return redirect()->route('admin.vendor.rooms.index', ['hotel_id' => $hotel->id])->with('success', 'Room created.');
    }

    public function edit(Room $room): View
    {
        $this->authorize('update', $room);
        $room->load('hotel');
        return view('admin.vendor.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'total_rooms' => 'required|integer|min:1',
            'cancellation_policy_preset' => 'nullable|string|in:,none,non_refundable,free_24,free_48,free_168,custom',
            'cancellation_policy_custom' => 'nullable|string',
        ]);
        $validated['cancellation_policy'] = $this->buildCancellationPolicyFromRequest($request);
        unset($validated['cancellation_policy_preset'], $validated['cancellation_policy_custom']);
        $room->update($validated);
        return redirect()->route('admin.vendor.rooms.index')->with('success', 'Room updated.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);
        $room->delete();
        return redirect()->route('admin.vendor.rooms.index')->with('success', 'Room deleted.');
    }

    public function availability(Room $room): View
    {
        $this->authorize('update', $room);
        $room->load('hotel');
        $availability = $room->availability()->orderBy('date')->paginate(30);
        return view('admin.vendor.rooms.availability', compact('room', 'availability'));
    }

    public function storeAvailability(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);
        $request->validate([
            'date' => 'required|date',
            'available_rooms' => 'required|integer|min:0',
            'price_override' => 'nullable|numeric|min:0',
        ]);
        RoomAvailability::updateOrCreate(
            ['room_id' => $room->id, 'date' => $request->date],
            [
                'available_rooms' => $request->available_rooms,
                'price_override' => $request->price_override ?: null,
            ]
        );
        return redirect()->route('admin.vendor.rooms.availability', $room)->with('success', 'Availability updated.');
    }

    /**
     * Build cancellation_policy array from request (preset or custom JSON). Returns null for none / use hotel default.
     *
     * @return array<string, mixed>|null
     */
    private function buildCancellationPolicyFromRequest(Request $request): ?array
    {
        $preset = $request->input('cancellation_policy_preset');
        if ($preset === null || $preset === '' || $preset === 'none') {
            return null;
        }
        if ($preset === 'custom') {
            $custom = $request->input('cancellation_policy_custom');
            if (empty(trim((string) $custom))) {
                return null;
            }
            $decoded = json_decode($custom, true);
            if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
            return $decoded;
        }
        $map = [
            'non_refundable' => ['type' => 'non_refundable'],
            'free_24' => ['type' => 'free_until_hours', 'hours' => 24],
            'free_48' => ['type' => 'free_until_hours', 'hours' => 48],
            'free_168' => ['type' => 'free_until_hours', 'hours' => 168],
        ];
        return $map[$preset] ?? null;
    }
}
