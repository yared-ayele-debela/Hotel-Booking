import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';

const CATEGORY_LABELS = {
  billing: 'Billing',
  booking: 'Booking',
  technical: 'Technical',
  other: 'Other',
};

function SupportContent() {
  const { data, isLoading, isError, error, refetch } = useQuery({
    queryKey: ['support-tickets'],
    queryFn: async () => {
      const res = await api.get('/support-tickets');
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load tickets');
      return res.data;
    },
  });

  const payload = data?.data;
  const tickets = Array.isArray(payload?.data) ? payload.data : [];
  const meta = payload?.meta || {};

  if (isLoading) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Support</h1>
        <p className="text-stone-600">Loading…</p>
      </div>
    );
  }
  if (isError) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Support</h1>
        <ErrorMessage message={error?.response?.data?.message || error?.message || 'Could not load tickets'} onRetry={() => refetch()} />
      </div>
    );
  }

  return (
    <div className="py-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 className="text-2xl font-bold text-stone-900">Support</h1>
        <Link
          to="/support/new"
          className="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700"
        >
          New ticket
        </Link>
      </div>
      <p className="text-stone-600 mb-6">Create a ticket for billing, booking, or technical help. We’ll reply here and email you.</p>
      {tickets.length === 0 ? (
        <div className="rounded-xl border border-stone-200 bg-white p-8 text-center">
          <p className="text-stone-600 mb-4">You don’t have any support tickets yet.</p>
          <Link to="/support/new" className="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700">
            Create your first ticket
          </Link>
        </div>
      ) : (
        <ul className="space-y-3">
          {tickets.map((t) => (
            <li key={t.id} className="rounded-xl border border-stone-200 bg-white p-4 hover:border-amber-200 transition-colors">
              <Link to={`/support/${t.id}`} className="block">
                <div className="flex flex-wrap items-center gap-2 mb-1">
                  <span className="font-medium text-stone-900">{t.subject}</span>
                  <span className="text-xs px-2 py-0.5 rounded bg-stone-100 text-stone-600">
                    {CATEGORY_LABELS[t.category] || t.category_label || t.category}
                  </span>
                  <span className="text-xs px-2 py-0.5 rounded bg-stone-200 text-stone-700">{t.status}</span>
                </div>
                <p className="text-sm text-stone-600 line-clamp-1">{t.body}</p>
                <p className="text-xs text-stone-500 mt-2">
                  #{t.id} · {t.replies_count != null ? `${t.replies_count} repl${t.replies_count === 1 ? 'y' : 'ies'}` : ''} · {t.created_at ? new Date(t.created_at).toLocaleDateString() : ''}
                </p>
              </Link>
            </li>
          ))}
        </ul>
      )}
      {meta.last_page > 1 && (
        <p className="text-sm text-stone-500 mt-4">
          Page {meta.current_page} of {meta.last_page} · {meta.total} ticket{meta.total !== 1 ? 's' : ''}
        </p>
      )}
    </div>
  );
}

export default function Support() {
  const { user } = useAuth();

  if (!user) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Support</h1>
        <p className="text-stone-600">Please log in to view and create support tickets.</p>
        <Link to="/login" className="text-amber-600 underline mt-2 inline-block">Log in</Link>
      </div>
    );
  }

  return <SupportContent />;
}
