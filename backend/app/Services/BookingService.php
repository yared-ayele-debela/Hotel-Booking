<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingRoom;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        protected AvailabilityService $availabilityService,
        protected PricingService $pricingService,
    ) {}

    /**
     * Validate that requested rooms have enough availability for the date range.
     *
     * @param  array<int, int>  $roomQuantities  [room_id => quantity]
     */
    public function validateAvailability(array $roomQuantities, string $checkIn, string $checkOut): bool
    {
        foreach ($roomQuantities as $roomId => $quantity) {
            $this->availabilityService->ensureAvailabilityRows($roomId, $checkIn, $checkOut);
            $min = $this->availabilityService->getMinimumAvailability($roomId, $checkIn, $checkOut);
            if ($min < $quantity) {
                return false;
            }
        }
        return true;
    }

    /**
     * Create a booking: lock inventory, create booking + booking_rooms, return booking.
     * On failure releases lock and rethrows.
     * For guest checkout pass customerId null and guest_email + guest_name.
     *
     * @param  array<int, int>  $roomQuantities  [room_id => quantity]
     */
    public function createBooking(
        ?int $customerId,
        int $hotelId,
        array $roomQuantities,
        string $checkIn,
        string $checkOut,
        string $currency = 'USD',
        ?string $couponCode = null,
        ?string $guestEmail = null,
        ?string $guestName = null,
    ): Booking {
        if ($customerId === null && (empty($guestEmail) || empty($guestName))) {
            throw new \InvalidArgumentException('Guest booking requires guest_email and guest_name.');
        }
        if ($customerId !== null && ($guestEmail !== null || $guestName !== null)) {
            throw new \InvalidArgumentException('Cannot set both customer_id and guest fields.');
        }

        return DB::transaction(function () use ($customerId, $hotelId, $roomQuantities, $checkIn, $checkOut, $currency, $couponCode, $guestEmail, $guestName) {
            foreach (array_keys($roomQuantities) as $roomId) {
                $this->availabilityService->ensureAvailabilityRows($roomId, $checkIn, $checkOut);
            }
            foreach ($roomQuantities as $roomId => $quantity) {
                $this->availabilityService->ensureAvailabilityRows($roomId, $checkIn, $checkOut);
                $min = $this->availabilityService->getMinimumAvailability($roomId, $checkIn, $checkOut);
                if ($min < $quantity) {
                    throw new \RuntimeException("Insufficient availability for room {$roomId}.");
                }
            }

            $breakdown = $this->pricingService->calculate(
                $roomQuantities,
                $checkIn,
                $checkOut,
                $hotelId,
                $couponCode,
                $customerId,
            );

            foreach ($roomQuantities as $roomId => $quantity) {
                $this->availabilityService->lockInventory($roomId, $quantity, $checkIn, $checkOut);
            }

            try {
                $booking = Booking::create([
                    'customer_id' => $customerId,
                    'guest_email' => $customerId === null ? $guestEmail : null,
                    'guest_name' => $customerId === null ? $guestName : null,
                    'hotel_id' => $hotelId,
                    'status' => BookingStatus::PENDING_PAYMENT->value,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'total_price' => $breakdown->total,
                    'currency' => $breakdown->currency,
                    'coupon_id' => $breakdown->couponId,
                    'discount_amount' => $breakdown->discount,
                    'tax_amount' => $breakdown->tax,
                ]);

                $room = \App\Models\Room::find(array_key_first($roomQuantities));
                $unitPrice = $room ? $room->base_price : $breakdown->total / max(1, array_sum($roomQuantities));
                foreach ($roomQuantities as $roomId => $quantity) {
                    $r = \App\Models\Room::find($roomId);
                    $up = $r ? $r->base_price : $unitPrice;
                    BookingRoom::create([
                        'booking_id' => $booking->id,
                        'room_id' => $roomId,
                        'quantity' => $quantity,
                        'unit_price' => $up,
                    ]);
                }
                return $booking;
            } catch (\Throwable $e) {
                foreach ($roomQuantities as $roomId => $quantity) {
                    $this->availabilityService->releaseInventory($roomId, $quantity, $checkIn, $checkOut);
                }
                throw $e;
            }
        });
    }

    /**
     * Cancel a booking: update status, release inventory. Refunds handled by PaymentService.
     */
    public function cancelBooking(Booking $booking): void
    {
        if ($booking->status === BookingStatus::CANCELLED->value) {
            return;
        }

        DB::transaction(function () use ($booking) {
            $checkInStr = $booking->check_in instanceof \Carbon\Carbon
                ? $booking->check_in->format('Y-m-d')
                : $booking->check_in;
            $checkOutStr = $booking->check_out instanceof \Carbon\Carbon
                ? $booking->check_out->format('Y-m-d')
                : $booking->check_out;
            $booking->bookingRooms->each(function (BookingRoom $br) use ($checkInStr, $checkOutStr) {
                $this->availabilityService->releaseInventory(
                    $br->room_id,
                    $br->quantity,
                    $checkInStr,
                    $checkOutStr
                );
            });
            $booking->update(['status' => BookingStatus::CANCELLED->value]);
        });
    }
}
