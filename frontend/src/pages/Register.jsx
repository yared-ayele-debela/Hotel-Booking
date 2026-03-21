import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { User, Mail, Lock, Loader2 } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import ErrorMessage from '../components/ErrorMessage';

export default function Register() {
  const navigate = useNavigate();
  const { register: registerUser } = useAuth();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    if (password !== passwordConfirmation) {
      setError('Passwords do not match');
      return;
    }
    setLoading(true);
    try {
      await registerUser(name, email, password, passwordConfirmation);
      navigate('/');
    } catch (err) {
      setError(err.response?.data?.message || err.response?.data?.errors ? JSON.stringify(err.response.data.errors) : err.message || 'Registration failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="py-16 sm:py-20 lg:py-24">
      <div className="max-w-md mx-auto">
        <div className="rounded-2xl border border-[#e8e4dd] bg-white shadow-[0_4px_12px_rgb(26_26_26_/0.06)] p-8 sm:p-10">
          <h1 className="font-serif text-2xl sm:text-3xl font-semibold text-[#1a1a1a] mb-2">Sign up</h1>
          <p className="text-[#5c5852] mb-6 leading-relaxed">Create an account to book hotels and manage your stays.</p>
          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-[#45423d] mb-1.5">Name</label>
              <div className="relative">
                <User className="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-[#7a756d] pointer-events-none" />
                <input
                  id="name"
                  type="text"
                  autoComplete="name"
                  required
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  className="w-full h-12 pl-11 pr-4 rounded-xl border border-[#e8e4dd] text-[#1a1a1a] placeholder-[#7a756d] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] bg-white"
                  placeholder="Jane Smith"
                />
              </div>
            </div>
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-[#45423d] mb-1.5">Email</label>
              <div className="relative">
                <Mail className="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-[#7a756d] pointer-events-none" />
                <input
                  id="email"
                  type="email"
                  autoComplete="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full h-12 pl-11 pr-4 rounded-xl border border-[#e8e4dd] text-[#1a1a1a] placeholder-[#7a756d] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] bg-white"
                  placeholder="you@example.com"
                />
              </div>
            </div>
            <div>
              <label htmlFor="password" className="block text-sm font-medium text-[#45423d] mb-1.5">Password</label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-[#7a756d] pointer-events-none" />
                <input
                  id="password"
                  type="password"
                  autoComplete="new-password"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full h-12 pl-11 pr-4 rounded-xl border border-[#e8e4dd] text-[#1a1a1a] placeholder-[#7a756d] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] bg-white"
                  placeholder="••••••••"
                />
              </div>
            </div>
            <div>
              <label htmlFor="password_confirmation" className="block text-sm font-medium text-[#45423d] mb-1.5">Confirm password</label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-[#7a756d] pointer-events-none" />
                <input
                  id="password_confirmation"
                  type="password"
                  autoComplete="new-password"
                  required
                  value={passwordConfirmation}
                  onChange={(e) => setPasswordConfirmation(e.target.value)}
                  className="w-full h-12 pl-11 pr-4 rounded-xl border border-[#e8e4dd] text-[#1a1a1a] placeholder-[#7a756d] focus:ring-2 focus:ring-[#b8860b]/30 focus:border-[#b8860b] bg-white"
                  placeholder="••••••••"
                />
              </div>
            </div>
            {error && <ErrorMessage message={error} />}
            <button
              type="submit"
              disabled={loading}
              className="w-full h-12 px-6 rounded-xl bg-[#1a1a1a] text-white font-semibold hover:bg-[#2d2a28] focus:ring-2 focus:ring-[#b8860b]/30 disabled:opacity-50 flex items-center justify-center gap-2 transition-colors"
            >
              {loading ? (
                <>
                  <Loader2 className="w-5 h-5 animate-spin" />
                  Creating account…
                </>
              ) : (
                'Sign up'
              )}
            </button>
          </form>
          <p className="mt-6 text-[#5c5852] text-sm text-center">
            Already have an account?{' '}
            <Link to="/login" className="text-[#b8860b] font-semibold hover:text-[#996f09]">
              Log in
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
