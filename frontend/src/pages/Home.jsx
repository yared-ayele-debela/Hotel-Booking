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
      className={`group relative block rounded-3xl overflow-hidden bg-[#1a1a1a] ring-1 ring-black/5 shadow-[0_8px_30px_rgb(26_26_26_/0.08)] hover:shadow-[0_20px_40px_rgb(26_26_26_/0.14)] hover:ring-[#b8860b]/30 transition-all duration-500 ${className}`}
    >
      <div className="aspect-[4/5] sm:aspect-[3/4] relative overflow-hidden">
        {image ? (
          <img
            src={image}
            alt={name}
            className="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-[800ms] ease-out"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#2d2a28] via-[#45423d] to-[#1a1a1a] text-[#a39e94]">
            <MapPin className="w-14 h-14 opacity-60" strokeWidth={1.25} />
          </div>
        )}
        <div className="absolute inset-0 bg-gradient-to-t from-[#1a1a1a] via-[#1a1a1a]/50 to-transparent opacity-95" />
        <div className="absolute inset-0 bg-gradient-to-br from-[#b8860b]/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
        {subtext && (
          <div className="absolute top-4 left-4 right-4 flex justify-start">
            <span className="inline-block rounded-full bg-black/35 backdrop-blur-md px-3 py-1 text-[10px] sm:text-xs font-semibold uppercase tracking-[0.18em] text-white/95 ring-1 ring-white/15">
              {subtext}
            </span>
          </div>
        )}
        <div className="absolute bottom-0 left-0 right-0 p-5 sm:p-6 pt-12">
          <span className="font-serif text-xl sm:text-2xl font-semibold text-white tracking-tight block leading-snug">
            {name}
          </span>
          <span className="mt-4 inline-flex items-center gap-2 text-sm font-medium text-white/90 group-hover:text-[#f9edd1] transition-colors">
            <span className="h-px w-8 bg-[#b8860b] group-hover:w-10 transition-all" />
            View stays
            <ArrowRight className="w-4 h-4 text-[#c9a227] group-hover:translate-x-0.5 transition-transform" />
          </span>
        </div>
      </div>
    </Link>
  );
}

const TRUST_BADGES = [
  {
    icon: CreditCard,
    label: 'Encrypted checkout',
    description: 'Card details are handled over secure connections with payment partners you already trust.',
  },
  {
    icon: RotateCcw,
    label: 'Flexible cancellation',
    description: 'Change of plans? On qualifying rates you can cancel at no charge up to 48 hours before arrival.',
  },
  {
    icon: Headphones,
    label: 'Support, not scripts',
    description: 'Reach a real team any time — whether it’s a booking tweak or a question before you travel.',
  },
  {
    icon: Shield,
    label: 'Reviews from real stays',
    description: 'Ratings come from guests who completed a booking — so you see feedback that actually reflects the stay.',
  },
];

const PROPERTY_TYPES = [
  { icon: Building2, label: 'City hotels', to: '/hotels?city=Rome', color: 'from-[#45423d] to-[#2d2a28]' },
  { icon: Waves, label: 'Beachfront', to: '/hotels?city=Venice', color: 'from-[#5c5852] to-[#45423d]' },
  { icon: Mountain, label: 'Countryside', to: '/hotels?city=Florence', color: 'from-[#3d3936] to-[#2d2a28]' },
  { icon: Sparkles, label: 'Luxury stays', to: '/hotels?min_rating=4', color: 'from-[#b8860b] to-[#996f09]' },
];

const TESTIMONIALS = [
  {
    quote:
      'I chose our Rome stay by dropping a pin on the map — no guesswork about which quarter we’d wake up in. The hotel matched what we saw online.',
    author: 'Maria K.',
    trip: 'Rome',
  },
  {
    quote: 'Booking took minutes. When our dates shifted, cancelling was straightforward. I’ve already used it again for Venice.',
    author: 'James L.',
    trip: 'Venice',
  },
  {
    quote: 'Seeing every hotel on the map before paying made comparing neighborhoods effortless. Exactly what planning a city trip should feel like.',
    author: 'Sofia R.',
    trip: 'Florence',
  },
];

const STATS = [
  { value: '500+', label: 'Hotels' },
  { value: '50+', label: 'Cities' },
  { value: '24/7', label: 'Support' },
  { value: 'premium', label: 'Quality' },
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
    <div className="min-h-screen bg-[#faf8f5]">
      {/* Hero */}
      <header className="relative overflow-hidden w-screen max-w-none left-1/2 -translate-x-1/2">
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(184,134,11,0.08),transparent)]" />
        <div className="absolute inset-0">
          <img
            src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&q=80"
            alt=""
            className="w-full h-full object-cover opacity-45"
          />
          <div className="absolute inset-0 bg-gradient-to-b from-[#1a1a1a]/92 via-[#1a1a1a]/85 to-[#1a1a1a]/95" />
        </div>
        <div className="relative w-full px-4 sm:px-6 lg:px-8 xl:px-12 pt-20 sm:pt-28 pb-24 sm:pb-32">
          <div className="text-center max-w-3xl mx-auto">
            <p className="text-[#c9a227] text-xs font-semibold tracking-[0.2em] uppercase mb-4">
              Your next stay starts here
            </p>
            <h1 className="font-serif text-4xl sm:text-5xl md:text-6xl font-semibold text-white tracking-tight mb-5 leading-tight">
              Find your perfect
              <span className="block text-[#c9a227] mt-1">hotel getaway</span>
            </h1>
            <p className="text-[#a39e94] text-lg sm:text-xl mb-12 leading-relaxed">
              {settings.site_description ||
                'Discover handpicked hotels across Italy. Search by city or pick a spot on the map — book with confidence.'}
            </p>

            {/* Search card */}
            <form
              onSubmit={handleSearch}
              className="bg-white/98 backdrop-blur-sm rounded-2xl shadow-[0_20px_40px_rgb(0_0_0_/0.15)] p-5 sm:p-6 border border-white/20"
            >
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                <div className="lg:col-span-2 relative">
                  <MapPin className="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-[#7a756d] pointer-events-none" />
                  <input
                    type="text"
                    list="location-suggestions"
                    placeholder="Where are you going?"
                    value={city}
                    onChange={(e) => setCity(e.target.value)}
                    className="w-full h-12 pl-11 pr-3 rounded-xl border border-[#e8e4dd] text-[#1a1a1a] placeholder-[#7a756d] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] bg-white"
                    autoComplete="off"
                  />
                  <datalist id="location-suggestions">
                    {locationOptions.map((opt) => (
                      <option key={opt.value} value={opt.label} />
                    ))}
                  </datalist>
                </div>
                <div className="relative">
                  <Calendar className="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-[#7a756d] pointer-events-none" />
                  <input
                    type="date"
                    value={checkIn}
                    onChange={(e) => setCheckIn(e.target.value)}
                    min={today}
                    className="w-full h-12 pl-11 pr-3 rounded-xl border border-[#e8e4dd] text-[#1a1a1a] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] bg-white"
                    aria-label="Check-in"
                  />
                </div>
                <div className="relative">
                  <Calendar className="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-[#7a756d] pointer-events-none" />
                  <input
                    type="date"
                    value={checkOut}
                    onChange={(e) => setCheckOut(e.target.value)}
                    min={checkIn || tomorrow}
                    className="w-full h-12 pl-11 pr-3 rounded-xl border border-[#e8e4dd] text-[#1a1a1a] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] bg-white"
                    aria-label="Check-out"
                  />
                </div>
                <div className="relative flex items-center">
                  <Users className="absolute left-3.5 w-5 h-5 text-[#7a756d] pointer-events-none" />
                  <select
                    value={guests}
                    onChange={(e) => setGuests(Number(e.target.value))}
                    className="w-full h-12 pl-11 pr-3 rounded-xl border border-[#e8e4dd] text-[#1a1a1a] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] appearance-none bg-white"
                    aria-label="Guests"
                  >
                    {[1, 2, 3, 4, 5, 6].map((n) => (
                      <option key={n} value={n}>
                        {n} {n === 1 ? 'guest' : 'guests'}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
              <div className="mt-4 flex flex-col sm:flex-row gap-3">
                <button
                  type="submit"
                  className="flex-1 h-12 px-8 rounded-xl bg-[#1a1a1a] text-white font-semibold hover:bg-[#2d2a28] focus:ring-2 focus:ring-[#b8860b]/30 flex items-center justify-center gap-2 transition-colors"
                >
                  <Search className="w-5 h-5" />
                  Search Hotels
                </button>
                <Link
                  to="/hotels/map"
                  className="flex-1 h-12 px-6 rounded-xl border-2 border-[#b8860b]/60 text-[#b8860b] font-semibold hover:bg-[#b8860b]/10 flex items-center justify-center gap-2 transition-colors"
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
      <section className="relative -mt-10 z-10 px-4 sm:px-6 lg:px-8 xl:px-12">
        <div className="max-w-6xl mx-auto">
          <div className="bg-white rounded-2xl shadow-[0_4px_12px_rgb(26_26_26_/0.06)] border border-[#e8e4dd] p-6 sm:p-8">
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-6 sm:gap-8">
              {STATS.map((stat, i) => (
                <div key={i} className="text-center">
                  <p className="font-serif text-2xl sm:text-3xl font-semibold text-[#b8860b]">{stat.value}</p>
                  <p className="text-sm text-[#5c5852] mt-1 font-medium">{stat.label}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <div className="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 py-20 sm:py-28 space-y-24 sm:space-y-32">
        {/* Property types */}
        <section aria-labelledby="property-types-heading">
          <h2 id="property-types-heading" className="font-serif text-2xl sm:text-3xl font-semibold text-[#1a1a1a] mb-2">
            Browse by type
          </h2>
          <p className="text-[#5c5852] mb-10 max-w-2xl leading-relaxed">
            Whether you want a city break, beach escape, or countryside retreat.
          </p>
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {PROPERTY_TYPES.map((type, i) => {
              const Icon = type.icon;
              return (
                <Link
                  key={i}
                  to={type.to}
                  className="group flex items-center gap-4 p-5 rounded-2xl border border-[#e8e4dd] bg-white hover:shadow-[0_12px_28px_rgb(26_26_26_/0.08)] hover:border-[#d4cec4] transition-all duration-300"
                >
                  <div
                    className={`w-14 h-14 rounded-xl bg-gradient-to-br ${type.color} flex items-center justify-center shrink-0`}
                  >
                    <Icon className="w-7 h-7 text-white" />
                  </div>
                  <div>
                    <span className="font-semibold text-[#1a1a1a] group-hover:text-[#b8860b] transition-colors">
                      {type.label}
                    </span>
                    <span className="block text-[#b8860b] text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                      Explore →
                    </span>
                  </div>
                </Link>
              );
            })}
          </div>
        </section>

        {/* Popular Destinations */}
        <section aria-labelledby="popular-destinations-heading" className="relative">
          <div className="absolute -top-px left-0 right-0 h-px bg-gradient-to-r from-transparent via-[#b8860b]/40 to-transparent" aria-hidden />
          <div className="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-10 sm:mb-12">
            <div className="max-w-2xl">
              <p className="text-[#b8860b] text-xs font-semibold tracking-[0.22em] uppercase mb-3">
                Destinations
              </p>
              <h2
                id="popular-destinations-heading"
                className="font-serif text-3xl sm:text-4xl font-semibold text-[#1a1a1a] tracking-tight mb-3"
              >
                Popular destinations
              </h2>
              <p className="text-[#5c5852] text-base sm:text-lg leading-relaxed">
                From Rome and Milan to Venice and Florence — curated cities where travelers love to stay.
              </p>
            </div>
            <Link
              to="/hotels"
              className="inline-flex items-center gap-2 self-start lg:self-auto shrink-0 px-5 py-3 rounded-xl border border-[#e8e4dd] bg-white text-[#1a1a1a] text-sm font-semibold hover:border-[#b8860b]/50 hover:bg-[#faf8f5] transition-all shadow-[0_2px_8px_rgb(26_26_26_/0.04)]"
            >
              Browse all cities
              <ArrowRight className="w-4 h-4 text-[#b8860b]" />
            </Link>
          </div>
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 lg:gap-6">
            {cities.length === 0 ? (
              [...Array(8)].map((_, i) => (
                <Skeleton key={i} className="aspect-[4/5] sm:aspect-[3/4] rounded-3xl" />
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

        {/* Trust — editorial split layout */}
        <section
          aria-labelledby="why-book-heading"
          className="rounded-3xl border border-[#e8e4dd] bg-white px-6 sm:px-10 lg:px-14 py-14 sm:py-20 shadow-[0_4px_24px_rgb(26_26_26_/0.05)]"
        >
          <div className="grid gap-12 lg:grid-cols-12 lg:gap-16 lg:items-start">
            <div className="lg:col-span-4 lg:sticky lg:top-28">
              <p className="text-[#b8860b] text-xs font-semibold tracking-[0.22em] uppercase mb-3">Expectations</p>
              <h2
                id="why-book-heading"
                className="font-serif text-3xl sm:text-[2.125rem] font-semibold text-[#1a1a1a] tracking-tight leading-tight mb-5"
              >
                The standard behind your stay
              </h2>
              <p className="text-[#5c5852] leading-relaxed text-[15px] sm:text-base">
                No clutter — just what matters after you&apos;ve picked a place on the map: safe payments, fair
                cancellation rules, human support, and reviews tied to completed visits.
              </p>
            </div>
            <div className="lg:col-span-8 border-y border-[#e8e4dd] divide-y divide-[#e8e4dd]">
              {TRUST_BADGES.map((badge, i) => {
                const Icon = badge.icon;
                return (
                  <div
                    key={badge.label}
                    className="grid grid-cols-[2.75rem_1fr] gap-4 sm:grid-cols-[3.25rem_1fr] sm:gap-6 py-8 first:pt-6 sm:first:pt-8 last:pb-6 sm:last:pb-8"
                  >
                    <span
                      className="font-serif text-xl sm:text-2xl text-[#b8860b]/90 tabular-nums leading-none pt-0.5"
                      aria-hidden
                    >
                      {String(i + 1).padStart(2, '0')}
                    </span>
                    <div className="flex flex-col gap-4 sm:flex-row sm:gap-5">
                      <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-[#e8e4dd] bg-[#faf8f5] text-[#1a1a1a]">
                        <Icon className="h-5 w-5" strokeWidth={1.5} aria-hidden />
                      </div>
                      <div className="min-w-0">
                        <h3 className="font-semibold text-[#1a1a1a] text-base sm:text-lg mb-2 tracking-tight">
                          {badge.label}
                        </h3>
                        <p className="text-sm sm:text-[15px] text-[#5c5852] leading-relaxed">{badge.description}</p>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </section>

        {/* Featured Hotels */}
        <section aria-labelledby="featured-hotels-heading">
          <div className="flex flex-wrap items-end justify-between gap-4 mb-10">
            <div>
              <h2 id="featured-hotels-heading" className="font-serif text-2xl sm:text-3xl font-semibold text-[#1a1a1a]">
                Featured hotels
              </h2>
              <p className="text-[#5c5852] mt-2 leading-relaxed">Handpicked stays for a memorable trip.</p>
            </div>
            <Link
              to="/hotels"
              className="inline-flex items-center gap-2 text-[#b8860b] font-semibold hover:text-[#996f09] group transition-colors"
            >
              View all hotels <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </Link>
          </div>
          {hotelsLoading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
              {[...Array(4)].map((_, i) => (
                <div key={i} className="rounded-2xl overflow-hidden border border-[#e8e4dd] bg-white">
                  <Skeleton className="aspect-[4/3] w-full" />
                  <div className="p-5 space-y-2">
                    <Skeleton className="h-5 w-3/4" />
                    <Skeleton className="h-4 w-1/2" />
                    <Skeleton className="h-4 w-1/3 mt-4" />
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
              {hotels.slice(0, 4).map((h) => (
                <HotelCard key={h.id} hotel={h} nights={2} />
              ))}
            </div>
          )}
          {!hotelsLoading && hotels.length === 0 && (
            <p className="text-[#5c5852] py-12 text-center">
              No featured hotels yet.{' '}
              <Link to="/hotels" className="text-[#b8860b] font-medium underline hover:text-[#996f09]">
                Browse all hotels
              </Link>
              .
            </p>
          )}
        </section>

        {/* Testimonials — dark editorial band */}
        <section
          aria-labelledby="testimonials-heading"
          className="relative overflow-hidden rounded-3xl border border-white/10 bg-[#1a1a1a] px-6 sm:px-10 lg:px-16 py-16 sm:py-20 text-white shadow-[0_24px_60px_rgb(26_26_26_/0.35)]"
        >
          <div
            className="pointer-events-none absolute -right-20 top-0 h-80 w-80 rounded-full bg-[#b8860b]/15 blur-3xl"
            aria-hidden
          />
          <div
            className="pointer-events-none absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-white/[0.04] blur-3xl"
            aria-hidden
          />
          <div className="relative">
            <p className="text-[#c9a227] text-xs font-semibold tracking-[0.28em] uppercase mb-4">Guest correspondence</p>
            <h2
              id="testimonials-heading"
              className="font-serif text-3xl sm:text-4xl lg:text-[2.75rem] font-semibold text-white tracking-tight leading-[1.15] mb-4 max-w-3xl"
            >
              Stays worth remembering
            </h2>
            <p className="text-[#a39e94] text-base sm:text-lg leading-relaxed mb-12 sm:mb-14 max-w-2xl">
              Notes from travelers who found their hotel through us — unedited, uncompensated, in their own words.
            </p>
            <div className="grid gap-6 md:grid-cols-3 md:gap-7">
              {TESTIMONIALS.map((t) => (
                <blockquote
                  key={t.author}
                  className="flex h-full flex-col rounded-2xl border border-white/10 bg-white/[0.045] p-6 sm:p-7 backdrop-blur-md transition-colors duration-300 hover:border-[#b8860b]/25 hover:bg-white/[0.07]"
                >
                  <Quote className="mb-5 h-9 w-9 shrink-0 text-[#b8860b]" strokeWidth={1.25} aria-hidden />
                  <p className="flex-1 text-[15px] sm:text-base leading-relaxed text-[#e8e4dd]">
                    &ldquo;{t.quote}&rdquo;
                  </p>
                  <footer className="mt-8 border-t border-white/10 pt-6">
                    <cite className="block font-semibold not-italic text-white">{t.author}</cite>
                    <p className="mt-1 text-sm text-[#a39e94]">{t.trip}</p>
                  </footer>
                </blockquote>
              ))}
            </div>
          </div>
        </section>
      </div>

      {/* Map search CTA — full viewport width, split content + live map */}
      <section
        aria-labelledby="map-search-heading"
        className="relative w-screen max-w-none left-1/2 -translate-x-1/2 border-y border-[#e8e4dd] bg-[#faf8f5] overflow-hidden shadow-[inset_0_1px_0_rgb(255_255_255/0.6)]"
      >
        <div className="grid grid-cols-1 lg:grid-cols-2 lg:min-h-[min(520px,70vh)]">
          <div className="flex flex-col justify-center px-6 sm:px-10 lg:pl-12 xl:pl-20 2xl:pl-28 py-14 sm:py-16 lg:py-20 order-2 lg:order-1">
            <div className="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#f9edd1] text-[#b8860b] mb-6">
              <MapPin className="w-7 h-7" />
            </div>
            <h2
              id="map-search-heading"
              className="font-serif text-2xl sm:text-3xl lg:text-4xl font-semibold text-[#1a1a1a] mb-4 text-left"
            >
              Search hotels on the map
            </h2>
            <p className="text-[#5c5852] max-w-lg mb-10 leading-relaxed text-left text-base sm:text-lg">
              Pick a location, set your radius, and discover hotels in that area. Perfect for finding stays near
              landmarks or neighborhoods.
            </p>
            <Link
              to="/hotels/map"
              className="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-8 py-4 rounded-xl bg-[#1a1a1a] text-white font-semibold hover:bg-[#2d2a28] transition-colors shadow-[0_8px_24px_rgb(26_26_26_/0.2)]"
            >
              <MapPin className="w-5 h-5 shrink-0" />
              Open map search
            </Link>
          </div>
          <div className="relative min-h-[280px] sm:min-h-[340px] lg:min-h-0 h-[320px] lg:h-auto order-1 lg:order-2 border-b lg:border-b-0 lg:border-l border-[#e8e4dd]">
            <iframe
              title="Map preview — Italy and surrounding region"
              src="https://www.openstreetmap.org/export/embed.html?bbox=6.5%2C35.5%2C19.5%2C47.5&amp;layer=mapnik"
              className="absolute inset-0 w-full h-full border-0 grayscale-[0.15] contrast-[1.02]"
              loading="lazy"
            />
            <div
              className="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#faf8f5]/90 via-transparent to-transparent lg:bg-gradient-to-r lg:from-[#faf8f5] lg:via-[#faf8f5]/20 lg:to-transparent lg:from-0% lg:via-15% lg:to-60%"
              aria-hidden
            />
            <div className="pointer-events-none absolute bottom-4 left-4 right-4 flex items-end justify-between gap-2 text-[10px] sm:text-xs text-[#5c5852]">
              <span className="bg-white/90 backdrop-blur-sm px-2 py-1 rounded-md border border-[#e8e4dd] shadow-sm">
                © OpenStreetMap contributors
              </span>
            </div>
          </div>
        </div>
      </section>

      <div className="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 pt-16 sm:pt-20 pb-20 sm:pb-28">
        {/* Newsletter */}
        <section className="py-16 sm:py-20 bg-[#1a1a1a] rounded-3xl px-4 sm:px-6 lg:px-8 xl:px-12 text-center w-full">
            <h2 className="font-serif text-2xl sm:text-3xl font-semibold text-white mb-2">
              Get travel inspiration
            </h2>
            <p className="text-[#a39e94] mb-6 max-w-md mx-auto leading-relaxed">
              Sign up for deals, new destinations, and tips for your next trip.
            </p>
            <form onSubmit={(e) => e.preventDefault()} className="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
              <input
                type="email"
                placeholder="Your email"
                className="flex-1 h-12 px-4 rounded-xl border border-[#45423d] bg-[#2d2a28] text-white placeholder-[#7a756d] focus:ring-2 focus:ring-[#b8860b]/40 focus:border-[#b8860b]"
              />
              <button
                type="submit"
                className="h-12 px-6 rounded-xl bg-[#b8860b] text-white font-semibold hover:bg-[#996f09] transition-colors"
              >
                Subscribe
              </button>
            </form>
        </section>
      </div>
    </div>
  );
}
