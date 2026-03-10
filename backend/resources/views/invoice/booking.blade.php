<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice – {{ $booking->uuid }}</title>
    <style>
        body { font-family: system-ui, sans-serif; font-size: 14px; line-height: 1.5; color: #1c1917; max-width: 700px; margin: 0 auto; padding: 24px; }
        h1 { font-size: 1.5rem; margin: 0 0 8px 0; }
        .meta { color: #78716c; font-size: 0.875rem; margin-bottom: 24px; }
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
    <h1>Invoice / Receipt</h1>
    <p class="meta">Booking reference: <strong>{{ $booking->uuid }}</strong> · Issued: {{ $booking->created_at->format('F j, Y') }}</p>

    <div class="two-col">
        <div>
            <strong>Guest</strong><br>
            {{ $booking->customer_id ? ($booking->customer->name ?? 'Guest') : ($booking->guest_name ?? 'Guest') }}<br>
            {{ $booking->customer_id ? ($booking->customer->email ?? '') : ($booking->guest_email ?? '') }}
        </div>
        <div>
            <strong>Hotel</strong><br>
            {{ $booking->hotel->name }}<br>
            @if($booking->hotel->address){{ $booking->hotel->address }}<br>@endif
            @if($booking->hotel->city || $booking->hotel->country){{ trim(implode(', ', array_filter([$booking->hotel->city, $booking->hotel->country]))) }}@endif
        </div>
    </div>

    <p><strong>Check-in:</strong> {{ $booking->check_in->format('F j, Y') }}@if($booking->hotel->check_in) at {{ date('g:i A', strtotime($booking->hotel->check_in)) }}@endif
        &nbsp; <strong>Check-out:</strong> {{ $booking->check_out->format('F j, Y') }}@if($booking->hotel->check_out) at {{ date('g:i A', strtotime($booking->hotel->check_out)) }}@endif
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
            <td>{{ $booking->hotel->tax_name ?? config('booking.default_tax_name', 'Tax') }} {{ ($booking->hotel->tax_inclusive ?? false) ? ' (included in prices)' : '' }}</td>
            <td class="text-right">{{ $booking->currency }} {{ number_format((float) $booking->tax_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>Total</td>
            <td class="text-right">{{ $booking->currency }} {{ number_format((float) $booking->total_price, 2) }}</td>
        </tr>
    </table>

    @php
        $payment = $booking->payments()->whereIn('status', ['succeeded', 'confirmed', 'captured', 'paid'])->first();
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
