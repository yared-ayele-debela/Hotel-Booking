import { useSearchParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import { useWishlist } from '../hooks/useWishlist';
import { HotelListSkeleton } from '../components/Skeleton';
import ErrorMessage from '../components/ErrorMessage';

function WishlistHeart({ hotelId, checkIn, checkOut, className = '' }) {
  const { user } = useAuth();
  const { isInWishlist, addToWishlist, removeFromWishlist, addPending, removePending } = useWishlist(!!user);

  if (!user) {
    return (
      <Link
        to="/login"
        className={`absolute top-2 right-2 p-2 rounded-full bg-white/90 shadow hover:bg-white ${className}`}
        aria-label="Log in to save to wishlist"
      >
        <HeartIcon filled={false} />
      </Link>
    );
  }

  const inList = isInWishlist(hotelId);
  const pending = addPending || removePending;

  const handleClick = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (pending) return;
    try {
      if (inList) await removeFromWishlist(hotelId);
      else await addToWishlist({ hotelId, checkIn, checkOut });
    } catch (_) {}
  };

  return (
    <button
      type="button"
      onClick={handleClick}
      disabled={pending}
      className={`absolute top-2 right-2 p-2 rounded-full bg-white/90 shadow hover:bg-white disabled:opacity-60 ${className}`}
      aria-label={inList ? 'Remove from wishlist' : 'Add to wishlist'}
    >
      <HeartIcon filled={inList} />
    </button>
  );
}

function HeartIcon({ filled }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill={filled ? '#b45309' : 'none'} stroke="#b45309" strokeWidth="2" className="w-5 h-5">
      <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
    </svg>
  );
}

export default function HotelList() {
  const [searchParams] = useSearchParams();
  const city = searchParams.get('city') || '';
  const checkIn = searchParams.get('check_in') || '';
  const checkOut = searchParams.get('check_out') || '';
  const page = searchParams.get('page') || '1';

  const { data, isLoading, isError, error, refetch } = useQuery({
    queryKey: ['hotels', city, checkIn, checkOut, page],
    queryFn: async () => {
      const params = { page, per_page: 12 };
      if (city) params.city = city;
      if (checkIn) params.check_in = checkIn;
      if (checkOut) params.check_out = checkOut;
      const res = await api.get('/hotels', { params });
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load');
      return res.data;
    },
  });

  const rawData = data?.data;
  const hotels = Array.isArray(rawData) ? rawData : (rawData?.data ?? []);
  const meta = data?.meta ?? {};

  if (isLoading) return <div className="py-6"><h1 className="text-2xl font-bold mb-6">Search results</h1><HotelListSkeleton count={6} /></div>;
  if (isError) return <div className="py-6"><ErrorMessage message={error?.response?.data?.message || error?.message || 'Could not load hotels'} onRetry={() => refetch()} /></div>;

  return (
    <div className="py-6">
      <h1 className="text-2xl font-bold text-stone-900 mb-6">Search results</h1>
      {hotels.length === 0 ? (
        <p className="text-stone-600">No hotels found.</p>
      ) : (
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {hotels.map((h) => (
            <Link key={h.id} to={`/hotels/${h.id}${checkIn ? `?check_in=${checkIn}&check_out=${checkOut}` : ''}`} className="block rounded-xl border border-stone-200 bg-white overflow-hidden shadow-sm hover:shadow-md relative">
              <div className="h-40 bg-stone-200 relative">
                {h.banner_image ? (
                  <img 
                    src={h.banner_image.url} 
                    alt={h.banner_image.alt_text || h.name}
                    className="w-full h-full object-cover"
                  />
                ) : h.images && h.images.length > 0 ? (
                  <img 
                    src={h.images[0].url} 
                    alt={h.images[0].alt_text || h.name}
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <div className="w-full h-full bg-stone-200 flex items-center justify-center" aria-hidden="true">
                    <div className="text-center text-stone-500">
                      <svg className="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                      <p className="text-xs">No image</p>
                    </div>
                  </div>
                )}
                <WishlistHeart hotelId={h.id} checkIn={checkIn || undefined} checkOut={checkOut || undefined} />
              </div>
              <div className="p-4">
                <h2 className="font-semibold text-stone-900">{h.name}</h2>
                <p className="text-sm text-stone-600">{[h.city, h.country].filter(Boolean).join(', ') || '—'}</p>
                {h.average_rating != null && <p className="text-sm mt-1">★ {h.average_rating}</p>}
                <div className="mt-3 flex flex-wrap gap-2">
                  <span className="inline-flex items-center text-amber-600 font-medium">View details →</span>
                  <span className="text-stone-400">·</span>
                  <span className="inline-flex items-center text-amber-600 font-medium">Book</span>
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
      {meta.last_page > 1 && (
        <nav className="mt-8 flex justify-center gap-2">
          {meta.current_page > 1 && <Link to={{ search: new URLSearchParams({ ...Object.fromEntries(searchParams), page: meta.current_page - 1 }).toString() }} className="px-4 py-2 rounded-lg border">Previous</Link>}
          <span className="px-4 py-2">Page {meta.current_page} of {meta.last_page}</span>
          {meta.current_page < meta.last_page && <Link to={{ search: new URLSearchParams({ ...Object.fromEntries(searchParams), page: meta.current_page + 1 }).toString() }} className="px-4 py-2 rounded-lg border">Next</Link>}
        </nav>
      )}
    </div>
  );
}
