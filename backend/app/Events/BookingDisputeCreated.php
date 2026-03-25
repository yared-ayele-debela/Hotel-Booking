<?php

namespace App\Events;

use App\Models\BookingDispute;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingDisputeCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public BookingDispute $dispute
    ) {}
}
