<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Http\Request;
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
}
