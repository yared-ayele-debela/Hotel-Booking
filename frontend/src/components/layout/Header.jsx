import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Search, Menu, X, Heart, User, LogOut, Home, MapPin, ChevronDown } from 'lucide-react';
import { useAuth } from '../../contexts/AuthContext';
import { useWebsiteSettings } from '../../contexts/WebsiteSettingsContext';
import { Button } from '../ui/Button';
import { cn } from '../../lib/utils';

const Header = () => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const { user, logout } = useAuth();
  const { settings } = useWebsiteSettings();
  const location = useLocation();
  const navigate = useNavigate();

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 10);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    const handleClickOutside = () => setIsUserMenuOpen(false);
    document.addEventListener('click', handleClickOutside);
    return () => document.removeEventListener('click', handleClickOutside);
  }, []);

  const handleLogout = () => {
    logout();
    navigate('/');
    setIsUserMenuOpen(false);
    setIsMobileMenuOpen(false);
  };

  const isActive = (path) => location.pathname === path;

  const navigation = [
    { name: 'Home', href: '/', icon: Home },
    { name: 'Search', href: '/hotels', icon: Search },
    { name: 'Wishlist', href: '/wishlist', icon: Heart, requiresAuth: true },
    { name: 'Profile', href: '/profile', icon: User, requiresAuth: true },
  ];

  const filteredNavigation = navigation.filter(item =>
    !item.requiresAuth || user
  );

  const navLinkClass = (item) => cn(
    'flex items-center gap-2 text-[15px] font-medium tracking-wide transition-colors duration-200 py-2 border-b-2 border-transparent -mb-[2px]',
    isActive(item.href)
      ? 'text-[#1a1a1a] border-[#b8860b]'
      : 'text-[#5c5852] hover:text-[#1a1a1a] hover:border-[#d6d1c8]'
  );

  return (
    <header
      className={cn(
        'fixed top-0 left-0 right-0 z-40 transition-all duration-300',
        isScrolled
          ? 'bg-white/98 backdrop-blur-md shadow-[0_1px_3px_rgb(26_26_26/0.06)]'
          : 'bg-white border-b border-[rgba(26,26,26,0.06)]'
      )}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-[72px]">
          {/* Logo */}
          <Link
            to="/"
            className="flex items-center gap-3 group"
          >
            {settings.site_logo ? (
              <img
                src={settings.site_logo}
                alt={settings.site_name}
                className="h-9 w-auto object-contain"
              />
            ) : (
              <div className="w-10 h-10 bg-[#1a1a1a] rounded-lg flex items-center justify-center group-hover:bg-[#2d2a28] transition-colors duration-200">
                <MapPin className="w-5 h-5 text-neutral-50" />
              </div>
            )}
            <span className="font-serif text-xl sm:text-2xl font-semibold text-[#1a1a1a] tracking-tight">
              {settings.site_name}
            </span>
          </Link>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center gap-8">
            {filteredNavigation.map((item) => {
              const Icon = item.icon;
              return (
                <Link
                  key={item.name}
                  to={item.href}
                  className={navLinkClass(item)}
                >
                  <Icon className="w-4 h-4 shrink-0 opacity-80" />
                  <span>{item.name}</span>
                </Link>
              );
            })}
          </nav>

          {/* User Actions */}
          <div className="flex items-center gap-3">
            {user ? (
              <div className="hidden md:block relative">
                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    setIsUserMenuOpen(!isUserMenuOpen);
                  }}
                  className={cn(
                    'flex items-center gap-2 px-4 py-2.5 rounded-lg transition-all duration-200',
                    'text-[#1a1a1a] font-medium text-sm',
                    'hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-[#b8860b]/30 focus:ring-offset-2',
                    isUserMenuOpen && 'bg-neutral-100'
                  )}
                >
                  <div className="w-8 h-8 rounded-full bg-neutral-200 flex items-center justify-center">
                    <User className="w-4 h-4 text-neutral-600" />
                  </div>
                  <span className="max-w-[120px] truncate">{user.name}</span>
                  <ChevronDown className={cn('w-4 h-4 text-neutral-600 transition-transform', isUserMenuOpen && 'rotate-180')} />
                </button>

                {isUserMenuOpen && (
                  <div
                    className="absolute right-0 top-full mt-2 w-56 rounded-xl bg-white shadow-[0_4px_20px_rgb(26_26_26/0.08)] border border-[rgba(26,26,26,0.06)] py-2"
                    onClick={(e) => e.stopPropagation()}
                  >
                    <div className="px-4 py-3 border-b border-[rgba(26,26,26,0.06)]">
                      <p className="text-xs text-neutral-600 uppercase tracking-wider">Signed in as</p>
                      <p className="text-sm font-medium text-[#1a1a1a] truncate">{user.email || user.name}</p>
                    </div>
                    <div className="py-2">
                      <Link
                        to="/profile"
                        onClick={() => setIsUserMenuOpen(false)}
                        className="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1a1a1a] hover:bg-neutral-50 transition-colors"
                      >
                        <User className="w-4 h-4 text-neutral-600" />
                        Profile
                      </Link>
                      <Link
                        to="/wishlist"
                        onClick={() => setIsUserMenuOpen(false)}
                        className="flex items-center gap-3 px-4 py-2.5 text-sm text-[#1a1a1a] hover:bg-neutral-50 transition-colors"
                      >
                        <Heart className="w-4 h-4 text-neutral-600" />
                        Wishlist
                      </Link>
                    </div>
                    <div className="border-t border-[rgba(26,26,26,0.06)] pt-2">
                      <button
                        onClick={handleLogout}
                        className="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-neutral-500 hover:bg-error-50 hover:text-error transition-colors"
                      >
                        <LogOut className="w-4 h-4" />
                        Sign out
                      </button>
                    </div>
                  </div>
                )}
              </div>
            ) : (
              <div className="hidden md:flex items-center gap-2">
                <Link
                  to="/login"
                  className="inline-flex items-center justify-center h-9 px-3 text-sm font-medium rounded-lg text-[#1a1a1a] hover:bg-neutral-100 transition-colors"
                >
                  Sign in
                </Link>
                <Link
                  to="/register"
                  className="inline-flex items-center justify-center h-9 px-3 text-sm font-medium rounded-lg bg-[#1a1a1a] text-white hover:bg-[#2d2a28] transition-colors"
                >
                  Get started
                </Link>
              </div>
            )}

            {/* Mobile Menu Button */}
            <button
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className="md:hidden p-2.5 -mr-2 rounded-lg text-[#1a1a1a] hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-[#b8860b]/30 focus:ring-offset-2 transition-colors"
              aria-expanded={isMobileMenuOpen}
            >
              {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
            </button>
          </div>
        </div>

        {/* Mobile Navigation */}
        <div
          className={cn(
            'md:hidden overflow-hidden transition-all duration-300 ease-out',
            isMobileMenuOpen ? 'max-h-[500px] opacity-100' : 'max-h-0 opacity-0'
          )}
        >
          <div className="border-t border-[rgba(26,26,26,0.06)] py-4">
            <nav className="space-y-1">
              {filteredNavigation.map((item) => {
                const Icon = item.icon;
                return (
                  <Link
                    key={item.name}
                    to={item.href}
                    onClick={() => setIsMobileMenuOpen(false)}
                    className={cn(
                      'flex items-center gap-3 px-4 py-3.5 text-[15px] font-medium rounded-lg transition-colors',
                      isActive(item.href)
                        ? 'bg-accent-100/50 text-[#1a1a1a]'
                        : 'text-neutral-600 hover:bg-neutral-100 hover:text-[#1a1a1a]'
                    )}
                  >
                    <Icon className="w-5 h-5 shrink-0" />
                    <span>{item.name}</span>
                  </Link>
                );
              })}
            </nav>

            <div className="mt-4 pt-4 border-t border-[rgba(26,26,26,0.06)]">
              {!user ? (
                <div className="px-4 space-y-2">
                  <Link
                    to="/login"
                    onClick={() => setIsMobileMenuOpen(false)}
                    className="flex items-center justify-center w-full h-9 px-3 text-sm font-medium rounded-lg text-[#1a1a1a] hover:bg-neutral-100 transition-colors"
                  >
                    Sign in
                  </Link>
                  <Link
                    to="/register"
                    onClick={() => setIsMobileMenuOpen(false)}
                    className="flex items-center justify-center w-full h-9 px-3 text-sm font-medium rounded-lg bg-[#1a1a1a] text-white hover:bg-[#2d2a28] transition-colors"
                  >
                    Get started
                  </Link>
                </div>
              ) : (
                <div className="px-4">
                  <p className="text-xs text-neutral-600 mb-2">{user.name}</p>
                  <Button
                    variant="outline"
                    size="sm"
                    className="w-full justify-center"
                    onClick={handleLogout}
                  >
                    <LogOut className="w-4 h-4 mr-2" />
                    Sign out
                  </Button>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;
