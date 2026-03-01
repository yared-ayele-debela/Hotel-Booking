import { Link, useParams } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';

const CATEGORY_LABELS = { billing: 'Billing', booking: 'Booking', technical: 'Technical', other: 'Other' };

export default function SupportTicketDetail() {
  const { user } = useAuth();
  const { id } = useParams();
  const { data, isLoading, isError, error, refetch } = useQuery({
    queryKey: ['support-ticket', id],
    queryFn: async () => {
      const res = await api.get(`/support-tickets/${id}`);
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load ticket');
      return res.data;
    },
    enabled: !!id && !!user,
  });

  const ticket = data?.data;

  if (!user) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Support ticket</h1>
        <p className="text-stone-600">Please log in to view this ticket.</p>
        <Link to="/login" className="text-amber-600 underline mt-2 inline-block">Log in</Link>
      </div>
    );
  }

  if (isLoading) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Support ticket</h1>
        <p className="text-stone-600">Loading…</p>
      </div>
    );
  }
  if (isError || !ticket) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Support ticket</h1>
        <ErrorMessage
          message={error?.response?.data?.message || error?.message || 'Ticket not found'}
          onRetry={() => refetch()}
        />
        <Link to="/support" className="text-amber-600 underline mt-4 inline-block">Back to Support</Link>
      </div>
    );
  }

  const replies = ticket.replies || [];

  return (
    <div className="py-6 max-w-2xl">
      <p className="mb-6">
        <Link to="/support" className="text-amber-600 hover:underline">← Back to Support</Link>
      </p>
      <div className="rounded-xl border border-stone-200 bg-white overflow-hidden">
        <div className="p-4 sm:p-6 border-b border-stone-200">
          <div className="flex flex-wrap items-center gap-2 mb-2">
            <h1 className="text-xl font-bold text-stone-900">{ticket.subject}</h1>
            <span className="text-xs px-2 py-0.5 rounded bg-stone-100 text-stone-600">
              {CATEGORY_LABELS[ticket.category] || ticket.category_label || ticket.category}
            </span>
            <span className="text-xs px-2 py-0.5 rounded bg-stone-200 text-stone-700">{ticket.status}</span>
          </div>
          <p className="text-sm text-stone-500">#{ticket.id} · Created {ticket.created_at ? new Date(ticket.created_at).toLocaleString() : ''}</p>
          <div className="mt-4 p-3 rounded-lg bg-stone-50 text-stone-800 whitespace-pre-wrap">{ticket.body}</div>
        </div>
        <div className="p-4 sm:p-6">
          <h2 className="font-semibold text-stone-900 mb-3">Replies</h2>
          {replies.length === 0 ? (
            <p className="text-stone-600 text-sm">No replies yet. Support will respond here and we’ll email you.</p>
          ) : (
            <ul className="space-y-4">
              {replies.map((r) => (
                <li key={r.id} className="pl-3 border-l-2 border-amber-200">
                  <p className="text-sm text-stone-600">
                    <span className="font-medium text-stone-800">{r.user?.name ?? 'Support'}</span>
                    {' · '}
                    {r.created_at ? new Date(r.created_at).toLocaleString() : ''}
                  </p>
                  <div className="mt-1 text-stone-800 whitespace-pre-wrap">{r.body}</div>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
    </div>
  );
}
