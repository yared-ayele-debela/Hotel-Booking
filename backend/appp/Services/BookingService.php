<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Room;
use App\Models\RoomAvailability;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingService
{
    public function create(
        int $customerId,
        int $hotelId,
        string $checkIn,
        string $checkOut,
        array $rooms
    ): Booking {
        $checkInDate = Carbon::parse($checkIn)->startOfDay();
        $checkOutDate = Carbon::parse($checkOut)->startOfDay();
        $nights = $checkInDate->diffInDays($checkOutDate);
        if ($nights < 1) {
            throw new InvalidArgumentException('Check-out must be after check-in.');
        }

        return DB::transaction(function () use ($customerId, $hotelId, $checkInDate, $checkOutDate, $nights, $rooms) {
            $totalPrice = 0;
            $bookingRoomsPayload = [];

            foreach ($rooms as $item) {
                $roomId = (int) $item['room_id'];
                $quantity = (int) $item['quantity'];
                $room = Room::where('id', $roomId)->where('hotel_id', $hotelId)->firstOrFail();

                for ($d = $checkInDate->copy(); $d->lt($checkOutDate); $d->addDay()) {
                    $av = RoomAvailability::where('room_id', $roomId)
                        ->where('date', $d->toDateString())
                        ->lockForUpdate()
                        ->first();
                    if (! $av || $av->available_rooms < $quantity) {
                        throw new InvalidArgumentException("Not enough availability for room {$room->name} on {$d->toDateString()}.");
                    }
                }

                $unitPrice = $room->base_price;
                $totalPrice += $unitPrice * $nights * $quantity;
                $bookingRoomsPayload[] = ['room' => $room, 'quantity' => $quantity, 'unit_price' => $unitPrice];
            }

            $booking = Booking::create([
                'customer_id' => $customerId,
                'hotel_id' => $hotelId,
                'status' => 'confirmed',
                'check_in' => $checkInDate,
                'check_out' => $checkOutDate,
                'total_price' => round($totalPrice, 2),
                'currency' => 'USD',
            ]);

            foreach ($bookingRoomsPayload as $item) {
                BookingRoom::create([
                    'booking_id' => $booking->id,
                    'room_id' => $item['room']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            for ($d = $checkInDate->copy(); $d->lt($checkOutDate); $d->addDay()) {
                foreach ($rooms as $item) {
                    $roomId = (int) $item['room_id'];
                    $quantity = (int) $item['quantity'];
                    RoomAvailability::where('room_id', $roomId)
                        ->where('date', $d->toDateString())
                        ->decrement('available_rooms', $quantity);
                }
            }

            return $booking;
        });
    }

    public function cancel(Booking $booking): void
    {
        if (in_array($booking->status, ['cancelled'], true)) {
            return;
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);

            $checkIn = Carbon::parse($booking->check_in)->startOfDay();
            $checkOut = Carbon::parse($booking->check_out)->startOfDay();

            foreach ($booking->bookingRooms as $br) {
                for ($d = $checkIn->copy(); $d->lt($checkOut); $d->addDay()) {
                    RoomAvailability::where('room_id', $br->room_id)
                        ->where('date', $d->toDateString())
                        ->increment('available_rooms', $br->quantity);
                }
            }
        });
    }
}
