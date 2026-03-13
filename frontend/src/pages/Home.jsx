import { useState, useMemo } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Search, MapPin, Calendar, Users, Shield, CreditCard, RotateCcw, Headphones } from 'lucide-react';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { HotelCard } from '../components/HotelCard';
import { Skeleton } from '../components/ui/Skeleton';

function LocationCard({ to, name, subtext, image, className = '' }) {
  return (
    <Link
      to={to}
      className={`group block rounded-2xl overflow-hidden border border-stone-200 bg-white shadow-sm hover:shadow-lg transition-all duration-300 ${className}`}
    >
      <div className="aspect-[3/2] bg-stone-200 relative overflow-hidden">
        {image ? (
          <img src={image} alt={name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
        ) : (
          <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-amber-100 to-stone-200 text-stone-500">
            <MapPin className="w-10 h-10" />
          </div>
        )}
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
        <div className="absolute bottom-0 left-0 right-0 p-4 text-white">
          <span className="font-semibold text-lg block">{name}</span>
          {subtext && <span className="text-sm text-white/90">{subtext}</span>}
        </div>
      </div>
    </Link>
  );
}

const TRUST_BADGES = [
  { icon: CreditCard, label: 'Secure payment', description: 'Your payment data is protected with industry-standard encryption.' },
  { icon: RotateCcw, label: 'Free cancellation', description: 'Flexible policies on most bookings. Cancel for free up to 48h before.' },
  { icon: Headphones, label: '24/7 support', description: 'Our team is here around the clock to help with your stay.' },
  { icon: Shield, label: 'Verified reviews', description: 'Real reviews from guests who stayed. No fake ratings.' },
];

export default function Home() {
  const navigate = useNavigate();
  const [city, setCity] = useState('');
  const [checkIn, setCheckIn] = useState('');
  const [checkOut, setCheckOut] = useState('');
  const [guests, setGuests] = useState(1);

  const handleSearch = (e) => {
    e.preventDefault();
    const params = new URLSearchParams();
    if (city.trim()) params.set('city', city.trim());
    if (checkIn) params.set('check_in', checkIn);
    if (checkOut) params.set('check_out', checkOut);
    if (guests > 0) params.set('min_capacity', guests);
    navigate({ pathname: '/hotels', search: params.toString() });
  };

  const { today, tomorrow } = useMemo(() => {
    const d = new Date();
    const t = d.toISOString().split('T')[0];
    const next = new Date(d);
    next.setDate(next.getDate() + 1);
    return { today: t, tomorrow: next.toISOString().split('T')[0] };
  }, []);

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

  const { data: hotelsData, isLoading: hotelsLoading } = useQuery({
    queryKey: ['hotels', 'home'],
    queryFn: async () => {
      const res = await api.get('/hotels', { params: { per_page: 8 } });
      if (!res.data?.success) throw new Error('Failed to load');
      const raw = res.data.data;
      const list = Array.isArray(raw) ? raw : raw?.data ?? [];
      return list;
    },
  });

  const cities = Array.isArray(citiesData) ? citiesData : [];
  const countries = Array.isArray(countriesData) ? countriesData : [];
  const hotels = Array.isArray(hotelsData) ? hotelsData : [];

  const locationOptions = useMemo(() => {
    const items = [];
    cities.forEach((c) => items.push({ value: c.name, label: c.country_name ? `${c.name}, ${c.country_name}` : c.name }));
    countries.forEach((c) => {
      if (!items.some((i) => i.value === c.name)) items.push({ value: c.name, label: c.name });
    });
    return items;
  }, [cities, countries]);

  return (
    <div className="min-h-screen bg-stone-50">
      {/* Hero Section — full-width with high-quality imagery */}
      <header className="relative bg-stone-900 text-white overflow-hidden w-screen max-w-none left-1/2 -translate-x-1/2 min-h-[420px] sm:min-h-[480px] flex flex-col justify-center">
        <div className="absolute inset-0">
          <img
            src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&q=80"
            alt=""
            className="w-full h-full object-cover opacity-60"
          />
          <div className="absolute inset-0 bg-gradient-to-b from-black/50 via-black/40 to-black/70" />
        </div>
        <div className="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 w-full">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold text-center mb-2">
            Find your perfect stay
          </h1>
          <p className="text-stone-200 text-center mb-8 max-w-xl mx-auto text-base sm:text-lg">
            Discover amazing hotels and unique stays. Book with confidence — secure payment, free cancellation, and 24/7 support.
          </p>

          {/* Overlay search card — mobile: stacked, desktop: inline */}
          <form onSubmit={handleSearch} className="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl p-4 sm:p-6">
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
              <div className="lg:col-span-2 relative">
                <MapPin className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
                <input
                  type="text"
                  list="location-suggestions"
                  placeholder="City or country"
                  value={city}
                  onChange={(e) => setCity(e.target.value)}
                  className="w-full h-11 pl-10 pr-3 rounded-lg border border-stone-300 text-stone-900 placeholder-stone-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                  autoComplete="off"
                />
                <datalist id="location-suggestions">
                  {locationOptions.map((opt) => (
                    <option key={opt.value} value={opt.label} />
                  ))}
                </datalist>
              </div>
              <div className="relative">
                <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
                <input
                  type="date"
                  value={checkIn}
                  onChange={(e) => setCheckIn(e.target.value)}
                  min={today}
                  className="w-full h-11 pl-10 pr-3 rounded-lg border border-stone-300 text-stone-900 focus:ring-2 focus:ring-amber-500"
                  aria-label="Check-in date"
                />
              </div>
              <div className="relative">
                <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
                <input
                  type="date"
                  value={checkOut}
                  onChange={(e) => setCheckOut(e.target.value)}
                  min={checkIn || tomorrow}
                  className="w-full h-11 pl-10 pr-3 rounded-lg border border-stone-300 text-stone-900 focus:ring-2 focus:ring-amber-500"
                  aria-label="Check-out date"
                />
              </div>
              <div className="relative flex items-center">
                <Users className="absolute left-3 w-5 h-5 text-stone-400 pointer-events-none" />
                <select
                  value={guests}
                  onChange={(e) => setGuests(Number(e.target.value))}
                  className="w-full h-11 pl-10 pr-3 rounded-lg border border-stone-300 text-stone-900 focus:ring-2 focus:ring-amber-500 appearance-none bg-white"
                  aria-label="Number of guests"
                >
                  {[1, 2, 3, 4, 5, 6].map((n) => (
                    <option key={n} value={n}>{n} {n === 1 ? 'guest' : 'guests'}</option>
                  ))}
                </select>
              </div>
            </div>
            <div className="mt-4 flex flex-col sm:flex-row gap-3">
              <button
                type="submit"
                className="w-full sm:w-auto sm:px-8 h-11 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 flex items-center justify-center gap-2 min-h-[44px]"
              >
                <Search className="w-5 h-5" />
                Search Hotels
              </button>
              <Link
                to="/hotels/map"
                className="w-full sm:w-auto h-11 px-6 rounded-lg border-2 border-amber-600 text-amber-600 font-medium hover:bg-amber-50 flex items-center justify-center gap-2 min-h-[44px] transition-colors"
              >
                <MapPin className="w-5 h-5" />
                Search on map
              </Link>
            </div>
          </form>
        </div>
      </header>

      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 space-y-16 sm:space-y-24">
        {/* Popular Destinations — grid of city cards */}
        <section aria-labelledby="popular-destinations-heading">
          <h2 id="popular-destinations-heading" className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2">
            Popular Destinations
          </h2>
          <p className="text-stone-600 mb-8 max-w-2xl">
            Explore Rome, Milan, Venice, Florence and more. Find hotels in the places guests love.
          </p>
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            {cities.length === 0 ? (
              [...Array(8)].map((_, i) => (
                <Skeleton key={i} className="aspect-[3/2] rounded-2xl" />
              ))
            ) : (
              cities.map((c) => (
                <LocationCard
                  key={c.id}
                  to={`/hotels?city_id=${c.id}&city=${encodeURIComponent(c.name)}${c.country_id ? `&country_id=${c.country_id}&country=${encodeURIComponent(c.country_name || '')}` : ''}`}
                  name={c.name}
                  subtext={c.country_name}
                  image={c.image}
                />
              ))
            )}
          </div>
        </section>

        {/* Why Book With Us — trust badges */}
        <section aria-labelledby="why-book-heading">
          <h2 id="why-book-heading" className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2">
            Why Book With Us
          </h2>
          <p className="text-stone-600 mb-8 max-w-2xl">
            We make booking simple, safe, and stress-free.
          </p>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            {TRUST_BADGES.map((badge, i) => {
              const Icon = badge.icon;
              return (
                <div
                  key={i}
                  className="flex flex-col items-center sm:items-start text-center sm:text-left p-6 rounded-2xl border border-stone-200 bg-white shadow-sm hover:shadow-md transition-shadow"
                >
                  <div className="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-4">
                    <Icon className="w-6 h-6 text-amber-600" />
                  </div>
                  <h3 className="font-semibold text-stone-900 mb-1">{badge.label}</h3>
                  <p className="text-sm text-stone-600">{badge.description}</p>
                </div>
              );
            })}
          </div>
        </section>

        {/* Featured Hotels — grid with skeleton on load */}
        <section aria-labelledby="featured-hotels-heading">
          <div className="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div>
              <h2 id="featured-hotels-heading" className="text-2xl sm:text-3xl font-bold text-stone-900">
                Featured Hotels
              </h2>
              <p className="text-stone-600 mt-2">Handpicked stays for a memorable trip.</p>
            </div>
            <Link to="/hotels" className="text-amber-600 font-medium hover:text-amber-700 inline-flex items-center gap-1">
              View all hotels
            </Link>
          </div>
          {hotelsLoading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
              {[...Array(4)].map((_, i) => (
                <div key={i} className="rounded-2xl overflow-hidden border border-stone-200 bg-white">
                  <Skeleton className="aspect-[4/3] w-full" />
                  <div className="p-4 space-y-2">
                    <Skeleton className="h-5 w-3/4" />
                    <Skeleton className="h-4 w-1/2" />
                    <Skeleton className="h-4 w-1/3 mt-4" />
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
              {hotels.slice(0, 4).map((h) => (
                <HotelCard key={h.id} hotel={h} nights={2} />
              ))}
            </div>
          )}
          {!hotelsLoading && hotels.length === 0 && (
            <p className="text-stone-500 py-8 text-center">
              No featured hotels yet. <Link to="/hotels" className="text-amber-600 underline">Browse all hotels</Link>.
            </p>
          )}
        </section>
      </div>
    </div>
  );
}
