<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomAvailability;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
    /**
     * Get minimum available room count for a room over a date range.
     * Uses room_availability; missing dates fall back to room.total_rooms.
     */
    public function getMinimumAvailability(int $roomId, string $checkIn, string $checkOut): int
    {
        $room = Room::findOrFail($roomId);
        $period = CarbonPeriod::create($checkIn, $checkOut)->excludeEndDate();
        $min = PHP_INT_MAX;

        foreach ($period as $date) {
            $av = RoomAvailability::where('room_id', $roomId)
                ->whereDate('date', $date)
                ->first();
            $min = min($min, $av ? (int) $av->available_rooms : $room->total_rooms);
        }

        return $min === PHP_INT_MAX ? 0 : $min;
    }

    /**
     * Get availability per date for a room in range (batch query).
     */
    public function getAvailabilityForRange(int $roomId, string $checkIn, string $checkOut): array
    {
        $room = Room::findOrFail($roomId);
        $rows = RoomAvailability::where('room_id', $roomId)
            ->whereBetween('date', [$checkIn, $checkOut])
            ->get()
            ->keyBy(fn ($r) => $r->date->format('Y-m-d'));

        $result = [];
        $period = CarbonPeriod::create($checkIn, $checkOut)->excludeEndDate();
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $result[$key] = isset($rows[$key]) ? (int) $rows[$key]->available_rooms : $room->total_rooms;
        }
        return $result;
    }

    /**
     * Ensure room_availability rows exist for room for each date in range (default to total_rooms).
     */
    public function ensureAvailabilityRows(int $roomId, string $checkIn, string $checkOut): void
    {
        $room = Room::findOrFail($roomId);
        $period = CarbonPeriod::create($checkIn, $checkOut)->excludeEndDate();

        foreach ($period as $date) {
            RoomAvailability::firstOrCreate(
                ['room_id' => $roomId, 'date' => $date->format('Y-m-d')],
                ['available_rooms' => $room->total_rooms]
            );
        }
    }

    /**
     * Lock inventory: decrement available_rooms for (room_id, date) for each date in range.
     * Uses pessimistic locking. Call within a DB transaction.
     */
    public function lockInventory(int $roomId, int $quantity, string $checkIn, string $checkOut): void
    {
        $period = CarbonPeriod::create($checkIn, $checkOut)->excludeEndDate();
        foreach ($period as $date) {
            $av = RoomAvailability::where('room_id', $roomId)
                ->whereDate('date', $date)
                ->lockForUpdate()
                ->first();
            if (! $av) {
                throw new \RuntimeException("No availability row for room {$roomId} on {$date->format('Y-m-d')}. Call ensureAvailabilityRows first.");
            }
            if ($av->available_rooms < $quantity) {
                throw new \RuntimeException("Insufficient availability for room {$roomId} on {$date->format('Y-m-d')}.");
            }
            $av->decrement('available_rooms', $quantity);
        }
    }

    /**
     * Release lock: increment available_rooms (compensating action on failure or cancellation).
     */
    public function releaseInventory(int $roomId, int $quantity, string $checkIn, string $checkOut): void
    {
        $period = CarbonPeriod::create($checkIn, $checkOut)->excludeEndDate();
        foreach ($period as $date) {
            RoomAvailability::where('room_id', $roomId)
                ->whereDate('date', $date)
                ->increment('available_rooms', $quantity);
        }
    }

    /**
     * Room IDs that have at least minQuantity available for the entire date range (batch query).
     *
     * @return array<int>
     */
    public function getRoomIdsWithAvailability(string $checkIn, string $checkOut, int $minQuantity = 1): array
    {
        $period = CarbonPeriod::create($checkIn, $checkOut)->excludeEndDate();
        $dates = array_map(fn ($d) => $d->format('Y-m-d'), iterator_to_array($period));
        if ($dates === []) {
            return [];
        }

        $rows = RoomAvailability::whereBetween('date', [min($dates), max($dates)])
            ->get()
            ->groupBy('room_id');

        $roomIds = Room::pluck('total_rooms', 'id');
        $result = [];
        foreach ($roomIds as $roomId => $totalRooms) {
            $avail = $rows->get($roomId);
            $min = PHP_INT_MAX;
            foreach ($dates as $date) {
                $r = $avail ? $avail->firstWhere(fn ($a) => $a->date->format('Y-m-d') === $date) : null;
                $min = min($min, $r ? (int) $r->available_rooms : (int) $totalRooms);
            }
            if ($min !== PHP_INT_MAX && $min >= $minQuantity) {
                $result[] = $roomId;
            }
        }
        return $result;
    }
}
