import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  FileDown,
  Loader2,
  Calendar,
  Building2,
  ChevronLeft,
  ChevronRight,
  ExternalLink,
  AlertTriangle,
} from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';
import { formatPrice, formatDate } from '../lib/utils';
import { cn } from '../lib/utils';

const STATUS_LABELS = {
  pending: 'Pending',
  pending_payment: 'Awaiting Payment',
  confirmed: 'Confirmed',
  cancelled: 'Cancelled',
  completed: 'Completed',
};

const STATUS_STYLES = {
  pending: 'bg-amber-100 text-amber-800',
  pending_payment: 'bg-amber-100 text-amber-800',
  confirmed: 'bg-green-100 text-green-800',
  cancelled: 'bg-stone-200 text-stone-600',
  completed: 'bg-stone-100 text-stone-700',
};

const CANCELLABLE_STATUSES = ['pending', 'pending_payment', 'confirmed'];

function StatusBadge({ status }) {
  const label = STATUS_LABELS[status] ?? status;
  const style = STATUS_STYLES[status] ?? 'bg-stone-100 text-stone-700';
  return (
    <span
      className={cn(
        'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide',
        style
      )}
    >
      {label}
    </span>
  );
}

function BookingCardSkeleton() {
  return (
    <div className="rounded-2xl border border-stone-200 bg-white p-6 animate-pulse">
      <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div className="flex-1 space-y-3">
          <div className="h-5 w-48 bg-stone-200 rounded" />
          <div className="h-4 w-32 bg-stone-100 rounded" />
          <div className="h-4 w-24 bg-stone-100 rounded" />
          <div className="h-6 w-20 bg-stone-200 rounded mt-4" />
        </div>
        <div className="flex gap-2">
          <div className="h-10 w-24 bg-stone-100 rounded-lg" />
          <div className="h-10 w-32 bg-stone-100 rounded-lg" />
        </div>
      </div>
    </div>
  );
}

export default function MyBookings() {
  const { user } = useAuth();
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [disputeModalUuid, setDisputeModalUuid] = useState(null);
  const [disputeNotes, setDisputeNotes] = useState('');
  const [disputeContactName, setDisputeContactName] = useState('');
  const [disputeContactEmail, setDisputeContactEmail] = useState('');
  const [disputeContactPhone, setDisputeContactPhone] = useState('');
  const [disputeFormError, setDisputeFormError] = useState(null);

  const { data, isLoading, isError, error } = useQuery({
    queryKey: ['bookings', page],
    queryFn: async () => {
      const res = await api.get('/bookings', { params: { page, per_page: 10 } });
      return res.data;
    },
    enabled: !!user,
  });

  const cancelMutation = useMutation({
    mutationFn: (uuid) => api.post(`/bookings/${uuid}/cancel`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bookings'] });
    },
  });

  const disputeMutation = useMutation({
    mutationFn: ({ uuid, body }) => api.post(`/bookings/${uuid}/dispute`, body),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bookings'] });
      setDisputeModalUuid(null);
      setDisputeNotes('');
      setDisputeContactName('');
      setDisputeContactEmail('');
      setDisputeContactPhone('');
      setDisputeFormError(null);
    },
  });

  const openDisputeModal = (b) => {
    setDisputeModalUuid(b.uuid);
    setDisputeNotes('');
    setDisputeContactName(user?.name || '');
    setDisputeContactEmail(user?.email || '');
    setDisputeContactPhone('');
    setDisputeFormError(null);
  };

  const submitDispute = async (e) => {
    e.preventDefault();
    if (!disputeModalUuid || disputeNotes.trim().length < 20) {
      setDisputeFormError('Please describe the issue in at least 20 characters.');
      return;
    }
    setDisputeFormError(null);
    try {
      await disputeMutation.mutateAsync({
        uuid: disputeModalUuid,
        body: {
          customer_notes: disputeNotes.trim(),
          contact_name: disputeContactName.trim() || undefined,
          contact_email: disputeContactEmail.trim() || undefined,
          contact_phone: disputeContactPhone.trim() || undefined,
        },
      });
    } catch (err) {
      const msg =
        err?.response?.data?.message ||
        err?.response?.data?.errors?.customer_notes?.[0] ||
        err?.message ||
        'Could not submit dispute';
      setDisputeFormError(msg);
    }
  };

  const payload = data?.data ?? {};
  const bookings = Array.isArray(payload?.data) ? payload.data : (Array.isArray(data?.data) ? data.data : []);
  const meta = payload?.meta ?? data?.meta ?? {};
  const total = meta.total ?? 0;
  const lastPage = meta.last_page ?? 1;
  const currentPage = meta.current_page ?? 1;

  const openInvoice = async (uuid) => {
    try {
      const res = await api.get(`/bookings/${uuid}/invoice`, { responseType: 'blob' });
      const url = URL.createObjectURL(new Blob([res.data], { type: 'text/html' }));
      window.open(url, '_blank', 'noopener');
    } catch (e) {
      alert(e?.response?.data?.message || 'Could not load invoice');
    }
  };

  const handleCancel = async (uuid) => {
    if (!window.confirm('Are you sure you want to cancel this booking?')) return;
    try {
      await cancelMutation.mutateAsync(uuid);
    } catch (e) {
      alert(e?.response?.data?.message || 'Could not cancel booking');
    }
  };

  if (!user) {
    return (
      <div className="py-12 sm:py-16">
        <div className="rounded-2xl border border-stone-200 bg-white p-8 sm:p-12 text-center max-w-md mx-auto">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-stone-100 text-stone-400 mb-4">
            <Calendar className="w-8 h-8" />
          </div>
          <h2 className="text-xl font-semibold text-stone-900 mb-2">Sign in to view your bookings</h2>
          <p className="text-stone-600 mb-6">Log in to see your reservations and manage your stays.</p>
          <Link
            to="/login"
            className="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 transition-colors"
          >
            Log in
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="py-6 sm:py-8 lg:py-10">
      <div className="max-w-4xl mx-auto">
        <div className="mb-8 sm:mb-10">
          <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">My Bookings</h1>
          <p className="text-stone-600 mt-1">View and manage your reservations</p>
        </div>

        {isLoading && (
          <div className="space-y-4">
            {[1, 2, 3].map((i) => (
              <BookingCardSkeleton key={i} />
            ))}
          </div>
        )}

        {isError && (
          <div className="rounded-2xl border border-red-200 bg-red-50/50 p-6">
            <ErrorMessage message={error?.response?.data?.message || error?.message} />
          </div>
        )}

        {!isLoading && !isError && bookings.length === 0 && (
          <div className="rounded-2xl border border-stone-200 bg-white p-12 sm:p-16 text-center">
            <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-stone-100 text-stone-400 mb-4">
              <Calendar className="w-8 h-8" />
            </div>
            <h3 className="text-lg font-semibold text-stone-900 mb-2">No bookings yet</h3>
            <p className="text-stone-600 mb-6 max-w-sm mx-auto">
              Start exploring and book your next stay. Your reservations will appear here.
            </p>
            <Link
              to="/hotels"
              className="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 transition-colors"
            >
              Search hotels
            </Link>
          </div>
        )}

        {!isLoading && !isError && bookings.length > 0 && (
          <>
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
              <p className="text-sm text-stone-500">
                {total} {total === 1 ? 'booking' : 'bookings'} total
              </p>
            </div>

            <div className="space-y-4 sm:space-y-5">
              {bookings.map((b) => (
                <article
                  key={b.uuid || b.id}
                  className="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200"
                >
                  <div className="p-5 sm:p-6 lg:p-6">
                    <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 lg:gap-6">
                      <div className="flex-1 min-w-0">
                        <div className="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                          <h3 className="font-semibold text-stone-900 text-lg truncate">
                            {b.hotel?.name ?? 'Hotel'}
                          </h3>
                          <StatusBadge status={b.status} />
                        </div>
                        <div className="flex flex-col sm:flex-row sm:items-center sm:gap-4 gap-1 text-sm text-stone-600">
                          <span className="flex items-center gap-1.5">
                            <Calendar className="w-4 h-4 shrink-0" />
                            {formatDate(b.check_in)} – {formatDate(b.check_out)}
                          </span>
                          {b.booking_rooms?.length > 0 && (
                            <span className="flex items-center gap-1.5">
                              <Building2 className="w-4 h-4 shrink-0" />
                              {b.booking_rooms.map((br) => `${br.room?.name ?? 'Room'} × ${br.quantity}`).join(', ')}
                            </span>
                          )}
                        </div>
                        <p className="font-semibold text-stone-900 mt-3 text-lg">
                          {formatPrice(b.total_price, b.currency)}
                        </p>
                        {b.uuid && (
                          <p className="text-xs text-stone-500 mt-1 font-mono">#{b.uuid}</p>
                        )}
                        {b.dispute && (
                          <p className="text-xs text-amber-800 mt-2 font-medium">
                            Dispute: {b.dispute.status?.replace(/_/g, ' ')}
                          </p>
                        )}
                      </div>
                      <div className="flex flex-wrap gap-2 shrink-0">
                        <Link
                          to={`/checkout/${b.uuid}`}
                          className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-stone-300 bg-white hover:bg-stone-50 text-sm font-medium text-stone-700 transition-colors min-h-[44px]"
                        >
                          <ExternalLink className="w-4 h-4" />
                          View
                        </Link>
                        <button
                          type="button"
                          onClick={() => openInvoice(b.uuid)}
                          className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-stone-300 bg-white hover:bg-stone-50 text-sm font-medium text-stone-700 transition-colors min-h-[44px]"
                        >
                          <FileDown className="w-4 h-4" />
                          Invoice
                        </button>
                        {b.can_open_dispute && (
                          <button
                            type="button"
                            onClick={() => openDisputeModal(b)}
                            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-200 bg-amber-50/80 hover:bg-amber-100 text-sm font-medium text-amber-900 transition-colors min-h-[44px]"
                          >
                            <AlertTriangle className="w-4 h-4 shrink-0" />
                            Report issue
                          </button>
                        )}
                        {CANCELLABLE_STATUSES.includes(b.status) && (
                          <button
                            type="button"
                            onClick={() => handleCancel(b.uuid)}
                            disabled={cancelMutation.isPending && cancelMutation.variables === b.uuid}
                            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-red-200 bg-white hover:bg-red-50 text-sm font-medium text-red-700 disabled:opacity-50 transition-colors min-h-[44px]"
                          >
                            {cancelMutation.isPending && cancelMutation.variables === b.uuid ? (
                              <Loader2 className="w-4 h-4 animate-spin" />
                            ) : null}
                            Cancel
                          </button>
                        )}
                      </div>
                    </div>
                  </div>
                </article>
              ))}
            </div>

            {lastPage > 1 && (
              <nav
                className="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4"
                aria-label="Bookings pagination"
              >
                <p className="text-sm text-stone-600 order-2 sm:order-1">
                  Showing page {currentPage} of {lastPage}
                </p>
                <div className="flex items-center gap-2 order-1 sm:order-2">
                  <button
                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                    disabled={currentPage <= 1}
                    className="inline-flex items-center justify-center gap-1 px-4 py-2.5 rounded-xl border border-stone-300 bg-white hover:bg-stone-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium text-stone-700 transition-colors min-h-[44px]"
                  >
                    <ChevronLeft className="w-4 h-4" />
                    Previous
                  </button>
                  <button
                    onClick={() => setPage((p) => Math.min(lastPage, p + 1))}
                    disabled={currentPage >= lastPage}
                    className="inline-flex items-center justify-center gap-1 px-4 py-2.5 rounded-xl border border-stone-300 bg-white hover:bg-stone-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium text-stone-700 transition-colors min-h-[44px]"
                  >
                    Next
                    <ChevronRight className="w-4 h-4" />
                  </button>
                </div>
              </nav>
            )}
          </>
        )}

        {disputeModalUuid && (
          <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
            role="dialog"
            aria-modal="true"
            aria-labelledby="dispute-modal-title"
          >
            <div className="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 border border-stone-200">
              <h2 id="dispute-modal-title" className="text-lg font-semibold text-stone-900 mb-2">
                Report an issue
              </h2>
              <p className="text-sm text-stone-600 mb-4">
                Describe what went wrong. Our support team will review your case and reply by email.
              </p>
              <form onSubmit={submitDispute} className="space-y-3">
                <div>
                  <label className="block text-sm font-medium text-stone-800 mb-1">Name (optional)</label>
                  <input
                    value={disputeContactName}
                    onChange={(e) => setDisputeContactName(e.target.value)}
                    className="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-stone-800 mb-1">Email (optional)</label>
                  <input
                    type="email"
                    value={disputeContactEmail}
                    onChange={(e) => setDisputeContactEmail(e.target.value)}
                    className="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-stone-800 mb-1">Phone (optional)</label>
                  <input
                    value={disputeContactPhone}
                    onChange={(e) => setDisputeContactPhone(e.target.value)}
                    className="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-stone-800 mb-1">Details</label>
                  <textarea
                    required
                    minLength={20}
                    rows={5}
                    value={disputeNotes}
                    onChange={(e) => setDisputeNotes(e.target.value)}
                    className="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"
                    placeholder="At least 20 characters…"
                  />
                </div>
                {disputeFormError && <p className="text-sm text-red-600">{disputeFormError}</p>}
                <div className="flex gap-2 justify-end pt-2">
                  <button
                    type="button"
                    onClick={() => setDisputeModalUuid(null)}
                    className="px-4 py-2 rounded-xl border border-stone-300 text-sm font-medium text-stone-700"
                  >
                    Close
                  </button>
                  <button
                    type="submit"
                    disabled={disputeMutation.isPending}
                    className="px-4 py-2 rounded-xl bg-amber-600 text-white text-sm font-medium hover:bg-amber-700 disabled:opacity-60"
                  >
                    {disputeMutation.isPending ? 'Submitting…' : 'Submit dispute'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
