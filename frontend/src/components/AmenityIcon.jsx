import {
  Wifi,
  Waves,
  Car,
  UtensilsCrossed,
  Dumbbell,
  Sparkles,
  Utensils,
  Wine,
  AirVent,
  ConciergeBell,
  Dog,
  Briefcase,
  Layout,
  Beer,
  CircleDot,
} from 'lucide-react';

const SLUG_TO_ICON = {
  wifi: Wifi,
  pool: Waves,
  parking: Car,
  breakfast: UtensilsCrossed,
  gym: Dumbbell,
  spa: Sparkles,
  restaurant: Utensils,
  bar: Wine,
  'air-conditioning': AirVent,
  'room-service': ConciergeBell,
  'pet-friendly': Dog,
  'business-center': Briefcase,
  'beach-access': Waves,
  balcony: Layout,
  minibar: Beer,
};

/**
 * Renders an icon for an amenity by slug. Falls back to a generic icon if slug unknown.
 */
export function AmenityIcon({ slug, className = 'w-4 h-4', title }) {
  const Icon = SLUG_TO_ICON[slug] ?? CircleDot;
  return <Icon className={className} aria-hidden={!title} title={title} />;
}
