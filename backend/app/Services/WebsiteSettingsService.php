<?php

namespace App\Services;

use App\Models\PlatformSetting;

class WebsiteSettingsService
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return PlatformSetting::get($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        PlatformSetting::set($key, $value);
    }

    public static function getSiteName(): string
    {
        return self::get('site_name', 'Hotel Booking Platform');
    }

    public static function getSiteDescription(): string
    {
        return self::get('site_description', 'Find and book the perfect hotel for your next trip');
    }

    public static function getSiteLogo(): ?string
    {
        $logo = self::get('site_logo');
        return $logo ? asset('storage/' . $logo) : null;
    }

    public static function getSiteFavicon(): ?string
    {
        $favicon = self::get('site_favicon');
        return $favicon ? asset('storage/' . $favicon) : null;
    }

    public static function getSiteEmail(): ?string
    {
        return self::get('site_email');
    }

    public static function getSitePhone(): ?string
    {
        return self::get('site_phone');
    }

    public static function getSiteAddress(): ?string
    {
        return self::get('site_address');
    }

    public static function getSocialLinks(): array
    {
        return [
            'facebook' => self::get('social_facebook'),
            'twitter' => self::get('social_twitter'),
            'instagram' => self::get('social_instagram'),
            'linkedin' => self::get('social_linkedin'),
        ];
    }

    public static function getMetaTitle(): string
    {
        return self::get('meta_title', self::getSiteName());
    }

    public static function getMetaDescription(): string
    {
        return self::get('meta_description', self::getSiteDescription());
    }

    public static function getMetaKeywords(): string
    {
        return self::get('meta_keywords', 'hotel, booking, travel, accommodation');
    }

    public static function getGoogleAnalytics(): ?string
    {
        return self::get('google_analytics');
    }

    public static function isMaintenanceMode(): bool
    {
        return self::get('maintenance_mode', '0') === '1';
    }

    public static function getMaintenanceMessage(): string
    {
        return self::get('maintenance_message', 'We are currently under maintenance. Please check back soon.');
    }

    public static function getAllSettings(): array
    {
        return [
            'site_name' => self::getSiteName(),
            'site_description' => self::getSiteDescription(),
            'site_logo' => self::getSiteLogo(),
            'site_favicon' => self::getSiteFavicon(),
            'site_email' => self::getSiteEmail(),
            'site_phone' => self::getSitePhone(),
            'site_address' => self::getSiteAddress(),
            'social_links' => self::getSocialLinks(),
            'meta_title' => self::getMetaTitle(),
            'meta_description' => self::getMetaDescription(),
            'meta_keywords' => self::getMetaKeywords(),
            'google_analytics' => self::getGoogleAnalytics(),
            'maintenance_mode' => self::isMaintenanceMode(),
            'maintenance_message' => self::getMaintenanceMessage(),
        ];
    }
}
