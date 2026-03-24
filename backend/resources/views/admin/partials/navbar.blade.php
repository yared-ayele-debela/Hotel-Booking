@php
    $siteName = \App\Services\WebsiteSettingsService::getSiteName();
    $siteLogo = \App\Services\WebsiteSettingsService::getSiteLogo();
    $defaultLogo = asset('admin/dist/assets/images/logo-sm.svg');
    $authUser = auth()->user();
    $authUser->loadMissing('vendorProfile');
    $vendorBusiness = $authUser->role === \App\Enums\Role::VENDOR
        ? ($authUser->vendorProfile?->business_name)
        : null;
@endphp
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ auth()->user()->role === \App\Enums\Role::VENDOR ? route('admin.vendor.dashboard') : route('admin.dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        @if($siteLogo)
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" height="24">
                        @else
                            <img src="{{ $defaultLogo }}" alt="" height="24">
                        @endif
                    </span>
                    <span class="logo-lg">
                        @if($siteLogo)
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" height="24">
                        @else
                            <img src="{{ $defaultLogo }}" alt="" height="24">
                        @endif
                        <span class="logo-txt">{{ $siteName }}</span>
                    </span>
                </a>

                <a href="{{ auth()->user()->role === \App\Enums\Role::VENDOR ? route('admin.vendor.dashboard') : route('admin.dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        @if($siteLogo)
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" height="24">
                        @else
                            <img src="{{ $defaultLogo }}" alt="" height="24">
                        @endif
                    </span>
                    <span class="logo-lg">
                        @if($siteLogo)
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" height="24">
                        @else
                            <img src="{{ $defaultLogo }}" alt="" height="24">
                        @endif
                        <span class="logo-txt">{{ $siteName }}</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

            <!-- App Search-->
{{--            <form class="app-search d-none d-lg-block">--}}
{{--                <div class="position-relative">--}}
{{--                    <input type="text" class="form-control" placeholder="Search...">--}}
{{--                    <button class="btn btn-primary" type="button"><i class="bx bx-search-alt align-middle"></i></button>--}}
{{--                </div>--}}
{{--            </form>--}}
        </div>

        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item" id="page-header-search-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="search" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                     aria-labelledby="page-header-search-dropdown">

                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search ..." aria-label="Search Result">

                                <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>



            <div class="dropdown d-none d-sm-inline-block">
                <button type="button" class="btn header-item" id="mode-setting-btn">
                    <i data-feather="moon" class="icon-lg layout-mode-dark"></i>
                    <i data-feather="sun" class="icon-lg layout-mode-light"></i>
                </button>
            </div>



            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon position-relative" id="page-header-notifications-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="bell" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                     aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-4 text-center text-muted small mb-0">No notifications yet.</div>
                </div>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-light-subtle border-start border-end" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="header-profile-user d-inline-block align-middle" style="line-height: 0;">
                        @include('admin.partials.user-avatar', ['user' => $authUser, 'size' => 32, 'class' => 'header-profile-user'])
                    </span>
                    <span class="d-none d-xl-inline-block ms-1 fw-medium text-start align-middle">
                        {{ $authUser->name }}
                        @if($vendorBusiness)
                            <span class="d-block small text-muted fw-normal text-truncate" style="max-width: 160px;">{{ $vendorBusiness }}</span>
                        @endif
                    </span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="mdi mdi-face-man font-size-16 align-middle me-1"></i> Profile</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="mdi mdi-logout font-size-16 align-middle me-1"></i> Logout</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>
