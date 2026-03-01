{{-- Website Meta Tags Component --}}
@php
    use App\Services\WebsiteSettingsService;
    
    $siteName = WebsiteSettingsService::getSiteName();
    $metaTitle = WebsiteSettingsService::getMetaTitle();
    $metaDescription = WebsiteSettingsService::getMetaDescription();
    $metaKeywords = WebsiteSettingsService::getMetaKeywords();
    $favicon = WebsiteSettingsService::getSiteFavicon();
    $googleAnalytics = WebsiteSettingsService::getGoogleAnalytics();
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="keywords" content="{{ $metaKeywords }}">
<meta name="author" content="{{ $siteName }}">

@if($favicon)
    <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
@endif

@if($googleAnalytics)
    {!! $googleAnalytics !!}
@endif

<!-- Open Graph Meta Tags -->
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $siteName }}">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
