<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\CommissionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            ? User::where('role', Role::VENDOR)->with('vendorProfile')->orderBy('name')->get()
            : [];

        $commissionRate = $isSuperAdmin ? $this->commissionService->getCommissionRate() : null;

        $revenueChart = $isSuperAdmin ? $this->revenueChartData() : [];
        $bookingsByStatus = $this->bookingsByStatusData();
        $bookingsTrendChart = $this->bookingsTrendChartData();
        $topVendorsChart = $isSuperAdmin ? $this->topVendorsByRevenueData() : [];
        $recentVendorDocuments = $isSuperAdmin ? $this->recentVendorBusinessDocuments(8) : [];

        return view('admin.dashboard', compact('kpis', 'vendors', 'commissionRate', 'revenueChart', 'bookingsByStatus', 'bookingsTrendChart', 'topVendorsChart', 'isSuperAdmin', 'recentVendorDocuments'));
    }

    /**
     * Latest business document uploads across vendors (for admin dashboard).
     *
     * @return array<int, array{vendor_id: int, vendor_name: string, file_name: string, document_id: string, uploaded_at: ?Carbon}>
     */
    protected function recentVendorBusinessDocuments(int $limit = 8): array
    {
        $rows = [];
        foreach (VendorProfile::query()->with('user')->get() as $profile) {
            foreach ($profile->documents ?? [] as $doc) {
                if (! is_array($doc) || empty($doc['id']) || empty($doc['path'])) {
                    continue;
                }
                $uploaded = null;
                if (! empty($doc['uploaded_at'])) {
                    try {
                        $uploaded = Carbon::parse($doc['uploaded_at']);
                    } catch (\Throwable) {
                        $uploaded = null;
                    }
                }
                $rows[] = [
                    'vendor_id' => $profile->user_id,
                    'vendor_name' => $profile->user?->name ?? '—',
                    'file_name' => $doc['original_name'] ?? basename($doc['path']),
                    'document_id' => $doc['id'],
                    'uploaded_at' => $uploaded,
                ];
            }
        }
        usort($rows, function ($a, $b) {
            $ta = $a['uploaded_at'] instanceof Carbon ? $a['uploaded_at']->timestamp : 0;
            $tb = $b['uploaded_at'] instanceof Carbon ? $b['uploaded_at']->timestamp : 0;

            return $tb <=> $ta;
        });

        return array_slice($rows, 0, $limit);
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

    /**
     * Bookings count by status (pending, confirmed, cancelled, completed).
     */
    protected function bookingsByStatusData(): array
    {
        $rows = DB::table('bookings')
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

    /**
     * Bookings count per month for last 6 months.
     */
    protected function bookingsTrendChartData(): array
    {
        $rows = DB::table('bookings')
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

    /**
     * Top 5 vendors by revenue (confirmed bookings).
     */
    protected function topVendorsByRevenueData(): array
    {
        $rows = DB::table('bookings')
            ->join('hotels', 'bookings.hotel_id', '=', 'hotels.id')
            ->join('users', 'hotels.vendor_id', '=', 'users.id')
            ->where('bookings.status', 'confirmed')
            ->whereNull('bookings.deleted_at')
            ->selectRaw('users.name as vendor_name, SUM(bookings.total_price) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => Str::limit($r->vendor_name, 18))->values()->all(),
            'data' => $rows->map(fn ($r) => (float) $r->total)->values()->all(),
        ];
    }
}
