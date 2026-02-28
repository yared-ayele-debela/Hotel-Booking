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
      const res = await api.post('/bookings', payload);
      return res.data;
    },
    onSuccess: (data) => {
      const uuid = data?.booking?.uuid || data?.data?.booking?.uuid;
      if (uuid) navigate(`/checkout/${uuid}`);
      else navigate('/profile');
    },
  });

  const hotel = hotelData?.data ?? hotelData;
  const room = hotel?.rooms?.find((r) => String(r.id) === String(roomId));

  if (!user) {
    return (
      <div className="py-6">
        <p className="text-stone-600">Please log in to book.</p>
        <Link to="/login" className="text-amber-600 underline mt-2 inline-block">Log in</Link>
      </div>
    );
  }
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

  return (
    <div className="py-6">
      <h1 className="text-2xl font-bold text-stone-900 mb-4">Complete booking</h1>
      <p className="text-stone-600 mb-4">{hotel.name} - {room.name}</p>
      <p className="text-sm text-stone-600">Check-in: {checkIn} - Check-out: {checkOut} - Quantity: {quantity}</p>
      <form onSubmit={(e) => { e.preventDefault(); createBooking.mutate(); }} className="mt-6 space-y-4 max-w-md">
        <div>
          <label htmlFor="quantity" className="block text-sm font-medium text-stone-700 mb-1">Quantity</label>
          <input id="quantity" type="number" min={1} max={room.total_rooms} value={quantity} onChange={(e) => setQuantity(Number(e.target.value) || 1)} className="w-full rounded-lg border border-stone-300 px-4 py-3" />
        </div>
        <div>
          <label htmlFor="coupon_code" className="block text-sm font-medium text-stone-700 mb-1">Coupon code (optional)</label>
          <input id="coupon_code" type="text" placeholder="e.g. SAVE10" value={couponCode} onChange={(e) => setCouponCode(e.target.value)} className="w-full rounded-lg border border-stone-300 px-4 py-3 uppercase" />
        </div>
        <button type="submit" disabled={createBooking.isPending} className="w-full px-6 py-3 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700 disabled:opacity-50">
          {createBooking.isPending ? 'Creating…' : 'Proceed to checkout'}
        </button>
        {createBooking.isError && <ErrorMessage message={createBooking.error?.response?.data?.message || createBooking.error?.message} />}
      </form>
    </div>
  );
}
