import { Outlet, useLocation } from 'react-router-dom';
import Header from './Header';

export default function Layout() {
  const location = useLocation();
  const isMapPage = location.pathname === '/hotels/map';

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main
        className={`flex-1 w-full min-h-0 ${isMapPage ? 'flex flex-col' : 'max-w-6xl mx-auto px-4 py-6 sm:px-6'}`}
        id="main-content"
        role="main"
      >
        <Outlet />
      </main>
      {!isMapPage && (
        <footer className="border-t border-stone-200 py-4 text-center text-sm text-stone-500">
          Hotel Booking — Phase 7
        </footer>
      )}
    </div>
  );
}
