<?php

namespace App\Listeners;

use App\Events\BookingDisputeCreated;
use App\Mail\BookingDisputeSubmittedMail;
use App\Models\PlatformSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingDisputeAdminNotification implements ShouldQueue
{
    public function handle(BookingDisputeCreated $event): void
    {
        $email = PlatformSetting::get('site_email');
        if (empty($email)) {
            return;
        }

        try {
            Mail::to($email)->send(new BookingDisputeSubmittedMail($event->dispute));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
