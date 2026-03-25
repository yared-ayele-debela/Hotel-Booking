<?php

namespace App\Mail;

use App\Models\Booking;
use App\Support\BookingInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function envelope(): Envelope
    {
        $this->booking->loadMissing('hotel');
        $hotelName = $this->booking->hotel?->name ?? 'Hotel';

        return new Envelope(
            subject: 'Booking confirmed – '.$hotelName.' (ref: '.$this->booking->uuid.')',
        );
    }

    public function content(): Content
    {
        $booking = Booking::query()
            ->whereKey($this->booking->id)
            ->with([
                'hotel.bannerImage',
                'hotel.images',
                'hotel.countryRelation',
                'hotel.cityRelation',
                'hotel.amenities',
                'bookingRooms.room',
                'customer',
                'coupon',
            ])
            ->firstOrFail();

        $nights = $booking->check_in->diffInDays($booking->check_out);
        $subtotal = (float) $booking->total_price - (float) ($booking->tax_amount ?? 0) + (float) ($booking->discount_amount ?? 0);

        return new Content(
            view: 'invoice.booking',
            with: BookingInvoice::viewData($booking, $nights, round($subtotal, 2)),
        );
    }
}
