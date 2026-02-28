<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Hotel::class);
        $hotels = Hotel::where('vendor_id', auth()->id())->with(['images', 'bannerImage'])->latest()->paginate(15);
        return view('admin.vendor.hotels.index', compact('hotels'));
    }

    public function create(): View
    {
        $this->authorize('create', Hotel::class);
        return view('admin.vendor.hotels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Hotel::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'check_in' => 'nullable|string|max:10',
            'check_out' => 'nullable|string|max:10',
        ]);
        $validated['vendor_id'] = auth()->id();
        $validated['status'] = 'active';
        if (! empty($validated['check_in'])) {
            $validated['check_in'] = \Carbon\Carbon::parse($validated['check_in'])->format('H:i:s');
        }
        if (! empty($validated['check_out'])) {
            $validated['check_out'] = \Carbon\Carbon::parse($validated['check_out'])->format('H:i:s');
        }
        Hotel::create($validated);
        return redirect()->route('admin.vendor.hotels.index')->with('success', 'Hotel created.');
    }

    public function edit(Hotel $hotel): View
    {
        $this->authorize('update', $hotel);
        return view('admin.vendor.hotels.edit', compact('hotel'));
    }

    public function update(Request $request, Hotel $hotel): RedirectResponse
    {
        $this->authorize('update', $hotel);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'check_in' => 'nullable|string|max:10',
            'check_out' => 'nullable|string|max:10',
            'status' => 'required|in:active,inactive',
        ]);
        if (! empty($validated['check_in'])) {
            $validated['check_in'] = \Carbon\Carbon::parse($validated['check_in'])->format('H:i:s');
        }
        if (! empty($validated['check_out'])) {
            $validated['check_out'] = \Carbon\Carbon::parse($validated['check_out'])->format('H:i:s');
        }
        $hotel->update($validated);
        return redirect()->route('admin.vendor.hotels.index')->with('success', 'Hotel updated.');
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        $this->authorize('delete', $hotel);
        $hotel->update(['status' => 'inactive']);
        return redirect()->route('admin.vendor.hotels.index')->with('success', 'Hotel deactivated.');
    }
}
