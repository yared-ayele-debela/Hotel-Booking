<?php

namespace App\DTOs;

readonly class PriceBreakdown
{
    public function __construct(
        public float $subtotal,
        public float $discount,
        public float $tax,
        public float $total,
        public string $currency = 'USD',
        public ?string $couponCode = null,
        public ?int $couponId = null,
    ) {}
}
