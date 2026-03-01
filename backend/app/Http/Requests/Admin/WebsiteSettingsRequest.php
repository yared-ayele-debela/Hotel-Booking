<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class WebsiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:1000',
            'site_email' => 'nullable|email|max:255',
            'site_phone' => 'nullable|string|max:50',
            'site_address' => 'nullable|string|max:500',
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_linkedin' => 'nullable|url|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'google_analytics' => 'nullable|string|max:500',
            'maintenance_mode' => 'required|boolean',
            'maintenance_message' => 'nullable|string|max:1000',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_favicon' => 'nullable|image|mimes:ico,png,jpg,jpeg|max:1024',
        ];
    }

    public function messages(): array
    {
        return [
            'site_name.required' => 'The site name field is required.',
            'site_name.max' => 'The site name may not be greater than 255 characters.',
            'site_description.max' => 'The site description may not be greater than 1000 characters.',
            'site_email.email' => 'Please enter a valid email address.',
            'site_email.max' => 'The email may not be greater than 255 characters.',
            'site_phone.max' => 'The phone number may not be greater than 50 characters.',
            'site_address.max' => 'The address may not be greater than 500 characters.',
            'social_facebook.url' => 'Please enter a valid URL for Facebook.',
            'social_twitter.url' => 'Please enter a valid URL for Twitter.',
            'social_instagram.url' => 'Please enter a valid URL for Instagram.',
            'social_linkedin.url' => 'Please enter a valid URL for LinkedIn.',
            'meta_title.max' => 'The meta title may not be greater than 255 characters.',
            'meta_description.max' => 'The meta description may not be greater than 500 characters.',
            'meta_keywords.max' => 'The meta keywords may not be greater than 500 characters.',
            'google_analytics.max' => 'The Google Analytics code may not be greater than 500 characters.',
            'maintenance_mode.required' => 'The maintenance mode field is required.',
            'maintenance_mode.boolean' => 'The maintenance mode must be true or false.',
            'maintenance_message.max' => 'The maintenance message may not be greater than 1000 characters.',
            'site_logo.image' => 'The logo must be an image file.',
            'site_logo.mimes' => 'The logo must be a file of type: jpeg, png, jpg, gif, svg.',
            'site_logo.max' => 'The logo may not be greater than 2MB.',
            'site_favicon.image' => 'The favicon must be an image file.',
            'site_favicon.mimes' => 'The favicon must be a file of type: ico, png, jpg, jpeg.',
            'site_favicon.max' => 'The favicon may not be greater than 1MB.',
        ];
    }
}
