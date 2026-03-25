<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public const COMMISSION_RATE_KEY = 'commission_rate';

    /**
     * Platform commission rate (e.g. 0.15 = 15%). From platform_settings or config.
     */
    protected float $commissionRate;

    public function __construct(?float $commissionRate = null)
    {
        if ($commissionRate !== null) {
            $this->commissionRate = $commissionRate;

            return;
        }
        $stored = PlatformSetting::get(self::COMMISSION_RATE_KEY);
        $this->commissionRate = $stored !== null ? (float) $stored : (float) config('booking.commission_rate', 0.10);
    }

    public function getCommissionRate(): float
    {
        return $this->commissionRate;
    }

    /**
     * Commission amount for a booking (from total price).
     */
    public function commissionForBooking(Booking $booking): float
    {
        return round((float) $booking->total_price * $this->commissionRate, 2);
    }

    /**
     * Vendor net earnings: booking total minus commission minus refunds.
     */
    public function vendorNetForBooking(Booking $booking): float
    {
        $total = (float) $booking->total_price;
        $commission = $this->commissionForBooking($booking);
        $refunded = (float) $booking->payments()->sum('refunded_amount');

        return round($total - $commission - $refunded, 2);
    }

    /**
     * Admin reporting: aggregates by vendor (hotel.vendor_id) and optional date range.
     */
    public function reportByVendor(?string $from = null, ?string $to = null): array
    {
        $query = DB::table('bookings')
            ->join('hotels', 'bookings.hotel_id', '=', 'hotels.id')
            ->where('bookings.status', 'confirmed')
            ->whereNull('bookings.deleted_at')
            ->select(
                'hotels.vendor_id',
                DB::raw('COUNT(bookings.id) as booking_count'),
                DB::raw('SUM(bookings.total_price) as gross')
            )
            ->selectRaw('SUM(bookings.total_price) * ? as commission', [$this->commissionRate])
            ->selectRaw('SUM(bookings.total_price) * (1 - ?) as net', [$this->commissionRate])
            ->groupBy('hotels.vendor_id');

        if ($from) {
            $query->whereDate('bookings.check_in', '>=', $from);
        }
        if ($to) {
            $query->whereDate('bookings.check_out', '<=', $to);
        }

        return $query->get()->map(fn ($row) => (array) $row)->all();
    }

    /**
     * Same aggregates as {@see reportByVendor} with vendor profile and payout contact fields for admin.
     *
     * @return list<array<string, mixed>>
     */
    public function reportByVendorWithDetails(?string $from = null, ?string $to = null): array
    {
        $rows = $this->reportByVendor($from, $to);
        if ($rows === []) {
            return [];
        }

        $ids = collect($rows)->pluck('vendor_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        $users = User::query()
            ->with([
                'vendorProfile',
                'bankAccounts' => fn ($q) => $q->orderByDesc('is_default')->orderBy('sort_order'),
            ])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($rows)->map(function (array $row) use ($users) {
            $vid = (int) $row['vendor_id'];
            $user = $users->get($vid);
            $profile = $user?->vendorProfile;
            $bank = $user?->bankAccounts->first();

            return array_merge($row, [
                'vendor_name' => $user?->name,
                'vendor_email' => $user?->email,
                'business_name' => $profile?->business_name,
                'business_phone' => $profile?->business_phone,
                'business_address' => $profile?->business_address,
                'tax_id' => $profile?->tax_id,
                'vendor_profile_status' => $profile?->status,
                'bank_name' => $bank?->bank_name,
                'bank_account_masked' => $bank?->masked_account_number,
                'bank_account_holder' => $bank?->account_holder_name,
                'bank_currency' => $bank?->currency,
            ]);
        })->all();
    }

    /**
     * Platform-wide totals for KPIs (revenue, commission, booking count).
     *
     * @return array{revenue: float, commission: float, booking_count: int}
     */
    public function platformTotals(?string $from = null, ?string $to = null): array
    {
        $query = DB::table('bookings')
            ->where('bookings.status', 'confirmed')
            ->whereNull('bookings.deleted_at');
        if ($from) {
            $query->whereDate('bookings.check_in', '>=', $from);
        }
        if ($to) {
            $query->whereDate('bookings.check_out', '<=', $to);
        }
        $revenue = (float) (clone $query)->sum('bookings.total_price');
        $bookingCount = (int) (clone $query)->count();
        $commission = round($revenue * $this->commissionRate, 2);

        return [
            'revenue' => $revenue,
            'commission' => $commission,
            'booking_count' => $bookingCount,
        ];
    }
}
