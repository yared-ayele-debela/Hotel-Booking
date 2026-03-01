<?php

namespace App\Listeners;

use App\Events\PaymentConfirmed;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

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

        $viewUrl = $booking->customer_id
            ? null
            : URL::temporarySignedRoute(
                'api.v1.bookings.guest-view',
                now()->addDays(30),
                ['uuid' => $booking->uuid]
            );

        $name = $booking->customer_id
            ? ($booking->customer?->name ?? 'Guest')
            : ($booking->guest_name ?? 'Guest');

        $hotelName = $booking->hotel?->name ?? 'Hotel';
        $nights = $booking->check_in && $booking->check_out
            ? $booking->check_in->diffInDays($booking->check_out)
            : 0;
        $total = number_format((float) $booking->total_price, 2);
        $currency = $booking->currency ?? 'USD';

        $lines = [
            "Hello {$name},",
            '',
            'Your booking is confirmed.',
            '',
            "Hotel: {$hotelName}",
            'Check-in: ' . ($booking->check_in?->format('F j, Y') ?? '—'),
            'Check-out: ' . ($booking->check_out?->format('F j, Y') ?? '—'),
            "Nights: {$nights}",
            "Total: {$currency} {$total}",
            '',
            'Booking reference: ' . $booking->uuid,
        ];
        if ($viewUrl) {
            $lines[] = '';
            $lines[] = 'View your booking (link valid 30 days):';
            $lines[] = $viewUrl;
            $lines[] = '';
            $lines[] = 'Create an account with this email to claim this booking and see it in your profile.';
        }
        $body = implode("\n", $lines);

        try {
            Mail::raw($body, function ($message) use ($email, $booking) {
                $message->to($email)
                    ->subject('Booking confirmed – ' . ($booking->hotel?->name ?? 'Hotel') . ' (ref: ' . $booking->uuid . ')');
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
