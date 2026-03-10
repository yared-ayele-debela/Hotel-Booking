<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $hotelIds = Hotel::where('vendor_id', auth()->id())->pluck('id');
        $query = Booking::whereIn('hotel_id', $hotelIds)->with(['hotel', 'customer']);

        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('check_in', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('check_out', '<=', $request->to);
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();
        $hotels = Hotel::where('vendor_id', auth()->id())->orderBy('name')->get();
        return view('admin.vendor.bookings.index', compact('bookings', 'hotels'));
    }

    /**
     * View/download invoice for a booking (vendor's hotel only).
     */
    public function invoice(string $uuid): Response
    {
        $booking = Booking::where('uuid', $uuid)->with(['hotel', 'bookingRooms.room', 'customer', 'coupon'])->firstOrFail();
        $hotelIds = Hotel::where('vendor_id', auth()->id())->pluck('id');
        if (! $hotelIds->contains($booking->hotel_id)) {
            abort(403, 'You do not have access to this invoice.');
        }

        $nights = $booking->check_in->diffInDays($booking->check_out);
        $subtotal = (float) $booking->total_price - (float) ($booking->tax_amount ?? 0) + (float) ($booking->discount_amount ?? 0);

        $html = view('invoice.booking', [
            'booking' => $booking,
            'nights' => $nights,
            'subtotal' => round($subtotal, 2),
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="invoice-'.$booking->uuid.'.html"',
        ]);
    }
}
