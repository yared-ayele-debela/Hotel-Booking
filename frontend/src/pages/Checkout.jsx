import { useState } from 'react';
import { useParams, Link, useLocation, useSearchParams } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { Lock, CheckCircle2, Loader2 } from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';
import { formatPrice, formatDate } from '../lib/utils';

export default function Checkout() {
  const { uuid } = useParams();
  const { user } = useAuth();
  const location = useLocation();
  const [searchParams] = useSearchParams();
  const stateBooking = location.state?.booking;
  const guestCheckoutUrl = location.state?.guestCheckoutUrl;
  const [isProcessing, setIsProcessing] = useState(false);
  const [paymentError, setPaymentError] = useState(null);

  const isSuccess = searchParams.get('success') === '1';

  const { data, isLoading, isError, error, refetch } = useQuery({
    queryKey: ['booking', uuid],
    queryFn: async () => {
      const res = await api.get(`/bookings/${uuid}`);
      return res.data;
    },
    enabled: !!uuid && !!user && !stateBooking,
    refetchInterval: (query) => {
      if (!query.state.data) return false;
      const b = query.state.data?.data ?? query.state.data?.booking ?? query.state.data;
      return b?.status === 'pending_payment' ? 3000 : false;
    },
  });

  const booking = stateBooking || (data?.data ?? data?.booking ?? data);
  const isConfirmed = booking?.status === 'confirmed';
  const showSuccess = isSuccess || isConfirmed;

  const handlePayClick = async () => {
    setPaymentError(null);
    setIsProcessing(true);
    try {
      let checkoutUrl;
      if (user) {
        const res = await api.post(`/bookings/${uuid}/checkout-session`);
        if (!res.data?.success) throw new Error(res.data?.message || 'Failed to create checkout');
        checkoutUrl = res.data?.data?.checkout_url;
      } else if (guestCheckoutUrl) {
        const res = await api.post(guestCheckoutUrl);
        if (!res.data?.success) throw new Error(res.data?.message || 'Failed to create checkout');
        checkoutUrl = res.data?.data?.checkout_url;
      } else {
        throw new Error('Please use the link from your confirmation email to complete payment.');
      }
      if (checkoutUrl) {
        window.location.href = checkoutUrl;
      } else {
        throw new Error('No checkout URL received');
      }
    } catch (err) {
      setIsProcessing(false);
      setPaymentError(err?.response?.data?.message || err?.message || 'Could not start payment');
    }
  };

  const handleRetry = () => {
    setPaymentError(null);
  };

  if (!uuid) {
    return (
      <div className="py-6">
        <ErrorMessage message="Missing booking reference." />
      </div>
    );
  }

  if (!booking && !user && !stateBooking) {
    return (
      <div className="py-6">
        <p className="text-stone-600 mb-2">This checkout session has expired or the page was refreshed.</p>
        <p className="text-stone-600 mb-4">We'll send a link to your email after you complete the booking — use that link to view and pay.</p>
        <Link to="/hotels" className="text-amber-600 underline">Search hotels</Link>
      </div>
    );
  }

  if (!stateBooking && !booking && isLoading) {
    return (
      <div className="py-6 flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-amber-600" />
      </div>
    );
  }

  if (!stateBooking && !booking && isError) {
    return (
      <div className="py-6">
        <ErrorMessage message={error?.response?.data?.message || error?.message || 'Booking not found'} onRetry={() => refetch()} />
      </div>
    );
  }

  if (showSuccess) {
    return (
      <div className="py-12 max-w-lg mx-auto text-center">
        <div className="rounded-2xl border border-stone-200/80 bg-white shadow-sm p-8 sm:p-12">
        <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-6">
          <CheckCircle2 className="w-10 h-10" />
        </div>
        <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2">Booking confirmed</h1>
        <p className="text-stone-600 mb-4">Thank you for your booking. A confirmation has been sent to your email.</p>
        <div className="p-4 rounded-xl bg-amber-50/50 border border-amber-200/60 mb-6">
          <p className="font-mono font-semibold text-stone-900">{booking?.uuid || uuid}</p>
          <p className="text-sm text-stone-600 mt-1">Your booking reference</p>
        </div>
        <div className="flex flex-col sm:flex-row gap-3 justify-center">
          {user ? (
            <Link to="/profile" className="px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-colors">
              View my bookings
            </Link>
          ) : (
            <Link to="/" className="px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-colors">
              Back to home
            </Link>
          )}
        </div>
        </div>
      </div>
    );
  }

  return (
    <div className="py-6">
      <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 mb-6">Checkout</h1>

      <div className="flex flex-col lg:flex-row gap-8">
        {/* Left: Booking summary */}
        <div className="flex-1">
          <div className="rounded-2xl border border-stone-200/80 bg-white shadow-sm p-6">
            <h2 className="font-semibold text-stone-900 mb-4">Booking summary</h2>
            <p className="text-stone-600 mb-1">
              <span className="font-mono font-medium text-stone-900">{booking?.uuid || uuid}</span>
            </p>
            {booking?.hotel && (
              <p className="text-stone-600 mb-1">{booking.hotel.name}</p>
            )}
            {booking?.guest_name && (
              <p className="text-stone-600 mb-1">Guest: {booking.guest_name}</p>
            )}
            {booking?.check_in && booking?.check_out && (
              <p className="text-stone-600 mb-4">{formatDate(booking.check_in)} – {formatDate(booking.check_out)}</p>
            )}
            {booking?.booking_rooms?.length > 0 && (
              <ul className="text-sm text-stone-600 mb-4">
                {booking.booking_rooms.map((br) => (
                  <li key={br.id}>
                    {br.room?.name} × {br.quantity}
                  </li>
                ))}
              </ul>
            )}
            {booking?.total_price != null && (
              <div className="border-t border-stone-200 pt-4 space-y-2">
                {booking.subtotal != null && (
                  <div className="flex justify-between text-sm">
                    <span className="text-stone-600">Subtotal</span>
                    <span>{formatPrice(booking.subtotal, booking.currency)}</span>
                  </div>
                )}
                {(booking.discount_amount ?? 0) > 0 && (
                  <div className="flex justify-between text-sm text-green-600">
                    <span>Discount</span>
                    <span>-{formatPrice(booking.discount_amount, booking.currency)}</span>
                  </div>
                )}
                {(booking.late_checkout_amount ?? 0) > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-stone-600">Late checkout</span>
                    <span>{formatPrice(booking.late_checkout_amount, booking.currency)}</span>
                  </div>
                )}
                {(booking.tax_amount ?? 0) > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-stone-600">{booking.hotel?.tax_name || 'Tax'}</span>
                    <span>{formatPrice(booking.tax_amount, booking.currency)}</span>
                  </div>
                )}
                <div className="flex justify-between font-semibold text-stone-900 pt-2">
                  <span>Total</span>
                  <span>{formatPrice(booking.total_price, booking.currency)}</span>
                </div>
              </div>
            )}
            {booking?.cancellation_policy_summary && (
              <p className="text-sm text-stone-500 mt-4">{booking.cancellation_policy_summary}</p>
            )}
          </div>
        </div>

        {/* Right: Payment form */}
        <div className="lg:w-96 shrink-0">
            <div className="rounded-2xl border border-stone-200/80 bg-white shadow-sm p-6 sticky top-24">
            <div className="flex items-center gap-2 text-stone-600 text-sm mb-4">
              <Lock className="w-4 h-4" />
              <span>Secure payment</span>
            </div>
            <p className="text-stone-600 text-sm mb-4">
              You will be redirected to Stripe Checkout to complete your payment securely.
            </p>

            {paymentError && (
              <div className="mb-4">
                <ErrorMessage message={paymentError} onRetry={handleRetry} />
              </div>
            )}

            <button
              type="button"
              onClick={handlePayClick}
              disabled={isProcessing}
              className="w-full py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-colors"
            >
              {isProcessing ? (
                <>
                  <Loader2 className="w-5 h-5 animate-spin" />
                  Processing…
                </>
              ) : (
                'Pay Now'
              )}
            </button>
          </div>
        </div>
      </div>

      <Link
        to={user ? '/profile' : '/'}
        className="inline-block mt-6 text-stone-600 hover:text-stone-900 text-sm"
      >
        ← Back to {user ? 'profile' : 'home'}
      </Link>
    </div>
  );
}
