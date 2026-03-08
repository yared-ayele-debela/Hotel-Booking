<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'roles'    => 'nullable|array'
        ]);

        $isVendor = $request->roles && in_array('vendor', $request->roles, true);
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $isVendor ? RoleEnum::VENDOR : RoleEnum::CUSTOMER,
        ]);

        if ($request->roles) {
            $user->syncRoles($request->roles);
        }

        if ($isVendor) {
            VendorProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['status' => VendorProfile::STATUS_PENDING]
            );
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'nullable|array'
        ]);

        $isVendor = $request->roles && in_array('vendor', $request->roles ?? [], true);
        $wasVendor = $user->role === RoleEnum::VENDOR;

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $isVendor ? RoleEnum::VENDOR : RoleEnum::CUSTOMER,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles($request->roles ?? []);

        if ($isVendor && ! $wasVendor) {
            VendorProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['status' => VendorProfile::STATUS_PENDING]
            );
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Protect super admin
        if ($user->hasRole('super-admin')) {
            return back()->with('error', 'Super Admin cannot be deleted.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
