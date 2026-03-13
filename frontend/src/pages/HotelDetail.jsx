import { useParams, useSearchParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { useState, useMemo } from 'react';
import { MapPin, Calendar, Users, X, ChevronLeft, ChevronRight, Star } from 'lucide-react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import { useWishlist } from '../hooks/useWishlist';
import { HotelDetailSkeleton } from '../components/Skeleton';
import ErrorMessage from '../components/ErrorMessage';
import { formatPrice, getRatingLabel, calculateNights, cn } from '../lib/utils';
import { AmenityIcon } from '../components/AmenityIcon';

const TABS = ['Overview', 'Rooms', 'Reviews', 'Location'];

function WishlistHeart({ hotelId, checkIn, checkOut }) {
  const { user } = useAuth();
  const { isInWishlist, addToWishlist, removeFromWishlist, addPending, removePending } = useWishlist(!!user);
  if (!user) {
    return (
      <Link to="/login" className="p-2 rounded-full border border-stone-200 hover:bg-stone-50 inline-flex" aria-label="Log in to save to wishlist">
        <HeartIcon filled={false} />
      </Link>
    );
  }
  const inList = isInWishlist(Number(hotelId));
  const pending = addPending || removePending;
  const handleClick = async () => {
    if (pending) return;
    try {
      if (inList) await removeFromWishlist(hotelId);
      else await addToWishlist({ hotelId: Number(hotelId), checkIn, checkOut });
    } catch (_) {}
  };
  return (
    <button type="button" onClick={handleClick} disabled={pending} className="p-2 rounded-full border border-stone-200 hover:bg-stone-50 disabled:opacity-60 inline-flex" aria-label={inList ? 'Remove from wishlist' : 'Add to wishlist'}>
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

function StarDistribution({ reviews }) {
  const counts = useMemo(() => {
    const c = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
    reviews.forEach((r) => {
      const n = Math.round(Number(r.rating));
      if (n >= 1 && n <= 5) c[n]++;
    });
    return c;
  }, [reviews]);
  const total = reviews.length;
  if (total === 0) return null;
  return (
    <div className="space-y-2">
      {[5, 4, 3, 2, 1].map((stars) => {
        const pct = total > 0 ? (counts[stars] / total) * 100 : 0;
        return (
          <div key={stars} className="flex items-center gap-3">
            <span className="text-sm text-stone-600 w-16">{stars} star{stars > 1 ? 's' : ''}</span>
            <div className="flex-1 h-2 bg-stone-200 rounded-full overflow-hidden">
              <div className="h-full bg-amber-500 rounded-full" style={{ width: `${pct}%` }} />
            </div>
            <span className="text-sm text-stone-600 w-12">{counts[stars]}</span>
          </div>
        );
      })}
    </div>
  );
}

export default function HotelDetail() {
  const { id } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const checkIn = searchParams.get('check_in');
  const checkOut = searchParams.get('check_out');
  const [activeTab, setActiveTab] = useState('Overview');
  const [localCheckIn, setLocalCheckIn] = useState(checkIn || '');
  const [localCheckOut, setLocalCheckOut] = useState(checkOut || '');
  const [guests, setGuests] = useState(1);
  const [selectedRoom, setSelectedRoom] = useState(null);
  const [roomQuantity, setRoomQuantity] = useState(1);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [isGalleryOpen, setIsGalleryOpen] = useState(false);
  const [currentGalleryImages, setCurrentGalleryImages] = useState([]);
  const [reviewsPage, setReviewsPage] = useState(1);

  const handleDateSearch = () => {
    if (localCheckIn && localCheckOut) {
      setSearchParams({ check_in: localCheckIn, check_out: localCheckOut });
    }
  };

  const { data, isLoading, isError, error, refetch } = useQuery({
    queryKey: ['hotel', id],
    queryFn: async () => {
      const res = await api.get(`/hotels/${id}`);
      if (!res.data?.success) throw new Error(res.data?.message || 'Failed to load');
      return res.data;
    },
    enabled: !!id,
  });

  const { data: reviewsData } = useQuery({
    queryKey: ['reviews', 'hotel', id, reviewsPage],
    queryFn: async () => {
      const res = await api.get('/reviews', { params: { hotel_id: id, per_page: 10, page: reviewsPage } });
      return res.data;
    },
    enabled: !!id,
  });

  const hotel = data?.data ?? data;
  const reviewsRaw = reviewsData?.data ?? reviewsData?.data?.data ?? [];
  const reviews = Array.isArray(reviewsRaw) ? reviewsRaw : [];
  const reviewsMeta = reviewsData?.meta ?? {};

  const today = useMemo(() => new Date().toISOString().split('T')[0], []);
  const tomorrow = useMemo(() => {
    const d = new Date();
    d.setDate(d.getDate() + 1);
    return d.toISOString().split('T')[0];
  }, []);

  const nights = checkIn && checkOut ? calculateNights(checkIn, checkOut) : null;
  const effectiveCheckIn = checkIn || localCheckIn;
  const effectiveCheckOut = checkOut || localCheckOut;
  const effectiveNights = effectiveCheckIn && effectiveCheckOut ? calculateNights(effectiveCheckIn, effectiveCheckOut) : null;

  const priceSummary = useMemo(() => {
    if (!selectedRoom || !effectiveNights || effectiveNights < 1) return null;
    const subTotal = Number(selectedRoom.base_price) * effectiveNights * roomQuantity;
    const taxRate = hotel?.tax_rate ?? 0;
    const taxRatePct = taxRate * 100;
    const taxAmount = hotel?.tax_inclusive ? 0 : subTotal * taxRate;
    const total = subTotal + taxAmount;
    return { subTotal, taxAmount, taxRatePct, total };
  }, [selectedRoom, effectiveNights, roomQuantity, hotel?.tax_rate, hotel?.tax_inclusive]);

  const bookUrl = selectedRoom && effectiveCheckIn && effectiveCheckOut
    ? `/book?hotel_id=${id}&room_id=${selectedRoom.id}&check_in=${effectiveCheckIn}&check_out=${effectiveCheckOut}&quantity=${roomQuantity}`
    : null;

  const images = hotel?.images ?? [];
  const rooms = hotel?.rooms ?? [];
  const roomsForGuests = useMemo(() => {
    const fit = rooms.filter((r) => r.capacity >= guests);
    return fit.length > 0 ? fit : rooms;
  }, [rooms, guests]);

  if (isLoading) {
    return (
      <div className="py-6">
        <HotelDetailSkeleton />
      </div>
    );
  }

  if (isError || !hotel) {
    return (
      <div className="py-6">
        <ErrorMessage message={error?.response?.data?.message || error?.message || 'Hotel not found'} onRetry={() => refetch()} />
      </div>
    );
  }

  return (
    <div className="py-4 sm:py-6">
      {/* Header: Image gallery + name, location, rating, wishlist */}
      <div className="mb-6">
        <div className="relative rounded-2xl overflow-hidden bg-stone-200 aspect-[21/9] sm:aspect-[3/1] max-h-[400px]">
          {images.length > 0 ? (
            <>
              <img
                src={images[selectedImageIndex]?.url || images[0]?.url}
                alt={images[selectedImageIndex]?.alt_text || hotel.name}
                className="w-full h-full object-cover cursor-pointer"
                onClick={() => {
                  setCurrentGalleryImages(images);
                  setSelectedImageIndex(0);
                  setIsGalleryOpen(true);
                }}
              />
              {images.length > 1 && (
                <>
                  <div className="absolute bottom-4 right-4 bg-black/60 text-white px-3 py-1 rounded-lg text-sm">
                    {selectedImageIndex + 1} / {images.length}
                  </div>
                  <button
                    type="button"
                    onClick={(e) => { e.stopPropagation(); setSelectedImageIndex((i) => (i - 1 + images.length) % images.length); }}
                    className="absolute left-2 top-1/2 -translate-y-1/2 p-2 rounded-full bg-white/90 hover:bg-white shadow"
                    aria-label="Previous image"
                  >
                    <ChevronLeft className="w-5 h-5" />
                  </button>
                  <button
                    type="button"
                    onClick={(e) => { e.stopPropagation(); setSelectedImageIndex((i) => (i + 1) % images.length); }}
                    className="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-full bg-white/90 hover:bg-white shadow"
                    aria-label="Next image"
                  >
                    <ChevronRight className="w-5 h-5" />
                  </button>
                </>
              )}
            </>
          ) : (
            <div className="w-full h-full flex items-center justify-center text-stone-500">
              <MapPin className="w-16 h-16" />
            </div>
          )}
        </div>

        {images.length > 1 && (
          <div className="flex gap-2 mt-3 overflow-x-auto pb-2">
            {images.map((img, idx) => (
              <button
                key={img.id}
                onClick={() => setSelectedImageIndex(idx)}
                className={cn(
                  'flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-all',
                  selectedImageIndex === idx ? 'border-amber-500 scale-105' : 'border-stone-300 hover:border-stone-400'
                )}
              >
                <img src={img.url} alt={img.alt_text || ''} className="w-full h-full object-cover" />
              </button>
            ))}
          </div>
        )}
      </div>

      <div className="flex flex-col lg:flex-row gap-6 lg:gap-8">
        {/* Main content */}
        <div className="flex-1 min-w-0">
          <div className="flex flex-wrap items-start justify-between gap-4 mb-4">
            <div>
              <h1 className="text-2xl sm:text-3xl font-bold text-stone-900">{hotel.name}</h1>
              <p className="text-stone-600 mt-1 flex items-center gap-1">
                <MapPin className="w-4 h-4" />
                {[hotel.city, hotel.country].filter(Boolean).join(', ')}
              </p>
              <div className="flex items-center gap-3 mt-2">
                {hotel.average_rating != null && (
                  <span className="inline-flex items-center gap-1 font-semibold text-stone-900">
                    <Star className="w-5 h-5 fill-amber-400 text-amber-400" />
                    {Number(hotel.average_rating).toFixed(1)} {getRatingLabel(hotel.average_rating)}
                  </span>
                )}
                {hotel.review_count != null && hotel.review_count > 0 && (
                  <span className="text-stone-600 text-sm">{hotel.review_count} reviews</span>
                )}
              </div>
            </div>
            <WishlistHeart hotelId={id} checkIn={checkIn} checkOut={checkOut} />
          </div>

          {/* Tabs */}
          <div className="border-b border-stone-200 mb-6">
            <nav className="flex gap-4 overflow-x-auto" aria-label="Sections">
              {TABS.map((tab) => (
                <button
                  key={tab}
                  onClick={() => setActiveTab(tab)}
                  className={cn(
                    'py-3 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors',
                    activeTab === tab ? 'border-amber-600 text-amber-600' : 'border-transparent text-stone-600 hover:text-stone-900'
                  )}
                >
                  {tab}
                </button>
              ))}
            </nav>
          </div>

          {/* Tab content */}
          {activeTab === 'Overview' && (
            <section className="space-y-6">
              {hotel.description && <p className="text-stone-700 leading-relaxed">{hotel.description}</p>}
              {hotel.amenities?.length > 0 && (
                <div>
                  <h3 className="font-semibold text-stone-900 mb-3">Amenities</h3>
                  <div className="flex flex-wrap gap-3">
                    {hotel.amenities.map((a) => (
                      <span key={a.id} className="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-stone-100 text-stone-700 text-sm">
                        <AmenityIcon slug={a.slug} className="w-4 h-4 text-amber-600" />
                        {a.name}
                      </span>
                    ))}
                  </div>
                </div>
              )}
              {(hotel.check_in || hotel.check_out) && (
                <div>
                  <h3 className="font-semibold text-stone-900 mb-2">Check-in / Check-out</h3>
                  <p className="text-stone-600 text-sm">Check-in: {hotel.check_in ?? '—'} · Check-out: {hotel.check_out ?? '—'}</p>
                </div>
              )}
              {(hotel.cancellation_policy_summary || hotel.cancellation_policy) && (
                <div>
                  <h3 className="font-semibold text-stone-900 mb-2">Cancellation policy</h3>
                  <p className="text-stone-600 text-sm">{hotel.cancellation_policy_summary || 'See cancellation terms at booking.'}</p>
                </div>
              )}
            </section>
          )}

          {activeTab === 'Rooms' && (
            <section className="space-y-6">
              {rooms.length === 0 ? (
                <p className="text-stone-600">No rooms available.</p>
              ) : (
                <ul className="space-y-6">
                  {rooms.map((room) => {
                    const roomImg = room.images?.[0] || room.banner_image || images[0];
                    const price = room.base_price != null ? Number(room.base_price) : null;
                    const totalPrice = price != null && nights ? price * nights : null;
                    return (
                      <li key={room.id} className="rounded-2xl overflow-hidden border border-stone-200 bg-white shadow-sm">
                        <div className="flex flex-col sm:flex-row">
                          <div
                            className="sm:w-72 flex-shrink-0 aspect-[4/3] sm:aspect-auto sm:h-52 bg-stone-200 relative cursor-pointer"
                            onClick={() => room.images?.length > 0 && (setCurrentGalleryImages(room.images), setSelectedImageIndex(0), setIsGalleryOpen(true))}
                          >
                            {roomImg?.url ? (
                              <img src={roomImg.url} alt={roomImg.alt_text || room.name} className="w-full h-full object-cover" />
                            ) : (
                              <div className="w-full h-full flex items-center justify-center text-stone-400">
                                <MapPin className="w-12 h-12" />
                              </div>
                            )}
                          </div>
                          <div className="p-4 sm:p-5 flex-1 flex flex-col sm:flex-row sm:justify-between gap-4">
                            <div>
                              <h3 className="font-semibold text-stone-900 text-lg">{room.name}</h3>
                              <p className="text-sm text-stone-600 mt-0.5">Up to {room.capacity} guests</p>
                              {nights != null && nights > 0 && (
                                <p className="text-sm text-stone-600 mt-1">{nights} {nights === 1 ? 'night' : 'nights'}</p>
                              )}
                              {room.amenities?.length > 0 && (
                                <div className="mt-2 flex flex-wrap gap-2">
                                  {room.amenities?.slice(0, 5).map((a) => (
                                    <span key={a.id} className="inline-flex items-center gap-1 text-stone-600 text-sm" title={a.name}>
                                      <AmenityIcon slug={a.slug} className="w-3.5 h-3.5" />
                                      {a.name}
                                    </span>
                                  ))}
                                </div>
                              )}
                              {room.cancellation_policy_summary && (
                                <p className="text-sm text-stone-500 mt-2">{room.cancellation_policy_summary}</p>
                              )}
                              {totalPrice != null && (
                                <div className="mt-2">
                                  <span className="font-semibold text-stone-900">{formatPrice(totalPrice)}</span>
                                  <span className="text-stone-600 text-sm">{nights > 0 ? ` total` : ''}</span>
                                </div>
                              )}
                              {price != null && !checkIn && !checkOut && (
                                <p className="mt-2 text-stone-700 font-medium">From {formatPrice(price)} / night</p>
                              )}
                            </div>
                            <div className="flex items-end">
                              {checkIn && checkOut ? (
                                <Link
                                  to={`/book?hotel_id=${hotel.id}&room_id=${room.id}&check_in=${checkIn}&check_out=${checkOut}`}
                                  className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700"
                                >
                                  Select
                                </Link>
                              ) : (
                                <span className="inline-block px-4 py-2 rounded-lg bg-stone-100 text-stone-500 text-sm">Select dates to book</span>
                              )}
                            </div>
                          </div>
                        </div>
                      </li>
                    );
                  })}
                </ul>
              )}
            </section>
          )}

          {activeTab === 'Reviews' && (
            <section className="space-y-6">
              {reviews.length === 0 ? (
                <p className="text-stone-600">No reviews yet.</p>
              ) : (
                <>
                  <div className="max-w-xs">
                    <h3 className="font-semibold text-stone-900 mb-3">Star distribution</h3>
                    <StarDistribution reviews={reviews} />
                  </div>
                  <ul className="space-y-4">
                    {reviews.map((r) => (
                      <li key={r.id} className="p-4 rounded-xl border border-stone-200 bg-stone-50">
                        <div className="flex items-center gap-2 mb-4">
                          <span className="font-semibold text-stone-900">★ {r.rating}</span>
                          <span className="text-stone-500 text-sm">Verified stay</span>
                        </div>
                        {r.comment && <p className="text-stone-700">{r.comment}</p>}
                      </li>
                    ))}
                  </ul>
                  {reviewsMeta.last_page > 1 && (
                    <nav className="flex justify-center gap-2" aria-label="Reviews pagination">
                      <button
                        onClick={() => setReviewsPage((p) => Math.max(1, p - 1))}
                        disabled={reviewsMeta.current_page <= 1}
                        className="px-4 py-2 rounded-lg border border-stone-300 hover:bg-stone-50 disabled:opacity-50"
                      >
                        Previous
                      </button>
                      <span className="px-4 py-2 text-stone-600 text-sm">
                        Page {reviewsMeta.current_page} of {reviewsMeta.last_page}
                      </span>
                      <button
                        onClick={() => setReviewsPage((p) => Math.min(reviewsMeta.last_page, p + 1))}
                        disabled={reviewsMeta.current_page >= reviewsMeta.last_page}
                        className="px-4 py-2 rounded-lg border border-stone-300 hover:bg-stone-50 disabled:opacity-50"
                      >
                        Next
                      </button>
                    </nav>
                  )}
                </>
              )}
            </section>
          )}

          {activeTab === 'Location' && (
            <section>
              <h3 className="font-semibold text-stone-900 mb-3">Location</h3>
              {hotel.address && <p className="text-stone-600 mb-2">{hotel.address}</p>}
              <p className="text-stone-600 mb-4">{[hotel.city, hotel.country].filter(Boolean).join(', ')}</p>
              {hotel.latitude != null && hotel.longitude != null ? (
                <div className="rounded-xl overflow-hidden border border-stone-200 shadow-sm">
                  <div className="aspect-video w-full">
                    <iframe
                      title="Hotel location map"
                      src={`https://www.openstreetmap.org/export/embed.html?bbox=${Number(hotel.longitude) - 0.02},${Number(hotel.latitude) - 0.02},${Number(hotel.longitude) + 0.02},${Number(hotel.latitude) + 0.02}&layer=mapnik&marker=${hotel.latitude}%2C${hotel.longitude}`}
                      className="w-full h-full border-0"
                      loading="lazy"
                      referrerPolicy="no-referrer-when-downgrade"
                    />
                  </div>
                  <div className="px-4 py-2 bg-stone-50 border-t border-stone-200 flex items-center justify-between">
                    <span className="text-sm text-stone-600">
                      {Number(hotel.latitude).toFixed(6)}, {Number(hotel.longitude).toFixed(6)}
                    </span>
                    <a
                      href={`https://www.openstreetmap.org/?mlat=${hotel.latitude}&mlon=${hotel.longitude}#map=15/${hotel.latitude}/${hotel.longitude}`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-sm font-medium text-amber-600 hover:text-amber-700"
                    >
                      Open in Maps →
                    </a>
                  </div>
                </div>
              ) : (
                <div className="aspect-video rounded-xl bg-stone-200 flex items-center justify-center text-stone-500">
                  <div className="text-center">
                    <MapPin className="w-12 h-12 mx-auto mb-2" />
                    <p>Coordinates not available</p>
                    <p className="text-sm">Add a location when creating or editing the hotel to show the map.</p>
                  </div>
                </div>
              )}
            </section>
          )}
        </div>

        {/* Sticky Booking Widget */}
        <aside className="lg:w-96 shrink-0">
          <div className="lg:sticky lg:top-24 rounded-2xl border border-stone-200 bg-white shadow-lg p-6">
            <h2 className="font-semibold text-stone-900 text-lg mb-4">Booking</h2>
            <form onSubmit={(e) => { e.preventDefault(); if (bookUrl) window.location.href = bookUrl; }} className="space-y-4">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-sm font-medium text-stone-700 mb-1">Check-in</label>
                  <input
                    type="date"
                    value={localCheckIn}
                    onChange={(e) => setLocalCheckIn(e.target.value)}
                    min={today}
                    className="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 focus:ring-2 focus:ring-amber-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-stone-700 mb-1">Check-out</label>
                  <input
                    type="date"
                    value={localCheckOut}
                    onChange={(e) => setLocalCheckOut(e.target.value)}
                    min={localCheckIn || tomorrow}
                    className="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 focus:ring-2 focus:ring-amber-500"
                  />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-stone-700 mb-1">Guests</label>
                <select
                  value={guests}
                  onChange={(e) => setGuests(Number(e.target.value))}
                  className="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 bg-white"
                >
                  {[1, 2, 3, 4, 5, 6].map((n) => (
                    <option key={n} value={n}>{n} {n === 1 ? 'guest' : 'guests'}</option>
                  ))}
                </select>
              </div>
              <button
                type="button"
                onClick={handleDateSearch}
                className="w-full py-2 text-sm font-medium text-amber-600 hover:text-amber-700 border border-amber-200 rounded-lg"
              >
                Check availability
              </button>

              {effectiveCheckIn && effectiveCheckOut && rooms.length > 0 && (
                <>
                  <div>
                    <label className="block text-sm font-medium text-stone-700 mb-2">Room</label>
                    <select
                      value={selectedRoom?.id ?? ''}
                      onChange={(e) => {
                        const r = rooms.find((x) => String(x.id) === e.target.value);
                        setSelectedRoom(r || null);
                        setRoomQuantity(1);
                      }}
                      className="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 bg-white"
                    >
                      <option value="">Select a room</option>
                      {roomsForGuests.map((r) => (
                        <option key={r.id} value={r.id}>
                          {r.name} — {formatPrice(r.base_price)}/night
                        </option>
                      ))}
                    </select>
                  </div>
                  {selectedRoom && (
                    <div>
                      <label className="block text-sm font-medium text-stone-700 mb-1">Quantity</label>
                      <select
                        value={roomQuantity}
                        onChange={(e) => setRoomQuantity(Number(e.target.value))}
                        className="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 bg-white"
                      >
                        {Array.from({ length: Math.min(selectedRoom.total_rooms || 5, 5) }, (_, i) => i + 1).map((n) => (
                          <option key={n} value={n}>{n} room{n > 1 ? 's' : ''}</option>
                        ))}
                      </select>
                    </div>
                  )}
                </>
              )}

              {priceSummary && (
                <div className="border-t border-stone-200 pt-4 space-y-2">
                  <div className="flex justify-between text-sm">
                    <span className="text-stone-600">Subtotal</span>
                    <span>{formatPrice(priceSummary.subTotal)}</span>
                  </div>
                  {priceSummary.taxAmount > 0 && (
                    <div className="flex justify-between text-sm">
                      <span className="text-stone-600">Tax ({priceSummary.taxRatePct}%)</span>
                      <span>{formatPrice(priceSummary.taxAmount)}</span>
                    </div>
                  )}
                  <div className="flex justify-between font-semibold text-stone-900 pt-2">
                    <span>Total</span>
                    <span>{formatPrice(priceSummary.total)}</span>
                  </div>
                </div>
              )}

              <button
                type="submit"
                disabled={!bookUrl}
                className="w-full py-3 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 disabled:bg-stone-300 disabled:text-stone-500 disabled:cursor-not-allowed"
              >
                Book Now
              </button>
            </form>
          </div>
        </aside>
      </div>

      {/* Lightbox */}
      {isGalleryOpen && currentGalleryImages.length > 0 && (
        <div
          className="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4"
          onClick={() => setIsGalleryOpen(false)}
        >
          <button
            onClick={() => setIsGalleryOpen(false)}
            className="absolute top-4 right-4 p-2 text-white hover:bg-white/10 rounded-full"
            aria-label="Close gallery"
          >
            <X className="w-6 h-6" />
          </button>
          <div className="relative max-w-5xl max-h-full" onClick={(e) => e.stopPropagation()}>
            <img
              src={currentGalleryImages[selectedImageIndex]?.url}
              alt={currentGalleryImages[selectedImageIndex]?.alt_text || hotel.name}
              className="max-w-full max-h-[80vh] object-contain rounded-lg"
            />
            {currentGalleryImages.length > 1 && (
              <>
                <button
                  onClick={() => setSelectedImageIndex((i) => (i - 1 + currentGalleryImages.length) % currentGalleryImages.length)}
                  className="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-12 p-2 rounded-full bg-white/20 hover:bg-white/30 text-white"
                  aria-label="Previous"
                >
                  <ChevronLeft className="w-6 h-6" />
                </button>
                <button
                  onClick={() => setSelectedImageIndex((i) => (i + 1) % currentGalleryImages.length)}
                  className="absolute right-0 top-1/2 -translate-y-1/2 translate-x-12 p-2 rounded-full bg-white/20 hover:bg-white/30 text-white"
                  aria-label="Next"
                >
                  <ChevronRight className="w-6 h-6" />
                </button>
                <div className="flex justify-center gap-2 mt-4">
                  {currentGalleryImages.map((_, idx) => (
                    <button
                      key={idx}
                      onClick={() => setSelectedImageIndex(idx)}
                      className={cn(
                        'w-2 h-2 rounded-full transition-colors',
                        selectedImageIndex === idx ? 'bg-white' : 'bg-white/50 hover:bg-white/75'
                      )}
                      aria-label={`Image ${idx + 1}`}
                    />
                  ))}
                </div>
              </>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
