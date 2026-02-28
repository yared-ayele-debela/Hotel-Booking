<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'amount',
        'currency',
        'provider',
        'external_id',
        'status',
        'payload',
        'refunded_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'payload' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * For policy vendor isolation: payment is accessed via booking -> hotel -> vendor_id.
     */
    public function getVendorIdAttribute(): ?int
    {
        return $this->booking?->hotel?->vendor_id;
    }

}