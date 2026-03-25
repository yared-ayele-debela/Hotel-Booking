import { useState } from 'react';
import { useParams, Link, useLocation, useSearchParams } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import { Lock, CheckCircle2, Loader2, AlertTriangle } from 'lucide-react';
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

  const submitGuestDispute = async (e) => {
    e.preventDefault();
    if (!guestDisputeUrl || disputeNotes.trim().length < 20) {
      setDisputeError('Please describe the issue in at least 20 characters.');
      return;
    }
    setDisputeError(null);
    setDisputeSubmitting(true);
    try {
      await axios.post(
        guestDisputeUrl,
        {
          customer_notes: disputeNotes.trim(),
          contact_name: disputeName.trim(),
          contact_email: disputeEmail.trim(),
          contact_phone: disputePhone.trim() || undefined,
        },
        { headers: { 'Content-Type': 'application/json', Accept: 'application/json' } }
      );
      setDisputeOpen(false);
      setDisputeNotes('');
      alert('Your dispute was submitted. We will get back to you by email.');
    } catch (err) {
      const msg =
        err?.response?.data?.message ||
        err?.response?.data?.errors?.customer_notes?.[0] ||
        err?.message ||
        'Could not submit dispute';
      setDisputeError(msg);
    } finally {
      setDisputeSubmitting(false);
    }
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
        <p className="text-[#5c5852] mb-2">This checkout session has expired or the page was refreshed.</p>
        <p className="text-[#5c5852] mb-4">We'll send a link to your email after you complete the booking — use that link to view and pay.</p>
        <Link to="/hotels" className="text-[#b8860b] underline hover:text-[#996f09]">Search hotels</Link>
      </div>
    );
  }

  if (!stateBooking && !booking && isLoading) {
    return (
      <div className="py-6 flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-[#b8860b]600" />
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
        <div className="rounded-2xl border border-[#e8e4dd] bg-white shadow-[0_4px_12px_rgb(26_26_26_/0.06)] p-8 sm:p-12">
        <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-6">
          <CheckCircle2 className="w-10 h-10" />
        </div>
        <h1 className="text-2xl sm:text-3xl font-bold text-[#1a1a1a] mb-2">Booking confirmed</h1>
        <p className="text-[#5c5852] mb-4">Thank you for your booking. A confirmation has been sent to your email.</p>
        <div className="p-4 rounded-xl bg-[#f9edd1]/60 border border-[#e5c261]/60 mb-6">
          <p className="font-mono font-semibold text-[#1a1a1a]">{booking?.uuid || uuid}</p>
          <p className="text-sm text-[#5c5852] mt-1">Your booking reference</p>
        </div>
        <div className="flex flex-col sm:flex-row gap-3 justify-center">
          {user ? (
            <Link to="/profile" className="px-6 py-3 rounded-xl bg-[#b8860b] text-white font-semibold hover:bg-[#996f09] transition-colors">
              View my bookings
            </Link>
          ) : (
            <Link to="/" className="px-6 py-3 rounded-xl bg-[#b8860b] text-white font-semibold hover:bg-[#996f09] transition-colors">
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
      <h1 className="text-2xl sm:text-3xl font-bold text-[#1a1a1a] mb-6">Checkout</h1>

      <div className="flex flex-col lg:flex-row gap-8">
        {/* Left: Booking summary */}
        <div className="flex-1">
          <div className="rounded-2xl border border-[#e8e4dd] bg-white shadow-[0_4px_12px_rgb(26_26_26_/0.06)] p-6">
            <h2 className="font-semibold text-[#1a1a1a] mb-4">Booking summary</h2>
            <p className="text-[#5c5852] mb-1">
              <span className="font-mono font-medium text-[#1a1a1a]">{booking?.uuid || uuid}</span>
            </p>
            {booking?.hotel && (
              <p className="text-[#5c5852] mb-1">{booking.hotel.name}</p>
            )}
            {booking?.guest_name && (
              <p className="text-[#5c5852] mb-1">Guest: {booking.guest_name}</p>
            )}
            {booking?.check_in && booking?.check_out && (
              <p className="text-[#5c5852] mb-4">{formatDate(booking.check_in)} – {formatDate(booking.check_out)}</p>
            )}
            {booking?.booking_rooms?.length > 0 && (
              <ul className="text-sm text-[#5c5852] mb-4">
                {booking.booking_rooms.map((br) => (
                  <li key={br.id}>
                    {br.room?.name} × {br.quantity}
                  </li>
                ))}
              </ul>
            )}
            {booking?.total_price != null && (
              <div className="border-t border-[#e8e4dd] pt-4 space-y-2">
                {booking.subtotal != null && (
                  <div className="flex justify-between text-sm">
                    <span className="text-[#5c5852]">Subtotal</span>
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
                    <span className="text-[#5c5852]">Late checkout</span>
                    <span>{formatPrice(booking.late_checkout_amount, booking.currency)}</span>
                  </div>
                )}
                {(booking.tax_amount ?? 0) > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-[#5c5852]">{booking.hotel?.tax_name || 'Tax'}</span>
                    <span>{formatPrice(booking.tax_amount, booking.currency)}</span>
                  </div>
                )}
                <div className="flex justify-between font-semibold text-[#1a1a1a] pt-2">
                  <span>Total</span>
                  <span>{formatPrice(booking.total_price, booking.currency)}</span>
                </div>
              </div>
            )}
            {booking?.cancellation_policy_summary && (
              <p className="text-sm text-[#5c5852] mt-4">{booking.cancellation_policy_summary}</p>
            )}
            {!user && guestDisputeUrl && (
              <div className="mt-4 pt-4 border-t border-[#e8e4dd]">
                <button
                  type="button"
                  onClick={() => {
                    setDisputeName(booking?.guest_name || '');
                    setDisputeEmail(booking?.guest_email || '');
                    setDisputeOpen(true);
                  }}
                  className="inline-flex items-center gap-2 text-sm font-medium text-amber-800 hover:text-amber-900"
                >
                  <AlertTriangle className="w-4 h-4 shrink-0" />
                  Report a problem with this booking
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Right: Payment form */}
        <div className="lg:w-96 shrink-0">
            <div className="rounded-2xl border border-[#e8e4dd] bg-white shadow-[0_4px_12px_rgb(26_26_26_/0.06)] p-6 sticky top-24">
            <div className="flex items-center gap-2 text-[#5c5852] text-sm mb-4">
              <Lock className="w-4 h-4" />
              <span>Secure payment</span>
            </div>
            <p className="text-[#5c5852] text-sm mb-4">
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
              className="w-full py-3 rounded-xl bg-[#b8860b] text-white font-semibold hover:bg-[#996f09] disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-colors"
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
        className="inline-block mt-6 text-[#5c5852] hover:text-[#1a1a1a] text-sm"
      >
        ← Back to {user ? 'profile' : 'home'}
      </Link>

      {!user && guestDisputeUrl && disputeOpen && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
          role="dialog"
          aria-modal="true"
          aria-labelledby="guest-dispute-title"
        >
          <div className="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6">
            <h2 id="guest-dispute-title" className="text-lg font-semibold text-[#1a1a1a] mb-2">
              Report a problem
            </h2>
            <p className="text-sm text-[#5c5852] mb-4">
              Describe your issue. Our team will review it and contact you by email.
            </p>
            <form onSubmit={submitGuestDispute} className="space-y-3">
              <div>
                <label className="block text-sm font-medium text-[#1a1a1a] mb-1">Your name</label>
                <input
                  required
                  value={disputeName}
                  onChange={(e) => setDisputeName(e.target.value)}
                  className="w-full rounded-lg border border-[#e8e4dd] px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-[#1a1a1a] mb-1">Email</label>
                <input
                  type="email"
                  required
                  value={disputeEmail}
                  onChange={(e) => setDisputeEmail(e.target.value)}
                  className="w-full rounded-lg border border-[#e8e4dd] px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-[#1a1a1a] mb-1">Phone (optional)</label>
                <input
                  value={disputePhone}
                  onChange={(e) => setDisputePhone(e.target.value)}
                  className="w-full rounded-lg border border-[#e8e4dd] px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-[#1a1a1a] mb-1">What happened?</label>
                <textarea
                  required
                  minLength={20}
                  rows={5}
                  value={disputeNotes}
                  onChange={(e) => setDisputeNotes(e.target.value)}
                  className="w-full rounded-lg border border-[#e8e4dd] px-3 py-2 text-sm"
                  placeholder="At least 20 characters…"
                />
              </div>
              {disputeError && <p className="text-sm text-red-600">{disputeError}</p>}
              <div className="flex gap-2 justify-end pt-2">
                <button
                  type="button"
                  onClick={() => setDisputeOpen(false)}
                  className="px-4 py-2 rounded-lg border border-[#e8e4dd] text-sm font-medium"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={disputeSubmitting}
                  className="px-4 py-2 rounded-lg bg-[#b8860b] text-white text-sm font-medium disabled:opacity-60"
                >
                  {disputeSubmitting ? 'Submitting…' : 'Submit'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
