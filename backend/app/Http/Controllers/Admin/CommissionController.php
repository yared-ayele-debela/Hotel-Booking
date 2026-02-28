<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\CommissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function __construct(
        protected CommissionService $commissionService
    ) {}

    public function index(): View
    {
        $rate = $this->commissionService->getCommissionRate();
        $report = $this->commissionService->reportByVendor();
        return view('admin.commission.index', compact('rate', 'report'));
    }

    public function edit(): View
    {
        $rate = $this->commissionService->getCommissionRate();
        return view('admin.commission.edit', compact('rate'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);
        $rate = (float) $request->commission_rate / 100;
        PlatformSetting::set(CommissionService::COMMISSION_RATE_KEY, (string) $rate);
        return redirect()->route('admin.commission.index')->with('success', 'Commission rate updated.');
    }
}
