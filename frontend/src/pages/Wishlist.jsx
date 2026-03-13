import { Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Heart, Loader2, X, MapPin, Search } from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import { HotelCard } from '../components/HotelCard';
import ErrorMessage from '../components/ErrorMessage';

function WishlistCardSkeleton() {
  return (
    <div className="rounded-2xl overflow-hidden border border-stone-200 bg-white shadow-sm">
      <div className="aspect-[4/3] bg-stone-200 animate-pulse" />
      <div className="p-4 space-y-3">
        <div className="h-5 w-3/4 bg-stone-200 rounded" />
        <div className="h-4 w-1/2 bg-stone-100 rounded" />
        <div className="h-4 w-1/3 bg-stone-100 rounded" />
        <div className="h-6 w-20 bg-stone-200 rounded mt-4" />
      </div>
    </div>
  );
}

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
      <div className="py-12 sm:py-16">
        <div className="rounded-2xl border border-stone-200 bg-white p-8 sm:p-12 text-center max-w-md mx-auto">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-stone-100 text-stone-400 mb-4">
            <Heart className="w-8 h-8" />
          </div>
          <h2 className="text-xl font-semibold text-stone-900 mb-2">Sign in to view your wishlist</h2>
          <p className="text-stone-600 mb-6">Save your favorite hotels and access them anytime.</p>
          <Link
            to="/login"
            className="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 transition-colors"
          >
            Log in
          </Link>
        </div>
      </div>
    );
  }

  if (isLoading) {
    return (
      <div className="py-6 sm:py-8 lg:py-10">
        <div className="max-w-6xl mx-auto">
          <div className="mb-8">
            <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">My Wishlist</h1>
            <p className="text-stone-600 mt-1">Your saved hotels</p>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-6">
            {[1, 2, 3, 4, 5, 6].map((i) => (
              <WishlistCardSkeleton key={i} />
            ))}
          </div>
        </div>
      </div>
    );
  }

  if (isError) {
    return (
      <div className="py-6 sm:py-8">
        <div className="max-w-6xl mx-auto">
          <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 mb-6">My Wishlist</h1>
          <div className="rounded-2xl border border-red-200 bg-red-50/50 p-6">
            <ErrorMessage
              message={error?.response?.data?.message || error?.message || 'Could not load wishlist'}
              onRetry={() => refetch()}
            />
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="py-6 sm:py-8 lg:py-10">
      <div className="max-w-6xl mx-auto">
        {/* Page header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
          <div>
            <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">My Wishlist</h1>
            <p className="text-stone-600 mt-1">
              {items.length === 0
                ? 'Save hotels you love to book them later'
                : `${items.length} ${items.length === 1 ? 'hotel' : 'hotels'} saved`}
            </p>
          </div>
          {items.length > 0 && (
            <Link
              to="/hotels"
              className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-stone-300 bg-white hover:bg-stone-50 text-sm font-medium text-stone-700 transition-colors shrink-0"
            >
              <Search className="w-4 h-4" />
              Discover more
            </Link>
          )}
        </div>

        {items.length === 0 ? (
          <div className="rounded-2xl border border-stone-200 bg-white p-12 sm:p-16 text-center">
            <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-amber-50 text-amber-500 mb-6">
              <Heart className="w-10 h-10" />
            </div>
            <h3 className="text-lg font-semibold text-stone-900 mb-2">Your wishlist is empty</h3>
            <p className="text-stone-600 mb-8 max-w-sm mx-auto">
              Browse hotels and tap the heart icon to save your favorites. They'll appear here for easy access.
            </p>
            <Link
              to="/hotels"
              className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 transition-colors"
            >
              <Search className="w-5 h-5" />
              Search hotels
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-6">
            {items.map((item) => {
              const hotel = item.hotel || item;
              const hotelId = item.hotel_id ?? hotel.id;
              const isRemoving = removeMutation.isPending && removeMutation.variables === hotelId;
              return (
                <article
                  key={item.id || hotelId}
                  className="group relative rounded-2xl overflow-hidden border border-stone-200 bg-white shadow-sm hover:shadow-xl transition-all duration-300"
                >
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
                        className={`
                          flex items-center justify-center w-10 h-10 rounded-full
                          bg-white/95 shadow-md hover:bg-white hover:shadow-lg
                          transition-all duration-200
                          disabled:opacity-60 disabled:cursor-not-allowed
                          focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2
                        `}
                        aria-label="Remove from wishlist"
                        title="Remove from wishlist"
                      >
                        {isRemoving ? (
                          <Loader2 className="w-5 h-5 text-stone-600 animate-spin" />
                        ) : (
                          <Heart className="w-5 h-5 text-amber-600 fill-amber-600" />
                        )}
                      </button>
                    }
                  >
                    <span className="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-amber-600 group-hover:text-amber-700">
                      View details
                      <span aria-hidden>→</span>
                    </span>
                  </HotelCard>
                </article>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}
