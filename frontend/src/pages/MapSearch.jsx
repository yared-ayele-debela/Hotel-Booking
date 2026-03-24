import { useState, useRef, useEffect, useMemo } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { MapContainer, TileLayer, Marker, Popup, useMapEvents } from 'react-leaflet';
import L from 'leaflet';
import { MapPin, Search, SlidersHorizontal, X, ChevronRight } from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import { useWishlist } from '../hooks/useWishlist';
import { HotelCard } from '../components/HotelCard';
import { HotelListSkeleton } from '../components/Skeleton';
import ErrorMessage from '../components/ErrorMessage';
import { parseHotelSearchResponse } from '../lib/hotelSearch';
import 'leaflet/dist/leaflet.css';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
});

/** Search area center — ring + dot (distinct from hotel markers) */
const searchAreaIcon = L.divIcon({
  className: 'map-marker-custom map-marker-search-area',
  html: `
    <div style="width:32px;height:32px;border-radius:50%;background:#fff;border:3px solid #b8860b;box-shadow:0 4px 14px rgba(26,26,26,0.22);display:flex;align-items:center;justify-content:center;">
      <div style="width:10px;height:10px;border-radius:50%;background:#b8860b;"></div>
    </div>`,
  iconSize: [32, 32],
  iconAnchor: [16, 16],
  popupAnchor: [0, -12],
});

/** Hotel — dark pin with gold accent + building glyph */
const hotelMapIcon = L.divIcon({
  className: 'map-marker-custom map-marker-hotel',
  html: `
    <div style="width:40px;height:48px;display:flex;flex-direction:column;align-items:center;filter:drop-shadow(0 4px 10px rgba(26,26,26,0.35));">
      <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(165deg,#2d2a28 0%,#1a1a1a 100%);border:2px solid #b8860b;display:flex;align-items:center;justify-content:center;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#faf8f5" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/>
        </svg>
      </div>
      <div style="width:0;height:0;border-left:7px solid transparent;border-right:7px solid transparent;border-top:9px solid #1a1a1a;margin-top:-2px;"></div>
    </div>`,
  iconSize: [40, 48],
  iconAnchor: [20, 48],
  popupAnchor: [0, -44],
});

const DEFAULT_CENTER = [41.9, 12.5]; // Rome
const DEFAULT_ZOOM = 5;
const RADIUS_OPTIONS = [5, 10, 25, 50, 100];

function LocationPicker({ onPick }) {
  useMapEvents({
    click(e) {
      onPick(e.latlng.lat, e.latlng.lng);
    },
  });
  return null;
}

function WishlistHeart({ hotelId, checkIn, checkOut }) {
  const { user } = useAuth();
  const { isInWishlist, addToWishlist, removeFromWishlist, addPending, removePending } = useWishlist(!!user);

  if (!user) {
    return (
      <Link to="/login" className="absolute top-2 right-2 p-2 rounded-full bg-white/90 shadow hover:bg-white" aria-label="Log in to save">
        <HeartIcon filled={false} />
      </Link>
    );
  }
  const inList = isInWishlist(hotelId);
  const handleClick = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (addPending || removePending) return;
    try {
      if (inList) await removeFromWishlist(hotelId);
      else await addToWishlist({ hotelId, checkIn, checkOut });
    } catch {}
  };
  return (
    <button
      type="button"
      onClick={handleClick}
      disabled={addPending || removePending}
      className="absolute top-2 right-2 p-2 rounded-full bg-white/90 shadow hover:bg-white disabled:opacity-60"
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

export default function MapSearch() {
  const [searchParams, setSearchParams] = useSearchParams();
  const latParam = searchParams.get('latitude');
  const lngParam = searchParams.get('longitude');
  const radiusParam = searchParams.get('radius_km');
  const checkIn = searchParams.get('check_in') || '';
  const checkOut = searchParams.get('check_out') || '';

  const [center, setCenter] = useState(
    latParam && lngParam ? [parseFloat(latParam), parseFloat(lngParam)] : DEFAULT_CENTER
  );
  const [zoom, setZoom] = useState(latParam && lngParam ? 12 : DEFAULT_ZOOM);
  const [marker, setMarker] = useState(
    latParam && lngParam ? [parseFloat(latParam), parseFloat(lngParam)] : null
  );
  const [radius, setRadius] = useState(radiusParam ? parseInt(radiusParam, 10) : 50);
  const [searchText, setSearchText] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const [suggestionsOpen, setSuggestionsOpen] = useState(false);
  const debounceRef = useRef(null);
  const searchRef = useRef(null);

  const hasSearched = latParam && lngParam && radiusParam;

  useEffect(() => {
    const handleClickOutside = (e) => {
      if (searchRef.current && !searchRef.current.contains(e.target)) setSuggestionsOpen(false);
    };
    document.addEventListener('click', handleClickOutside);
    return () => document.removeEventListener('click', handleClickOutside);
  }, []);

  const fetchSuggestions = (text) => {
    if (text.length < 2) {
      setSuggestions([]);
      return;
    }
    api.get('/geocode/autocomplete', { params: { text } })
      .then((res) => setSuggestions(res.data?.features ?? []))
      .catch(() => setSuggestions([]));
  };

  const handleSearchInput = (e) => {
    const v = e.target.value;
    setSearchText(v);
    clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => fetchSuggestions(v), 300);
    setSuggestionsOpen(true);
  };

  const applySearchFromCoords = (lat, lng, radiusKm) => {
    const next = new URLSearchParams(searchParams);
    next.set('latitude', String(lat));
    next.set('longitude', String(lng));
    next.set('radius_km', String(radiusKm));
    next.set('page', '1');
    if (checkIn) next.set('check_in', checkIn);
    if (checkOut) next.set('check_out', checkOut);
    setSearchParams(next);
  };

  const selectSuggestion = (f) => {
    const p = f.properties || {};
    const lat = p.lat ?? f.lat;
    const lon = p.lon ?? f.lon;
    if (lat != null && lon != null) {
      setCenter([lat, lon]);
      setMarker([lat, lon]);
      setZoom(14);
      setSearchText(p.formatted || p.address_line1 || '');
      applySearchFromCoords(lat, lon, radius);
    }
    setSuggestionsOpen(false);
  };

  const handleMapClick = (lat, lng) => {
    setMarker([lat, lng]);
    setCenter([lat, lng]);
    applySearchFromCoords(lat, lng, radius);
  };

  const applySearch = () => {
    if (!marker) return;
    const [lat, lng] = marker;
    applySearchFromCoords(lat, lng, radius);
  };

  const { data, isLoading, isError, isFetching, error } = useQuery({
    queryKey: ['hotels', 'map', latParam, lngParam, radiusParam, checkIn, checkOut],
    queryFn: async () => {
      const params = { latitude: latParam, longitude: lngParam, radius_km: radiusParam, per_page: 24 };
      if (checkIn) params.check_in = checkIn;
      if (checkOut) params.check_out = checkOut;
      const res = await api.get('/hotels', { params });
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load');
      return res.data;
    },
    enabled: !!hasSearched,
    placeholderData: keepPreviousData,
  });

  const { hotels, total } = useMemo(() => parseHotelSearchResponse(data), [data]);

  return (
    <div className="flex-1 flex flex-col min-h-0 w-full">
      {/* Search bar — full width */}
      <div className="bg-white border-b border-stone-200 shrink-0">
        <div className="w-full px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex flex-col sm:flex-row gap-4">
            <div className="flex-1 relative" ref={searchRef}>
              <MapPin className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
              <input
                type="text"
                value={searchText}
                onChange={handleSearchInput}
                onFocus={() => suggestions.length > 0 && setSuggestionsOpen(true)}
                placeholder="Search city or address..."
                className="w-full h-12 pl-10 pr-4 rounded-xl border border-stone-200 text-stone-900 placeholder-stone-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white"
              />
              {suggestionsOpen && suggestions.length > 0 && (
                <div className="absolute top-full left-0 right-0 mt-1 bg-white rounded-xl border border-stone-200 shadow-lg py-2 max-h-64 overflow-y-auto z-30">
                  {suggestions.map((f, i) => {
                    const p = f.properties || {};
                    const label = p.formatted || p.address_line1 || 'Unknown';
                    return (
                      <button
                        key={i}
                        type="button"
                        onClick={() => selectSuggestion(f)}
                        className="w-full px-4 py-2.5 text-left text-sm text-stone-700 hover:bg-amber-50 flex items-center gap-2 transition-colors"
                      >
                        <MapPin className="w-4 h-4 shrink-0 text-amber-600" />
                        {label}
                      </button>
                    );
                  })}
                </div>
              )}
            </div>
            <div className="flex items-center gap-3">
              <div className="flex items-center gap-2">
                <SlidersHorizontal className="w-5 h-5 text-stone-500" />
                <span className="text-sm font-medium text-stone-700">Radius:</span>
                <select
                  value={radius}
                  onChange={(e) => setRadius(Number(e.target.value))}
                  className="rounded-xl border border-stone-200 px-4 py-2.5 text-sm text-stone-700 bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                >
                  {RADIUS_OPTIONS.map((r) => (
                    <option key={r} value={r}>{r} km</option>
                  ))}
                </select>
              </div>
              <button
                type="button"
                onClick={applySearch}
                disabled={!marker}
                className="h-12 px-6 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 transition-colors"
              >
                <Search className="w-5 h-5" />
                Search hotels
              </button>
            </div>
          </div>
          <p className="mt-2 text-sm text-stone-500">
            Click the map or choose a place from search to find hotels (default radius 50 km). Adjust radius and click &quot;Search hotels&quot; to refresh.
          </p>
          {hasSearched && (
            <div
              className="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 rounded-xl border border-stone-200/80 bg-stone-50/90 px-4 py-3 text-sm"
              aria-live="polite"
            >
              {isLoading && !data ? (
                <span className="inline-block h-5 w-48 max-w-full rounded-md bg-stone-200 animate-pulse" />
              ) : (
                <>
                  <span className="font-semibold text-stone-900">
                    {total} {total === 1 ? 'hotel' : 'hotels'} match this search
                  </span>
                  <span className="text-stone-300" aria-hidden>
                    ·
                  </span>
                  <span className="text-stone-600">
                    {radiusParam} km radius
                    {checkIn && checkOut && (
                      <span className="text-stone-500">
                        {' '}
                        · dates applied
                      </span>
                    )}
                  </span>
                  {isFetching && !isLoading && (
                    <span className="text-xs font-medium text-amber-600">Refreshing…</span>
                  )}
                </>
              )}
            </div>
          )}
        </div>
      </div>

      {/* Map + Results — padded card layout */}
      <div className="flex-1 flex flex-col lg:flex-row min-h-[400px] overflow-hidden px-4 sm:px-6 lg:px-8 pb-6 sm:pb-8 pt-2 gap-4 sm:gap-5">
        {/* Map — takes half on desktop, explicit height required for Leaflet */}
        <div className="lg:w-1/2 shrink-0 h-[400px] sm:h-[500px] lg:h-[calc(100vh-10rem)] min-h-[350px] p-1 sm:p-1.5">
          <div className="w-full h-full rounded-2xl overflow-hidden border border-stone-200/90 bg-stone-100 shadow-[0_4px_24px_rgba(26,26,26,0.06)] ring-1 ring-stone-200/50">
            <MapContainer
              center={center}
              zoom={zoom}
              style={{ height: '100%', width: '100%' }}
              scrollWheelZoom={true}
            >
            <TileLayer
              attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <LocationPicker onPick={handleMapClick} />
            {marker && (
              <Marker position={marker} icon={searchAreaIcon}>
                <Popup>Search area center. Results update automatically when you click the map.</Popup>
              </Marker>
            )}
            {hasSearched && hotels.filter((h) => h.latitude != null && h.longitude != null).map((h) => (
              <Marker key={h.id} position={[h.latitude, h.longitude]} icon={hotelMapIcon}>
                <Popup>
                  <Link to={`/hotels/${h.id}`} className="font-medium text-amber-600 hover:underline">
                    {h.name}
                  </Link>
                  <br />
                  <span className="text-stone-600 text-sm">{[h.city, h.country].filter(Boolean).join(', ')}</span>
                </Popup>
              </Marker>
            ))}
            </MapContainer>
          </div>
        </div>

        {/* Results panel */}
        <div className="lg:w-1/2 flex flex-col min-h-0 p-1 sm:p-1.5">
          <div className="flex flex-col flex-1 bg-stone-50 rounded-2xl border border-stone-200/90 overflow-y-auto min-h-[280px] lg:min-h-0 shadow-[0_4px_24px_rgba(26,26,26,0.06)] ring-1 ring-stone-200/50">
          <div className="p-4 sm:p-6 lg:p-8">
            <nav className="text-sm text-stone-600 mb-4">
              <Link to="/" className="hover:text-amber-600">Home</Link>
              <span className="mx-1">›</span>
              <Link to="/hotels" className="hover:text-amber-600">Hotels</Link>
              <span className="mx-1">›</span>
              <span className="text-stone-900 font-medium">Map search</span>
            </nav>

            {!hasSearched ? (
              <div className="py-12 text-center">
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 text-amber-600 mb-4">
                  <MapPin className="w-8 h-8" />
                </div>
                <h2 className="text-xl font-semibold text-stone-900 mb-2">Pick a location on the map</h2>
                <p className="text-stone-600 max-w-sm mx-auto mb-6">
                  Search for a city or click anywhere on the map — hotels load automatically (50 km radius by default). Change radius and use Search hotels to refresh.
                </p>
                <div className="flex flex-wrap justify-center gap-3">
                  <button
                    type="button"
                    onClick={() => {
                      setCenter(DEFAULT_CENTER);
                      setMarker(DEFAULT_CENTER);
                      setZoom(5);
                      setSearchText('');
                    }}
                    className="px-4 py-2.5 rounded-xl border border-stone-200 hover:bg-amber-50 text-sm font-medium transition-colors"
                  >
                    Reset map
                  </button>
                </div>
              </div>
            ) : isError ? (
              <ErrorMessage message={error?.response?.data?.message || error?.message || 'Could not load hotels'} />
            ) : isLoading ? (
              <HotelListSkeleton count={4} />
            ) : hotels.length === 0 ? (
              <div className="py-12 text-center">
                <p className="text-stone-600 text-lg font-medium mb-2">No hotels found in this area</p>
                <p className="text-stone-500 mb-6">Try increasing the search radius or picking a different location.</p>
                <button
                  type="button"
                  onClick={() => setSearchParams({})}
                  className="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-colors"
                >
                  <MapPin className="w-5 h-5" />
                  Pick another location
                </button>
              </div>
            ) : (
              <>
                <div className="flex items-center justify-between mb-6 gap-3">
                  <div className="min-w-0">
                    <h2 className="text-xl font-bold text-stone-900">
                      {total} {total === 1 ? 'hotel' : 'hotels'} in this area
                    </h2>
                    <p className="mt-1 text-sm text-stone-500">
                      {radiusParam} km radius
                      {isFetching && !isLoading && (
                        <span className="ml-2 text-xs font-medium text-amber-600">Updating…</span>
                      )}
                    </p>
                  </div>
                  <Link
                    to={`/hotels?latitude=${latParam}&longitude=${lngParam}&radius_km=${radiusParam}${checkIn ? `&check_in=${checkIn}&check_out=${checkOut}` : ''}`}
                    className="text-sm font-medium text-amber-600 hover:text-amber-700 flex items-center gap-1"
                  >
                    View all <ChevronRight className="w-4 h-4" />
                  </Link>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  {hotels.map((h) => (
                    <HotelCard
                      key={h.id}
                      hotel={h}
                      to={`/hotels/${h.id}${checkIn ? `?check_in=${checkIn}&check_out=${checkOut}` : ''}`}
                      imageOverlay={<WishlistHeart hotelId={h.id} checkIn={checkIn || undefined} checkOut={checkOut || undefined} />}
                    >
                      <span className="mt-3 inline-flex items-center text-sm font-medium text-amber-600">
                        View details →
                      </span>
                    </HotelCard>
                  ))}
                </div>
              </>
            )}
          </div>
          </div>
        </div>
      </div>
    </div>
  );
}
