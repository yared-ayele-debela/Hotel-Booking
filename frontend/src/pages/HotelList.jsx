import { useSearchParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import { useWishlist } from '../hooks/useWishlist';
import { HotelCard } from '../components/HotelCard';
import { HotelListSkeleton } from '../components/Skeleton';
import ErrorMessage from '../components/ErrorMessage';
import { calculateNights } from '../lib/utils';

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
    } catch { /* ignore */ }
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

const REVIEW_SCORE_OPTIONS = [
  { label: 'Excellent: 5', value: 5, desc: 'Based on guest reviews' },
  { label: 'Very Good: 4+', value: 4, desc: '' },
  { label: 'Good: 3+', value: 3, desc: '' },
  { label: 'Pleasant: 2+', value: 2, desc: '' },
];

export default function HotelList() {
  const [searchParams, setSearchParams] = useSearchParams();
  const city = searchParams.get('city') || '';
  const country = searchParams.get('country') || '';
  const cityId = searchParams.get('city_id') || '';
  const countryId = searchParams.get('country_id') || '';
  const checkIn = searchParams.get('check_in') || '';
  const checkOut = searchParams.get('check_out') || '';
  const page = searchParams.get('page') || '1';
  const minRating = searchParams.get('min_rating') || '';
  const minCapacity = searchParams.get('min_capacity') || '';
  const minPrice = searchParams.get('min_price') || '';
  const maxPrice = searchParams.get('max_price') || '';
  const sort = searchParams.get('sort') || '';
  const amenitiesParam = searchParams.get('amenities') || '';
  const selectedAmenities = amenitiesParam ? amenitiesParam.split(',').filter(Boolean) : [];

  const applyFilters = (updates) => {
    const next = new URLSearchParams(searchParams);
    next.set('page', '1');
    Object.entries(updates).forEach(([k, v]) => {
      if (v === '' || v == null) next.delete(k);
      else next.set(k, String(v));
    });
    setSearchParams(next);
  };

  const { data: amenitiesData } = useQuery({
    queryKey: ['amenities'],
    queryFn: async () => {
      const res = await api.get('/amenities');
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load');
      return res.data;
    },
  });

  const amenities = Array.isArray(amenitiesData?.data) ? amenitiesData.data : amenitiesData?.data?.data ?? [];

  const { data, isLoading, isError, error, refetch } = useQuery({
    queryKey: ['hotels', city, country, cityId, countryId, checkIn, checkOut, page, minRating, minCapacity, minPrice, maxPrice, sort, selectedAmenities.join(',')],
    queryFn: async () => {
      const params = { page, per_page: 12 };
      if (cityId) params.city_id = cityId;
      else if (city) params.city = city;
      if (countryId) params.country_id = countryId;
      else if (country) params.country = country;
      if (checkIn) params.check_in = checkIn;
      if (checkOut) params.check_out = checkOut;
      if (minRating) params.min_rating = minRating;
      if (minCapacity) params.min_capacity = minCapacity;
      if (minPrice) params.min_price = minPrice;
      if (maxPrice) params.max_price = maxPrice;
      if (sort) params.sort = sort;
      if (selectedAmenities.length > 0) params.amenities = selectedAmenities;
      const res = await api.get('/hotels', { params });
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load');
      return res.data;
    },
  });

  const rawData = data?.data;
  const hotels = Array.isArray(rawData) ? rawData : (rawData?.data ?? []);
  const meta = data?.meta ?? {};
  const total = meta.total ?? hotels.length;
  const nights = checkIn && checkOut ? calculateNights(checkIn, checkOut) : null;

  const buildSearch = (overrides = {}) => {
    const p = { ...Object.fromEntries(searchParams), ...overrides };
    return new URLSearchParams(p).toString();
  };

  if (isError) {
    return (
      <div className="py-6">
        <ErrorMessage message={error?.response?.data?.message || error?.message || 'Could not load hotels'} onRetry={() => refetch()} />
      </div>
    );
  }

  return (
    <div className="py-6 flex flex-col lg:flex-row gap-6 lg:gap-8">
      {/* Left sidebar – filters */}
      <aside className="lg:w-72 shrink-0">
        <div className="sticky top-24 space-y-6 rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
          <h2 className="font-semibold text-stone-900 text-lg">Filter by</h2>

          {/* Guests */}
          <section>
            <h3 className="text-sm font-medium text-stone-700 mb-3">Guests</h3>
            <p className="text-xs text-stone-500 mb-2">Rooms that fit at least</p>
            <select
              value={minCapacity}
              onChange={(e) => applyFilters({ min_capacity: e.target.value })}
              className="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-700 bg-white"
            >
              <option value="">Any</option>
              {[1, 2, 3, 4, 5, 6, 8, 10].map((n) => (
                <option key={n} value={n}>{n} {n === 1 ? 'guest' : 'guests'}+</option>
              ))}
            </select>
          </section>

          {/* Review score */}
          <section>
            <h3 className="text-sm font-medium text-stone-700 mb-3">Review score</h3>
            <p className="text-xs text-stone-500 mb-2">Based on guest reviews</p>
            <div className="space-y-2">
              {REVIEW_SCORE_OPTIONS.map((opt) => (
                <label key={opt.value} className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="min_rating"
                    checked={minRating === String(opt.value)}
                    onChange={() => applyFilters({ min_rating: minRating === String(opt.value) ? '' : opt.value })}
                    className="rounded-full border-stone-300"
                  />
                  <span className="text-sm text-stone-700">{opt.label}</span>
                </label>
              ))}
            </div>
          </section>

          {/* Property rating (stars) – same as review score, alternative UI */}
          <section>
            <h3 className="text-sm font-medium text-stone-700 mb-3">Property rating</h3>
            <p className="text-xs text-stone-500 mb-2">Find high-quality hotels</p>
            <div className="space-y-2">
              {[5, 4, 3, 2, 1].map((stars) => (
                <label key={stars} className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="min_rating_stars"
                    checked={minRating === String(stars)}
                    onChange={() => applyFilters({ min_rating: minRating === String(stars) ? '' : stars })}
                    className="rounded-full border-stone-300"
                  />
                  <span className="text-sm text-stone-700">{stars} star{stars > 1 ? 's' : ''}</span>
                </label>
              ))}
            </div>
          </section>

          {/* Amenities */}
          {amenities.length > 0 && (
            <section>
              <h3 className="text-sm font-medium text-stone-700 mb-3">Amenities</h3>
              <div className="space-y-2 max-h-48 overflow-y-auto">
                {amenities.map((a) => (
                  <label key={a.id} className="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={selectedAmenities.includes(a.slug)}
                      onChange={() => {
                        const next = selectedAmenities.includes(a.slug)
                          ? selectedAmenities.filter((s) => s !== a.slug)
                          : [...selectedAmenities, a.slug];
                        applyFilters({ amenities: next.join(',') });
                      }}
                      className="rounded border-stone-300"
                    />
                    <span className="text-sm text-stone-700">{a.name}</span>
                  </label>
                ))}
              </div>
            </section>
          )}

          {/* Price */}
          <section>
            <h3 className="text-sm font-medium text-stone-700 mb-3">Price (per night)</h3>
            <div className="grid grid-cols-2 gap-2">
              <div>
                <label className="block text-xs text-stone-500 mb-1">Min €</label>
                <input
                  type="number"
                  min="0"
                  step="10"
                  placeholder="0"
                  value={minPrice}
                  onChange={(e) => applyFilters({ min_price: e.target.value })}
                  className="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label className="block text-xs text-stone-500 mb-1">Max €</label>
                <input
                  type="number"
                  min="0"
                  step="10"
                  placeholder="Any"
                  value={maxPrice}
                  onChange={(e) => applyFilters({ max_price: e.target.value })}
                  className="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"
                />
              </div>
            </div>
          </section>

          {/* Property type – placeholder */}
          <section>
            <h3 className="text-sm font-medium text-stone-700 mb-3">Property type</h3>
            <div className="space-y-2">
              <div className="flex items-center justify-between text-sm text-stone-600">
                <span>Hotels</span>
                <span>{total}</span>
              </div>
            </div>
          </section>

          <button
            type="button"
            onClick={() => {
              const base = { check_in: checkIn, check_out: checkOut };
              if (cityId) base.city_id = cityId;
              else if (city) base.city = city;
              if (countryId) base.country_id = countryId;
              else if (country) base.country = country;
              setSearchParams(new URLSearchParams(base));
            }}
            aria-label="Clear all filters"
            className="w-full py-2 text-sm font-medium text-amber-600 hover:text-amber-700 border border-amber-200 rounded-lg"
          >
            Clear filters
          </button>
        </div>
      </aside>

      {/* Main – breadcrumb, header, results */}
      <div className="flex-1 min-w-0">
        {/* Breadcrumb: Home > Country > City */}
        <nav className="text-sm text-stone-600 mb-4" aria-label="Breadcrumb">
          <Link to="/" className="hover:text-amber-600">Home</Link>
          {country && (
            <>
              <span className="mx-1">›</span>
              <span>{country}</span>
            </>
          )}
          {city && (
            <>
              <span className="mx-1">›</span>
              <span className="text-stone-900 font-medium">{city || 'Search results'}</span>
            </>
          )}
          {!country && !city && <span className="text-stone-900 font-medium">Search results</span>}
        </nav>

        <div className="flex flex-wrap items-center justify-between gap-4 mb-6">
          <h1 className="text-2xl font-bold text-stone-900">
            {city ? `${city}: ` : ''}{total} propert{total === 1 ? 'y' : 'ies'} found
          </h1>
          <div className="flex items-center gap-2">
            <label htmlFor="sort" className="text-sm text-stone-600">Sort by</label>
            <select
              id="sort"
              value={sort}
              onChange={(e) => applyFilters({ sort: e.target.value })}
              className="rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-700 bg-white"
            >
              <option value="">Recommended</option>
              <option value="price_low">Price: low to high</option>
              <option value="price_high">Price: high to low</option>
              <option value="rating">Highest rated</option>
              <option value="name">Name A–Z</option>
            </select>
            <button
              type="button"
              className="text-sm text-amber-600 hover:text-amber-700 font-medium"
              aria-label="Show on map (coming soon)"
            >
              Show on map
            </button>
          </div>
        </div>

        {isLoading ? (
          <HotelListSkeleton count={6} />
        ) : hotels.length === 0 ? (
          <p className="text-stone-600 py-8">No hotels found. Try changing your filters or search.</p>
        ) : (
          <>
            <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
              {hotels.map((h) => (
                <HotelCard
                  key={h.id}
                  hotel={h}
                  to={`/hotels/${h.id}${checkIn ? `?check_in=${checkIn}&check_out=${checkOut}` : ''}`}
                  nights={nights ?? undefined}
                  dealLabel={nights ? 'Early 2026 Deal' : undefined}
                  imageOverlay={<WishlistHeart hotelId={h.id} checkIn={checkIn || undefined} checkOut={checkOut || undefined} />}
                />
              ))}
            </div>
            {meta.last_page > 1 && (
              <nav className="mt-8 flex justify-center gap-2" aria-label="Pagination">
                {meta.current_page > 1 && (
                  <Link
                    to={{ search: buildSearch({ page: meta.current_page - 1 }) }}
                    className="px-4 py-2 rounded-lg border border-stone-300 hover:bg-stone-50"
                  >
                    Previous
                  </Link>
                )}
                <span className="px-4 py-2 text-stone-600">
                  Page {meta.current_page} of {meta.last_page}
                </span>
                {meta.current_page < meta.last_page && (
                  <Link
                    to={{ search: buildSearch({ page: meta.current_page + 1 }) }}
                    className="px-4 py-2 rounded-lg border border-stone-300 hover:bg-stone-50"
                  >
                    Next
                  </Link>
                )}
              </nav>
            )}
          </>
        )}

        <p className="mt-8 text-xs text-stone-500">
          Commission paid on bookings, and other factors can affect property rankings.
        </p>
      </div>
    </div>
  );
}
