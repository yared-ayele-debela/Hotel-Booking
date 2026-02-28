import { Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';

function WishlistContent() {
  const queryClient = useQueryClient();
  const { data, isLoading, isError, error, refetch } = useQuery({
    queryKey: ['wishlist'],
    queryFn: async () => {
      const res = await api.get('/wishlist');
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load');
      return res.data;
    },
  });

  const removeMutation = useMutation({
    mutationFn: (hotelId) => api.delete(`/wishlist/${hotelId}`),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['wishlist'] }),
  });

  const items = data?.data?.data ?? data?.data ?? [];

  if (isLoading) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Wishlist</h1>
        <p className="text-stone-600">Loading…</p>
      </div>
    );
  }
  if (isError) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Wishlist</h1>
        <ErrorMessage message={error?.response?.data?.message || error?.message || 'Could not load wishlist'} onRetry={() => refetch()} />
      </div>
    );
  }

  return (
    <div className="py-6">
      <h1 className="text-2xl font-bold text-stone-900 mb-6">Wishlist</h1>
      {items.length === 0 ? (
        <p className="text-stone-600">You haven’t saved any hotels yet. Browse <Link to="/hotels" className="text-amber-600 underline">hotels</Link> and tap the heart to add them here.</p>
      ) : (
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((item) => {
            const h = item.hotel || item;
            const hotelId = item.hotel_id ?? h.id;
            return (
              <div key={item.id || hotelId} className="rounded-xl border border-stone-200 bg-white overflow-hidden shadow-sm hover:shadow-md flex flex-col">
                <Link to={`/hotels/${hotelId}`} className="flex-1 block">
                  <div className="h-40 bg-stone-200" />
                  <div className="p-4">
                    <h2 className="font-semibold text-stone-900">{h.name}</h2>
                    <p className="text-sm text-stone-600">{[h.city, h.country].filter(Boolean).join(', ') || '—'}</p>
                    {h.average_rating != null && <p className="text-sm mt-1">★ {h.average_rating}</p>}
                  </div>
                </Link>
                <div className="p-4 pt-0 flex gap-2">
                  <Link
                    to={`/hotels/${hotelId}`}
                    className="flex-1 text-center px-4 py-2 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700"
                  >
                    View & book
                  </Link>
                  <button
                    type="button"
                    onClick={() => removeMutation.mutate(hotelId)}
                    disabled={removeMutation.isPending}
                    className="px-4 py-2 rounded-lg border border-stone-300 text-stone-600 hover:bg-stone-100 disabled:opacity-50"
                    aria-label="Remove from wishlist"
                  >
                    Remove
                  </button>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}

export default function Wishlist() {
  const { user } = useAuth();

  if (!user) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Wishlist</h1>
        <p className="text-stone-600">Please <Link to="/login" className="text-amber-600 underline">log in</Link> to see your saved hotels.</p>
      </div>
    );
  }

  return <WishlistContent />;
}
