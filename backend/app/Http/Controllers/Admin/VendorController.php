<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(): View
    {
        $vendors = User::where('role', Role::VENDOR)->orderBy('name')->paginate(15);
        return view('admin.vendors.index', compact('vendors'));
    }

    public function updateStatus(Request $request, User $vendor): RedirectResponse
    {
        if ($vendor->role !== Role::VENDOR) {
            abort(404);
        }
        $request->validate(['status' => 'required|in:active,suspended']);
        $vendor->update(['status' => $request->status]);
        $message = $request->status === 'active' ? 'Vendor approved.' : 'Vendor suspended.';
        return redirect()->back()->with('success', $message);
    }
}
