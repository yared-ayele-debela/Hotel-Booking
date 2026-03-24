@php
    $authPageTitle = 'Vendor registration';
    $authHotelBackground = asset('images/auth/vendor-bg.png');
@endphp
@include('admin.layouts.css')
@include('auth.partials.auth-hotel-bg-styles')
<div class="auth-page">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-xxl-3 col-lg-4 col-md-5">
                <div class="auth-full-page-content d-flex p-sm-5 p-4">
                    <div class="w-100">
                        <div class="d-flex flex-column h-100">
                            @include('auth.partials.auth-brand')
                            <div class="auth-content my-auto">
                                <div class="text-center">
                                    <h5 class="mb-0">Register as vendor</h5>
                                    <p class="text-muted mt-2">Apply to list your properties and manage bookings.</p>
                                </div>
                                <form class="mt-4 pt-2" method="POST" action="{{ route('register.vendor') }}">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label" for="name">Name *</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                               placeholder="Your full name" value="{{ old('name') }}" required autofocus>
                                        @error('name')<span class="text-danger mt-1 d-block">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="email">Email *</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                                               placeholder="Enter your email" value="{{ old('email') }}" required>
                                        @error('email')<span class="text-danger mt-1 d-block">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="password">Password *</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                                                   name="password" placeholder="Enter password" required>
                                            <button class="btn btn-light shadow-none ms-0" type="button" id="password-addon">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </button>
                                        </div>
                                        @error('password')<span class="text-danger mt-1 d-block">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="password_confirmation">Confirm Password *</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                                                   placeholder="Confirm password" required>
                                            <button class="btn btn-light shadow-none ms-0" type="button" id="password-confirm-addon">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="business_name">Business name</label>
                                        <input type="text" class="form-control" id="business_name" name="business_name"
                                               placeholder="Your hotel or business name" value="{{ old('business_name') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="business_details">Business details</label>
                                        <textarea class="form-control" id="business_details" name="business_details" rows="3"
                                                  placeholder="Address, phone, website, or other info">{{ old('business_details') }}</textarea>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <a class="text-muted" href="{{ route('login') }}">Already registered?</a>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Apply</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-9 col-lg-8 col-md-7">
                <div class="auth-bg auth-bg--hotel-booking position-relative pt-md-5 p-4 d-flex">
                    <div class="bg-overlay bg-primary"></div>
                    <ul class="bg-bubbles">
                        @for($i = 0; $i < 10; $i++)<li></li>@endfor
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.layouts.javascript')
