# Website Settings Feature

This document explains how to use and extend the website settings feature in the Hotel Booking Platform.

## Overview

The website settings feature allows super administrators to customize various aspects of the website including:

- **General Settings**: Site name, description, contact information
- **Brand Assets**: Logo and favicon upload/management
- **Social Media**: Links to social media profiles
- **SEO Settings**: Meta tags, keywords, Google Analytics
- **Maintenance Mode**: Enable/disable maintenance mode with custom message

## Access

Website settings can only be accessed by users with **SUPER_ADMIN** role:
1. Login to admin panel
2. Navigate to "Website Settings" in the sidebar menu
3. URL: `/admin/website-settings`

## Available Settings

### General Settings
- **Site Name**: The main name of your website (required)
- **Site Description**: Brief description of your website
- **Contact Email**: Primary contact email address
- **Contact Phone**: Contact phone number
- **Address**: Physical address

### Brand Assets
- **Site Logo**: Upload a logo image (JPEG, PNG, JPG, GIF, SVG - max 2MB)
- **Favicon**: Upload a favicon (ICO, PNG, JPG, JPEG - max 1MB)

### Social Media Links
- **Facebook URL**: Link to Facebook page
- **Twitter URL**: Link to Twitter profile
- **Instagram URL**: Link to Instagram profile
- **LinkedIn URL**: Link to LinkedIn page

### SEO Settings
- **Meta Title**: Default title for SEO
- **Meta Description**: Default description for SEO
- **Meta Keywords**: Keywords for SEO (comma-separated)
- **Google Analytics**: Google Analytics tracking code

### Maintenance Mode
- **Enable Maintenance Mode**: Toggle to enable/disable
- **Maintenance Message**: Custom message shown during maintenance

## Usage in Code

### Using the Service Class

```php
use App\Services\WebsiteSettingsService;

// Get individual settings
$siteName = WebsiteSettingsService::getSiteName();
$siteLogo = WebsiteSettingsService::getSiteLogo();
$isMaintenance = WebsiteSettingsService::isMaintenanceMode();

// Get any setting by key
$email = WebsiteSettingsService::get('site_email', 'default@example.com');

// Get all settings as array
$allSettings = WebsiteSettingsService::getAllSettings();
```

### Using the Trait

```php
use App\Traits\HasWebsiteSettings;

class YourClass
{
    use HasWebsiteSettings;
    
    public function someMethod()
    {
        $siteName = $this->getSiteName();
        $isMaintenance = $this->isMaintenanceMode();
    }
}
```

### Using Blade Components

```blade
<!-- Include meta tags in your layout head -->
<x-website.meta />

<!-- Get individual settings in views -->
{{ WebsiteSettingsService::getSiteName() }}

<!-- Display logo if exists -->
@if(WebsiteSettingsService::getSiteLogo())
    <img src="{{ WebsiteSettingsService::getSiteLogo() }}" alt="Logo">
@endif
```

## File Storage

Uploaded files (logo, favicon) are stored in:
- **Storage Path**: `storage/app/public/website/`
- **Public URL**: `public/storage/website/`

Make sure to run: `php artisan storage:link` to make files publicly accessible.

## Maintenance Mode

When maintenance mode is enabled:
- All non-admin routes will show the maintenance page
- Admin panel remains accessible
- API requests return JSON with maintenance status
- AJAX requests receive proper JSON responses

### Customizing Maintenance Page

The maintenance view is located at: `resources/views/maintenance.blade.php`

You can customize the styling and content as needed.

## Database Schema

Settings are stored in the `platform_settings` table:
- `key` (string, primary): Setting identifier
- `value` (text, nullable): Setting value
- `timestamps`: Created/updated timestamps

## Adding New Settings

### 1. Add to Controller

In `WebsiteSettingsController@index`, add your new setting:

```php
$new_setting => PlatformSetting::get('new_setting', 'default_value'),
```

### 2. Add Validation

In `WebsiteSettingsRequest`, add validation rules:

```php
'new_setting' => 'required|string|max:255',
```

### 3. Add to View

In `index.blade.php`, add the form field:

```blade
<div class="mb-3">
    <label for="new_setting" class="form-label">New Setting</label>
    <input type="text" class="form-control" id="new_setting" name="new_setting" value="{{ $settings['new_setting'] }}">
</div>
```

### 4. Add Service Method (Optional)

In `WebsiteSettingsService`, add a helper method:

```php
public static function getNewSetting(): string
{
    return self::get('new_setting', 'default_value');
}
```

## Security Considerations

- Only SUPER_ADMIN users can access website settings
- File uploads are validated for type and size
- Old files are automatically deleted when new ones are uploaded
- All input is properly validated and sanitized

## Performance

Settings are cached by Laravel's query cache when accessed via the `PlatformSetting` model. For high-traffic applications, consider implementing additional caching layers in the service class.

## Troubleshooting

### Files Not Displaying
- Run `php artisan storage:link`
- Check file permissions in `storage/app/public/website/`
- Verify the `APP_URL` in your `.env` file

### Maintenance Mode Not Working
- Ensure the `CheckMaintenanceMode` middleware is registered
- Check that the middleware is applied to your routes
- Verify the setting is saved correctly in the database

### Settings Not Saving
- Check that you have SUPER_ADMIN role
- Verify form validation is passing
- Check database connection and permissions
