<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\CommissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $bookingsByStatus = $this->bookingsByStatusData($hotelIds);
        $bookingsTrendChart = $this->bookingsTrendChartData($hotelIds);
        $topHotelsChart = $this->topHotelsByRevenueData($hotelIds);
        $vendorApproved = auth()->user()->isVendorApproved();

        return view('admin.vendor.dashboard', compact(
            'revenue', 'bookingCount', 'commissionAmount', 'net', 'revenueChart', 'bookingsByStatus', 'bookingsTrendChart', 'topHotelsChart', 'vendorApproved'
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

    protected function bookingsByStatusData($hotelIds): array
    {
        $rows = DB::table('bookings')
            ->whereIn('hotel_id', $hotelIds)
            ->whereNull('deleted_at')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $labels = ['Pending', 'Confirmed', 'Cancelled', 'Completed'];
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        $data = [];
        foreach ($statuses as $s) {
            $data[] = (int) ($rows[$s] ?? 0);
        }
        return ['labels' => $labels, 'data' => $data];
    }

    protected function bookingsTrendChartData($hotelIds): array
    {
        $rows = DB::table('bookings')
            ->whereIn('hotel_id', $hotelIds)
            ->whereNull('deleted_at')
            ->where('check_in', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(check_in, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $data[] = (int) ($rows[$month] ?? 0);
        }
        return ['labels' => $labels, 'data' => $data];
    }

    protected function topHotelsByRevenueData($hotelIds): array
    {
        $rows = DB::table('bookings')
            ->join('hotels', 'bookings.hotel_id', '=', 'hotels.id')
            ->whereIn('bookings.hotel_id', $hotelIds)
            ->where('bookings.status', 'confirmed')
            ->whereNull('bookings.deleted_at')
            ->selectRaw('hotels.name as hotel_name, SUM(bookings.total_price) as total')
            ->groupBy('hotels.id', 'hotels.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => Str::limit($r->hotel_name, 20))->values()->all(),
            'data' => $rows->map(fn ($r) => (float) $r->total)->values()->all(),
        ];
    }
}
