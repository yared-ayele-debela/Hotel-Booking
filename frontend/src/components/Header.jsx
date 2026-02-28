import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function Header() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/');
  };

  return (
    <header className="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-stone-200">
      <div className="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
        <Link to="/" className="text-xl font-semibold text-amber-700 hover:text-amber-800">
          HotelBook
        </Link>
        <nav className="flex items-center gap-2" aria-label="Main navigation">
          <Link to="/hotels" className="px-3 py-2 rounded-lg hover:bg-amber-50 text-stone-700">
            Hotels
          </Link>
          {user ? (
            <>
              <Link to="/wishlist" className="px-3 py-2 rounded-lg hover:bg-amber-50 text-stone-700">
                Wishlist
              </Link>
              <Link to="/profile" className="px-3 py-2 rounded-lg hover:bg-amber-50 text-stone-700">
                Profile
              </Link>
              <button
                type="button"
                onClick={handleLogout}
                className="px-3 py-2 rounded-lg hover:bg-stone-100 text-stone-600"
              >
                Log out
              </button>
            </>
          ) : (
            <>
              <Link to="/login" className="px-3 py-2 rounded-lg hover:bg-amber-50 text-stone-700">
                Log in
              </Link>
              <Link
                to="/register"
                className="px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700"
              >
                Sign up
              </Link>
            </>
          )}
        </nav>
      </div>
    </header>
  );
}
