<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Mail\BookingReceiptMail;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmationNotification implements ShouldQueue
{
    public function handle(PaymentConfirmed $event): void
    {
        $payment = $event->payment;
        $booking = $payment->booking;
        if (! $booking instanceof Booking) {
            return;
        }

        $email = $booking->customer_id
            ? $booking->customer?->email
            : $booking->guest_email;
        if (empty($email)) {
            return;
        }

        $booking->loadMissing('hotel', 'customer');

        try {
            Mail::to($email)->send(new BookingReceiptMail($booking));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
