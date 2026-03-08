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
        'country_id',
        'city_id',
        'city',
        'country',
        'latitude',
        'longitude',
        'check_in',
        'check_out',
        'status',
        'tax_rate',
        'tax_name',
        'cancellation_policy',
    ];

    public function countryRelation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function cityRelation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    protected function casts(): array
    {
        return [
            'cancellation_policy' => 'array',
        ];
    }

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

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'hotel_amenity')->orderBy('amenities.sort_order');
    }
}

