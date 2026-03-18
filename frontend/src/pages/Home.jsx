import { useState, useMemo } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
  Search,
  MapPin,
  Calendar,
  Users,
  Shield,
  CreditCard,
  RotateCcw,
  Headphones,
  Sparkles,
  Waves,
  Building2,
  Mountain,
  Quote,
  ArrowRight,
  Star,
} from 'lucide-react';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { useWebsiteSettings } from '../contexts/WebsiteSettingsContext';
import { HotelCard } from '../components/HotelCard';
import { Skeleton } from '../components/ui/Skeleton';

function LocationCard({ to, name, subtext, image, className = '' }) {
  return (
    <Link
      to={to}
      className={`group block rounded-2xl overflow-hidden border border-stone-200/80 bg-white shadow-sm hover:shadow-xl hover:border-amber-200/60 transition-all duration-300 ${className}`}
    >
      <div className="aspect-[3/2] bg-stone-200 relative overflow-hidden">
        {image ? (
          <img
            src={image}
            alt={name}
            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-amber-100 via-stone-100 to-stone-200 text-stone-400">
            <MapPin className="w-12 h-12" />
          </div>
        )}
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
        <div className="absolute bottom-0 left-0 right-0 p-4 sm:p-5 text-white">
          <span className="font-semibold text-lg block">{name}</span>
          {subtext && <span className="text-sm text-white/90">{subtext}</span>}
          <span className="mt-2 inline-flex items-center text-sm font-medium text-amber-300 opacity-0 group-hover:opacity-100 transition-opacity">
            Explore <ArrowRight className="w-4 h-4 ml-1" />
          </span>
        </div>
      </div>
    </Link>
  );
}

const TRUST_BADGES = [
  { icon: CreditCard, label: 'Secure payment', description: 'Industry-standard encryption.' },
  { icon: RotateCcw, label: 'Free cancellation', description: 'Cancel free up to 48h before.' },
  { icon: Headphones, label: '24/7 support', description: 'We&apos;re here around the clock.' },
  { icon: Shield, label: 'Verified reviews', description: 'Real reviews from real guests.' },
];

const PROPERTY_TYPES = [
  { icon: Building2, label: 'City hotels', to: '/hotels?city=Rome', color: 'from-slate-500 to-slate-700' },
  { icon: Waves, label: 'Beachfront', to: '/hotels?city=Venice', color: 'from-cyan-500 to-teal-600' },
  { icon: Mountain, label: 'Countryside', to: '/hotels?city=Florence', color: 'from-emerald-600 to-green-700' },
  { icon: Sparkles, label: 'Luxury stays', to: '/hotels?min_rating=4', color: 'from-amber-500 to-amber-700' },
];

const TESTIMONIALS = [
  {
    quote: 'Found the perfect boutique hotel in Rome. The map search made it so easy to pick the right neighborhood.',
    author: 'Maria K.',
    trip: 'Rome, Italy',
  },
  {
    quote: 'Smooth booking, great prices, and the free cancellation gave me peace of mind. Will book again!',
    author: 'James L.',
    trip: 'Venice, Italy',
  },
  {
    quote: 'Love that I can search by map and see exactly where hotels are. Game changer for planning trips.',
    author: 'Sofia R.',
    trip: 'Florence, Italy',
  },
];

const STATS = [
  { value: '500+', label: 'Hotels' },
  { value: '50+', label: 'Cities' },
  { value: '24/7', label: 'Support' },
  { value: '4.8', label: 'Avg rating' },
];

export default function Home() {
  const navigate = useNavigate();
  const { settings } = useWebsiteSettings();
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
      return Array.isArray(raw) ? raw : raw?.data ?? [];
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
    <div className="min-h-screen bg-stone-50/80">
      {/* Hero — full viewport width */}
      <header className="relative overflow-hidden w-screen max-w-none left-1/2 -translate-x-1/2">
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(251,191,36,0.15),transparent)]" />
        <div className="absolute inset-0">
          <img
            src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&q=80"
            alt=""
            className="w-full h-full object-cover opacity-50"
          />
          <div className="absolute inset-0 bg-gradient-to-b from-stone-900/90 via-stone-900/70 to-stone-900/95" />
        </div>
        <div className="relative w-full px-4 sm:px-6 lg:px-8 xl:px-12 pt-16 sm:pt-24 pb-20 sm:pb-28">
          <div className="text-center max-w-3xl mx-auto">
            <p className="text-amber-400 text-sm font-semibold tracking-widest uppercase mb-4">Your next stay starts here</p>
            <h1 className="text-4xl sm:text-5xl md:text-6xl font-bold text-white tracking-tight mb-4">
              Find your perfect
              <span className="block text-amber-400">hotel getaway</span>
            </h1>
            <p className="text-stone-300 text-lg sm:text-xl mb-10">
              {settings.site_description || 'Discover handpicked hotels across Italy. Search by city or pick a spot on the map — book with confidence.'}
            </p>

            {/* Search card — elevated, modern */}
            <form
              onSubmit={handleSearch}
              className="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl shadow-black/20 p-4 sm:p-6 border border-white/20"
            >
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                <div className="lg:col-span-2 relative">
                  <MapPin className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
                  <input
                    type="text"
                    list="location-suggestions"
                    placeholder="Where are you going?"
                    value={city}
                    onChange={(e) => setCity(e.target.value)}
                    className="w-full h-12 pl-10 pr-3 rounded-xl border border-stone-200 text-stone-900 placeholder-stone-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white"
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
                    className="w-full h-12 pl-10 pr-3 rounded-xl border border-stone-200 text-stone-900 focus:ring-2 focus:ring-amber-500 bg-white"
                    aria-label="Check-in"
                  />
                </div>
                <div className="relative">
                  <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
                  <input
                    type="date"
                    value={checkOut}
                    onChange={(e) => setCheckOut(e.target.value)}
                    min={checkIn || tomorrow}
                    className="w-full h-12 pl-10 pr-3 rounded-xl border border-stone-200 text-stone-900 focus:ring-2 focus:ring-amber-500 bg-white"
                    aria-label="Check-out"
                  />
                </div>
                <div className="relative flex items-center">
                  <Users className="absolute left-3 w-5 h-5 text-stone-400 pointer-events-none" />
                  <select
                    value={guests}
                    onChange={(e) => setGuests(Number(e.target.value))}
                    className="w-full h-12 pl-10 pr-3 rounded-xl border border-stone-200 text-stone-900 focus:ring-2 focus:ring-amber-500 appearance-none bg-white"
                    aria-label="Guests"
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
                  className="flex-1 h-12 px-8 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 focus:ring-2 focus:ring-amber-500 flex items-center justify-center gap-2 transition-colors"
                >
                  <Search className="w-5 h-5" />
                  Search Hotels
                </button>
                <Link
                  to="/hotels/map"
                  className="flex-1 h-12 px-6 rounded-xl border-2 border-amber-500/80 text-amber-500 font-semibold hover:bg-amber-500/10 flex items-center justify-center gap-2 transition-colors"
                >
                  <MapPin className="w-5 h-5" />
                  Search on map
                </Link>
              </div>
            </form>
          </div>
        </div>
      </header>

      {/* Stats strip */}
      <section className="relative -mt-8 z-10 px-4 sm:px-6 lg:px-8 xl:px-12">
        <div className="w-full">
          <div className="bg-white rounded-2xl shadow-xl border border-stone-200/80 p-6 sm:p-8">
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-6 sm:gap-8">
              {STATS.map((stat, i) => (
                <div key={i} className="text-center">
                  <p className="text-2xl sm:text-3xl font-bold text-amber-600">{stat.value}</p>
                  <p className="text-sm text-stone-600 mt-1">{stat.label}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <div className="w-full px-4 sm:px-6 lg:px-8 xl:px-12 py-16 sm:py-24 space-y-20 sm:space-y-28">
        {/* Property types — quick filters */}
        <section aria-labelledby="property-types-heading">
          <h2 id="property-types-heading" className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2">
            Browse by type
          </h2>
          <p className="text-stone-600 mb-8 max-w-2xl">
            Whether you want a city break, beach escape, or countryside retreat.
          </p>
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {PROPERTY_TYPES.map((type, i) => {
              const Icon = type.icon;
              return (
                <Link
                  key={i}
                  to={type.to}
                  className="group flex items-center gap-4 p-5 rounded-2xl border border-stone-200 bg-white hover:shadow-lg hover:border-amber-200/60 transition-all duration-300"
                >
                  <div className={`w-14 h-14 rounded-xl bg-gradient-to-br ${type.color} flex items-center justify-center shrink-0`}>
                    <Icon className="w-7 h-7 text-white" />
                  </div>
                  <div>
                    <span className="font-semibold text-stone-900 group-hover:text-amber-700 transition-colors">{type.label}</span>
                    <span className="block text-amber-600 text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                      Explore →
                    </span>
                  </div>
                </Link>
              );
            })}
          </div>
        </section>

        {/* Popular Destinations — bento grid */}
        <section aria-labelledby="popular-destinations-heading">
          <h2 id="popular-destinations-heading" className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2">
            Popular destinations
          </h2>
          <p className="text-stone-600 mb-8 max-w-2xl">
            Explore Rome, Milan, Venice, Florence and more. Find hotels in the places guests love.
          </p>
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-5">
            {cities.length === 0 ? (
              [...Array(8)].map((_, i) => (
                <Skeleton key={i} className="aspect-[3/2] rounded-2xl" />
              ))
            ) : (
              cities.slice(0, 8).map((c) => (
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

        {/* Trust badges — compact horizontal */}
        <section aria-labelledby="why-book-heading" className="py-12 sm:py-16 bg-white rounded-3xl border border-stone-200/80 px-6 sm:px-10">
          <h2 id="why-book-heading" className="text-2xl sm:text-3xl font-bold text-stone-900 mb-8 text-center">
            Why book with us
          </h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {TRUST_BADGES.map((badge, i) => {
              const Icon = badge.icon;
              return (
                <div key={i} className="flex flex-col items-center text-center">
                  <div className="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center mb-3">
                    <Icon className="w-7 h-7 text-amber-600" />
                  </div>
                  <h3 className="font-semibold text-stone-900 mb-1">{badge.label}</h3>
                  <p className="text-sm text-stone-600">{badge.description}</p>
                </div>
              );
            })}
          </div>
        </section>

        {/* Featured Hotels */}
        <section aria-labelledby="featured-hotels-heading">
          <div className="flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
              <h2 id="featured-hotels-heading" className="text-2xl sm:text-3xl font-bold text-stone-900">
                Featured hotels
              </h2>
              <p className="text-stone-600 mt-2">Handpicked stays for a memorable trip.</p>
            </div>
            <Link
              to="/hotels"
              className="inline-flex items-center gap-2 text-amber-600 font-semibold hover:text-amber-700 group"
            >
              View all hotels <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </Link>
          </div>
          {hotelsLoading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
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
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 sm:gap-6">
              {hotels.slice(0, 4).map((h) => (
                <HotelCard key={h.id} hotel={h} nights={2} />
              ))}
            </div>
          )}
          {!hotelsLoading && hotels.length === 0 && (
            <p className="text-stone-500 py-12 text-center">
              No featured hotels yet. <Link to="/hotels" className="text-amber-600 font-medium underline">Browse all hotels</Link>.
            </p>
          )}
        </section>

        {/* Testimonials */}
        <section aria-labelledby="testimonials-heading" className="py-12 sm:py-16 bg-gradient-to-br from-stone-100 to-stone-50 rounded-3xl border border-stone-200/60 px-6 sm:px-10">
          <h2 id="testimonials-heading" className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2 text-center">
            What guests say
          </h2>
          <p className="text-stone-600 mb-10 text-center max-w-xl mx-auto">
            Real experiences from travelers who booked with us.
          </p>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
            {TESTIMONIALS.map((t) => (
              <blockquote key={t.author} className="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-stone-200/60">
                <Quote className="w-10 h-10 text-amber-400/60 mb-4" />
                <p className="text-stone-700 leading-relaxed mb-4">&ldquo;{t.quote}&rdquo;</p>
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-amber-200 flex items-center justify-center">
                    <Star className="w-5 h-5 text-amber-600" />
                  </div>
                  <div>
                    <cite className="font-semibold text-stone-900 not-italic">{t.author}</cite>
                    <p className="text-sm text-stone-500">{t.trip}</p>
                  </div>
                </div>
              </blockquote>
            ))}
          </div>
        </section>

        {/* Map search CTA */}
        <section className="text-center py-16 sm:py-20 bg-white rounded-3xl border border-stone-200/80 overflow-hidden relative w-full">
          <div className="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(251,191,36,0.08),transparent)]" />
          <div className="relative">
            <div className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 mb-6">
              <MapPin className="w-8 h-8" />
            </div>
            <h2 className="text-2xl sm:text-3xl font-bold text-stone-900 mb-3">
              Search hotels on the map
            </h2>
            <p className="text-stone-600 max-w-md mx-auto mb-8">
              Pick a location, set your radius, and discover hotels in that area. Perfect for finding stays near landmarks or neighborhoods.
            </p>
            <Link
              to="/hotels/map"
              className="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-colors"
            >
              <MapPin className="w-5 h-5" />
              Open map search
            </Link>
          </div>
        </section>

        {/* Newsletter — full viewport width */}
        <div className="w-screen max-w-none relative left-1/2 -translate-x-1/2">
          <section className="py-12 sm:py-16 bg-stone-900 sm:rounded-3xl px-4 sm:px-6 lg:px-8 xl:px-12 text-center">
          <h2 className="text-2xl sm:text-3xl font-bold text-white mb-2">
            Get travel inspiration
          </h2>
          <p className="text-stone-400 mb-6 max-w-md mx-auto">
            Sign up for deals, new destinations, and tips for your next trip.
          </p>
          <form
            onSubmit={(e) => e.preventDefault()}
            className="flex flex-col sm:flex-row gap-3 max-w-md mx-auto"
          >
            <input
              type="email"
              placeholder="Your email"
              className="flex-1 h-12 px-4 rounded-xl border border-stone-600 bg-stone-800 text-white placeholder-stone-500 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
            />
            <button
              type="submit"
              className="h-12 px-6 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-colors"
            >
              Subscribe
            </button>
          </form>
          </section>
        </div>
      </div>
    </div>
  );
}
