import { useParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';

export default function Checkout() {
  const { uuid } = useParams();
  const { user } = useAuth();

  const { data, isLoading, isError, error } = useQuery({
    queryKey: ['booking', uuid],
    queryFn: async () => {
      const res = await api.get(`/bookings/${uuid}`);
      return res.data;
    },
    enabled: !!uuid && !!user,
  });

  const booking = data?.data ?? data?.booking ?? data;

  if (!user) {
    return (
      <div className="py-6">
        <p className="text-stone-600">Please log in.</p>
        <Link to="/login" className="text-amber-600 underline mt-2 inline-block">Log in</Link>
      </div>
    );
  }
  if (isLoading) return <div className="py-6"><p>Loading...</p></div>;
  if (isError) return <div className="py-6"><ErrorMessage message={error?.response?.data?.message || error?.message || 'Booking not found'} /></div>;

  return (
    <div className="py-6">
      <h1 className="text-2xl font-bold text-stone-900 mb-4">Checkout</h1>
      <p className="text-stone-600 mb-2">Booking: {booking?.uuid || uuid}</p>
      {booking?.hotel && <p className="text-stone-600 mb-2">Hotel: {booking.hotel.name}</p>}
      {booking?.total_price != null && <p className="font-medium">Total: ${Number(booking.total_price).toFixed(2)}</p>}
      <div className="mt-6 p-4 rounded-xl bg-amber-50 border border-amber-200">
        <p className="text-amber-900 font-medium">Payment (Phase 8)</p>
        <p className="text-sm text-amber-800 mt-1">Stripe/PayPal will be connected here.</p>
      </div>
      <Link to="/profile" className="inline-block mt-6 px-6 py-3 rounded-lg bg-amber-600 text-white hover:bg-amber-700">Back to profile</Link>
    </div>
  );
}
