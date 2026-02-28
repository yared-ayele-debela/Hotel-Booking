<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'check_in',
        'check_out',
        'status',
        'tax_rate',
        'tax_name',
    ];

    public function vendor()
    {
        return $this->belongsTo(\App\Models\User::class, 'vendor_id');
    }

    public function rooms()
    {
        return $this->hasMany(\App\Models\Room::class);
    }

    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class);
    }

    public function images()
    {
        return $this->hasMany(\App\Models\HotelImage::class)->ordered();
    }

    public function bannerImage()
    {
        return $this->hasOne(\App\Models\HotelImage::class)->banner();
    }

    public function savedByUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SavedHotel::class);
    }
}

