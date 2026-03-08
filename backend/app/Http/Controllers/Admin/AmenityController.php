<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AmenityController extends Controller
{
    public function index(): View
    {
        $amenities = Amenity::orderBy('sort_order')->orderBy('name')->paginate(20);
        return view('admin.amenities.index', compact('amenities'));
    }

    public function create(): View
    {
        return view('admin.amenities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:amenities,slug',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = Amenity::max('sort_order') + 1;
        }
        Amenity::create($validated);
        return redirect()->route('admin.amenities.index')->with('success', 'Amenity created.');
    }

    public function edit(Amenity $amenity): View
    {
        return view('admin.amenities.edit', compact('amenity'));
    }

    public function update(Request $request, Amenity $amenity): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:amenities,slug,' . $amenity->id,
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $amenity->update($validated);
        return redirect()->route('admin.amenities.index')->with('success', 'Amenity updated.');
    }

    public function destroy(Amenity $amenity): RedirectResponse
    {
        $amenity->hotels()->detach();
        $amenity->rooms()->detach();
        $amenity->delete();
        return redirect()->route('admin.amenities.index')->with('success', 'Amenity deleted.');
    }
}
