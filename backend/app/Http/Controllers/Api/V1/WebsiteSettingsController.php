<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WebsiteSettingsService;
use Illuminate\Http\JsonResponse;

class WebsiteSettingsController extends Controller
{
    /**
     * Public endpoint for frontend to fetch website settings.
     * No auth required.
     */
    public function index(): JsonResponse
    {
        $settings = [
            'site_name' => WebsiteSettingsService::getSiteName(),
            'site_description' => WebsiteSettingsService::getSiteDescription(),
            'site_logo' => WebsiteSettingsService::getSiteLogo(),
            'site_favicon' => WebsiteSettingsService::getSiteFavicon(),
            'site_email' => WebsiteSettingsService::getSiteEmail(),
            'site_phone' => WebsiteSettingsService::getSitePhone(),
            'site_address' => WebsiteSettingsService::getSiteAddress(),
            'social_links' => WebsiteSettingsService::getSocialLinks(),
            'meta_title' => WebsiteSettingsService::getMetaTitle(),
            'meta_description' => WebsiteSettingsService::getMetaDescription(),
            'meta_keywords' => WebsiteSettingsService::getMetaKeywords(),
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
