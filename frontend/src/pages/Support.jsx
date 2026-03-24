import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { PlusCircle, Headphones, MessageSquare } from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';
import { Skeleton } from '../components/ui/Skeleton';

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
      <div className="w-full py-6 sm:py-8">
        <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2">Support</h1>
        <p className="text-stone-600 mb-8">Create a ticket for billing, booking, or technical help.</p>
        <div className="space-y-4">
          {[1, 2, 3].map((i) => (
            <Skeleton key={i} className="h-24 rounded-2xl" />
          ))}
        </div>
      </div>
    );
  }
  if (isError) {
    return (
      <div className="w-full py-6 sm:py-8">
        <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 mb-6">Support</h1>
        <ErrorMessage message={error?.response?.data?.message || error?.message || 'Could not load tickets'} onRetry={() => refetch()} />
      </div>
    );
  }

  return (
    <div className="w-full py-6 sm:py-8">
      <div className="flex flex-row sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 className="text-2xl sm:text-3xl font-bold text-stone-900">Support</h1>
          <p className="text-stone-600 mt-1 max-w-2xl">Create a ticket for billing, booking, or technical help. We&apos;ll reply here and email you.</p>
        </div>
        <Link
          to="/support/new"
          className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-colors shrink-0"
        >
          <PlusCircle className="w-5 h-5" />
          New ticket
        </Link>
      </div>

      {tickets.length === 0 ? (
        <div className="rounded-2xl border border-stone-200/80 bg-white shadow-sm p-12 sm:p-16 text-center">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 text-amber-600 mb-6">
            <Headphones className="w-8 h-8" />
          </div>
          <h3 className="text-lg font-semibold text-stone-900 mb-2">No support tickets yet</h3>
          <p className="text-stone-600 mb-8 max-w-sm mx-auto">
            Have a question or need help? Create a ticket and we&apos;ll get back to you as soon as possible.
          </p>
          <Link
            to="/support/new"
            className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-colors"
          >
            <PlusCircle className="w-5 h-5" />
            Create your first ticket
          </Link>
        </div>
      ) : (
        <ul className="w-full list-none space-y-4 p-0 m-0">
          {tickets.map((t) => (
            <li key={t.id} className="w-full min-w-0">
              <Link
                to={`/support/${t.id}`}
                className="block w-full min-w-0 rounded-2xl border border-stone-200/80 bg-white shadow-sm p-5 sm:p-6 hover:shadow-xl hover:border-amber-200/60 transition-all duration-300"
              >
                <div className="flex flex-wrap items-center gap-2 mb-2">
                  <span className="font-semibold text-stone-900">{t.subject}</span>
                  <span className="px-2.5 py-0.5 rounded-lg bg-amber-100 text-amber-800 text-xs font-medium">
                    {CATEGORY_LABELS[t.category] || t.category_label || t.category}
                  </span>
                  <span className="px-2.5 py-0.5 rounded-lg bg-stone-100 text-stone-700 text-xs font-medium">{t.status}</span>
                </div>
                <p className="text-sm text-stone-600 line-clamp-1">{t.body}</p>
                <p className="text-xs text-stone-500 mt-3 flex items-center gap-1">
                  <MessageSquare className="w-3.5 h-3.5" />
                  #{t.id} · {t.replies_count != null ? `${t.replies_count} repl${t.replies_count === 1 ? 'y' : 'ies'}` : ''} · {t.created_at ? new Date(t.created_at).toLocaleDateString() : ''}
                </p>
              </Link>
            </li>
          ))}
        </ul>
      )}
      {meta.last_page > 1 && (
        <p className="text-sm text-stone-500 mt-6">
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
      <div className="py-12 sm:py-16">
        <div className="rounded-2xl border border-stone-200/80 bg-white shadow-sm p-8 sm:p-12 text-center max-w-md mx-auto">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-stone-100 text-stone-400 mb-4">
            <Headphones className="w-8 h-8" />
          </div>
          <h2 className="text-xl font-semibold text-stone-900 mb-2">Sign in to access support</h2>
          <p className="text-stone-600 mb-6">Log in to view and create support tickets.</p>
          <Link
            to="/login"
            className="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-colors"
          >
            Log in
          </Link>
        </div>
      </div>
    );
  }

  return <SupportContent />;
}
