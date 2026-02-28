<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'rating',
        'comment',
        'approved',
        'hidden',
        'moderated_at',
        'moderated_by',
    ];

    protected function casts(): array
    {
        return [
            'approved' => 'boolean',
            'hidden' => 'boolean',
            'moderated_at' => 'datetime',
        ];
    }

    public function moderatedBy()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

