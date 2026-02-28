<?php

namespace App\Services;

use App\DTOs\PriceBreakdown;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomAvailability;
use Carbon\CarbonPeriod;

class PricingService
{
    public function __construct(
        protected CouponService $couponService,
    ) {}

    /**
     * Calculate price breakdown for given rooms and date range.
     * Base price from room (and room_availability price_override when present).
     * Applies length-of-stay discount and optional coupon (validated via CouponService).
     *
     * @param  array<int, int>  $roomQuantities  room_id => quantity
     */
    public function calculate(
        array $roomQuantities,
        string $checkIn,
        string $checkOut,
        int $hotelId,
        ?string $couponCode = null,
        ?int $userId = null,
    ): PriceBreakdown {
        $subtotal = 0.0;
        $period = CarbonPeriod::create($checkIn, $checkOut)->excludeEndDate();

        foreach ($roomQuantities as $roomId => $quantity) {
            $room = Room::findOrFail($roomId);
            foreach ($period as $date) {
                $av = RoomAvailability::where('room_id', $roomId)
                    ->whereDate('date', $date)
                    ->first();
                $pricePerNight = $av && $av->price_override !== null
                    ? (float) $av->price_override
                    : (float) $room->base_price;
                $subtotal += $pricePerNight * $quantity;
            }
        }

        $nights = $period->count();
        [$discount, $coupon] = $this->applyDiscounts($subtotal, $checkIn, $checkOut, $nights, $couponCode, $hotelId, $roomQuantities, $userId);
        $taxableAmount = max(0, $subtotal - $discount);
        $taxRate = $this->getTaxRateForHotel($hotelId);
        $tax = round($taxableAmount * $taxRate, 2);
        $total = $subtotal - $discount + $tax;

        return new PriceBreakdown(
            subtotal: round($subtotal, 2),
            discount: round($discount, 2),
            tax: round($tax, 2),
            total: round(max(0, $total), 2),
            currency: 'USD',
            couponCode: $couponCode,
            couponId: $coupon?->id,
        );
    }

    /**
     * @param  array<int, int>  $roomQuantities
     * @return array{0: float, 1: \App\Models\Coupon|null}
     */
    protected function applyDiscounts(float $subtotal, string $checkIn, string $checkOut, int $nights, ?string $couponCode, int $hotelId, array $roomQuantities, ?int $userId): array
    {
        $discount = 0.0;
        if ($nights >= 7) {
            $discount += $subtotal * 0.05;
        }

        $coupon = null;
        if ($couponCode !== null && $couponCode !== '') {
            [$couponDiscount, $coupon] = $this->couponService->validateAndGetDiscount(
                $couponCode,
                $hotelId,
                $roomQuantities,
                $subtotal,
                $checkIn,
                $checkOut,
                $nights,
                $userId,
            );
            $discount += $couponDiscount;
        }

        return [$discount, $coupon];
    }

    protected function getTaxRateForHotel(int $hotelId): float
    {
        $hotel = Hotel::find($hotelId);
        if ($hotel && $hotel->tax_rate !== null) {
            return (float) $hotel->tax_rate;
        }
        return (float) config('booking.default_tax_rate', 0);
    }
}
