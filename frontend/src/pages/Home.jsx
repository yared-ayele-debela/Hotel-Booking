import { useState, useMemo } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Search, MapPin, Calendar, Users, ChevronRight } from 'lucide-react';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { HotelCard } from '../components/HotelCard';

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
    if (guests > 0) params.set('guests', guests);
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
      const res = await api.get('/cities', { params: { limit: 12 } });
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

  const { data: topRatedData } = useQuery({
    queryKey: ['hotels', 'top-rated'],
    queryFn: async () => {
      const res = await api.get('/hotels', { params: { per_page: 4, min_rating: 4 } });
      if (!res.data?.success) throw new Error('Failed to load');
      const raw = res.data.data;
      const list = Array.isArray(raw) ? raw : raw?.data ?? [];
      return list;
    },
  });

  const cities = Array.isArray(citiesData) ? citiesData : [];
  const countries = Array.isArray(countriesData) ? countriesData : [];
  const hotels = Array.isArray(hotelsData) ? hotelsData : [];
  const topRated = Array.isArray(topRatedData) ? topRatedData : [];
  const weekendDeals = hotels.slice(0, 4);
  const guestsLove = topRated.length ? topRated : hotels.slice(0, 4);

  return (
    <main className="min-h-screen bg-stone-50 overflow-x-hidden" role="main">
      {/* Hero + Search – full width of screen */}
      <header className="relative bg-stone-900 text-white overflow-hidden w-screen max-w-none left-1/2 -translate-x-1/2">
        <div className="absolute inset-0">
          <img
            src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&q=80"
            alt=""
            className="w-full h-full object-cover opacity-60"
          />
          <div className="absolute inset-0 bg-gradient-to-b from-black/50 to-black/70" />
        </div>
        <div className="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold text-center mb-2">
            Find your next stay
          </h1>
          <p className="text-stone-200 text-center mb-8 max-w-xl mx-auto">
            Search hotels and unique stays by city and dates.
          </p>
          <form onSubmit={handleSearch} className="max-w-4xl mx-auto bg-white rounded-xl shadow-xl p-4 sm:p-6">
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
              <div className="lg:col-span-2 relative">
                <MapPin className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" />
                <input
                  type="text"
                  placeholder="City or destination"
                  value={city}
                  onChange={(e) => setCity(e.target.value)}
                  className="w-full h-11 pl-10 pr-3 rounded-lg border border-stone-300 text-stone-900 placeholder-stone-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                />
              </div>
              <div className="relative">
                <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" />
                <input
                  type="date"
                  value={checkIn}
                  onChange={(e) => setCheckIn(e.target.value)}
                  min={today}
                  className="w-full h-11 pl-10 pr-3 rounded-lg border border-stone-300 text-stone-900 focus:ring-2 focus:ring-amber-500"
                />
              </div>
              <div className="relative">
                <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" />
                <input
                  type="date"
                  value={checkOut}
                  onChange={(e) => setCheckOut(e.target.value)}
                  min={checkIn || tomorrow}
                  className="w-full h-11 pl-10 pr-3 rounded-lg border border-stone-300 text-stone-900 focus:ring-2 focus:ring-amber-500"
                />
              </div>
              <div className="relative flex items-center">
                <Users className="absolute left-3 w-5 h-5 text-stone-400" />
                <select
                  value={guests}
                  onChange={(e) => setGuests(Number(e.target.value))}
                  className="w-full h-11 pl-10 pr-3 rounded-lg border border-stone-300 text-stone-900 focus:ring-2 focus:ring-amber-500 appearance-none bg-white"
                >
                  {[1, 2, 3, 4, 5, 6].map((n) => (
                    <option key={n} value={n}>{n} {n === 1 ? 'guest' : 'guests'}</option>
                  ))}
                </select>
              </div>
            </div>
            <button
              type="submit"
              className="mt-4 w-full sm:w-auto sm:px-8 h-11 rounded-lg bg-amber-600 text-white font-medium hover:bg-amber-700 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 flex items-center justify-center gap-2"
            >
              <Search className="w-5 h-5" />
              Search
            </button>
          </form>
        </div>
      </header>

      <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-14 sm:space-y-20">
        {/* Trending destinations – cities with images */}
        <section aria-labelledby="trending-heading">
          <h2 id="trending-heading" className="text-2xl sm:text-3xl font-bold text-stone-900 mb-6">
            Trending destinations
          </h2>
          <p className="text-stone-600 mb-8 max-w-2xl">
            Explore popular cities and find hotels in the places guests love.
          </p>
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            {cities.length === 0 ? (
              [...Array(8)].map((_, i) => (
                <div key={i} className="aspect-[3/2] rounded-2xl bg-stone-200 animate-pulse" />
              ))
            ) : (
              cities.map((c) => (
                <LocationCard
                  key={c.id}
                  to={`/hotels?city=${encodeURIComponent(c.name)}`}
                  name={c.name}
                  subtext={c.country_name}
                  image={c.image}
                />
              ))
            )}
          </div>
        </section>

        {/* Stay at our top unique properties */}
        <section aria-labelledby="top-properties-heading">
          <div className="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div>
              <h2 id="top-properties-heading" className="text-2xl sm:text-3xl font-bold text-stone-900">
                Stay at our top unique properties
              </h2>
              <p className="text-stone-600 mt-2">Handpicked stays for a memorable trip.</p>
            </div>
            <Link to="/hotels" className="text-amber-600 font-medium hover:text-amber-700 inline-flex items-center gap-1">
              View all <ChevronRight className="w-4 h-4" />
            </Link>
          </div>
          {hotelsLoading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {[...Array(4)].map((_, i) => (
                <div key={i} className="rounded-2xl bg-stone-200 animate-pulse h-64" />
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {(hotels.slice(0, 4)).map((h) => (
                <HotelCard key={h.id} hotel={h} nights={2} />
              ))}
            </div>
          )}
        </section>

        {/* Deals for the weekend */}
        <section aria-labelledby="deals-heading">
          <h2 id="deals-heading" className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2">
            Deals for the weekend
          </h2>
          <p className="text-stone-600 mb-8">Save on your next short break.</p>
          {hotelsLoading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {[...Array(4)].map((_, i) => (
                <div key={i} className="rounded-2xl bg-stone-200 animate-pulse h-64" />
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {weekendDeals.map((h) => (
                <HotelCard key={h.id} hotel={h} dealLabel="Early 2026 Deal" nights={2} />
              ))}
            </div>
          )}
        </section>

        {/* Homes guests love */}
        <section aria-labelledby="guests-love-heading">
          <h2 id="guests-love-heading" className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2">
            Homes guests love
          </h2>
          <p className="text-stone-600 mb-8">Highly rated by guests.</p>
          {hotelsLoading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {[...Array(4)].map((_, i) => (
                <div key={i} className="rounded-2xl bg-stone-200 animate-pulse h-64" />
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {guestsLove.map((h) => (
                <HotelCard key={h.id} hotel={h} nights={2} />
              ))}
            </div>
          )}
        </section>

        {/* Browse popular locations – by country or city */}
        <section aria-labelledby="browse-heading">
          <h2 id="browse-heading" className="text-2xl sm:text-3xl font-bold text-stone-900 mb-2">
            Browse popular locations
          </h2>
          <p className="text-stone-600 mb-8">Explore hotels by country or city.</p>
          <div className="space-y-8">
            {countries.length > 0 && (
              <div>
                <h3 className="text-lg font-semibold text-stone-800 mb-4">By country</h3>
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                  {countries.map((c) => (
                    <LocationCard
                      key={c.id}
                      to={`/hotels?country=${encodeURIComponent(c.name)}`}
                      name={c.name}
                      image={c.image}
                    />
                  ))}
                </div>
              </div>
            )}
            {cities.length > 0 && (
              <div>
                <h3 className="text-lg font-semibold text-stone-800 mb-4">By city</h3>
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                  {cities.slice(0, 8).map((c) => (
                    <LocationCard
                      key={c.id}
                      to={`/hotels?city=${encodeURIComponent(c.name)}`}
                      name={c.name}
                      subtext={c.country_name}
                      image={c.image}
                    />
                  ))}
                </div>
              </div>
            )}
            {countries.length === 0 && cities.length === 0 && (
              <p className="text-stone-500">No locations to show yet. Browse all <Link to="/hotels" className="text-amber-600 underline">hotels</Link>.</p>
            )}
          </div>
        </section>
      </div>
    </main>
  );
}
