<?php

namespace App\Mail;

use App\Models\BookingDispute;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingDisputeSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BookingDispute $dispute
    ) {
        $this->dispute->loadMissing(['booking.hotel', 'booking.customer']);
    }

    public function envelope(): Envelope
    {
        $ref = $this->dispute->booking?->uuid ?? (string) $this->dispute->booking_id;

        return new Envelope(
            subject: 'New booking dispute #'.$this->dispute->id.' (ref: '.$ref.')',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.booking-dispute-submitted',
            with: [
                'dispute' => $this->dispute,
                'booking' => $this->dispute->booking,
            ],
        );
    }
}
