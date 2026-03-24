import { useState, useMemo } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { MapPin, Calendar, Users, Search, SlidersHorizontal, X } from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import { useWishlist } from '../hooks/useWishlist';
import { HotelCard } from '../components/HotelCard';
import { HotelListSkeleton } from '../components/Skeleton';
import ErrorMessage from '../components/ErrorMessage';
import { AmenityIcon } from '../components/AmenityIcon';
import { calculateNights } from '../lib/utils';
import { parseHotelSearchResponse } from '../lib/hotelSearch';

const PRICE_MAX = 500;
const REVIEW_SCORE_OPTIONS = [
  { label: 'Excellent: 5', value: 5 },
  { label: 'Very Good: 4+', value: 4 },
  { label: 'Good: 3+', value: 3 },
  { label: 'Pleasant: 2+', value: 2 },
];

const SORT_OPTIONS = [
  { value: '', label: 'Recommended' },
  { value: 'price_low', label: 'Price: low to high' },
  { value: 'price_high', label: 'Price: high to low' },
  { value: 'rating', label: 'Highest rated' },
  { value: 'name', label: 'Name A–Z' },
  { value: 'distance', label: 'Distance', disabled: true },
];

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

function FilterDrawer({ open, onClose, children }) {
  if (!open) return null;
  return (
    <>
      <div
        className="fixed inset-0 bg-black/40 z-40 lg:hidden"
        onClick={onClose}
        aria-hidden="true"
      />
      <aside
        className="fixed top-0 right-0 bottom-0 w-full max-w-sm bg-white shadow-[0_20px_40px_rgb(26_26_26_/0.1)] z-50 overflow-y-auto lg:hidden rounded-l-2xl"
        role="dialog"
        aria-modal="true"
        aria-label="Filters"
      >
        <div className="sticky top-0 bg-white border-b border-[#e8e4dd] px-4 py-4 flex items-center justify-between rounded-tl-2xl">
          <h2 className="font-semibold text-[#1a1a1a]">Filters</h2>
          <button
            type="button"
            onClick={onClose}
            className="p-2 rounded-lg hover:bg-[#f5f2ed]"
            aria-label="Close filters"
          >
            <X className="w-5 h-5" />
          </button>
        </div>
        <div className="p-4">{children}</div>
      </aside>
    </>
  );
}

function HotelList() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [filterDrawerOpen, setFilterDrawerOpen] = useState(false);
  const city = searchParams.get('city') || '';
  const country = searchParams.get('country') || '';
  const cityId = searchParams.get('city_id') || '';
  const countryId = searchParams.get('country_id') || '';
  const latitude = searchParams.get('latitude') || '';
  const longitude = searchParams.get('longitude') || '';
  const radiusKm = searchParams.get('radius_km') || '';
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

  const handleSearch = (e) => {
    e.preventDefault();
    const form = e.target;
    const loc = form.location?.value?.trim() || '';
    const locParts = loc.split(',').map((s) => s.trim()).filter(Boolean);
    const updates = {
      check_in: form.check_in?.value || '',
      check_out: form.check_out?.value || '',
      min_capacity: form.guests?.value || '',
    };
    if (locParts.length >= 2) {
      updates.city = locParts[0];
      updates.country = locParts[1];
      updates.city_id = '';
      updates.country_id = '';
    } else if (locParts.length === 1) {
      updates.city = locParts[0];
      updates.country = '';
      updates.city_id = '';
      updates.country_id = '';
    } else {
      updates.city = '';
      updates.country = '';
      updates.city_id = '';
      updates.country_id = '';
    }
    applyFilters(updates);
  };

  const { data: citiesData } = useQuery({
    queryKey: ['locations', 'cities'],
    queryFn: async () => {
      const res = await api.get('/cities', { params: { limit: 20 } });
      if (!res.data?.success) throw new Error('Failed to load');
      return res.data.data?.data ?? [];
    },
  });

  const { data: countriesData } = useQuery({
    queryKey: ['locations', 'countries'],
    queryFn: async () => {
      const res = await api.get('/countries');
      if (!res.data?.success) throw new Error('Failed to load');
      return res.data.data?.data ?? [];
    },
  });

  const locationOptions = useMemo(() => {
    const items = [];
    (citiesData || []).forEach((c) => items.push({ value: c.name, label: c.country_name ? `${c.name}, ${c.country_name}` : c.name }));
    (countriesData || []).forEach((c) => {
      if (!items.some((i) => i.value === c.name)) items.push({ value: c.name, label: c.name });
    });
    return items;
  }, [citiesData, countriesData]);

  const { data: amenitiesData } = useQuery({
    queryKey: ['amenities'],
    queryFn: async () => {
      const res = await api.get('/amenities');
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load');
      return res.data;
    },
  });

  const amenities = Array.isArray(amenitiesData?.data) ? amenitiesData.data : amenitiesData?.data?.data ?? [];

  const { data, isLoading, isError, isFetching, error, refetch } = useQuery({
    queryKey: ['hotels', city, country, cityId, countryId, latitude, longitude, radiusKm, checkIn, checkOut, page, minRating, minCapacity, minPrice, maxPrice, sort, selectedAmenities.join(',')],
    queryFn: async () => {
      const params = { page, per_page: 12 };
      if (latitude && longitude && radiusKm) {
        params.latitude = latitude;
        params.longitude = longitude;
        params.radius_km = radiusKm;
      } else {
        if (cityId) params.city_id = cityId;
        else if (city) params.city = city;
        if (countryId) params.country_id = countryId;
        else if (country) params.country = country;
      }
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
    placeholderData: keepPreviousData,
  });

  const { hotels, meta, total } = useMemo(() => parseHotelSearchResponse(data), [data]);
  const nights = checkIn && checkOut ? calculateNights(checkIn, checkOut) : null;

  const buildSearch = (overrides = {}) => {
    const p = { ...Object.fromEntries(searchParams), ...overrides };
    return new URLSearchParams(p).toString();
  };

  const filterCount = [minRating, minCapacity, minPrice, maxPrice].filter(Boolean).length + (selectedAmenities.length ? 1 : 0);

  const resultsHeadline = useMemo(() => {
    const n = total;
    const unit = n === 1 ? 'hotel' : 'hotels';
    if (latitude && longitude && radiusKm) {
      return { line1: `${n} ${unit} within ${radiusKm} km`, line2: 'Results follow your map area and filters.' };
    }
    if (city) {
      return { line1: `${city}: ${n} ${unit}`, line2: 'Totals update when you change filters or dates.' };
    }
    if (country && !city) {
      return { line1: `${country}: ${n} ${unit}`, line2: 'Totals update when you change filters or dates.' };
    }
    return { line1: `${n} ${unit} found`, line2: 'Totals update when you change filters or dates.' };
  }, [total, city, country, latitude, longitude, radiusKm]);

  const { today, tomorrow } = useMemo(() => {
    const d = new Date();
    const t = d.toISOString().split('T')[0];
    const next = new Date(d);
    next.setDate(next.getDate() + 1);
    return { today: t, tomorrow: next.toISOString().split('T')[0] };
  }, []);

  const priceMax = maxPrice ? Number(maxPrice) : PRICE_MAX;

  const FilterContent = () => (
    <div className="space-y-6">
      <section>
        <h3 className="text-sm font-medium text-[#45423d] mb-3">Guests</h3>
        <p className="text-xs text-[#5c5852] mb-2">Rooms that fit at least</p>
        <select
          value={minCapacity}
          onChange={(e) => applyFilters({ min_capacity: e.target.value })}
          className="w-full rounded-xl border border-[#e8e4dd]200 px-4 py-2.5 text-sm text-[#45423d] bg-white focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b]"
        >
          <option value="">Any</option>
          {[1, 2, 3, 4, 5, 6, 8, 10].map((n) => (
            <option key={n} value={n}>{n} {n === 1 ? 'guest' : 'guests'}+</option>
          ))}
        </select>
      </section>

      <section>
        <h3 className="text-sm font-medium text-[#45423d] mb-3">Minimum rating</h3>
        <p className="text-xs text-[#5c5852] mb-2">Based on guest reviews</p>
        <div className="space-y-2">
          {REVIEW_SCORE_OPTIONS.map((opt) => (
            <label key={opt.value} className="flex items-center gap-2 cursor-pointer">
              <input
                type="radio"
                name="min_rating"
                checked={minRating === String(opt.value)}
                onChange={() => applyFilters({ min_rating: minRating === String(opt.value) ? '' : opt.value })}
                className="rounded-full border-[#e8e4dd]300 text-[#b8860b] focus:ring-[#b8860b]/30"
              />
              <span className="text-sm text-[#45423d]">{opt.label}</span>
            </label>
          ))}
        </div>
      </section>

      <section>
        <h3 className="text-sm font-medium text-[#45423d] mb-3">Price range (per night)</h3>
        <div className="space-y-4">
          <div className="flex gap-2 items-center">
            <input
              type="number"
              min="0"
              max={PRICE_MAX}
              step="10"
              value={minPrice || ''}
              onChange={(e) => applyFilters({ min_price: e.target.value })}
              placeholder="Min"
              className="w-24 rounded-xl border border-[#e8e4dd]200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#b8860b]/30"
            />
            <span className="text-[#7a756d]">–</span>
            <input
              type="number"
              min="0"
              max={PRICE_MAX}
              step="10"
              value={maxPrice || ''}
              onChange={(e) => applyFilters({ max_price: e.target.value })}
              placeholder="Max"
              className="w-24 rounded-xl border border-[#e8e4dd]200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#b8860b]/30"
            />
          </div>
          <div className="space-y-2">
            <input
              type="range"
              min="0"
              max={PRICE_MAX}
              step="10"
              value={priceMax}
              onChange={(e) => applyFilters({ max_price: e.target.value })}
              className="w-full h-2 rounded-lg appearance-none bg-stone-200 accent-[#b8860b]"
            />
            <p className="text-xs text-[#5c5852]">Max: ${priceMax}</p>
          </div>
        </div>
      </section>

      {amenities.length > 0 && (
        <section>
          <h3 className="text-sm font-medium text-[#45423d] mb-3">Amenities</h3>
          <div className="grid grid-cols-2 gap-2">
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
                  className="rounded border-[#e8e4dd]300 text-[#b8860b] focus:ring-[#b8860b]/30"
                />
                <span className="text-sm text-[#45423d] truncate">{a.name}</span>
              </label>
            ))}
          </div>
        </section>
      )}

      <button
        type="button"
        onClick={() => {
          const base = { check_in: checkIn, check_out: checkOut };
          if (cityId) base.city_id = cityId;
          else if (city) base.city = city;
          if (countryId) base.country_id = countryId;
          else if (country) base.country = country;
          setSearchParams(new URLSearchParams(base));
          setFilterDrawerOpen(false);
        }}
        aria-label="Clear all filters"
        className="w-full py-2.5 text-sm font-medium text-[#b8860b] hover:text-[#996f09] border border-[#e5c261] rounded-xl transition-colors hover:bg-[#f9edd1]"
      >
        Clear filters
      </button>
    </div>
  );

  if (isError) {
    return (
      <div className="py-6">
        <ErrorMessage message={error?.response?.data?.message || error?.message || 'Could not load hotels'} onRetry={() => refetch()} />
      </div>
    );
  }

  return (
    <div className="py-6 sm:py-8">
      {/* Sticky search bar — persist params from home, editable */}
      <form
        onSubmit={handleSearch}
        className="sticky top-16 z-30 bg-white/98 backdrop-blur-md border-b border-[#e8e4dd] -mx-4 px-4 py-5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 mb-8 shadow-sm"
      >
        <div className="max-w-6xl mx-auto">
          <div className="flex flex-col sm:flex-row gap-3 sm:gap-4">
            <div className="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
              <div className="flex-1 lg:col-span-2 relative">
                <MapPin className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[#7a756d] pointer-events-none" />
                <input
                  name="location"
                  type="text"
                  list="hotel-list-location"
                  defaultValue={city && country ? `${city}, ${country}` : city || country}
                  placeholder="City or country"
                  className="w-full h-12 pl-10 pr-3 rounded-xl border border-[#e8e4dd] text-[#1a1a1a] placeholder-[#7a756d] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] bg-white"
                  autoComplete="off"
                />
                <datalist id="hotel-list-location">
                  {locationOptions.map((opt) => (
                    <option key={opt.value} value={opt.label} />
                  ))}
                </datalist>
              </div>
              <div className="relative">
                <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[#7a756d] pointer-events-none" />
                <input
                  name="check_in"
                  type="date"
                  defaultValue={checkIn}
                  min={today}
                  className="w-full h-12 pl-10 pr-3 rounded-xl border border-[#e8e4dd]200 text-[#1a1a1a] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] bg-white"
                  aria-label="Check-in"
                />
              </div>
              <div className="relative">
                <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-[#7a756d] pointer-events-none" />
                <input
                  name="check_out"
                  type="date"
                  defaultValue={checkOut}
                  min={checkIn || tomorrow}
                  className="w-full h-12 pl-10 pr-3 rounded-xl border border-[#e8e4dd]200 text-[#1a1a1a] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] bg-white"
                  aria-label="Check-out"
                />
              </div>
              <div className="relative flex items-center">
                <Users className="absolute left-3 w-5 h-5 text-[#7a756d] pointer-events-none" />
                <select
                  name="guests"
                  defaultValue={minCapacity || '1'}
                  className="w-full h-12 pl-10 pr-3 rounded-xl border border-[#e8e4dd]200 text-[#1a1a1a] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] appearance-none bg-white"
                  aria-label="Guests"
                >
                  {[1, 2, 3, 4, 5, 6].map((n) => (
                    <option key={n} value={n}>{n} {n === 1 ? 'guest' : 'guests'}</option>
                  ))}
                </select>
              </div>
            </div>
            <div className="flex gap-2">
              <Link
                to={`/hotels/map${checkIn && checkOut ? `?check_in=${checkIn}&check_out=${checkOut}` : ''}`}
                className="h-12 px-4 rounded-xl border border-[#e8e4dd] hover:bg-[#faf8f5] flex items-center gap-2 text-[#45423d] font-medium transition-colors"
              >
                <MapPin className="w-5 h-5" />
                Map
              </Link>
              <button
                type="submit"
                className="h-12 px-6 rounded-xl bg-[#1a1a1a] text-white font-semibold hover:bg-[#2d2a28] focus:ring-2 focus:ring-[#b8860b]/30 flex items-center justify-center gap-2 transition-colors"
              >
                <Search className="w-5 h-5" />
                Search
              </button>
            </div>
          </div>
        </div>
      </form>

      <div className="flex flex-col lg:flex-row gap-6 lg:gap-8">
        {/* Desktop: left sidebar filters */}
        <aside className="hidden lg:block lg:w-72 shrink-0">
          <div className="sticky top-24 space-y-6 rounded-2xl border border-[#e8e4dd] bg-white p-6 shadow-[0_4px_12px_rgb(26_26_26_/0.06)]">
            <h2 className="font-semibold text-[#1a1a1a] text-lg">Filters</h2>
            <FilterContent />
          </div>
        </aside>

        {/* Mobile: filter drawer */}
        <FilterDrawer open={filterDrawerOpen} onClose={() => setFilterDrawerOpen(false)}>
          <FilterContent />
        </FilterDrawer>

        {/* Main: results */}
        <div className="flex-1 min-w-0">
          <nav className="text-sm text-[#5c5852] mb-4" aria-label="Breadcrumb">
            <Link to="/" className="hover:text-[#b8860b]">Home</Link>
            {(country || city || (latitude && longitude)) && (
              <>
                <span className="mx-1">›</span>
                <span className="text-[#1a1a1a] font-medium">
                  {city || country || (latitude && longitude ? 'Map search' : 'Search results')}
                </span>
              </>
            )}
            {!country && !city && !latitude && !longitude && <span className="text-[#1a1a1a] font-medium">Search results</span>}
          </nav>

          <div className="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div className="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
              <div className="flex min-w-0 flex-1 flex-col gap-1">
                {isLoading && !data ? (
                  <>
                    <div className="h-8 w-56 max-w-full rounded-lg bg-[#e8e4dd] animate-pulse" />
                    <div className="h-4 w-72 max-w-full rounded bg-[#f5f2ed] animate-pulse" />
                  </>
                ) : (
                  <>
                    <div className="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                      <h1 className="font-serif text-xl sm:text-2xl font-semibold text-[#1a1a1a]">
                        {resultsHeadline.line1}
                      </h1>
                      {isFetching && !isLoading && (
                        <span className="text-xs font-medium whitespace-nowrap text-[#b8860b]" aria-live="polite">
                          Updating…
                        </span>
                      )}
                    </div>
                    <p className="text-sm text-[#5c5852]">{resultsHeadline.line2}</p>
                  </>
                )}
              </div>
              <button
                type="button"
                onClick={() => setFilterDrawerOpen(true)}
                className="flex shrink-0 items-center gap-2 self-start rounded-xl border border-[#e8e4dd] px-4 py-2 text-sm font-medium transition-colors hover:bg-[#faf8f5] lg:hidden"
              >
                <SlidersHorizontal className="h-4 w-4" />
                Filters
                {filterCount > 0 && (
                  <span className="ml-1 rounded-full bg-[#f9edd1] px-2 py-0.5 text-xs font-medium text-[#996f09]">
                    {filterCount}
                  </span>
                )}
              </button>
            </div>
            <div className="flex items-center gap-2">
              <label htmlFor="sort" className="text-sm text-[#5c5852]">Sort by</label>
              <select
                id="sort"
                value={sort}
                onChange={(e) => applyFilters({ sort: e.target.value })}
                className="rounded-xl border border-[#e8e4dd] px-4 py-2.5 text-sm text-[#45423d] bg-white min-w-[160px] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b]"
              >
                {SORT_OPTIONS.map((opt) => (
                  <option key={opt.value} value={opt.value} disabled={opt.disabled}>
                    {opt.label}{opt.disabled ? ' (coming soon)' : ''}
                  </option>
                ))}
              </select>
            </div>
          </div>

          {/* Selected amenity chips */}
          {selectedAmenities.length > 0 && (
            <div className="flex flex-wrap gap-2 mb-4">
              {selectedAmenities.map((slug) => {
                const a = amenities.find((x) => x.slug === slug);
                return (
                  <button
                    key={slug}
                    type="button"
                    onClick={() => {
                      const next = selectedAmenities.filter((s) => s !== slug);
                      applyFilters({ amenities: next.join(',') });
                    }}
                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#f9edd1] text-[#996f09] text-sm font-medium hover:bg-[#f0d999]"
                  >
                    <AmenityIcon slug={slug} className="w-3.5 h-3.5" />
                    {a?.name || slug}
                    <X className="w-3.5 h-3.5" />
                  </button>
                );
              })}
            </div>
          )}

          {isLoading ? (
            <HotelListSkeleton count={6} />
          ) : hotels.length === 0 ? (
            <div className="py-16 text-center" role="status">
              <p className="text-[#5c5852] text-lg font-medium mb-2">No hotels found</p>
              <p className="text-[#5c5852] mb-6">Try adjusting your filters or search criteria to find more options.</p>
              <Link
                to="/"
                className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#1a1a1a] text-white font-semibold hover:bg-[#2d2a28] transition-colors"
              >
                <Search className="w-5 h-5" />
                Search from home
              </Link>
            </div>
          ) : (
            <>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4 sm:gap-6">
                {hotels.map((h) => (
                  <HotelCard
                    key={h.id}
                    hotel={h}
                    to={`/hotels/${h.id}${checkIn ? `?check_in=${checkIn}&check_out=${checkOut}` : ''}`}
                    nights={nights ?? undefined}
                    imageOverlay={<WishlistHeart hotelId={h.id} checkIn={checkIn || undefined} checkOut={checkOut || undefined} />}
                  >
                    <span className="mt-3 inline-flex items-center text-sm font-medium text-[#b8860b]">
                      View details →
                    </span>
                  </HotelCard>
                ))}
              </div>
              {meta.last_page > 1 && (
                <nav className="mt-8 flex justify-center gap-2" aria-label="Pagination">
                  {meta.current_page > 1 && (
                    <Link
                      to={{ search: buildSearch({ page: meta.current_page - 1 }) }}
                      className="px-4 py-2.5 rounded-xl border border-[#e8e4dd]200 hover:bg-[#faf8f5] text-sm font-medium transition-colors"
                    >
                      Previous
                    </Link>
                  )}
                  <span className="px-4 py-2 text-[#5c5852] text-sm">
                    Page {meta.current_page} of {meta.last_page}
                  </span>
                  {meta.current_page < meta.last_page && (
                    <Link
                      to={{ search: buildSearch({ page: meta.current_page + 1 }) }}
                      className="px-4 py-2.5 rounded-xl border border-[#e8e4dd]200 hover:bg-[#faf8f5] text-sm font-medium transition-colors"
                    >
                      Next
                    </Link>
                  )}
                </nav>
              )}
            </>
          )}

          <p className="mt-8 text-xs text-[#5c5852]">
            Commission paid on bookings, and other factors can affect property rankings.
          </p>
        </div>
      </div>
    </div>
  );
}

export default HotelList;
