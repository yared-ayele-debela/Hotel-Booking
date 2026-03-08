<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class VendorReportService
{

    /**
     * Occupancy % for vendor's hotels by period.
     * Occupancy = booked room-nights / available room-nights * 100.
     *
     * @param  array<int>  $hotelIds
     * @return array{occupancy: float, booked_nights: int, available_nights: int, period_label: string}
     */
    public function occupancyByPeriod(array $hotelIds, string $period = 'month'): array
    {
        if (empty($hotelIds)) {
            return ['occupancy' => 0.0, 'booked_nights' => 0, 'available_nights' => 0, 'period_label' => ''];
        }

        [$from, $to, $label] = $this->periodRange($period);
        $days = max(1, $from->diffInDays($to));

        $roomIds = Room::whereIn('hotel_id', $hotelIds)->pluck('id');
        $availableNights = 0;
        if ($roomIds->isNotEmpty()) {
            $availabilityRows = DB::table('room_availability')
                ->whereIn('room_id', $roomIds)
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('room_id, SUM(available_rooms) as total')
                ->groupBy('room_id')
                ->pluck('total', 'room_id');

            $rooms = Room::whereIn('id', $roomIds)->get(['id', 'total_rooms']);
            foreach ($rooms as $room) {
                $availableNights += (int) ($availabilityRows[$room->id] ?? $room->total_rooms * $days);
            }
        }

        $bookedNights = (int) DB::table('booking_rooms')
            ->join('bookings', 'booking_rooms.booking_id', '=', 'bookings.id')
            ->whereIn('bookings.hotel_id', $hotelIds)
            ->where('bookings.status', 'confirmed')
            ->whereNull('bookings.deleted_at')
            ->where('bookings.check_in', '<', $to->copy()->addDay()->toDateString())
            ->where('bookings.check_out', '>', $from->toDateString())
            ->selectRaw('SUM(
                booking_rooms.quantity * (
                    DATEDIFF(
                        LEAST(bookings.check_out, ?),
                        GREATEST(bookings.check_in, ?)
                    )
                )
            ) as total', [$to->toDateString(), $from->toDateString()])
            ->value('total');

        $occupancy = $availableNights > 0 ? round(($bookedNights / $availableNights) * 100, 1) : 0.0;

        return [
            'occupancy' => $occupancy,
            'booked_nights' => $bookedNights,
            'available_nights' => $availableNights,
            'period_label' => $label,
        ];
    }

    /**
     * Revenue by room type for vendor's hotels.
     *
     * @param  array<int>  $hotelIds
     * @return array<array{room_name: string, room_id: int, revenue: float, bookings: int}>
     */
    public function revenueByRoomType(array $hotelIds, ?string $from = null, ?string $to = null): array
    {
        if (empty($hotelIds)) {
            return [];
        }

        $query = DB::table('booking_rooms')
            ->join('bookings', 'booking_rooms.booking_id', '=', 'bookings.id')
            ->join('rooms', 'booking_rooms.room_id', '=', 'rooms.id')
            ->whereIn('bookings.hotel_id', $hotelIds)
            ->where('bookings.status', 'confirmed')
            ->whereNull('bookings.deleted_at');

        if ($from) {
            $query->whereDate('bookings.check_in', '>=', $from);
        }
        if ($to) {
            $query->whereDate('bookings.check_out', '<=', $to);
        }

        return $query
            ->selectRaw('rooms.id as room_id, rooms.name as room_name, SUM(booking_rooms.quantity * booking_rooms.unit_price) as revenue, COUNT(DISTINCT bookings.id) as bookings')
            ->groupBy('rooms.id', 'rooms.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($r) => [
                'room_id' => (int) $r->room_id,
                'room_name' => $r->room_name,
                'revenue' => round((float) $r->revenue, 2),
                'bookings' => (int) $r->bookings,
            ])
            ->values()
            ->all();
    }

    /**
     * Current vs previous period comparison (revenue, bookings).
     *
     * @param  array<int>  $hotelIds
     * @return array{current: array{revenue: float, bookings: int}, previous: array{revenue: float, bookings: int}, revenue_change_pct: float, bookings_change_pct: float}
     */
    public function periodComparison(array $hotelIds, string $period = 'month'): array
    {
        if (empty($hotelIds)) {
            return [
                'current' => ['revenue' => 0.0, 'bookings' => 0],
                'previous' => ['revenue' => 0.0, 'bookings' => 0],
                'revenue_change_pct' => 0.0,
                'bookings_change_pct' => 0.0,
            ];
        }

        [$currentFrom, $currentTo] = $this->periodRange($period);
        $prevTo = $currentFrom->copy()->subDay();
        $prevFrom = $prevTo->copy()->subDays($currentFrom->diffInDays($currentTo));

        $current = $this->revenueAndBookingsForRange($hotelIds, $currentFrom->toDateString(), $currentTo->toDateString());
        $previous = $this->revenueAndBookingsForRange($hotelIds, $prevFrom->toDateString(), $prevTo->toDateString());

        $revenueChange = $previous['revenue'] > 0
            ? round((($current['revenue'] - $previous['revenue']) / $previous['revenue']) * 100, 1)
            : ($current['revenue'] > 0 ? 100.0 : 0.0);
        $bookingsChange = $previous['bookings'] > 0
            ? round((($current['bookings'] - $previous['bookings']) / $previous['bookings']) * 100, 1)
            : ($current['bookings'] > 0 ? 100.0 : 0.0);

        return [
            'current' => $current,
            'previous' => $previous,
            'revenue_change_pct' => $revenueChange,
            'bookings_change_pct' => $bookingsChange,
        ];
    }

    /**
     * Revenue chart data for last N months (for dashboard).
     *
     * @param  array<int>  $hotelIds
     * @return array{labels: array<string>, data: array<float>}
     */
    public function revenueChartData(array $hotelIds, int $months = 6): array
    {
        $rows = DB::table('bookings')
            ->whereIn('hotel_id', $hotelIds)
            ->where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->where('check_in', '>=', now()->subMonths($months)->startOfMonth())
            ->selectRaw("DATE_FORMAT(check_in, '%Y-%m') as month, SUM(total_price) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $data[] = (float) ($rows[$month] ?? 0);
        }
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Export report as CSV rows (for download).
     *
     * @param  array<int>  $hotelIds
     * @return array<array<string>>
     */
    public function exportCsv(array $hotelIds, ?string $from = null, ?string $to = null): array
    {
        $rows = [];
        $rows[] = ['Report', 'Vendor Reports', 'Generated', now()->toDateTimeString()];
        $rows[] = [];

        $revenueByRoom = $this->revenueByRoomType($hotelIds, $from, $to);
        $rows[] = ['Revenue by Room Type', '', '', ''];
        $rows[] = ['Room', 'Revenue ($)', 'Bookings', ''];
        foreach ($revenueByRoom as $r) {
            $rows[] = [$r['room_name'], $r['revenue'], $r['bookings'], ''];
        }
        $rows[] = [];

        $comparison = $this->periodComparison($hotelIds, 'month');
        $rows[] = ['Period Comparison (month)', '', '', ''];
        $rows[] = ['Metric', 'Current', 'Previous', 'Change %'];
        $rows[] = ['Revenue ($)', $comparison['current']['revenue'], $comparison['previous']['revenue'], $comparison['revenue_change_pct'] . '%'];
        $rows[] = ['Bookings', $comparison['current']['bookings'], $comparison['previous']['bookings'], $comparison['bookings_change_pct'] . '%'];
        $rows[] = [];

        foreach (['day', 'week', 'month'] as $p) {
            $occ = $this->occupancyByPeriod($hotelIds, $p);
            $rows[] = ['Occupancy (' . $occ['period_label'] . ')', $occ['occupancy'] . '%', 'Booked: ' . $occ['booked_nights'], 'Available: ' . $occ['available_nights']);
        }

        return $rows;
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon, 2: string}
     */
    private function periodRange(string $period): array
    {
        $now = now();
        return match ($period) {
            'day' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), 'Last 7 days'],
            'week' => [$now->copy()->subWeeks(4)->startOfDay(), $now->copy()->endOfDay(), 'Last 4 weeks'],
            'month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth(), 'Last month'],
            default => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth(), 'Last month'],
        };
    }

    /**
     * @return array{revenue: float, bookings: int}
     */
    private function revenueAndBookingsForRange(array $hotelIds, string $from, string $to): array
    {
        $row = DB::table('bookings')
            ->whereIn('hotel_id', $hotelIds)
            ->where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->where('check_in', '<', date('Y-m-d', strtotime($to . ' +1 day')))
            ->where('check_out', '>', $from)
            ->selectRaw('COALESCE(SUM(total_price), 0) as revenue, COUNT(*) as bookings')
            ->first();

        return [
            'revenue' => round((float) ($row->revenue ?? 0), 2),
            'bookings' => (int) ($row->bookings ?? 0),
        ];
    }
}
