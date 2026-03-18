<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Services\CommissionService;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function __construct(
        protected CommissionService $commissionService
    ) {}

    public function index(): View
    {
        $userId = auth()->id();
        $report = $this->commissionService->reportByVendor();
        $vendorRow = collect($report)->firstWhere('vendor_id', (string) $userId);
        $totals = $vendorRow ? [
            'booking_count' => (int) $vendorRow['booking_count'],
            'gross' => (float) $vendorRow['gross'],
            'commission' => (float) $vendorRow['commission'],
            'net' => (float) $vendorRow['net'],
        ] : ['booking_count' => 0, 'gross' => 0, 'commission' => 0, 'net' => 0];

        $payouts = auth()->user()->payouts()->latest()->paginate(20);

        return view('admin.vendor.payouts.index', compact('totals', 'payouts'));
    }
}
