# HotelBook — React Frontend (Phase 7)

- **Stack:** React 19, Vite 7, Tailwind CSS 4, React Query, Axios, React Router.
- **API:** Laravel backend at `http://127.0.0.1:8000` (proxy in dev: `/api` → backend).

## Setup

```bash
cd frontend
npm install
npm run dev
```

Open http://localhost:5173. Ensure the Laravel backend is running and CORS allows the frontend origin.

## Build

```bash
npm run build
```

## Pages

- **Home** — Search (location, dates, guests); redirects to hotel list.
- **Hotel list** — Results from search API; filters via URL; pagination; skeleton loaders.
- **Hotel detail** — Single hotel, rooms, availability for selected dates, reviews (approved only).
- **Booking** — Select room quantity; create booking via API; redirect to checkout.
- **Checkout** — Show booking summary; payment placeholder (Phase 8).
- **Profile** — User info and booking history (auth required).
- **Login / Register** — Token-based auth (Sanctum); token stored in localStorage.

## Env

Optional: create `.env` with `VITE_API_URL=http://127.0.0.1:8000/api/v1` if not using the Vite proxy.
