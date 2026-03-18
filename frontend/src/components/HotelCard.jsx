import { Link } from 'react-router-dom';
import { MapPin } from 'lucide-react';
import { AmenityIcon } from './AmenityIcon';
import { formatPrice, getRatingLabel } from '../lib/utils';

function getHotelMinPrice(hotel) {
  if (!hotel?.rooms?.length) return null;
  const prices = hotel.rooms.map((r) => Number(r.base_price)).filter(Boolean);
  return prices.length ? Math.min(...prices) : null;
}

/**
 * Hotel card: image on top, then name, city/country, rating + label, review count, optional deal badge, nights, price.
 * @param {Object} props
 * @param {Object} props.hotel - hotel object (name, city, country, average_rating, review_count, images, rooms…)
 * @param {number} [props.nights] - optional number of nights (e.g. for "2 nights")
 * @param {string} [props.dealLabel] - optional badge e.g. "Early 2026 Deal"
 * @param {number} [props.originalPrice] - optional original price (shown strikethrough when dealPrice present)
 * @param {string} [props.to] - link href (default: /hotels/:id)
 * @param {string} [props.currency] - default USD
 * @param {React.ReactNode} [props.imageOverlay] - e.g. wishlist heart, rendered in top-right of image
 */
export function HotelCard({ hotel, nights, dealLabel, originalPrice, to, currency = 'USD', imageOverlay, children }) {
  const minPrice = getHotelMinPrice(hotel);
  const dealPrice = minPrice != null && originalPrice != null && originalPrice > minPrice ? minPrice : null;
  const displayPrice = dealPrice ?? minPrice;
  const img = hotel.banner_image || hotel.images?.[0];
  const rating = hotel.average_rating != null ? Number(hotel.average_rating) : null;
  const reviewCount = hotel.review_count != null ? Number(hotel.review_count) : 0;
  const href = to ?? `/hotels/${hotel.id}`;

  const content = (
    <>
      <div className="aspect-[4/3] bg-stone-200 relative overflow-hidden flex-shrink-0">
        {img?.url ? (
          <img
            src={img.url}
            alt={img.alt_text || hotel.name}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center text-stone-400">
            <MapPin className="w-12 h-12" />
          </div>
        )}
        {imageOverlay && <div className="absolute inset-0 pointer-events-none"><div className="absolute top-2 right-2 pointer-events-auto">{imageOverlay}</div></div>}
      </div>
      <div className="p-4 flex flex-col flex-1">
        <h3 className="font-semibold text-stone-900 group-hover:text-amber-700 transition-colors truncate">
          {hotel.name}
        </h3>
        <p className="text-sm text-stone-600 mt-0.5">
          {[hotel.city, hotel.country].filter(Boolean).join(', ') || '—'}
        </p>
        {hotel.amenities?.length > 0 && (
          <div className="mt-2 flex flex-wrap gap-1.5" aria-label="Amenities">
            {hotel.amenities.slice(0, 5).map((a) => (
              <span key={a.id} className="inline-flex items-center gap-1 text-stone-500" title={a.name}>
                <AmenityIcon slug={a.slug} className="w-3.5 h-3.5" />
              </span>
            ))}
          </div>
        )}
        {rating != null && (
          <div className="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1">
            <span className="font-semibold text-stone-900">{rating.toFixed(1)}</span>
            <span className="text-stone-600 text-sm">{getRatingLabel(rating)}</span>
            {reviewCount > 0 && (
              <span className="text-stone-500 text-sm">{reviewCount} {reviewCount === 1 ? 'review' : 'reviews'}</span>
            )}
          </div>
        )}
        {dealLabel && (
          <span className="inline-block mt-2 px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-sm font-medium w-fit">
            {dealLabel}
          </span>
        )}
        {nights != null && nights > 0 && (
          <p className="text-sm text-stone-600 mt-1">{nights} {nights === 1 ? 'night' : 'nights'}</p>
        )}
        {displayPrice != null && (
          <div className="mt-2 flex items-baseline gap-2 flex-wrap">
            {originalPrice != null && dealPrice != null && (
              <span className="text-stone-400 line-through text-sm">{formatPrice(originalPrice, currency)}</span>
            )}
            <span className="font-semibold text-stone-900">
              {formatPrice(dealPrice ?? displayPrice, currency)}
            </span>
          </div>
        )}
        {children}
      </div>
    </>
  );

  if (href.startsWith('http') || !href) {
    return (
      <div className="group flex flex-col rounded-2xl overflow-hidden border border-stone-200/80 bg-white shadow-sm hover:shadow-xl hover:border-amber-200/60 transition-all duration-300">
        {content}
      </div>
    );
  }

  return (
    <Link
      to={href}
      className="group flex flex-col rounded-2xl overflow-hidden border border-stone-200/80 bg-white shadow-sm hover:shadow-xl hover:border-amber-200/60 transition-all duration-300"
    >
      {content}
    </Link>
  );
}
