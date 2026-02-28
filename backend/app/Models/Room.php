<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'name',
        'capacity',
        'base_price',
        'total_rooms',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function availability()
    {
        return $this->hasMany(RoomAvailability::class);
    }

    public function images()
    {
        return $this->hasMany(\App\Models\RoomImage::class)->ordered();
    }

    public function bannerImage()
    {
        return $this->hasOne(\App\Models\RoomImage::class)->banner();
    }
}

