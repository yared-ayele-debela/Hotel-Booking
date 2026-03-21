import { Link } from 'react-router-dom';
import { MapPin } from 'lucide-react';
import { AmenityIcon } from './AmenityIcon';
import { formatPrice, getRatingLabel } from '../lib/utils';

function getHotelMinPrice(hotel) {
  if (!hotel?.rooms?.length) return null;
  const prices = hotel.rooms.map((r) => Number(r.base_price)).filter(Boolean);
  return prices.length ? Math.min(...prices) : null;
}

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
      <div className="aspect-[4/3] bg-[#e8e4dd] relative overflow-hidden flex-shrink-0">
        {img?.url ? (
          <img
            src={img.url}
            alt={img.alt_text || hotel.name}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center text-[#a39e94]">
            <MapPin className="w-12 h-12" />
          </div>
        )}
        {imageOverlay && (
          <div className="absolute inset-0 pointer-events-none">
            <div className="absolute top-3 right-3 pointer-events-auto">{imageOverlay}</div>
          </div>
        )}
      </div>
      <div className="p-5 flex flex-col flex-1">
        <h3 className="font-serif font-semibold text-lg text-[#1a1a1a] group-hover:text-[#b8860b] transition-colors truncate">
          {hotel.name}
        </h3>
        <p className="text-sm text-[#5c5852] mt-0.5">
          {[hotel.city, hotel.country].filter(Boolean).join(', ') || '—'}
        </p>
        {hotel.amenities?.length > 0 && (
          <div className="mt-2 flex flex-wrap gap-1.5" aria-label="Amenities">
            {hotel.amenities.slice(0, 5).map((a) => (
              <span key={a.id} className="inline-flex items-center gap-1 text-[#7a756d]" title={a.name}>
                <AmenityIcon slug={a.slug} className="w-3.5 h-3.5" />
              </span>
            ))}
          </div>
        )}
        {rating != null && (
          <div className="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1">
            <span className="font-semibold text-[#1a1a1a]">{rating.toFixed(1)}</span>
            <span className="text-[#5c5852] text-sm">{getRatingLabel(rating)}</span>
            {reviewCount > 0 && (
              <span className="text-[#7a756d] text-sm">
                {reviewCount} {reviewCount === 1 ? 'review' : 'reviews'}
              </span>
            )}
          </div>
        )}
        {dealLabel && (
          <span className="inline-block mt-2 px-2.5 py-1 rounded-lg bg-[#f9edd1] text-[#996f09] text-sm font-medium w-fit">
            {dealLabel}
          </span>
        )}
        {nights != null && nights > 0 && (
          <p className="text-sm text-[#5c5852] mt-1">{nights} {nights === 1 ? 'night' : 'nights'}</p>
        )}
        {displayPrice != null && (
          <div className="mt-2 flex items-baseline gap-2 flex-wrap">
            {originalPrice != null && dealPrice != null && (
              <span className="text-[#7a756d] line-through text-sm">{formatPrice(originalPrice, currency)}</span>
            )}
            <span className="font-semibold text-[#1a1a1a]">{formatPrice(dealPrice ?? displayPrice, currency)}</span>
          </div>
        )}
        {children}
      </div>
    </>
  );

  if (href.startsWith('http') || !href) {
    return (
      <div className="group flex flex-col rounded-2xl overflow-hidden border border-[#e8e4dd] bg-white shadow-[0_4px_12px_rgb(26_26_26_/0.06)] hover:shadow-[0_12px_28px_rgb(26_26_26_/0.1)] hover:border-[#d4cec4] transition-all duration-300">
        {content}
      </div>
    );
  }

  return (
    <Link
      to={href}
      className="group flex flex-col rounded-2xl overflow-hidden border border-[#e8e4dd] bg-white shadow-[0_4px_12px_rgb(26_26_26_/0.06)] hover:shadow-[0_12px_28px_rgb(26_26_26_/0.1)] hover:border-[#d4cec4] transition-all duration-300"
    >
      {content}
    </Link>
  );
}
