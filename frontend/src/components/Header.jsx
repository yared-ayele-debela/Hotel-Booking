import { useState, useRef, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ChevronDown, User, Headphones, LogOut, CalendarCheck } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';

export default function Header() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [dropdownOpen, setDropdownOpen] = useState(false);
  const dropdownRef = useRef(null);

  useEffect(() => {
    const handleClickOutside = (e) => {
      if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
        setDropdownOpen(false);
      }
    };
    document.addEventListener('click', handleClickOutside);
    return () => document.removeEventListener('click', handleClickOutside);
  }, []);

  const handleLogout = async () => {
    setDropdownOpen(false);
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
              <div className="relative" ref={dropdownRef}>
                <button
                  type="button"
                  onClick={() => setDropdownOpen((o) => !o)}
                  className="flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-amber-50 text-stone-700"
                  aria-expanded={dropdownOpen}
                  aria-haspopup="true"
                >
                  <span className="font-medium">{user.name}</span>
                  <ChevronDown className={`w-4 h-4 transition-transform ${dropdownOpen ? 'rotate-180' : ''}`} />
                </button>
                {dropdownOpen && (
                  <div className="absolute right-0 mt-1 w-48 py-1 bg-white rounded-lg shadow-lg border border-stone-200">
                    <Link
                      to="/bookings"
                      onClick={() => setDropdownOpen(false)}
                      className="flex items-center gap-2 px-4 py-2.5 text-sm text-stone-700 hover:bg-amber-50"
                    >
                      <CalendarCheck className="w-4 h-4" />
                      My Bookings
                    </Link>
                    <Link
                      to="/support"
                      onClick={() => setDropdownOpen(false)}
                      className="flex items-center gap-2 px-4 py-2.5 text-sm text-stone-700 hover:bg-amber-50"
                    >
                      <Headphones className="w-4 h-4" />
                      Support
                    </Link>
                    <Link
                      to="/profile"
                      onClick={() => setDropdownOpen(false)}
                      className="flex items-center gap-2 px-4 py-2.5 text-sm text-stone-700 hover:bg-amber-50"
                    >
                      <User className="w-4 h-4" />
                      Profile
                    </Link>
                    <hr className="my-1 border-stone-200" />
                    <button
                      type="button"
                      onClick={handleLogout}
                      className="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-stone-700 hover:bg-red-50 hover:text-red-700"
                    >
                      <LogOut className="w-4 h-4" />
                      Log out
                    </button>
                  </div>
                )}
              </div>
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
