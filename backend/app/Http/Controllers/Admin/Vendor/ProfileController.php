<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $profile = auth()->user()->vendorProfile ?? VendorProfile::create([
            'user_id' => auth()->id(),
            'status' => VendorProfile::STATUS_APPROVED,
        ]);

        return view('admin.vendor.profile.edit', compact('profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:500',
            'business_phone' => 'nullable|string|max:50',
            'business_website' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'business_details' => 'nullable|string|max:2000',
        ]);

        $profile = auth()->user()->vendorProfile ?? VendorProfile::create([
            'user_id' => auth()->id(),
            'status' => VendorProfile::STATUS_APPROVED,
        ]);

        $profile->update($validated);

        return redirect()->route('admin.vendor.profile.edit')
            ->with('success', 'Business details updated.');
    }
}
