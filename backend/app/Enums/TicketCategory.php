<?php

namespace App\Enums;

enum TicketCategory: string
{
    case BILLING = 'billing';
    case BOOKING = 'booking';
    case TECHNICAL = 'technical';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BILLING => 'Billing',
            self::BOOKING => 'Booking',
            self::TECHNICAL => 'Technical',
            self::OTHER => 'Other',
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}
