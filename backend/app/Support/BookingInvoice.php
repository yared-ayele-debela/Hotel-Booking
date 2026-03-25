<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Storage;

class BookingInvoice
{
    /**
     * Data for the HTML booking receipt (platform branding + hotel media).
     *
     * @return array<string, mixed>
     */
    public static function viewData(Booking $booking, int $nights, float $subtotal): array
    {
        $booking->loadMissing([
            'hotel.bannerImage',
            'hotel.images',
            'hotel.countryRelation',
            'hotel.cityRelation',
            'hotel.amenities',
        ]);

        $logoPath = PlatformSetting::get('site_logo');
        $companyLogoUrl = null;
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $companyLogoUrl = asset('storage/'.$logoPath);
        }

        $hotel = $booking->hotel;
        $hotelImageUrl = null;
        $hotelAmenities = null;
        if ($hotel) {
            if ($hotel->bannerImage) {
                $hotelImageUrl = $hotel->bannerImage->image_url;
            } elseif ($hotel->images->isNotEmpty()) {
                $hotelImageUrl = $hotel->images->first()->image_url;
            }
            if ($hotel->amenities->isNotEmpty()) {
                $hotelAmenities = $hotel->amenities->pluck('name')->take(15)->implode(', ');
            }
        }

        return [
            'booking' => $booking,
            'nights' => $nights,
            'subtotal' => $subtotal,
            'company_name' => PlatformSetting::get('site_name', config('app.name', 'Hotel Booking')),
            'company_logo_url' => $companyLogoUrl,
            'company_description' => PlatformSetting::get('site_description'),
            'company_email' => PlatformSetting::get('site_email'),
            'company_phone' => PlatformSetting::get('site_phone'),
            'company_address' => PlatformSetting::get('site_address'),
            'hotel_image_url' => $hotelImageUrl,
            'hotel_amenities' => $hotelAmenities,
        ];
    }
}
