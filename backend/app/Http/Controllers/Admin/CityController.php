<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(Request $request): View
    {
        $cities = City::with('country')->latest()->paginate(15);
        return view('admin.cities.index', compact('cities'));
    }

    public function create(): View
    {
        $countries = Country::orderBy('name')->get();
        return view('admin.cities.create', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('locations/cities', 'public');
        }
        City::create($validated);
        return redirect()->route('admin.cities.index')->with('success', 'City created.');
    }

    public function edit(City $city): View
    {
        $countries = Country::orderBy('name')->get();
        return view('admin.cities.edit', compact('city', 'countries'));
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        if ($request->hasFile('image')) {
            if ($city->image && Storage::disk('public')->exists($city->image)) {
                Storage::disk('public')->delete($city->image);
            }
            $validated['image'] = $request->file('image')->store('locations/cities', 'public');
        }
        $city->update($validated);
        return redirect()->route('admin.cities.index')->with('success', 'City updated.');
    }

    public function destroy(City $city): RedirectResponse
    {
        if ($city->image && Storage::disk('public')->exists($city->image)) {
            Storage::disk('public')->delete($city->image);
        }
        $city->delete();
        return redirect()->route('admin.cities.index')->with('success', 'City deleted.');
    }
}
