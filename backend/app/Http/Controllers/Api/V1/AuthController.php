<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login: email + password. Returns Sanctum token and user (customer only).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages(['email' => ['Account is not active.']]);
        }

        if ($user->role !== Role::CUSTOMER) {
            throw ValidationException::withMessages(['email' => ['Only customer accounts can sign in here.']]);
        }

        $user->tokens()->where('name', 'spa')->delete();
        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->userApiPayload($user),
            ],
        ]);
    }

    /**
     * Register as vendor: name, email, password, optional business info. Creates VENDOR + VendorProfile (pending).
     * Returns token + user. Vendor cannot add hotels until approved.
     */
    public function registerVendor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'business_name' => 'nullable|string|max:255',
            'business_details' => 'nullable|string|max:2000',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => Role::VENDOR,
            'status' => 'active',
        ]);

        $user->assignRole('vendor');

        \App\Models\VendorProfile::create([
            'user_id' => $user->id,
            'status' => \App\Models\VendorProfile::STATUS_PENDING,
            'business_name' => $validated['business_name'] ?? null,
            'business_details' => $validated['business_details'] ?? null,
        ]);

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->userApiPayload($user),
            ],
        ], 201);
    }

    /**
     * Register: name, email, password. Creates CUSTOMER + active, returns token + user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => Role::CUSTOMER,
            'status' => 'active',
        ]);

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->userApiPayload($user),
            ],
        ], 201);
    }

    /**
     * Logout: revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Current user (auth:sanctum).
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->userApiPayload($request->user())]);
    }

    /**
     * Update current user profile (name, email, optional password, optional avatar).
     * Send JSON as usual, or multipart/form-data to upload/remove a profile photo.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules);

        if ($request->boolean('remove_avatar') && $user->avatar) {
            if (Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->name = $validated['name'] ?? $user->name;
        $user->email = $validated['email'] ?? $user->email;
        if (! empty($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return response()->json(['success' => true, 'data' => $this->userApiPayload($user->fresh())]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function userApiPayload(User $user): array
    {
        $data = [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'avatar_url' => $user->avatarUrl(),
        ];
        if ($user->role === Role::VENDOR) {
            $data['vendor_approved'] = $user->isVendorApproved();
        }

        return $data;
    }
}
