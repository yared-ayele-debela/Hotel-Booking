
@extends('admin.layouts.app')
@section('title')
    Dashboard
@endsection
@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <x-page-title
            title="Profile"
            :breadcrumbs="[
                ['label' => 'Admin', 'url' => route('admin.dashboard')],
                ['label' => 'Profile']
            ]"
        />
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <x-alert />

                        <section>
                            <header class="mb-4">
                                <h2 class="text-lg font-medium text-gray-900">
                                    Profile Information
                                </h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    Update your account's profile information and email address.
                                </p>
                            </header>

                            <!-- Verification Form (hidden submit) -->
                            <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
                                @csrf
                            </form>

                            <form method="POST" action="{{ route('profile.update') }}" class="mt-4">
                                @csrf
                                @method('patch')

                                <!-- Name -->
                                <div class="mb-3">
                                    <label class="form-label" for="name">Name</label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $user->name) }}"
                                           required
                                           autofocus>
                                    @error('name')
                                    <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $user->email) }}"
                                           required>
                                    @error('email')
                                    <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                    @enderror

                                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-800">
                                                Your email address is unverified.
                                                <button type="submit" form="send-verification" class="btn btn-link p-0 text-decoration-underline">
                                                    Click here to re-send the verification email.
                                                </button>
                                            </p>

                                            @if (session('status') === 'verification-link-sent')
                                                <p class="mt-2 text-success">
                                                    A new verification link has been sent to your email address.
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Save Button -->
                                <div class="d-flex align-items-center gap-3">
                                    <button type="submit" class="btn btn-primary">Save</button>

                                    @if (session('status') === 'profile-updated')
                                        <p class="text-muted mb-0" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)">
                                            Saved.
                                        </p>
                                    @endif
                                </div>
                            </form>
                        </section>
                        <div class="my-4"></div>
                        <section>
                            <header class="mb-4">
                                <h2 class="text-lg font-medium text-gray-900">
                                    Update Password
                                </h2>

                                <p class="mt-1 text-sm text-gray-600">
                                    Ensure your account is using a long, random password to stay secure.
                                </p>
                            </header>

                            <form method="POST" action="{{ route('password.update') }}" class="mt-4">
                                @csrf
                                @method('put')

                                <!-- Current Password -->
                                <div class="mb-3">
                                    <label class="form-label" for="update_password_current_password">Current Password</label>
                                    <input type="password"
                                           class="form-control @error('current_password') is-invalid @enderror"
                                           id="update_password_current_password"
                                           name="current_password"
                                           autocomplete="current-password"
                                           placeholder="Enter current password">
                                    @error('current_password')
                                    <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- New Password -->
                                <div class="mb-3">
                                    <label class="form-label" for="update_password_password">New Password</label>
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="update_password_password"
                                           name="password"
                                           autocomplete="new-password"
                                           placeholder="Enter new password">
                                    @error('password')
                                    <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-3">
                                    <label class="form-label" for="update_password_password_confirmation">Confirm Password</label>
                                    <input type="password"
                                           class="form-control @error('password_confirmation') is-invalid @enderror"
                                           id="update_password_password_confirmation"
                                           name="password_confirmation"
                                           autocomplete="new-password"
                                           placeholder="Confirm new password">
                                    @error('password_confirmation')
                                    <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Save Button -->
                                <div class="d-flex align-items-center gap-3">
                                    <button type="submit" class="btn btn-primary">Save</button>

                                    @if (session('status') === 'password-updated')
                                        <p class="text-muted mb-0"
                                           x-data="{ show: true }"
                                           x-show="show"
                                           x-transition
                                           x-init="setTimeout(() => show = false, 2000)">
                                            Saved.
                                        </p>
                                    @endif
                                </div>
                            </form>
                        </section>
                        <hr class="my-4">
                        <section class="mb-4">
                            <header class="mb-3">
                                <h2 class="text-lg font-medium text-gray-900">Delete Account</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    Once your account is deleted, all of its resources and data will be permanently deleted.
                                    Before deleting your account, please download any data or information that you wish to retain.
                                </p>
                            </header>

                            <!-- Delete Button -->
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
                                Delete Account
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('profile.destroy') }}" class="p-4">
                                            @csrf
                                            @method('delete')

                                            <div class="modal-header">
                                                <h5 class="modal-title" id="confirmUserDeletionLabel">Are you sure you want to delete your account?</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <p class="text-sm text-gray-600">
                                                    Once your account is deleted, all of its resources and data will be permanently deleted.
                                                    Please enter your password to confirm you would like to permanently delete your account.
                                                </p>

                                                <div class="mt-3">
                                                    <label class="form-label" for="password">Password</label>
                                                    <input type="password"
                                                           class="form-control @error('password') is-invalid @enderror"
                                                           id="password"
                                                           name="password"
                                                           placeholder="Password">

                                                    @error('password')
                                                    <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete Account</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </section>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
