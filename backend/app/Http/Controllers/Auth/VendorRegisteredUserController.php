<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class VendorRegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register-vendor');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_details' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => Role::VENDOR,
            'status' => 'active',
        ]);

        $user->assignRole('vendor');

        VendorProfile::create([
            'user_id' => $user->id,
            'status' => VendorProfile::STATUS_PENDING,
            'business_name' => $validated['business_name'] ?? null,
            'business_details' => $validated['business_details'] ?? null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('admin.vendor.dashboard')
            ->with('success', 'Registration successful. Your account is pending approval. You will be able to add hotels once approved by our team.');
    }
}
