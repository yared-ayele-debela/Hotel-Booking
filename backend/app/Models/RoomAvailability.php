<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAvailability extends Model
{
    use HasFactory;

    protected $table = 'room_availability';

    protected $fillable = [
        'room_id',
        'date',
        'available_rooms',
        'price_override',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}

