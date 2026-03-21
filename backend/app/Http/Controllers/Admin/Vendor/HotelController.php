<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Hotel::class);
        $hotels = Hotel::where('vendor_id', auth()->id())->with(['images', 'bannerImage', 'cityRelation', 'countryRelation'])->latest()->paginate(15);
        return view('admin.vendor.hotels.index', compact('hotels'));
    }

    public function create(): View
    {
        $this->authorize('create', Hotel::class);
        $countries = Country::orderBy('name')->get();
        $cities = City::with('country')->orderBy('name')->get();
        $amenities = Amenity::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.vendor.hotels.create', compact('countries', 'cities', 'amenities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Hotel::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'check_in' => 'nullable|string|max:10',
            'check_out' => 'nullable|string|max:10',
            'late_checkout_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_name' => 'nullable|string|max:64',
            'tax_inclusive' => 'nullable|in:0,1',
            'cancellation_policy_preset' => 'nullable|string|in:,none,non_refundable,free_24,free_48,free_168,custom',
            'cancellation_policy_custom' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);
        $validated['vendor_id'] = auth()->id();
        $validated['status'] = 'active';
        $validated['cancellation_policy'] = $this->buildCancellationPolicyFromRequest($request);
        unset($validated['cancellation_policy_preset'], $validated['cancellation_policy_custom']);
        $this->syncCityCountryFromIds($validated);
        $this->syncCityCountryFromText($validated);
        if (! empty($validated['check_in'])) {
            $validated['check_in'] = \Carbon\Carbon::parse($validated['check_in'])->format('H:i:s');
        }
        if (! empty($validated['check_out'])) {
            $validated['check_out'] = \Carbon\Carbon::parse($validated['check_out'])->format('H:i:s');
        }
        $validated['late_checkout_price'] = isset($validated['late_checkout_price']) && $validated['late_checkout_price'] > 0 ? $validated['late_checkout_price'] : null;
        $validated['tax_rate'] = isset($validated['tax_rate']) && $validated['tax_rate'] > 0 ? $validated['tax_rate'] / 100 : null;
        $validated['tax_inclusive'] = (bool) ($validated['tax_inclusive'] ?? false);
        $amenityIds = $validated['amenities'] ?? [];
        unset($validated['amenities']);
        $hotel = Hotel::create($validated);
        $hotel->amenities()->sync($amenityIds);
        return redirect()->route('admin.vendor.hotels.index')->with('success', 'Hotel created.');
    }

    public function edit(Hotel $hotel): View
    {
        $this->authorize('update', $hotel);
        $hotel->load('amenities');
        $countries = Country::orderBy('name')->get();
        $cities = City::with('country')->orderBy('name')->get();
        $amenities = Amenity::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.vendor.hotels.edit', compact('hotel', 'countries', 'cities', 'amenities'));
    }

    public function update(Request $request, Hotel $hotel): RedirectResponse
    {
        $this->authorize('update', $hotel);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'check_in' => 'nullable|string|max:10',
            'check_out' => 'nullable|string|max:10',
            'late_checkout_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_name' => 'nullable|string|max:64',
            'tax_inclusive' => 'nullable|in:0,1',
            'status' => 'required|in:active,inactive',
            'cancellation_policy_preset' => 'nullable|string|in:,none,non_refundable,free_24,free_48,free_168,custom',
            'cancellation_policy_custom' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);
        $validated['cancellation_policy'] = $this->buildCancellationPolicyFromRequest($request);
        unset($validated['cancellation_policy_preset'], $validated['cancellation_policy_custom']);
        $this->syncCityCountryFromIds($validated);
        $this->syncCityCountryFromText($validated);
        if (! empty($validated['check_in'])) {
            $validated['check_in'] = \Carbon\Carbon::parse($validated['check_in'])->format('H:i:s');
        }
        if (! empty($validated['check_out'])) {
            $validated['check_out'] = \Carbon\Carbon::parse($validated['check_out'])->format('H:i:s');
        }
        $validated['late_checkout_price'] = isset($validated['late_checkout_price']) && $validated['late_checkout_price'] > 0 ? $validated['late_checkout_price'] : null;
        $validated['tax_rate'] = isset($validated['tax_rate']) && $validated['tax_rate'] > 0 ? $validated['tax_rate'] / 100 : null;
        $validated['tax_inclusive'] = (bool) ($validated['tax_inclusive'] ?? false);
        $amenityIds = $validated['amenities'] ?? [];
        unset($validated['amenities']);
        $hotel->update($validated);
        $hotel->amenities()->sync($amenityIds);
        return redirect()->route('admin.vendor.hotels.index')->with('success', 'Hotel updated.');
    }

    /**
     * Set city and country strings from city_id/country_id when present.
     *
     * @param array<string, mixed> $validated
     */
    private function syncCityCountryFromIds(array &$validated): void
    {
        if (! empty($validated['city_id'])) {
            $city = City::find($validated['city_id']);
            if ($city) {
                $validated['city'] = $city->name;
                if (empty($validated['country_id']) && $city->country_id) {
                    $validated['country_id'] = $city->country_id;
                    $validated['country'] = $city->country->name ?? $validated['country'] ?? null;
                }
            }
        }
        if (! empty($validated['country_id'])) {
            $country = Country::find($validated['country_id']);
            if ($country) {
                $validated['country'] = $country->name;
            }
        }
    }

    /**
     * When address search (Geoapify) fills city/country as text but not city_id/country_id,
     * try to resolve them from the database so hotels link to cities for search/filtering.
     *
     * @param array<string, mixed> $validated
     */
    private function syncCityCountryFromText(array &$validated): void
    {
        $cityName = trim((string) ($validated['city'] ?? ''));
        $countryName = trim((string) ($validated['country'] ?? ''));

        if (empty($cityName) && empty($countryName)) {
            return;
        }

        // Resolve country_id from country text if not already set
        if (empty($validated['country_id']) && ! empty($countryName)) {
            $country = Country::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($countryName)])
                ->orWhereRaw('LOWER(TRIM(code)) = ?', [strtolower($countryName)])
                ->first();
            if ($country) {
                $validated['country_id'] = $country->id;
                $validated['country'] = $country->name;
            }
        }

        // Resolve city_id from city text (optionally scoped by country)
        if (empty($validated['city_id']) && ! empty($cityName)) {
            $cityQuery = City::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cityName)]);
            if (! empty($validated['country_id'])) {
                $cityQuery->where('country_id', $validated['country_id']);
            }
            $city = $cityQuery->first();
            if ($city) {
                $validated['city_id'] = $city->id;
                $validated['city'] = $city->name;
                if (empty($validated['country_id']) && $city->country_id) {
                    $validated['country_id'] = $city->country_id;
                    $validated['country'] = $city->country->name ?? $validated['country'];
                }
            }
        }
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        $this->authorize('delete', $hotel);
        $hotel->update(['status' => 'inactive']);
        return redirect()->route('admin.vendor.hotels.index')->with('success', 'Hotel deactivated.');
    }

    /**
     * Build cancellation_policy array from request (preset or custom JSON). Returns null for none.
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
