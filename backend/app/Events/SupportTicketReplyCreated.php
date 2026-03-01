<?php

namespace App\Events;

use App\Models\SupportTicketReply;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketReplyCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public SupportTicketReply $reply
    ) {}
}
