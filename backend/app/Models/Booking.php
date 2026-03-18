<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yared\SmartStripe\Contracts\Payable;

class Booking extends Model implements Payable
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
        'marked_old',
        'check_in',
        'check_out',
        'total_price',
        'currency',
        'late_checkout',
        'late_checkout_amount',
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
            'late_checkout' => 'boolean',
            'marked_old' => 'boolean',
            'late_checkout_amount' => 'decimal:2',
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

    /**
     * Mark booking as paid (called from laravel-smart-stripe webhook).
     */
    public function markAsPaid(?string $sessionId = null, ?string $paymentIntentId = null): void
    {
        if ($this->isPaid()) {
            return;
        }
        $payment = \App\Models\Payment::where('booking_id', $this->id)
            ->where('provider', 'stripe')
            ->where('external_id', $sessionId)
            ->first();
        if ($payment && $paymentIntentId) {
            $payment->update(['external_id' => $paymentIntentId]);
        }
        if ($payment) {
            app(\App\Services\PaymentService::class)->confirmPayment($payment);
        } else {
            $this->update(['status' => BookingStatus::CONFIRMED->value]);
        }
    }

    public function isPaid(): bool
    {
        return $this->status === BookingStatus::CONFIRMED->value;
    }
}

