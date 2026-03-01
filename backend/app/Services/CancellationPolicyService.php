<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Computes refund amount and human-readable summary from hotel/room cancellation policies.
 *
 * Policy JSON shape (on hotel or room):
 * - type: "non_refundable" | "free_until_hours" | "rules"
 * - hours: int (for free_until_hours: cancel at least this many hours before check-in for 100% refund)
 * - rules: array of { hours_before: int, refund_percent: int } (ordered desc by hours_before; first matching rule wins)
 */
class CancellationPolicyService
{
    /**
     * Get the effective cancellation policy for a booking (from first room or hotel).
     *
     * @return array<string, mixed>|null
     */
    public function getPolicyForBooking(Booking $booking): ?array
    {
        $booking->loadMissing(['bookingRooms.room.hotel', 'hotel']);
        $hotelPolicy = $booking->hotel?->cancellation_policy;
        if (is_string($hotelPolicy)) {
            $hotelPolicy = json_decode($hotelPolicy, true);
        }
        foreach ($booking->bookingRooms ?? [] as $br) {
            $room = $br->room;
            if (! $room) {
                continue;
            }
            $roomPolicy = $room->cancellation_policy;
            if (is_string($roomPolicy)) {
                $roomPolicy = json_decode($roomPolicy, true);
            }
            if (! empty($roomPolicy)) {
                return $roomPolicy;
            }
        }
        return is_array($hotelPolicy) ? $hotelPolicy : null;
    }

    /**
     * Compute refund amount for a booking if cancelled at the given time (default: now).
     */
    public function getRefundAmount(Booking $booking, ?CarbonInterface $canceledAt = null): float
    {
        $canceledAt = $canceledAt ?? Carbon::now();
        $policy = $this->getPolicyForBooking($booking);
        if (empty($policy)) {
            return (float) $booking->total_price;
        }
        $total = (float) $booking->total_price;
        $checkIn = $booking->check_in instanceof CarbonInterface
            ? Carbon::parse($booking->check_in)
            : Carbon::parse($booking->check_in);
        $hoursUntilCheckIn = $canceledAt->diffInHours($checkIn, false);
        if ($hoursUntilCheckIn <= 0) {
            return 0.0;
        }
        $type = $policy['type'] ?? 'non_refundable';
        if ($type === 'non_refundable') {
            return 0.0;
        }
        if ($type === 'free_until_hours') {
            $hours = (int) ($policy['hours'] ?? 0);
            return $hoursUntilCheckIn >= $hours ? $total : 0.0;
        }
        if ($type === 'rules' && ! empty($policy['rules']) && is_array($policy['rules'])) {
            $rules = $policy['rules'];
            usort($rules, fn ($a, $b) => ($b['hours_before'] ?? 0) <=> ($a['hours_before'] ?? 0));
            foreach ($rules as $rule) {
                $hoursBefore = (int) ($rule['hours_before'] ?? 0);
                if ($hoursUntilCheckIn >= $hoursBefore) {
                    $pct = (int) ($rule['refund_percent'] ?? 0);
                    return round($total * $pct / 100, 2);
                }
            }
        }
        return 0.0;
    }

    /**
     * Human-readable summary of the policy for display (e.g. "Free cancellation until 48 hours before check-in").
     */
    public function getSummaryForPolicy(?array $policy): string
    {
        if (empty($policy) || ! is_array($policy)) {
            return 'Cancellation policy applies as per hotel.';
        }
        $type = $policy['type'] ?? 'non_refundable';
        if ($type === 'non_refundable') {
            return 'Non-refundable.';
        }
        if ($type === 'free_until_hours') {
            $hours = (int) ($policy['hours'] ?? 0);
            if ($hours >= 24) {
                $days = (int) floor($hours / 24);
                return "Free cancellation until {$days} day(s) before check-in.";
            }
            return "Free cancellation until {$hours} hours before check-in.";
        }
        if ($type === 'rules' && ! empty($policy['rules']) && is_array($policy['rules'])) {
            $parts = [];
            foreach ($policy['rules'] as $rule) {
                $h = (int) ($rule['hours_before'] ?? 0);
                $pct = (int) ($rule['refund_percent'] ?? 0);
                if ($h >= 24) {
                    $d = (int) floor($h / 24);
                    $parts[] = "{$pct}% refund if cancelled {$d}+ day(s) before check-in";
                } else {
                    $parts[] = "{$pct}% refund if cancelled {$h}+ hours before check-in";
                }
            }
            return implode('; ', $parts);
        }
        return 'Cancellation policy applies as per hotel.';
    }

    /**
     * Summary for a booking (uses effective policy).
     */
    public function getSummaryForBooking(Booking $booking): string
    {
        return $this->getSummaryForPolicy($this->getPolicyForBooking($booking));
    }
}
