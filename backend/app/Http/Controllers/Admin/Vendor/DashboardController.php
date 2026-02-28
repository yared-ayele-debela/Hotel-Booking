<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\CommissionService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        protected CommissionService $commissionService
    ) {}

    public function index()
    {
        $userId = auth()->id();
        $hotelIds = Hotel::where('vendor_id', $userId)->pluck('id');

        $revenue = (float) Booking::whereIn('hotel_id', $hotelIds)
            ->where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->sum('total_price');
        $bookingCount = Booking::whereIn('hotel_id', $hotelIds)->whereNull('deleted_at')->count();
        $rate = $this->commissionService->getCommissionRate();
        $commissionAmount = round($revenue * $rate, 2);
        $net = $revenue - $commissionAmount;

        $revenueChart = $this->revenueChartData($hotelIds);

        return view('admin.vendor.dashboard', compact(
            'revenue', 'bookingCount', 'commissionAmount', 'net', 'revenueChart'
        ));
    }

    protected function revenueChartData($hotelIds): array
    {
        $rows = DB::table('bookings')
            ->whereIn('hotel_id', $hotelIds)
            ->where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->where('check_in', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(check_in, '%Y-%m') as month, SUM(total_price) as total")
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
