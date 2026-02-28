import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';

export default function Profile() {
  const { user } = useAuth();

  const openInvoice = async (uuid) => {
    try {
      const res = await api.get(`/bookings/${uuid}/invoice`, { responseType: 'text' });
      const w = window.open('', '_blank');
      if (w) {
        w.document.write(res.data);
        w.document.close();
      }
    } catch (e) {
      alert(e?.response?.data?.message || 'Could not load invoice');
    }
  };

  const { data, isLoading, isError, error } = useQuery({
    queryKey: ['bookings'],
    queryFn: async () => {
      const res = await api.get('/bookings');
      return res.data;
    },
    enabled: !!user,
  });

  const bookings = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);

  if (!user) {
    return (
      <div className="py-6">
        <p className="text-stone-600">Please log in to view your profile.</p>
        <Link to="/login" className="text-amber-600 underline mt-2 inline-block">Log in</Link>
      </div>
    );
  }

  return (
    <div className="py-6">
      <h1 className="text-2xl font-bold text-stone-900 mb-4">Profile</h1>
      <div className="rounded-xl border border-stone-200 bg-white p-4 mb-6">
        <p className="font-medium text-stone-900">{user.name}</p>
        <p className="text-stone-600">{user.email}</p>
      </div>
      <h2 className="text-lg font-semibold text-stone-900 mb-3">Booking history</h2>
      {isLoading && <p className="text-stone-600">Loading…</p>}
      {isError && <ErrorMessage message={error?.response?.data?.message || error?.message} />}
      {!isLoading && !isError && bookings.length === 0 && <p className="text-stone-600">No bookings yet.</p>}
      {!isLoading && !isError && bookings.length > 0 && (
        <ul className="space-y-3">
          {bookings.map((b) => (
            <li key={b.uuid || b.id} className="p-4 rounded-xl border border-stone-200 bg-white">
              <p className="font-medium">{b.hotel?.name ?? 'Hotel'}</p>
              <p className="text-sm text-stone-600">{b.check_in} – {b.check_out} · ${b.total_price != null ? Number(b.total_price).toFixed(2) : '—'}</p>
              <p className="text-sm">Status: {b.status}</p>
              <div className="flex gap-3 mt-2">
                <Link to={`/checkout/${b.uuid}`} className="text-amber-600 text-sm">View</Link>
                <button type="button" onClick={() => openInvoice(b.uuid)} className="text-amber-600 text-sm hover:underline">Invoice</button>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
