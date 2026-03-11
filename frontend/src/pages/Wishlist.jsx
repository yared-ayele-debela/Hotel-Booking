import { Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Heart, Loader2, X } from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import { HotelCard } from '../components/HotelCard';
import ErrorMessage from '../components/ErrorMessage';

export default function Wishlist() {
  const { user } = useAuth();
  const queryClient = useQueryClient();

  const { data, isLoading, isError, error, refetch } = useQuery({
    queryKey: ['wishlist'],
    queryFn: async () => {
      const res = await api.get('/wishlist');
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load');
      return res.data;
    },
    enabled: !!user,
  });

  const removeMutation = useMutation({
    mutationFn: (hotelId) => api.delete(`/wishlist/${hotelId}`),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['wishlist'] }),
  });

  const items = Array.isArray(data?.data) ? data.data : (data?.data?.data ?? []);

  if (!user) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Wishlist</h1>
        <p className="text-stone-600">Please <Link to="/login" className="text-amber-600 underline">log in</Link> to see your saved hotels.</p>
      </div>
    );
  }

  if (isLoading) {
    return (
      <div className="py-6">
        <h1 className="text-2xl font-bold text-stone-900 mb-6">Wishlist</h1>
        <div className="flex items-center justify-center py-12">
          <Loader2 className="w-8 h-8 animate-spin text-amber-600" />
        </div>
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
        <div className="rounded-2xl border border-stone-200 bg-white p-12 text-center">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-stone-100 text-stone-400 mb-4">
            <Heart className="w-8 h-8" />
          </div>
          <p className="text-stone-600 mb-4">Your wishlist is empty</p>
          <Link
            to="/hotels"
            className="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700"
          >
            Search hotels
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
          {items.map((item) => {
            const hotel = item.hotel || item;
            const hotelId = item.hotel_id ?? hotel.id;
            return (
              <div key={item.id || hotelId} className="relative">
                <HotelCard
                  hotel={hotel}
                  to={`/hotels/${hotelId}`}
                  imageOverlay={
                    <button
                      type="button"
                      onClick={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        removeMutation.mutate(hotelId);
                      }}
                      disabled={removeMutation.isPending}
                      className="p-2 rounded-full bg-white/90 shadow hover:bg-white disabled:opacity-60"
                      aria-label="Remove from wishlist"
                    >
                      <X className="w-5 h-5 text-stone-600" />
                    </button>
                  }
                />
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
