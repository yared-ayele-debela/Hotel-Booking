<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WebsiteSettingsRequest;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name' => PlatformSetting::get('site_name', 'Hotel Booking Platform'),
            'site_description' => PlatformSetting::get('site_description', 'Find and book the perfect hotel for your next trip'),
            'site_logo' => PlatformSetting::get('site_logo'),
            'site_favicon' => PlatformSetting::get('site_favicon'),
            'site_email' => PlatformSetting::get('site_email', 'info@hotelbooking.com'),
            'site_phone' => PlatformSetting::get('site_phone'),
            'site_address' => PlatformSetting::get('site_address'),
            'social_facebook' => PlatformSetting::get('social_facebook'),
            'social_twitter' => PlatformSetting::get('social_twitter'),
            'social_instagram' => PlatformSetting::get('social_instagram'),
            'social_linkedin' => PlatformSetting::get('social_linkedin'),
            'meta_title' => PlatformSetting::get('meta_title', 'Hotel Booking Platform'),
            'meta_description' => PlatformSetting::get('meta_description', 'Find and book the perfect hotel for your next trip'),
            'meta_keywords' => PlatformSetting::get('meta_keywords', 'hotel, booking, travel, accommodation'),
            'google_analytics' => PlatformSetting::get('google_analytics'),
            'maintenance_mode' => PlatformSetting::get('maintenance_mode', '0'),
            'maintenance_message' => PlatformSetting::get('maintenance_message', 'We are currently under maintenance. Please check back soon.'),
        ];

        return view('admin.website-settings.index', compact('settings'));
    }

    public function update(WebsiteSettingsRequest $request)
    {
        
        $validated = $request->validated();
        // dd($validated);

        // Handle file uploads
        if ($request->hasFile('site_logo')) {
            $logo = $request->file('site_logo');
            $logoName = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
            
            // Delete old logo if exists
            $oldLogo = PlatformSetting::get('site_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            
            $logoPath = $logo->storeAs('website', $logoName, 'public');
            PlatformSetting::set('site_logo', $logoPath);
        }

        if ($request->hasFile('site_favicon')) {
            $favicon = $request->file('site_favicon');
            $faviconName = 'favicon_' . time() . '.' . $favicon->getClientOriginalExtension();
            
            // Delete old favicon if exists
            $oldFavicon = PlatformSetting::get('site_favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            
            $faviconPath = $favicon->storeAs('website', $faviconName, 'public');
            PlatformSetting::set('site_favicon', $faviconPath);
        }

        // Update text settings
        foreach ($validated as $key => $value) {
            if (!in_array($key, ['site_logo', 'site_favicon'])) {
                PlatformSetting::set($key, $value);
            }
        }
    

        return redirect()
            ->route('admin.website-settings.index')
            ->with('success', 'Website settings updated successfully!');
    }

    public function removeLogo()
    {
        $logo = PlatformSetting::get('site_logo');
        if ($logo && Storage::disk('public')->exists($logo)) {
            Storage::disk('public')->delete($logo);
        }
        PlatformSetting::set('site_logo', null);

        return redirect()
            ->route('admin.website-settings.index')
            ->with('success', 'Logo removed successfully!');
    }

    public function removeFavicon()
    {
        $favicon = PlatformSetting::get('site_favicon');
        if ($favicon && Storage::disk('public')->exists($favicon)) {
            Storage::disk('public')->delete($favicon);
        }
        PlatformSetting::set('site_favicon', null);

        return redirect()
            ->route('admin.website-settings.index')
            ->with('success', 'Favicon removed successfully!');
    }
}
