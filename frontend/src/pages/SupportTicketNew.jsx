import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
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
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">New support ticket</h1>
        <p className="text-stone-600">Please log in to create a support ticket.</p>
        <Link to="/login" className="text-amber-600 underline mt-2 inline-block">Log in</Link>
      </div>
    );
  }

  const errMsg = createMutation.error && !(createMutation.error.response?.data?.errors)
    ? (createMutation.error.response?.data?.message || createMutation.error.message)
    : null;

  return (
    <div className="py-6 max-w-xl">
      <h1 className="text-2xl font-bold text-stone-900 mb-2">New support ticket</h1>
      <p className="text-stone-600 mb-6">
        <Link to="/support" className="text-amber-600 hover:underline">Back to Support</Link>
      </p>
      <form onSubmit={handleSubmit} className="space-y-4 rounded-xl border border-stone-200 bg-white p-6">
        <div>
          <label htmlFor="category" className="block text-sm font-medium text-stone-700 mb-1">Category</label>
          <select
            id="category"
            value={category}
            onChange={(e) => setCategory(e.target.value)}
            className="w-full rounded-lg border border-stone-300 px-4 py-2.5 bg-white"
            required
          >
            {CATEGORIES.map((c) => (
              <option key={c.value} value={c.value}>{c.label}</option>
            ))}
          </select>
          {validationErrors.category && <p className="text-sm text-red-600 mt-1">{validationErrors.category[0]}</p>}
        </div>
        <div>
          <label htmlFor="subject" className="block text-sm font-medium text-stone-700 mb-1">Subject</label>
          <input
            id="subject"
            type="text"
            value={subject}
            onChange={(e) => setSubject(e.target.value)}
            className="w-full rounded-lg border border-stone-300 px-4 py-2.5"
            placeholder="Short summary of your issue"
            maxLength={255}
            required
          />
          {validationErrors.subject && <p className="text-sm text-red-600 mt-1">{validationErrors.subject[0]}</p>}
        </div>
        <div>
          <label htmlFor="body" className="block text-sm font-medium text-stone-700 mb-1">Message</label>
          <textarea
            id="body"
            value={body}
            onChange={(e) => setBody(e.target.value)}
            className="w-full rounded-lg border border-stone-300 px-4 py-2.5 min-h-[120px]"
            placeholder="Describe your issue or question in detail..."
            maxLength={10000}
            required
          />
          {validationErrors.body && <p className="text-sm text-red-600 mt-1">{validationErrors.body[0]}</p>}
        </div>
        <div>
          <label htmlFor="priority" className="block text-sm font-medium text-stone-700 mb-1">Priority</label>
          <select
            id="priority"
            value={priority}
            onChange={(e) => setPriority(e.target.value)}
            className="w-full rounded-lg border border-stone-300 px-4 py-2.5 bg-white"
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
            className="px-6 py-2.5 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700 disabled:opacity-50"
          >
            {createMutation.isPending ? 'Submitting…' : 'Submit ticket'}
          </button>
          <Link to="/support" className="px-6 py-2.5 rounded-lg border border-stone-300 text-stone-700 hover:bg-stone-50">
            Cancel
          </Link>
        </div>
      </form>
    </div>
  );
}
