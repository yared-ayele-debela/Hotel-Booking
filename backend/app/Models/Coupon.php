<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_nights',
        'min_amount',
        'valid_from',
        'valid_to',
        'usage_limit_total',
        'usage_limit_per_user',
        'hotel_ids',
        'room_ids',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'valid_from' => 'date',
            'valid_to' => 'date',
            'hotel_ids' => 'array',
            'room_ids' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Number of times this coupon has been used (confirmed or pending bookings).
     */
    public function redemptionCount(): int
    {
        return $this->bookings()->count();
    }

    /**
     * Number of times this coupon has been used by a specific user.
     */
    public function redemptionCountForUser(int $userId): int
    {
        return $this->bookings()->where('customer_id', $userId)->count();
    }
}
