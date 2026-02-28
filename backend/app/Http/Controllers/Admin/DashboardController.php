<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected CommissionService $commissionService
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        if ($user->role === Role::VENDOR) {
            return redirect()->route('admin.vendor.dashboard');
        }
        $isSuperAdmin = $user->role === Role::SUPER_ADMIN;

        $kpis = [
            'total_hotels' => Hotel::count(),
            'total_bookings' => Booking::count(),
            'revenue' => 0.0,
            'commission' => 0.0,
        ];

        if ($isSuperAdmin) {
            $totals = $this->commissionService->platformTotals();
            $kpis['revenue'] = $totals['revenue'];
            $kpis['commission'] = $totals['commission'];
            $kpis['confirmed_bookings'] = $totals['booking_count'];
        }

        $vendors = $isSuperAdmin
            ? User::where('role', Role::VENDOR)->orderBy('name')->get()
            : [];

        $commissionRate = $isSuperAdmin ? $this->commissionService->getCommissionRate() : null;

        $revenueChart = $isSuperAdmin ? $this->revenueChartData() : [];

        return view('admin.dashboard', compact('kpis', 'vendors', 'commissionRate', 'revenueChart', 'isSuperAdmin'));
    }

    /**
     * Revenue per month for last 6 months (confirmed bookings).
     */
    protected function revenueChartData(): array
    {
        $rows = DB::table('bookings')
            ->where('bookings.status', 'confirmed')
            ->whereNull('bookings.deleted_at')
            ->where('bookings.check_in', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(bookings.check_in, '%Y-%m') as month, SUM(bookings.total_price) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $data[] = (float) ($rows[$month] ?? 0);
        }
        return ['labels' => $labels, 'data' => $data];
    }
}
