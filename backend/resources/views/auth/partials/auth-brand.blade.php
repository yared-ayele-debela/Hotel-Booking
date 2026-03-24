@php
    $authSiteName = \App\Services\WebsiteSettingsService::getSiteName();
    $authSiteLogo = \App\Services\WebsiteSettingsService::getSiteLogo();
    $authSiteTagline = \App\Services\WebsiteSettingsService::getSiteDescription();
@endphp
<div class="mb-4 mb-md-5 text-center">
    <a href="{{ url('/') }}" class="text-decoration-none d-flex flex-column align-items-center">
        @if($authSiteLogo)
            <img src="{{ $authSiteLogo }}" alt="{{ $authSiteName }}" class="mb-2" style="max-height: 52px; max-width: 220px; width: auto; height: auto; object-fit: contain;">
        @else
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary mb-2" style="width: 52px; height: 52px;">
                <i class="mdi mdi-office-building-outline fs-3"></i>
            </span>
        @endif
        <span class="text-dark fw-semibold fs-5">{{ $authSiteName }}</span>
        @if($authSiteTagline)
            <span class="text-muted small mt-2 px-1 lh-base" style="max-width: 280px;">{{ $authSiteTagline }}</span>
        @endif
    </a>
</div>
