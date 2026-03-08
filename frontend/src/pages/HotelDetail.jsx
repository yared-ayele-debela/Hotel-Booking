import { useParams, useSearchParams, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { useState } from 'react';
import { api } from '../lib/api';
import { useAuth } from '../contexts/AuthContext';
import { useWishlist } from '../hooks/useWishlist';
import { HotelDetailSkeleton } from '../components/Skeleton';
import ErrorMessage from '../components/ErrorMessage';
import { formatPrice, getRatingLabel, calculateNights } from '../lib/utils';

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

export default function HotelDetail() {
  const { id } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const checkIn = searchParams.get('check_in');
  const checkOut = searchParams.get('check_out');
  
  // Local state for date selection
  const [localCheckIn, setLocalCheckIn] = useState(checkIn || '');
  const [localCheckOut, setLocalCheckOut] = useState(checkOut || '');
  // State for image gallery
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [isGalleryOpen, setIsGalleryOpen] = useState(false);
  const [currentRoomImages, setCurrentRoomImages] = useState([]);

  const handleDateSearch = () => {
    if (localCheckIn && localCheckOut) {
      setSearchParams({
        check_in: localCheckIn,
        check_out: localCheckOut
      });
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
    queryKey: ['reviews', 'hotel', id],
    queryFn: async () => {
      const res = await api.get('/reviews', { params: { hotel_id: id, per_page: 10 } });
      return res.data;
    },
    enabled: !!id,
  });

  const hotel = data?.data ?? data;
  const reviews = Array.isArray(reviewsData?.data?.data) ? reviewsData?.data?.data : 
                   Array.isArray(reviewsData?.data) ? reviewsData?.data : [];

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
        <ErrorMessage
          message={error?.response?.data?.message || error?.message || 'Hotel not found'}
          onRetry={() => refetch()}
        />
      </div>
    );
  }

  const query = checkIn && checkOut ? `?check_in=${checkIn}&check_out=${checkOut}` : '';

  // Get today's date for min attribute
  const today = new Date().toISOString().split('T')[0];

  return (
    <div className="py-6">
      <div className="flex items-start gap-3 mb-2">
        <h1 className="text-2xl sm:text-3xl font-bold text-stone-900 flex-1">{hotel.name}</h1>
        <WishlistHeart hotelId={id} checkIn={checkIn} checkOut={checkOut} />
      </div>
      <p className="text-stone-600 mb-4">
        {[hotel.city, hotel.country].filter(Boolean).join(', ')}
        {hotel.average_rating != null && (
          <span className="ml-2">★ {hotel.average_rating} ({hotel.review_count ?? 0} reviews)</span>
        )}
      </p>
      {hotel.description && (
        <p className="text-stone-700 mb-6">{hotel.description}</p>
      )}
      
      {/* Image Gallery */}
      <div className="mb-6">
        {hotel.images && hotel.images.length > 0 ? (
          <div className="space-y-4">
            {/* Main Image */}
            <div className="relative rounded-xl overflow-hidden cursor-pointer" onClick={() => setIsGalleryOpen(true)}>
              <img
                src={hotel.images[selectedImageIndex]?.url || '/placeholder-hotel.jpg'}
                alt={hotel.images[selectedImageIndex]?.alt_text || hotel.name}
                className="w-full h-96 object-cover"
              />
              {hotel.images.length > 1 && (
                <div className="absolute bottom-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-lg text-sm">
                  {selectedImageIndex + 1} / {hotel.images.length}
                </div>
              )}
            </div>
            
            {/* Thumbnail Strip */}
            {hotel.images.length > 1 && (
              <div className="flex gap-2 overflow-x-auto pb-2">
                {hotel.images.map((image, index) => (
                  <button
                    key={image.id}
                    onClick={() => setSelectedImageIndex(index)}
                    className={`flex-shrink-0 rounded-lg overflow-hidden border-2 transition-all ${
                      selectedImageIndex === index ? 'border-amber-500 scale-105' : 'border-stone-300 hover:border-stone-400'
                    }`}
                  >
                    <img
                      src={image.url}
                      alt={image.alt_text || `${hotel.name} - Image ${index + 1}`}
                      className="w-20 h-20 object-cover"
                    />
                  </button>
                ))}
              </div>
            )}
          </div>
        ) : (
          <div className="rounded-xl bg-stone-200 h-96 flex items-center justify-center" aria-hidden="true">
            <div className="text-center text-stone-500">
              <svg className="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p>No images available</p>
            </div>
          </div>
        )}
      </div>
      
      {/* Full Screen Gallery Modal */}
      {isGalleryOpen && ((currentRoomImages && currentRoomImages.length > 0) || (hotel.images && hotel.images.length > 0)) && (
        <div className="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4" onClick={() => setIsGalleryOpen(false)}>
          <div className="relative max-w-6xl max-h-full">
            <button
              onClick={() => setIsGalleryOpen(false)}
              className="absolute -top-12 right-0 text-white hover:text-stone-300 transition-colors"
              aria-label="Close gallery"
            >
              <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            
            <div className="flex items-center gap-4">
              {/* Previous Button */}
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  const images = currentRoomImages.length > 0 ? currentRoomImages : hotel.images;
                  setSelectedImageIndex((prev) => (prev - 1 + images.length) % images.length);
                }}
                className="text-white hover:text-stone-300 transition-colors disabled:opacity-50"
                disabled={(currentRoomImages.length > 0 ? currentRoomImages : hotel.images).length <= 1}
                aria-label="Previous image"
              >
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              
              {/* Main Gallery Image */}
              <img
                src={(currentRoomImages.length > 0 ? currentRoomImages : hotel.images)[selectedImageIndex]?.url}
                alt={(currentRoomImages.length > 0 ? currentRoomImages : hotel.images)[selectedImageIndex]?.alt_text || hotel.name}
                className="max-w-full max-h-[80vh] object-contain rounded-lg"
                onClick={(e) => e.stopPropagation()}
              />
              
              {/* Next Button */}
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  const images = currentRoomImages.length > 0 ? currentRoomImages : hotel.images;
                  setSelectedImageIndex((prev) => (prev + 1) % images.length);
                }}
                className="text-white hover:text-stone-300 transition-colors disabled:opacity-50"
                disabled={(currentRoomImages.length > 0 ? currentRoomImages : hotel.images).length <= 1}
                aria-label="Next image"
              >
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
            
            {/* Gallery Navigation Dots */}
            {(currentRoomImages.length > 0 ? currentRoomImages : hotel.images).length > 1 && (
              <div className="flex justify-center gap-2 mt-4">
                {(currentRoomImages.length > 0 ? currentRoomImages : hotel.images).map((_, index) => (
                  <button
                    key={index}
                    onClick={(e) => {
                      e.stopPropagation();
                      setSelectedImageIndex(index);
                    }}
                    className={`w-2 h-2 rounded-full transition-colors ${
                      selectedImageIndex === index ? 'bg-white' : 'bg-white bg-opacity-50 hover:bg-opacity-75'
                    }`}
                    aria-label={`Go to image ${index + 1}`}
                  />
                ))}
              </div>
            )}
          </div>
        </div>
      )}
      
      {/* Date Selection Section */}
      <div className="bg-white rounded-xl border-2 border-amber-200 p-6 mb-6">
        <h2 className="text-lg font-semibold text-stone-900 mb-1">Select your dates to book</h2>
        <p className="text-sm text-stone-600 mb-4">Choose check-in and check-out, then click Check Availability to see room prices and the Book button.</p>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
          <div>
            <label htmlFor="check-in" className="block text-sm font-medium text-stone-700 mb-1">
              Check-in Date
            </label>
            <input
              id="check-in"
              type="date"
              min={today}
              value={localCheckIn}
              onChange={(e) => setLocalCheckIn(e.target.value)}
              className="w-full rounded-lg border border-stone-300 px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
            />
          </div>
          <div>
            <label htmlFor="check-out" className="block text-sm font-medium text-stone-700 mb-1">
              Check-out Date
            </label>
            <input
              id="check-out"
              type="date"
              min={localCheckIn || today}
              value={localCheckOut}
              onChange={(e) => setLocalCheckOut(e.target.value)}
              className="w-full rounded-lg border border-stone-300 px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
            />
          </div>
        </div>
        <button
          onClick={handleDateSearch}
          disabled={!localCheckIn || !localCheckOut}
          className="w-full sm:w-auto px-6 py-3 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 disabled:bg-stone-300 disabled:text-stone-500 disabled:cursor-not-allowed"
        >
          Check Availability &amp; see Book options
        </button>
        {checkIn && checkOut && (
          <div className="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
            <p className="text-sm text-green-800">
              ✓ Selected: {checkIn} to {checkOut}
            </p>
          </div>
        )}
      </div>
      <h2 className="text-lg font-semibold text-stone-900 mb-3">Rooms</h2>
      {(!checkIn || !checkOut) && (
        <div className="mb-4 p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-900">
          <p className="text-sm font-medium">Select check-in and check-out dates above, then click “Check Availability” to see prices and book a room.</p>
        </div>
      )}
      <ul className="space-y-6 mb-8">
        {(hotel.rooms || []).map((room) => {
          const roomImage = room.images?.[0] || room.banner_image || hotel.images?.[0];
          const rating = hotel.average_rating != null ? Number(hotel.average_rating) : null;
          const reviewCount = hotel.review_count != null ? Number(hotel.review_count) : 0;
          const nights = checkIn && checkOut ? calculateNights(checkIn, checkOut) : null;
          const price = room.base_price != null ? Number(room.base_price) : null;
          const totalPrice = price != null && nights ? price * nights : null;
          return (
            <li key={room.id} className="rounded-2xl overflow-hidden border border-stone-200 bg-white shadow-sm hover:shadow-lg transition-shadow">
              <div className="flex flex-col sm:flex-row">
                <div
                  className="sm:w-72 flex-shrink-0 aspect-[4/3] sm:aspect-auto sm:h-52 bg-stone-200 relative cursor-pointer"
                  onClick={() => room.images?.length > 0 && (setSelectedImageIndex(0), setCurrentRoomImages(room.images), setIsGalleryOpen(true))}
                >
                  {roomImage?.url ? (
                    <img src={roomImage.url} alt={roomImage.alt_text || room.name} className="w-full h-full object-cover" />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center text-stone-400">
                      <svg className="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                  )}
                </div>
                <div className="p-4 sm:p-5 flex-1 flex flex-col sm:flex-row sm:justify-between gap-4">
                  <div>
                    <h3 className="font-semibold text-stone-900 text-lg">{room.name}</h3>
                    <p className="text-sm text-stone-600 mt-0.5">{[hotel.city, hotel.country].filter(Boolean).join(', ') || '—'}</p>
                    {rating != null && (
                      <div className="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span className="font-semibold text-stone-900">{rating.toFixed(1)}</span>
                        <span className="text-stone-600 text-sm">{getRatingLabel(rating)}</span>
                        {reviewCount > 0 && (
                          <span className="text-stone-500 text-sm">{reviewCount} {reviewCount === 1 ? 'review' : 'reviews'}</span>
                        )}
                      </div>
                    )}
                    <span className="inline-block mt-2 px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-sm font-medium w-fit">Early 2026 Deal</span>
                    {nights != null && nights > 0 && <p className="text-sm text-stone-600 mt-1">{nights} {nights === 1 ? 'night' : 'nights'}</p>}
                    {totalPrice != null && (
                      <div className="mt-2">
                        <span className="font-semibold text-stone-900">{formatPrice(totalPrice, 'EUR')}</span>
                      </div>
                    )}
                    {price != null && !checkIn && !checkOut && (
                      <p className="mt-2 text-stone-700 font-medium">From {formatPrice(price, 'EUR')} / night</p>
                    )}
                    {room.cancellation_policy_summary && (
                      <p className="text-sm text-stone-500 mt-2" title="Cancellation policy">{room.cancellation_policy_summary}</p>
                    )}
                  </div>
                  <div className="flex flex-col justify-end">
                    {checkIn && checkOut ? (
                      <Link
                        to={`/book?hotel_id=${hotel.id}&room_id=${room.id}&check_in=${checkIn}&check_out=${checkOut}`}
                        className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 text-center shadow-sm"
                      >
                        <span>Book this room</span>
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" /></svg>
                      </Link>
                    ) : (
                      <span className="inline-block px-4 py-2 rounded-lg bg-stone-100 text-stone-500 text-sm" title="Select check-in and check-out dates above">
                        Select dates to book
                      </span>
                    )}
                  </div>
                </div>
              </div>
            </li>
          );
        })}
      </ul>
      <h2 className="text-lg font-semibold text-stone-900 mb-3">Reviews</h2>
      {reviews.length === 0 ? (
        <p className="text-stone-600">No reviews yet.</p>
      ) : (
        <ul className="space-y-3" role="list">
          {reviews.map((r) => (
            <li key={r.id} className="p-4 rounded-lg border border-stone-100 bg-stone-50">
              <p className="font-medium">★ {r.rating}</p>
              {r.comment && <p className="text-stone-700 mt-1">{r.comment}</p>}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
