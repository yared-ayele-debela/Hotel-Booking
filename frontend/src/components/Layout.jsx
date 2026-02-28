import { Outlet } from 'react-router-dom';
import Header from './Header';

export default function Layout() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1 w-full max-w-6xl mx-auto px-4 py-6 sm:px-6" id="main-content" role="main">
        <Outlet />
      </main>
      <footer className="border-t border-stone-200 py-4 text-center text-sm text-stone-500">
        Hotel Booking — Phase 7
      </footer>
    </div>
  );
}
