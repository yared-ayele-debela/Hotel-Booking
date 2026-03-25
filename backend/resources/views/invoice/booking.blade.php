<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company_name }} – Invoice – {{ $booking->uuid }}</title>
    <style>
        body { font-family: system-ui, sans-serif; font-size: 14px; line-height: 1.5; color: #1c1917; max-width: 700px; margin: 0 auto; padding: 24px; }
        h1 { font-size: 1.5rem; margin: 0 0 8px 0; }
        h2 { font-size: 1.125rem; margin: 0 0 8px 0; }
        .meta { color: #78716c; font-size: 0.875rem; margin-bottom: 24px; }
        .invoice-header { display: flex; flex-wrap: wrap; gap: 24px; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #e7e5e4; }
        .company-brand { display: flex; gap: 16px; align-items: center; flex: 1; min-width: 220px; }
        .company-logo { max-height: 56px; max-width: 180px; width: auto; height: auto; object-fit: contain; }
        .company-name { font-size: 1.25rem; font-weight: 700; color: #1c1917; }
        .company-tagline { margin: 4px 0 0 0; max-width: 360px; }
        .company-contact { font-size: 0.8125rem; color: #57534e; text-align: right; }
        .company-contact div { margin-bottom: 4px; }
        .hotel-detail { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 24px; padding: 16px; background: #fafaf9; border-radius: 8px; border: 1px solid #e7e5e4; }
        .hotel-photo { width: 200px; max-width: 100%; height: 140px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
        .hotel-detail-body { flex: 1; min-width: 200px; }
        .hotel-description { margin: 8px 0 0 0; color: #44403c; font-size: 0.875rem; }
        .hotel-amenities { margin: 10px 0 0 0; font-size: 0.8125rem; color: #57534e; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #e7e5e4; }
        th { font-weight: 600; background: #fafaf9; }
        .text-right { text-align: right; }
        .totals { margin-top: 24px; margin-left: auto; width: 260px; }
        .totals tr { border-bottom: none; }
        .totals td { padding: 6px 0; }
        .totals .total-row { font-weight: 700; font-size: 1.125rem; border-top: 2px solid #1c1917; padding-top: 10px; margin-top: 8px; }
        .two-col { display: flex; gap: 32px; flex-wrap: wrap; margin-bottom: 24px; }
        .two-col > div { flex: 1; min-width: 200px; }
        .payment-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.875rem; font-weight: 500; }
        .payment-paid { background: #dcfce7; color: #166534; }
        .payment-pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <header class="invoice-header">
        <div class="company-brand">
            @if(!empty($company_logo_url))
                <img src="{{ $company_logo_url }}" alt="{{ $company_name }}" class="company-logo">
            @endif
            <div>
                <div class="company-name">{{ $company_name }}</div>
                @if(!empty($company_description))
                    <p class="meta company-tagline">{{ \Illuminate\Support\Str::limit($company_description, 180) }}</p>
                @endif
            </div>
        </div>
        @if(!empty($company_email) || !empty($company_phone) || !empty($company_address))
        <div class="company-contact">
            <div><strong>Issued by</strong></div>
            @if(!empty($company_email))
                <div>{{ $company_email }}</div>
            @endif
            @if(!empty($company_phone))
                <div>{{ $company_phone }}</div>
            @endif
            @if(!empty($company_address))
                <div style="white-space: pre-line;">{{ $company_address }}</div>
            @endif
        </div>
        @endif
    </header>

    <h1>Invoice / Receipt</h1>
    <p class="meta">Booking reference: <strong>{{ $booking->uuid }}</strong> · Issued: {{ $booking->created_at->format('F j, Y') }}</p>

    @php
        $h = $booking->hotel;
        $hotelCity = $h?->cityRelation?->name ?? $h?->city;
        $hotelCountry = $h?->countryRelation?->name ?? $h?->country;
        $hotelLocation = trim(implode(', ', array_filter([$hotelCity, $hotelCountry])));
        $hotelDescription = $h && $h->description ? \Illuminate\Support\Str::limit(trim(strip_tags($h->description)), 450) : null;
    @endphp

    @if($h)
    <div class="hotel-detail">
        @if(!empty($hotel_image_url))
            <img src="{{ $hotel_image_url }}" alt="{{ $h->name }}" class="hotel-photo">
        @endif
        <div class="hotel-detail-body">
            <h2>{{ $h->name }}</h2>
            @if($hotelDescription)
                <p class="hotel-description">{{ $hotelDescription }}</p>
            @endif
            @if($h->address)
                <p class="meta" style="margin: 8px 0 0 0;">{{ $h->address }}</p>
            @endif
            @if($hotelLocation !== '')
                <p class="meta" style="margin: 4px 0 0 0;">{{ $hotelLocation }}</p>
            @endif
            @if($h->latitude && $h->longitude)
                <p class="meta" style="margin: 4px 0 0 0;">{{ number_format((float) $h->latitude, 5) }}, {{ number_format((float) $h->longitude, 5) }}</p>
            @endif
            @if(!empty($hotel_amenities))
                <p class="hotel-amenities"><strong>Amenities:</strong> {{ $hotel_amenities }}</p>
            @endif
        </div>
    </div>
    @else
    <p class="meta" style="margin-bottom: 24px;">Hotel details are no longer available.</p>
    @endif

    <div class="two-col">
        <div>
            <strong>Guest</strong><br>
            {{ $booking->customer_id ? ($booking->customer->name ?? 'Guest') : ($booking->guest_name ?? 'Guest') }}<br>
            {{ $booking->customer_id ? ($booking->customer->email ?? '') : ($booking->guest_email ?? '') }}
        </div>
        @if($h)
        <div>
            <strong>Property contact (hotel)</strong><br>
            {{ $h->name }}<br>
            @if($h->address){{ $h->address }}<br>@endif
            @if($hotelLocation !== ''){{ $hotelLocation }}@endif
        </div>
        @endif
    </div>

    <p><strong>Check-in:</strong> {{ $booking->check_in->format('F j, Y') }}@if($h && $h->check_in) at {{ date('g:i A', strtotime($h->check_in)) }}@endif
        &nbsp; <strong>Check-out:</strong> {{ $booking->check_out->format('F j, Y') }}@if($h && $h->check_out) at {{ date('g:i A', strtotime($h->check_out)) }}@endif
        &nbsp; <strong>Nights:</strong> {{ $nights }}</p>

    <table>
        <thead>
            <tr>
                <th>Room</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Nights</th>
                <th class="text-right">Unit price</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($booking->bookingRooms as $br)
            @php
                $lineTotal = (float) $br->unit_price * (int) $br->quantity * $nights;
            @endphp
            <tr>
                <td>{{ $br->room->name ?? 'Room' }}</td>
                <td class="text-right">{{ $br->quantity }}</td>
                <td class="text-right">{{ $nights }}</td>
                <td class="text-right">{{ $booking->currency }} {{ number_format((float) $br->unit_price, 2) }}</td>
                <td class="text-right">{{ $booking->currency }} {{ number_format($lineTotal, 2) }}</td>
            </tr>
            @endforeach
            @if($booking->late_checkout && (float) ($booking->late_checkout_amount ?? 0) > 0)
            <tr>
                <td>Late checkout</td>
                <td class="text-right">1</td>
                <td class="text-right">–</td>
                <td class="text-right">{{ $booking->currency }} {{ number_format((float) $booking->late_checkout_amount, 2) }}</td>
                <td class="text-right">{{ $booking->currency }} {{ number_format((float) $booking->late_checkout_amount, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">{{ $booking->currency }} {{ number_format($subtotal, 2) }}</td>
        </tr>
        @if((float) ($booking->discount_amount ?? 0) > 0)
        <tr>
            <td>Discount @if($booking->coupon) ({{ $booking->coupon->code }})@endif</td>
            <td class="text-right">−{{ $booking->currency }} {{ number_format((float) $booking->discount_amount, 2) }}</td>
        </tr>
        @endif
        @if((float) ($booking->tax_amount ?? 0) > 0)
        <tr>
            <td>{{ ($h?->tax_name) ?? config('booking.default_tax_name', 'Tax') }} {{ ($h && ($h->tax_inclusive ?? false)) ? ' (included in prices)' : '' }}</td>
            <td class="text-right">{{ $booking->currency }} {{ number_format((float) $booking->tax_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>Total</td>
            <td class="text-right">{{ $booking->currency }} {{ number_format((float) $booking->total_price, 2) }}</td>
        </tr>
    </table>

    @php
        $payment = $booking->payments()->whereIn('status', ['succeeded', 'confirmed', 'captured', 'paid', 'completed'])->first();
    @endphp
    <p><strong>Payment:</strong>
        @if($payment)
            <span class="payment-badge payment-paid">Paid</span> {{ $booking->currency }} {{ number_format((float) $payment->amount, 2) }} @if($payment->provider)({{ $payment->provider }})@endif
        @else
            <span class="payment-badge payment-pending">Pending</span>
        @endif
    </p>

    @php
        $viewBookingUrl = $booking->isGuest()
            ? \Illuminate\Support\Facades\URL::temporarySignedRoute('api.v1.bookings.guest-view', now()->addDays(30), ['uuid' => $booking->uuid])
            : rtrim(config('app.frontend_url', config('app.url')), '/') . '/checkout/' . $booking->uuid;
        $qrCodeDataUri = qrCodeDataUri($viewBookingUrl, 120);
    @endphp
    <div style="margin-top: 24px; padding: 16px; border: 1px solid #e7e5e4; border-radius: 8px; display: inline-block;">
        <img src="{{ $qrCodeDataUri }}" alt="Booking QR Code" width="120" height="120" style="display: block;">
        <p class="meta" style="margin: 8px 0 0 0; font-size: 0.75rem;">Scan to view booking</p>
    </div>

    <p class="meta" style="margin-top: 32px;">Thank you for your booking. This document serves as your receipt.</p>
</body>
</html>
