import { useParams, Link, useLocation } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';

export default function Checkout() {
  const { uuid } = useParams();
  const { user } = useAuth();
  const location = useLocation();
  const stateBooking = location.state?.booking;

  const { data, isLoading, isError, error } = useQuery({
    queryKey: ['booking', uuid],
    queryFn: async () => {
      const res = await api.get(`/bookings/${uuid}`);
      return res.data;
    },
    enabled: !!uuid && !!user && !stateBooking,
  });

  const booking = stateBooking || (data?.data ?? data?.booking ?? data);

  if (!uuid) {
    return (
      <div className="py-6">
        <ErrorMessage message="Missing booking reference." />
      </div>
    );
  }

  if (!booking && !user) {
    return (
      <div className="py-6">
        <p className="text-stone-600 mb-2">This checkout session has expired or the page was refreshed.</p>
        <p className="text-stone-600 mb-4">We’ll send a link to your email after you complete the booking — use that link to view and pay.</p>
        <Link to="/hotels" className="text-amber-600 underline">Search hotels</Link>
      </div>
    );
  }

  if (!stateBooking && !booking && isLoading) return <div className="py-6"><p>Loading...</p></div>;
  if (!stateBooking && !booking && isError) return <div className="py-6"><ErrorMessage message={error?.response?.data?.message || error?.message || 'Booking not found'} /></div>;

  return (
    <div className="py-6">
      <h1 className="text-2xl font-bold text-stone-900 mb-4">Checkout</h1>
      <p className="text-stone-600 mb-2">Booking: {booking?.uuid || uuid}</p>
      {booking?.hotel && <p className="text-stone-600 mb-2">Hotel: {booking.hotel.name}</p>}
      {booking?.guest_name && <p className="text-stone-600 mb-2">Guest: {booking.guest_name}</p>}
      {booking?.total_price != null && (
        <div className="mt-4 p-4 rounded-lg bg-stone-50 border border-stone-200">
          <h3 className="font-semibold text-stone-900 mb-2">Price breakdown</h3>
          {booking.subtotal != null && (
            <p className="text-sm text-stone-600">Subtotal: {booking.currency || 'USD'} {Number(booking.subtotal).toFixed(2)}</p>
          )}
          {(booking.discount_amount ?? 0) > 0 && (
            <p className="text-sm text-stone-600">Discount: −{booking.currency || 'USD'} {Number(booking.discount_amount).toFixed(2)}</p>
          )}
          {(booking.late_checkout_amount ?? 0) > 0 && (
            <p className="text-sm text-stone-600">Late checkout: +{booking.currency || 'USD'} {Number(booking.late_checkout_amount).toFixed(2)}</p>
          )}
          {(booking.tax_amount ?? 0) > 0 && (
            <p className="text-sm text-stone-600">
              {booking.hotel?.tax_name || 'Tax'}{booking.hotel?.tax_inclusive ? ' (included)' : ''}: {booking.currency || 'USD'} {Number(booking.tax_amount).toFixed(2)}
            </p>
          )}
          <p className="font-medium text-stone-900 mt-2">Total: {booking.currency || 'USD'} {Number(booking.total_price).toFixed(2)}</p>
        </div>
      )}
      {booking?.cancellation_policy_summary && (
        <p className="text-sm text-stone-600 mt-2">{booking.cancellation_policy_summary}</p>
      )}
      <div className="mt-6 p-4 rounded-xl bg-amber-50 border border-amber-200">
        <p className="text-amber-900 font-medium">Payment (Phase 8)</p>
        <p className="text-sm text-amber-800 mt-1">Stripe/PayPal will be connected here.</p>
      </div>
      <Link to={user ? '/profile' : '/'} className="inline-block mt-6 px-6 py-3 rounded-lg bg-amber-600 text-white hover:bg-amber-700">Back to {user ? 'profile' : 'home'}</Link>
    </div>
  );
}
