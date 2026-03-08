import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs) {
  return twMerge(clsx(inputs));
}

export function formatPrice(price, currency = 'USD') {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
    minimumFractionDigits: 2,
  }).format(price);
}

/** Rating label for display (e.g. 9.6 → "Exceptional") */
export function getRatingLabel(score) {
  const n = Number(score);
  if (n >= 9) return 'Exceptional';
  if (n >= 8) return 'Great';
  if (n >= 7) return 'Good';
  return 'Pleasant';
}

export function formatDate(dateString) {
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(new Date(dateString));
}

export function formatDateRange(checkIn, checkOut) {
  return `${formatDate(checkIn)} - ${formatDate(checkOut)}`;
}

export function calculateNights(checkIn, checkOut) {
  const start = new Date(checkIn);
  const end = new Date(checkOut);
  const diffTime = Math.abs(end - start);
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}
