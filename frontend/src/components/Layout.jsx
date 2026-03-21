import { Outlet, useLocation } from 'react-router-dom';
import Header from './Header';
import Footer from './layout/Footer';
import DocumentHead from './DocumentHead';

export default function Layout() {
  const location = useLocation();
  const isMapPage = location.pathname === '/hotels/map';
  const isHomePage = location.pathname === '/';

  return (
    <div className="min-h-screen flex flex-col bg-[#faf8f5]">
      <DocumentHead />
      <Header />
      <main
        className={`flex-1 w-full min-h-0 ${
          isMapPage ? 'flex flex-col' : isHomePage ? 'flex flex-col px-0' : 'max-w-6xl mx-auto px-4 py-8 sm:px-6 lg:px-8 xl:px-12'
        }`}
        id="main-content"
        role="main"
      >
        <Outlet />
      </main>
      {!isMapPage && <Footer />}
    </div>
  );
}
