<?php

namespace App\Services;

use App\Models\Coupon;

class CouponService
{
    /**
     * Validate coupon and return discount amount for given context.
     * Returns [discountAmount, coupon] or [0, null] if invalid.
     *
     * @param  array<int, int>  $roomQuantities  room_id => quantity
     */
    public function validateAndGetDiscount(
        string $code,
        int $hotelId,
        array $roomQuantities,
        float $subtotal,
        string $checkIn,
        string $checkOut,
        int $nights,
        ?int $userId = null,
    ): array {
        $coupon = Coupon::where('code', strtoupper($code))->where('is_active', true)->first();
        if (! $coupon) {
            return [0.0, null];
        }

        if ($coupon->valid_from && $coupon->valid_from->isAfter($checkIn)) {
            return [0.0, null];
        }
        if ($coupon->valid_to && $coupon->valid_to->isBefore($checkOut)) {
            return [0.0, null];
        }

        if ($coupon->min_nights !== null && $nights < $coupon->min_nights) {
            return [0.0, null];
        }
        if ($coupon->min_amount !== null && $subtotal < (float) $coupon->min_amount) {
            return [0.0, null];
        }

        if ($coupon->hotel_ids !== null && count($coupon->hotel_ids) > 0 && ! in_array($hotelId, $coupon->hotel_ids, true)) {
            return [0.0, null];
        }

        $roomIds = array_keys($roomQuantities);
        if ($coupon->room_ids !== null && count($coupon->room_ids) > 0) {
            $allowed = array_intersect($roomIds, $coupon->room_ids);
            if (empty($allowed)) {
                return [0.0, null];
            }
        }

        if ($coupon->usage_limit_total !== null) {
            if ($coupon->redemptionCount() >= $coupon->usage_limit_total) {
                return [0.0, null];
            }
        }
        if ($userId !== null && $coupon->usage_limit_per_user !== null) {
            if ($coupon->redemptionCountForUser($userId) >= $coupon->usage_limit_per_user) {
                return [0.0, null];
            }
        }

        $discount = $this->computeDiscount($coupon, $subtotal);
        return [$discount, $coupon];
    }

    public function computeDiscount(Coupon $coupon, float $subtotal): float
    {
        $value = (float) $coupon->value;
        if ($coupon->type === Coupon::TYPE_PERCENTAGE) {
            $value = min(100, max(0, $value));
            return round($subtotal * ($value / 100), 2);
        }
        return round(min($value, $subtotal), 2);
    }
}
