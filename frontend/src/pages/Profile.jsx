import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Pencil, FileDown, X, Loader2 } from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';
import { formatPrice, formatDate } from '../lib/utils';
import { cn } from '../lib/utils';

const STATUS_LABELS = {
  pending: 'Pending',
  pending_payment: 'Pending',
  confirmed: 'Confirmed',
  cancelled: 'Cancelled',
  completed: 'Completed',
};

const STATUS_STYLES = {
  pending: 'bg-amber-100 text-amber-800',
  pending_payment: 'bg-amber-100 text-amber-800',
  confirmed: 'bg-green-100 text-green-800',
  cancelled: 'bg-stone-200 text-stone-700',
  completed: 'bg-cyan-100 text-cyan-800',
};

const CANCELLABLE_STATUSES = ['pending', 'pending_payment', 'confirmed'];

function StatusBadge({ status }) {
  const label = STATUS_LABELS[status] ?? status;
  const style = STATUS_STYLES[status] ?? 'bg-stone-100 text-stone-700';
  return (
    <span className={cn('inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium', style)}>
      {label}
    </span>
  );
}

export default function Profile() {
  const { user } = useAuth();
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [editModalOpen, setEditModalOpen] = useState(false);

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

  const bookings = Array.isArray(data?.data) ? data.data : [];
  const meta = data?.meta ?? {};
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
      <div className="py-6">
        <p className="text-stone-600">Please log in to view your profile.</p>
        <Link to="/login" className="text-amber-600 underline mt-2 inline-block">Log in</Link>
      </div>
    );
  }

  return (
    <div className="py-6 max-w-3xl">
      <h1 className="text-2xl font-bold text-stone-900 mb-6">Profile</h1>

      {/* User info */}
      <section className="rounded-2xl border border-stone-200 bg-white p-6 mb-8">
        <div className="flex items-start justify-between gap-4">
          <div>
            <h2 className="font-semibold text-stone-900 text-lg">{user.name}</h2>
            <p className="text-stone-600 mt-0.5">{user.email}</p>
          </div>
          <button
            type="button"
            onClick={() => setEditModalOpen(true)}
            className="flex items-center gap-2 px-3 py-2 rounded-lg border border-stone-300 hover:bg-stone-50 text-sm font-medium text-stone-700"
            aria-label="Edit profile"
          >
            <Pencil className="w-4 h-4" />
            Edit
          </button>
        </div>
      </section>

      {/* My Bookings */}
      <section>
        <h2 className="text-lg font-semibold text-stone-900 mb-4">My Bookings</h2>

        {isLoading && (
          <div className="flex items-center justify-center py-12">
            <Loader2 className="w-8 h-8 animate-spin text-amber-600" />
          </div>
        )}

        {isError && (
          <ErrorMessage message={error?.response?.data?.message || error?.message} />
        )}

        {!isLoading && !isError && bookings.length === 0 && (
          <div className="rounded-2xl border border-stone-200 bg-white p-12 text-center">
            <p className="text-stone-600 mb-4">No bookings yet.</p>
            <Link to="/hotels" className="text-amber-600 font-medium hover:text-amber-700">
              Search hotels
            </Link>
          </div>
        )}

        {!isLoading && !isError && bookings.length > 0 && (
          <ul className="space-y-4">
            {bookings.map((b) => (
              <li
                key={b.uuid || b.id}
                className="rounded-2xl border border-stone-200 bg-white p-4 sm:p-6 hover:shadow-sm transition-shadow"
              >
                <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                  <div className="flex-1 min-w-0">
                    <div className="flex flex-wrap items-center gap-2 mb-1">
                      <h3 className="font-semibold text-stone-900">{b.hotel?.name ?? 'Hotel'}</h3>
                      <StatusBadge status={b.status} />
                    </div>
                    <p className="text-sm text-stone-600">
                      {formatDate(b.check_in)} – {formatDate(b.check_out)}
                    </p>
                    {b.booking_rooms?.length > 0 && (
                      <p className="text-sm text-stone-600 mt-0.5">
                        {b.booking_rooms.map((br) => `${br.room?.name ?? 'Room'} × ${br.quantity}`).join(', ')}
                      </p>
                    )}
                    <p className="font-semibold text-stone-900 mt-2">
                      {formatPrice(b.total_price, b.currency)}
                    </p>
                  </div>
                  <div className="flex flex-wrap gap-2 shrink-0">
                    <Link
                      to={`/checkout/${b.uuid}`}
                      className="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-stone-300 hover:bg-stone-50 text-sm font-medium text-stone-700"
                    >
                      View
                    </Link>
                    <button
                      type="button"
                      onClick={() => openInvoice(b.uuid)}
                      className="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-stone-300 hover:bg-stone-50 text-sm font-medium text-stone-700"
                    >
                      <FileDown className="w-4 h-4" />
                      Download Invoice
                    </button>
                    {CANCELLABLE_STATUSES.includes(b.status) && (
                      <button
                        type="button"
                        onClick={() => handleCancel(b.uuid)}
                        disabled={cancelMutation.isPending && cancelMutation.variables === b.uuid}
                        className="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-red-200 hover:bg-red-50 text-sm font-medium text-red-700 disabled:opacity-50"
                      >
                        {cancelMutation.isPending && cancelMutation.variables === b.uuid ? <Loader2 className="w-4 h-4 animate-spin" /> : null}
                        Cancel
                      </button>
                    )}
                  </div>
                </div>
              </li>
            ))}
          </ul>
        )}

        {/* Pagination */}
        {lastPage > 1 && (
          <nav className="mt-6 flex items-center justify-center gap-2" aria-label="Bookings pagination">
            <button
              onClick={() => setPage((p) => Math.max(1, p - 1))}
              disabled={currentPage <= 1}
              className="px-4 py-2 rounded-lg border border-stone-300 hover:bg-stone-50 disabled:opacity-50 text-sm font-medium"
            >
              Previous
            </button>
            <span className="px-4 py-2 text-stone-600 text-sm">
              Page {currentPage} of {lastPage}
            </span>
            <button
              onClick={() => setPage((p) => Math.min(lastPage, p + 1))}
              disabled={currentPage >= lastPage}
              className="px-4 py-2 rounded-lg border border-stone-300 hover:bg-stone-50 disabled:opacity-50 text-sm font-medium"
            >
              Next
            </button>
          </nav>
        )}
      </section>

      {/* Edit modal (placeholder) */}
      {editModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
          <div className="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-stone-900">Edit profile</h3>
              <button
                type="button"
                onClick={() => setEditModalOpen(false)}
                className="p-2 rounded-lg hover:bg-stone-100"
                aria-label="Close"
              >
                <X className="w-5 h-5" />
              </button>
            </div>
            <p className="text-stone-600 text-sm mb-4">
              Profile editing will be available soon. Contact support if you need to update your details.
            </p>
            <button
              type="button"
              onClick={() => setEditModalOpen(false)}
              className="w-full py-2 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700"
            >
              Close
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
