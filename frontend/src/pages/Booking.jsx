import { useState } from 'react';
import { useSearchParams, useNavigate, Link } from 'react-router-dom';
import { useQuery, useMutation } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';
import { HotelDetailSkeleton } from '../components/Skeleton';

export default function Booking() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { user } = useAuth();
  const hotelId = searchParams.get('hotel_id') || searchParams.get('hotelId');
  const roomId = searchParams.get('room_id');
  const checkIn = searchParams.get('check_in');
  const checkOut = searchParams.get('check_out');
  const [quantity, setQuantity] = useState(1);
  const [couponCode, setCouponCode] = useState('');
  const [lateCheckout, setLateCheckout] = useState(false);
  const [guestEmail, setGuestEmail] = useState('');
  const [guestName, setGuestName] = useState('');

  const isGuest = !user;

  const { data: hotelData, isLoading: hotelLoading, isError: hotelError } = useQuery({
    queryKey: ['hotel', hotelId],
    queryFn: async () => {
      const res = await api.get(`/hotels/${hotelId}`);
      return res.data;
    },
    enabled: !!hotelId,
  });

  const createBooking = useMutation({
    mutationFn: async () => {
      const payload = {
        hotel_id: Number(hotelId),
        check_in: checkIn,
        check_out: checkOut,
        rooms: [{ room_id: Number(roomId), quantity }],
      };
      if (couponCode.trim()) payload.coupon_code = couponCode.trim();
      if (lateCheckout) payload.late_checkout = true;
      if (isGuest) {
        payload.guest_email = guestEmail.trim();
        payload.guest_name = guestName.trim();
        const res = await api.post('/bookings/guest', payload);
        return res.data;
      }
      const res = await api.post('/bookings', payload);
      return res.data;
    },
    onSuccess: (data) => {
      const payload = data?.data ?? data;
      const booking = payload?.booking ?? payload;
      const uuid = booking?.uuid;
      const paymentIntent = payload?.payment_intent ?? data?.payment_intent;
      if (uuid) {
        navigate(`/checkout/${uuid}`, { state: { booking, paymentIntent } });
      } else {
        navigate('/profile');
      }
    },
  });

  const hotel = hotelData?.data ?? hotelData;
  const room = hotel?.rooms?.find((r) => String(r.id) === String(roomId));

  if (!hotelId || !roomId || !checkIn || !checkOut) {
    return (
      <div className="py-6">
        <ErrorMessage message="Missing search params. Start from a hotel and select dates and room." />
      </div>
    );
  }
  if (hotelLoading) return <div className="py-6"><HotelDetailSkeleton /></div>;
  if (hotelError || !hotel) return <div className="py-6"><ErrorMessage message="Hotel or room not found." /></div>;
  if (!room) return <div className="py-6"><ErrorMessage message="Room not found." /></div>;

  const canSubmit = isGuest ? (guestEmail.trim() && guestName.trim()) : true;

  return (
    <div className="py-6">
      <h1 className="text-2xl font-bold text-stone-900 mb-4">Complete booking</h1>
      {isGuest && (
        <p className="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-sm">
          Book as guest — no account needed. We’ll email your confirmation and a link to view the booking.
        </p>
      )}
      <p className="text-stone-600 mb-4">{hotel.name} – {room.name}</p>
      <p className="text-sm text-stone-600 mb-4">Check-in: {checkIn} · Check-out: {checkOut} · Quantity: {quantity}</p>
      {hotel.check_in && hotel.check_out && (
        <p className="text-sm text-stone-500 mb-2">Hotel times: Check-in {hotel.check_in} · Check-out {hotel.check_out}</p>
      )}
      {hotel.late_checkout_price != null && hotel.late_checkout_price > 0 && (
        <div className="mb-4 p-3 rounded-lg border border-stone-200 bg-stone-50">
          <label className="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" checked={lateCheckout} onChange={(e) => setLateCheckout(e.target.checked)} className="rounded border-stone-300" />
            <span className="text-sm font-medium text-stone-700">Add late checkout</span>
            <span className="text-sm text-stone-600">(+{Number(hotel.late_checkout_price).toFixed(2)} USD)</span>
          </label>
          <p className="text-xs text-stone-500 mt-1 ml-6">Extend your check-out time for an additional fee.</p>
        </div>
      )}
      {room.cancellation_policy_summary && (
        <p className="text-sm text-stone-500 mb-4">{room.cancellation_policy_summary}</p>
      )}
      <form onSubmit={(e) => { e.preventDefault(); createBooking.mutate(); }} className="mt-6 space-y-4 max-w-md">
        {isGuest && (
          <>
            <div>
              <label htmlFor="guest_name" className="block text-sm font-medium text-stone-700 mb-1">Your name</label>
              <input id="guest_name" type="text" required value={guestName} onChange={(e) => setGuestName(e.target.value)} placeholder="e.g. Jane Smith" className="w-full rounded-lg border border-stone-300 px-4 py-3" />
            </div>
            <div>
              <label htmlFor="guest_email" className="block text-sm font-medium text-stone-700 mb-1">Email</label>
              <input id="guest_email" type="email" required value={guestEmail} onChange={(e) => setGuestEmail(e.target.value)} placeholder="you@example.com" className="w-full rounded-lg border border-stone-300 px-4 py-3" />
            </div>
          </>
        )}
        <div>
          <label htmlFor="quantity" className="block text-sm font-medium text-stone-700 mb-1">Quantity</label>
          <input id="quantity" type="number" min={1} max={room.total_rooms} value={quantity} onChange={(e) => setQuantity(Number(e.target.value) || 1)} className="w-full rounded-lg border border-stone-300 px-4 py-3" />
        </div>
        <div>
          <label htmlFor="coupon_code" className="block text-sm font-medium text-stone-700 mb-1">Coupon code (optional)</label>
          <input id="coupon_code" type="text" placeholder="e.g. SAVE10" value={couponCode} onChange={(e) => setCouponCode(e.target.value)} className="w-full rounded-lg border border-stone-300 px-4 py-3 uppercase" />
        </div>
        <button type="submit" disabled={createBooking.isPending || !canSubmit} className="w-full px-6 py-3 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700 disabled:opacity-50">
          {createBooking.isPending ? 'Creating…' : 'Proceed to checkout'}
        </button>
        {createBooking.isError && <ErrorMessage message={createBooking.error?.response?.data?.message || createBooking.error?.message} />}
        {isGuest && (
          <p className="text-sm text-stone-500">
            Already have an account? <Link to="/login" className="text-amber-600 underline">Log in</Link> to book and see your history.
          </p>
        )}
      </form>
    </div>
  );
}
