import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, MapPin, Calendar, Users, Star, Shield, Heart, Globe } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { Card, CardContent } from '../components/ui/Card';
import { Skeleton } from '../components/ui/Skeleton';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { formatPrice } from '../lib/utils';

export default function Home() {
  const navigate = useNavigate();
  const [city, setCity] = useState('');
  const [checkIn, setCheckIn] = useState('');
  const [checkOut, setCheckOut] = useState('');
  const [guests, setGuests] = useState(1);

  const handleSearch = (e) => {
    e.preventDefault();
    const params = new URLSearchParams();
    if (city.trim()) params.set('city', city.trim());
    if (checkIn) params.set('check_in', checkIn);
    if (checkOut) params.set('check_out', checkOut);
    if (guests > 0) params.set('guests', guests);
    navigate({ pathname: '/hotels', search: params.toString() });
  };

  // Fetch featured hotels
  const { data: featuredHotels, isLoading: isLoadingFeatured } = useQuery({
    queryKey: ['featured-hotels'],
    queryFn: async () => {
      const res = await api.get('/hotels', { 
        params: { per_page: 6, sort: 'rating', order: 'desc' } 
      });
      return res.data?.data?.data || [];
    },
  });

  const today = new Date().toISOString().split('T')[0];
  const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];

  return (
    <div className="min-h-screen ">
      {/* Hero Section */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
        {/* Background Image */}
        <div className="absolute inset-0 z-0">
          <div className="absolute inset-0 bg-gradient-to-br from-cyan-600/20 via-cyan-700/30 to-neutral-900/80" />
          <img
            src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=2850&q=80"
            alt="Luxury hotel view"
            className="w-full h-full object-cover"
          />
        </div>

        {/* Hero Content */}
        <div className="relative z-10 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <div className="mb-8">
            <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
              Find Your Perfect
              <span className="block text-cyan-400">Stay</span>
            </h1>
            <p className="text-xl md:text-2xl text-neutral-200 mb-8 max-w-2xl mx-auto">
              Discover amazing hotels and create unforgettable memories around the world. 
              Your journey starts here.
            </p>
          </div>

          {/* Search Form */}
          <Card className="bg-white/95 backdrop-blur-md shadow-2xl max-w-4xl mx-auto">
            <CardContent className="p-6">
              <form onSubmit={handleSearch} className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                  {/* Destination */}
                  <div className="relative">
                    <MapPin className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400" />
                    <Input
                      type="text"
                      placeholder="Where to?"
                      value={city}
                      onChange={(e) => setCity(e.target.value)}
                      className="pl-10"
                      required
                    />
                  </div>

                  {/* Check-in */}
                  <div className="relative">
                    <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400" />
                    <Input
                      type="date"
                      placeholder="Check-in"
                      value={checkIn}
                      onChange={(e) => setCheckIn(e.target.value)}
                      min={today}
                      className="pl-10"
                    />
                  </div>

                  {/* Check-out */}
                  <div className="relative">
                    <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400" />
                    <Input
                      type="date"
                      placeholder="Check-out"
                      value={checkOut}
                      onChange={(e) => setCheckOut(e.target.value)}
                      min={checkIn || tomorrow}
                      className="pl-10"
                    />
                  </div>

                  {/* Guests */}
                  <div className="relative">
                    <Users className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400" />
                    <select
                      value={guests}
                      onChange={(e) => setGuests(Number(e.target.value))}
                      className="w-full h-10 pl-10 pr-3 rounded-lg border border-neutral-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                    >
                      {[1, 2, 3, 4, 5, 6].map(num => (
                        <option key={num} value={num}>
                          {num} {num === 1 ? 'Guest' : 'Guests'}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>

                <Button type="submit" size="lg" className="w-full md:w-auto px-8">
                  <Search className="w-5 h-5 mr-2" />
                  Search Hotels
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </section>

      {/* Trust Indicators */}
      <section className="py-12 bg-neutral-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div className="flex items-center justify-center space-x-4">
              <div className="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <Shield className="w-6 h-6 text-cyan-600" />
              </div>
              <div className="text-left">
                <h3 className="font-semibold text-neutral-900">Secure Booking</h3>
                <p className="text-sm text-neutral-600">Safe and encrypted transactions</p>
              </div>
            </div>
            <div className="flex items-center justify-center space-x-4">
              <div className="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <Star className="w-6 h-6 text-cyan-600" />
              </div>
              <div className="text-left">
                <h3 className="font-semibold text-neutral-900">Verified Reviews</h3>
                <p className="text-sm text-neutral-600">Real guest experiences</p>
              </div>
            </div>
            <div className="flex items-center justify-center space-x-4">
              <div className="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <Heart className="w-6 h-6 text-cyan-600" />
              </div>
              <div className="text-left">
                <h3 className="font-semibold text-neutral-900">Loved by Travelers</h3>
                <p className="text-sm text-neutral-600">2M+ happy customers</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Featured Hotels */}
      <section className="py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold text-neutral-900 mb-4">
              Trending Destinations
            </h2>
            <p className="text-lg text-neutral-600 max-w-2xl mx-auto">
              Discover our most popular hotels and start planning your next adventure
            </p>
          </div>

          {isLoadingFeatured ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {[...Array(6)].map((_, i) => (
                <Card key={i} className="overflow-hidden">
                  <Skeleton className="h-48" />
                  <CardContent className="p-6">
                    <Skeleton className="h-6 w-3/4 mb-2" />
                    <Skeleton className="h-4 w-1/2 mb-4" />
                    <Skeleton className="h-4 w-full mb-2" />
                    <Skeleton className="h-10 w-full" />
                  </CardContent>
                </Card>
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {featuredHotels.map((hotel) => (
                <Card key={hotel.id} className="overflow-hidden hover:shadow-xl transition-shadow duration-300">
                  <div className="h-48 bg-neutral-200 relative">
                    {hotel.banner_image ? (
                      <img
                        src={hotel.banner_image.url}
                        alt={hotel.banner_image.alt_text || hotel.name}
                        className="w-full h-full object-cover"
                      />
                    ) : hotel.images && hotel.images.length > 0 ? (
                      <img
                        src={hotel.images[0].url}
                        alt={hotel.images[0].alt_text || hotel.name}
                        className="w-full h-full object-cover"
                      />
                    ) : (
                      <div className="w-full h-full bg-neutral-200 flex items-center justify-center">
                        <Globe className="w-12 h-12 text-neutral-400" />
                      </div>
                    )}
                    {hotel.average_rating && (
                      <div className="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg">
                        <div className="flex items-center space-x-1">
                          <Star className="w-4 h-4 text-amber-500 fill-current" />
                          <span className="text-sm font-medium">{hotel.average_rating}</span>
                        </div>
                      </div>
                    )}
                  </div>
                  <CardContent className="p-6">
                    <h3 className="text-xl font-semibold text-neutral-900 mb-2">{hotel.name}</h3>
                    <p className="text-neutral-600 mb-4">
                      {[hotel.city, hotel.country].filter(Boolean).join(', ')}
                    </p>
                    <div className="flex items-center justify-between">
                      <div>
                        <span className="text-2xl font-bold text-cyan-600">
                          {formatPrice(hotel.min_price || 99)}
                        </span>
                        <span className="text-sm text-neutral-500"> /night</span>
                      </div>
                      <Button variant="outline" size="sm" asChild>
                        <a href={`/hotels/${hotel.id}`}>View Details</a>
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          )}

          <div className="text-center mt-12">
            <Button size="lg" asChild>
              <a href="/hotels">Explore All Hotels</a>
            </Button>
          </div>
        </div>
      </section>
    </div>
  );
}
