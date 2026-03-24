@php
    $authPageTitle = 'Login';
    $authHotelBackground = asset('images/auth/login-bg.jpg');
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
                                <div class="mb-4 mb-md-5 text-center">
                                    <a href="{{ url('/') }}" class="d-block auth-logo">
                                        @include('auth.partials.auth-brand')
                                    </a>
                                </div>
                                <div class="auth-content my-auto">
                                    <div class="text-center">
                                        <h5 class="mb-0">Welcome back</h5>
                                        <p class="text-muted mt-2">Sign in to continue to the admin panel.</p>
                                    </div>
                                    <form class="mt-4 pt-2" method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <!-- Username / Email -->
                                        <div class="mb-3">
                                            <label class="form-label" for="email">Email</label>
                                            <input type="email"
                                                   class="form-control @error('email') is-invalid @enderror"
                                                   id="email"
                                                   name="email"
                                                   placeholder="Enter email"
                                                   value="{{ old('email') }}"
                                                   required
                                                   autofocus>
                                            @error('email')
                                            <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Password -->
                                        <div class="mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <label class="form-label" for="password">Password</label>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    @if(Route::has('password.request'))
                                                        <a href="{{ route('password.request') }}" class="text-muted">Forgot password?</a>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="input-group auth-pass-inputgroup">
                                                <input type="password"
                                                       class="form-control @error('password') is-invalid @enderror"
                                                       id="password"
                                                       name="password"
                                                       placeholder="Enter password"
                                                       aria-label="Password"
                                                       aria-describedby="password-addon"
                                                       required>
                                                <button class="btn btn-light shadow-none ms-0" type="button" id="password-addon">
                                                    <i class="mdi mdi-eye-outline"></i>
                                                </button>
                                            </div>
                                            @error('password')
                                            <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Remember Me -->
                                        <div class="row mb-4">
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="remember-check" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="remember-check">
                                                        Remember me
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="mb-3">
                                            <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Log In</button>
                                        </div>
                                    </form>




                                    <div class="mt-5 text-center">
                                        <p class="text-muted mb-0">Don't have an account? <a href="{{ route('register') }}" class="text-primary fw-semibold">Sign up as customer</a></p>
                                        <p class="text-muted mb-0 mt-1">List your property? <a href="{{ route('register.vendor') }}" class="text-primary fw-semibold">Register as vendor</a></p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end auth full page content -->
                </div>
                <!-- end col -->
                <div class="col-xxl-9 col-lg-8 col-md-7">
                    <div class="auth-bg auth-bg--hotel-booking position-relative pt-md-5 p-4 d-flex">
                        <div class="bg-overlay bg-primary"></div>
                        <ul class="bg-bubbles">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>
                        <!-- end bubble effect -->
                    </div>
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container fluid -->
    </div>
@include('admin.layouts.javascript')

