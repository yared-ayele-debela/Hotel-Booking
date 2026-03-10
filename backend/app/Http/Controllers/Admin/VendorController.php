<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', Role::VENDOR)->with('vendorProfile');
        if ($request->filled('status')) {
            $status = $request->status;
            if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
                $query->whereHas('vendorProfile', fn ($q) => $q->where('status', $status));
            } elseif ($status === 'suspended') {
                $query->where('status', 'suspended');
            }
        }
        $vendors = $query->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.vendors.index', compact('vendors'));
    }

    public function show(User $vendor): View
    {
        if ($vendor->role !== Role::VENDOR) {
            abort(404);
        }
        $vendor->load('vendorProfile');
        return view('admin.vendors.show', compact('vendor'));
    }

    public function approve(User $vendor): RedirectResponse
    {
        if ($vendor->role !== Role::VENDOR) {
            abort(404);
        }
        $profile = $vendor->vendorProfile ?? VendorProfile::create([
            'user_id' => $vendor->id,
            'status' => VendorProfile::STATUS_PENDING,
        ]);
        $profile->update([
            'status' => VendorProfile::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejection_reason' => null,
        ]);
        $vendor->update(['status' => 'active']);
        return redirect()->back()->with('success', 'Vendor approved.');
    }

    public function reject(Request $request, User $vendor): RedirectResponse
    {
        if ($vendor->role !== Role::VENDOR) {
            abort(404);
        }
        $request->validate(['rejection_reason' => 'nullable|string|max:500']);
        $profile = $vendor->vendorProfile ?? VendorProfile::create([
            'user_id' => $vendor->id,
            'status' => VendorProfile::STATUS_PENDING,
        ]);
        $profile->update([
            'status' => VendorProfile::STATUS_REJECTED,
            'rejection_reason' => $request->rejection_reason,
            'approved_at' => null,
            'approved_by' => null,
        ]);
        return redirect()->back()->with('success', 'Vendor rejected.');
    }

    public function updateStatus(Request $request, User $vendor): RedirectResponse
    {
        if ($vendor->role !== Role::VENDOR) {
            abort(404);
        }
        $request->validate(['status' => 'required|in:active,suspended']);
        $vendor->update(['status' => $request->status]);
        $message = $request->status === 'active' ? 'Vendor activated.' : 'Vendor suspended.';
        return redirect()->back()->with('success', $message);
    }
}
