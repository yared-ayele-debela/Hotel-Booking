<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'icon',
        'sort_order',
    ];

    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'hotel_amenity');
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_amenity');
    }
}
