<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case PENDING_PAYMENT = 'pending_payment';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
}
