<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;

class PayoutService
{
    public function __construct(
        protected CommissionService $commissionService
    ) {}

    /**
     * Generate payouts for a period. Creates one payout per vendor with confirmed
     * bookings in the period that haven't been included in any payout yet.
     *
     * @return Payout[] Created payouts
     */
    public function generateForPeriod(string $periodStart, string $periodEnd): array
    {
        $bookings = Booking::query()
            ->join('hotels', 'bookings.hotel_id', '=', 'hotels.id')
            ->where('bookings.status', 'confirmed')
            ->whereNull('bookings.deleted_at')
            ->whereDate('bookings.check_in', '>=', $periodStart)
            ->whereDate('bookings.check_in', '<=', $periodEnd)
            ->whereNotExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('payout_booking')
                    ->whereColumn('payout_booking.booking_id', 'bookings.id');
            })
            ->select('bookings.*')
            ->with('hotel')
            ->get();

        $byVendor = $bookings->groupBy(fn (Booking $b) => $b->hotel->vendor_id);

        $created = [];
        foreach ($byVendor as $vendorId => $vendorBookings) {
            $amount = 0;
            $commission = 0;
            foreach ($vendorBookings as $booking) {
                $amount += (float) $booking->total_price;
                $commission += $this->commissionService->commissionForBooking($booking);
            }
            $net = round($amount - $commission, 2);
            if ($net <= 0) {
                continue;
            }

            $payout = Payout::create([
                'vendor_id' => $vendorId,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'amount' => round($amount, 2),
                'commission' => round($commission, 2),
                'net' => $net,
                'status' => Payout::STATUS_PENDING,
            ]);

            $payout->bookings()->attach($vendorBookings->pluck('id'));
            $created[] = $payout;
        }

        return $created;
    }
}
