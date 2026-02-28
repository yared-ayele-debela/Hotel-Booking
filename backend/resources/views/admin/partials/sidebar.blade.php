<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" data-key="t-menu">Menu</li>

                @if(auth()->user()->role === \App\Enums\Role::VENDOR)
                {{-- Vendor menu --}}
                <li>
                    <a href="{{ route('admin.vendor.dashboard') }}">
                        <i data-feather="home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.vendor.hotels.index') }}">
                        <i data-feather="building"></i>
                        <span>Hotels</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.vendor.rooms.index') }}">
                        <i data-feather="grid"></i>
                        <span>Rooms</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.vendor.bookings.index') }}">
                        <i data-feather="calendar"></i>
                        <span>Bookings</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.vendor.payouts.index') }}">
                        <i data-feather="dollar-sign"></i>
                        <span>Payouts</span>
                    </a>
                </li>
                @else
                {{-- Admin / Super Admin menu --}}
                <li>
                    <a href="{{ route('admin.dashboard') }}">
                        <i data-feather="home"></i>
                        <span data-key="t-dashboard">Dashboard</span>
                    </a>
                </li>

                @if(auth()->user()->role === \App\Enums\Role::SUPER_ADMIN)
                <li>
                    <a href="{{ route('admin.vendors.index') }}">
                        <i data-feather="users"></i>
                        <span>Vendors</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.commission.index') }}">
                        <i data-feather="percent"></i>
                        <span>Commission</span>
                    </a>
                </li>
                @endif

                <li>
                    <a href="{{ route('admin.disputes.index') }}">
                        <i data-feather="alert-circle"></i>
                        <span>Disputes</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reviews.index') }}">
                        <i data-feather="star"></i>
                        <span>Review Moderation</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.support-tickets.index') }}">
                        <i data-feather="message-circle"></i>
                        <span>Support Tickets</span>
                    </a>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="grid"></i>
                        <span data-key="t-apps">Access Control</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('admin.roles.index') }}"><span data-key="t-roles">Roles</span></a></li>
                        <li><a href="{{ route('admin.permissions.index') }}"><span data-key="t-permissions">Permissions</span></a></li>
                    </ul>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="users"></i>
                        <span data-key="t-forms">Users</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('admin.users.index') }}" data-key="t-form-elements">Users</a></li>
                    </ul>
                </li>
                @endif

{{--                <li>--}}
{{--                    <a href="javascript: void(0);" class="has-arrow">--}}
{{--                        <i data-feather="users"></i>--}}
{{--                        <span data-key="t-authentication">Authentication</span>--}}
{{--                    </a>--}}
{{--                    <ul class="sub-menu" aria-expanded="false">--}}
{{--                        <li><a href="auth-login.html" data-key="t-login">Login</a></li>--}}
{{--                        <li><a href="auth-register.html" data-key="t-register">Register</a></li>--}}
{{--                        <li><a href="auth-recoverpw.html" data-key="t-recover-password">Recover Password</a></li>--}}
{{--                        <li><a href="auth-lock-screen.html" data-key="t-lock-screen">Lock Screen</a></li>--}}
{{--                        <li><a href="auth-logout.html" data-key="t-logout">Log Out</a></li>--}}
{{--                        <li><a href="auth-confirm-mail.html" data-key="t-confirm-mail">Confirm Mail</a></li>--}}
{{--                        <li><a href="auth-email-verification.html" data-key="t-email-verification">Email Verification</a></li>--}}
{{--                        <li><a href="auth-two-step-verification.html" data-key="t-two-step-verification">Two Step Verification</a></li>--}}
{{--                    </ul>--}}
{{--                </li>--}}


            </ul>


        </div>
        <!-- Sidebar -->
    </div>
</div>
