<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'customer_id',
        'guest_email',
        'guest_name',
        'hotel_id',
        'status',
        'check_in',
        'check_out',
        'total_price',
        'currency',
        'coupon_id',
        'discount_amount',
        'tax_amount',
    ];

    public function isGuest(): bool
    {
        return $this->customer_id === null && $this->guest_email !== null;
    }

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'total_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
        ];
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function bookingRooms()
    {
        return $this->hasMany(BookingRoom::class);
    }

    public function dispute()
    {
        return $this->hasOne(BookingDispute::class);
    }
}

