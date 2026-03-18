import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, Loader2 } from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';
import { Skeleton } from '../components/ui/Skeleton';

const CATEGORY_LABELS = { billing: 'Billing', booking: 'Booking', technical: 'Technical', other: 'Other' };

export default function SupportTicketDetail() {
  const { user } = useAuth();
  const { id } = useParams();
  const queryClient = useQueryClient();
  const [replyBody, setReplyBody] = useState('');
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

  const replyMutation = useMutation({
    mutationFn: async (body) => {
      const res = await api.post(`/support-tickets/${id}/replies`, { body });
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to send reply');
      return res.data;
    },
    onSuccess: () => {
      setReplyBody('');
      queryClient.invalidateQueries({ queryKey: ['support-ticket', id] });
      queryClient.invalidateQueries({ queryKey: ['support-tickets'] });
    },
  });

  const handleReplySubmit = (e) => {
    e.preventDefault();
    const body = replyBody.trim();
    if (!body) return;
    replyMutation.mutate(body);
  };

  if (!user) {
    return (
      <div className="py-12 sm:py-16">
        <div className="rounded-2xl border border-stone-200/80 bg-white shadow-sm p-8 sm:p-12 text-center max-w-md mx-auto">
          <h2 className="text-xl font-semibold text-stone-900 mb-2">Sign in to view this ticket</h2>
          <p className="text-stone-600 mb-6">Please log in to view support ticket details.</p>
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

  if (isLoading) {
    return (
      <div className="py-6 sm:py-8">
        <Skeleton className="h-6 w-48 mb-6" />
        <Skeleton className="h-64 rounded-2xl mb-4" />
        <Skeleton className="h-32 rounded-2xl" />
      </div>
    );
  }
  if (isError || !ticket) {
    return (
      <div className="py-6 sm:py-8">
        <Link to="/support" className="inline-flex items-center gap-2 text-stone-600 hover:text-amber-600 font-medium mb-6 transition-colors">
          <ArrowLeft className="w-4 h-4" />
          Back to Support
        </Link>
        <ErrorMessage
          message={error?.response?.data?.message || error?.message || 'Ticket not found'}
          onRetry={() => refetch()}
        />
      </div>
    );
  }

  const replies = ticket.replies || [];

  return (
    <div className="py-6 sm:py-8 max-w-2xl">
      <Link
        to="/support"
        className="inline-flex items-center gap-2 text-stone-600 hover:text-amber-600 font-medium mb-6 transition-colors"
      >
        <ArrowLeft className="w-4 h-4" />
        Back to Support
      </Link>

      <div className="rounded-2xl border border-stone-200/80 bg-white shadow-sm overflow-hidden">
        <div className="p-5 sm:p-6 border-b border-stone-200">
          <div className="flex flex-wrap items-center gap-2 mb-2">
            <h1 className="text-xl sm:text-2xl font-bold text-stone-900">{ticket.subject}</h1>
            <span className="px-2.5 py-0.5 rounded-lg bg-amber-100 text-amber-800 text-xs font-medium">
              {CATEGORY_LABELS[ticket.category] || ticket.category_label || ticket.category}
            </span>
            <span className="px-2.5 py-0.5 rounded-lg bg-stone-100 text-stone-700 text-xs font-medium">{ticket.status}</span>
          </div>
          <p className="text-sm text-stone-500">#{ticket.id} · Created {ticket.created_at ? new Date(ticket.created_at).toLocaleString() : ''}</p>
        </div>
        <div className="p-5 sm:p-6">
          <h2 className="font-semibold text-stone-900 mb-4">Messages</h2>
          <ul className="space-y-4">
            <li className="pl-4 border-l-2 border-amber-400 rounded-r-lg py-2">
              <p className="text-sm text-stone-600 mb-1">
                <span className="font-medium text-stone-800">{user?.name ?? 'You'}</span>
                {' · '}
                {ticket.created_at ? new Date(ticket.created_at).toLocaleString() : ''}
              </p>
              <div className="text-stone-800 whitespace-pre-wrap">{ticket.body}</div>
            </li>
            {replies.map((r) => (
              <li key={r.id} className="pl-4 border-l-2 border-stone-300 rounded-r-lg py-2">
                <p className="text-sm text-stone-600 mb-1">
                  <span className="font-medium text-stone-800">{r.user?.name ?? 'Support'}</span>
                  {' · '}
                  {r.created_at ? new Date(r.created_at).toLocaleString() : ''}
                </p>
                <div className="text-stone-800 whitespace-pre-wrap">{r.body}</div>
              </li>
            ))}
          </ul>
          {replies.length === 0 && (
            <p className="text-stone-500 text-sm mt-4">No replies yet. Support will respond here and we&apos;ll email you.</p>
          )}
        </div>
        {ticket.status !== 'closed' && (
          <div className="p-5 sm:p-6 border-t border-stone-200 bg-stone-50/50">
            <h2 className="font-semibold text-stone-900 mb-3">Add a reply</h2>
            <form onSubmit={handleReplySubmit} className="space-y-4">
              <textarea
                value={replyBody}
                onChange={(e) => setReplyBody(e.target.value)}
                placeholder="Type your reply..."
                className="w-full rounded-xl border border-stone-200 px-4 py-3 min-h-[100px] bg-white text-stone-900 placeholder-stone-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 resize-none"
                maxLength={10000}
                required
              />
              {replyMutation.error && (
                <ErrorMessage message={replyMutation.error?.response?.data?.message || replyMutation.error?.message} />
              )}
              <button
                type="submit"
                disabled={replyMutation.isPending || !replyBody.trim()}
                className="px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 disabled:opacity-50 flex items-center gap-2 transition-colors"
              >
                {replyMutation.isPending ? (
                  <>
                    <Loader2 className="w-5 h-5 animate-spin" />
                    Sending…
                  </>
                ) : (
                  'Send reply'
                )}
              </button>
            </form>
          </div>
        )}
      </div>
    </div>
  );
}
