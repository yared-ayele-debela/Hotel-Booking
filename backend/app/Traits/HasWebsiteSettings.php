<?php

namespace App\Traits;

use App\Services\WebsiteSettingsService;

trait HasWebsiteSettings
{
    /**
     * Get website setting value
     */
    public function getWebsiteSetting(string $key, mixed $default = null): mixed
    {
        return WebsiteSettingsService::get($key, $default);
    }

    /**
     * Get all website settings
     */
    public function getAllWebsiteSettings(): array
    {
        return WebsiteSettingsService::getAllSettings();
    }

    /**
     * Get site name
     */
    public function getSiteName(): string
    {
        return WebsiteSettingsService::getSiteName();
    }

    /**
     * Get site logo URL
     */
    public function getSiteLogo(): ?string
    {
        return WebsiteSettingsService::getSiteLogo();
    }

    /**
     * Check if maintenance mode is enabled
     */
    public function isMaintenanceMode(): bool
    {
        return WebsiteSettingsService::isMaintenanceMode();
    }
}
