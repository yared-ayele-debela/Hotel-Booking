import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ArrowLeft, Loader2 } from 'lucide-react';
import { useMutation } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';

const CATEGORIES = [
  { value: 'billing', label: 'Billing' },
  { value: 'booking', label: 'Booking' },
  { value: 'technical', label: 'Technical' },
  { value: 'other', label: 'Other' },
];

const PRIORITIES = [
  { value: 'low', label: 'Low' },
  { value: 'normal', label: 'Normal' },
  { value: 'high', label: 'High' },
];

export default function SupportTicketNew() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [subject, setSubject] = useState('');
  const [body, setBody] = useState('');
  const [category, setCategory] = useState('other');
  const [priority, setPriority] = useState('normal');
  const [validationErrors, setValidationErrors] = useState({});

  const createMutation = useMutation({
    mutationFn: async (payload) => {
      const res = await api.post('/support-tickets', payload);
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to create ticket');
      return res.data;
    },
    onSuccess: (data) => {
      const ticketId = data?.data?.id;
      navigate(ticketId ? `/support/${ticketId}` : '/support');
    },
    onError: (err) => {
      const errors = err.response?.data?.errors;
      if (errors && typeof errors === 'object') setValidationErrors(errors);
      else setValidationErrors({});
    },
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    setValidationErrors({});
    createMutation.mutate({
      subject: subject.trim(),
      body: body.trim(),
      category,
      priority,
    });
  };

  if (!user) {
    return (
      <div className="py-12 sm:py-16">
        <div className="rounded-2xl border border-stone-200/80 bg-white shadow-sm p-8 sm:p-12 text-center max-w-md mx-auto">
          <h2 className="text-xl font-semibold text-stone-900 mb-2">Sign in to create a ticket</h2>
          <p className="text-stone-600 mb-6">Please log in to create a support ticket.</p>
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

  const errMsg = createMutation.error && !(createMutation.error.response?.data?.errors)
    ? (createMutation.error.response?.data?.message || createMutation.error.message)
    : null;

  return (
    <div className="py-6 sm:py-8">
      <Link
        to="/support"
        className="inline-flex items-center gap-2 text-stone-600 hover:text-amber-600 font-medium mb-6 transition-colors"
      >
        <ArrowLeft className="w-4 h-4" />
        Back to Support
      </Link>

      <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2">New support ticket</h1>
      <p className="text-stone-600 mb-8 max-w-2xl">Describe your issue and we&apos;ll get back to you as soon as possible.</p>

      <form onSubmit={handleSubmit} className="rounded-2xl border border-stone-200/80 bg-white shadow-sm p-6 sm:p-8 space-y-5 max-w-xl">
        <div>
          <label htmlFor="category" className="block text-sm font-medium text-stone-700 mb-1.5">Category</label>
          <select
            id="category"
            value={category}
            onChange={(e) => setCategory(e.target.value)}
            className="w-full h-12 rounded-xl border border-stone-200 px-4 py-2.5 bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
            required
          >
            {CATEGORIES.map((c) => (
              <option key={c.value} value={c.value}>{c.label}</option>
            ))}
          </select>
          {validationErrors.category && <p className="text-sm text-red-600 mt-1">{validationErrors.category[0]}</p>}
        </div>
        <div>
          <label htmlFor="subject" className="block text-sm font-medium text-stone-700 mb-1.5">Subject</label>
          <input
            id="subject"
            type="text"
            value={subject}
            onChange={(e) => setSubject(e.target.value)}
            className="w-full h-12 rounded-xl border border-stone-200 px-4 py-2.5 text-stone-900 placeholder-stone-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white"
            placeholder="Short summary of your issue"
            maxLength={255}
            required
          />
          {validationErrors.subject && <p className="text-sm text-red-600 mt-1">{validationErrors.subject[0]}</p>}
        </div>
        <div>
          <label htmlFor="body" className="block text-sm font-medium text-stone-700 mb-1.5">Message</label>
          <textarea
            id="body"
            value={body}
            onChange={(e) => setBody(e.target.value)}
            className="w-full rounded-xl border border-stone-200 px-4 py-3 min-h-[120px] text-stone-900 placeholder-stone-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white resize-none"
            placeholder="Describe your issue or question in detail..."
            maxLength={10000}
            required
          />
          {validationErrors.body && <p className="text-sm text-red-600 mt-1">{validationErrors.body[0]}</p>}
        </div>
        <div>
          <label htmlFor="priority" className="block text-sm font-medium text-stone-700 mb-1.5">Priority</label>
          <select
            id="priority"
            value={priority}
            onChange={(e) => setPriority(e.target.value)}
            className="w-full h-12 rounded-xl border border-stone-200 px-4 py-2.5 bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
          >
            {PRIORITIES.map((p) => (
              <option key={p.value} value={p.value}>{p.label}</option>
            ))}
          </select>
        </div>
        {errMsg && <ErrorMessage message={errMsg} />}
        <div className="flex gap-3 pt-2">
          <button
            type="submit"
            disabled={createMutation.isPending}
            className="px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 disabled:opacity-50 flex items-center gap-2 transition-colors"
          >
            {createMutation.isPending ? (
              <>
                <Loader2 className="w-5 h-5 animate-spin" />
                Submitting…
              </>
            ) : (
              'Submit ticket'
            )}
          </button>
          <Link
            to="/support"
            className="px-6 py-3 rounded-xl border border-stone-200 text-stone-700 font-medium hover:bg-amber-50 transition-colors"
          >
            Cancel
          </Link>
        </div>
      </form>
    </div>
  );
}
