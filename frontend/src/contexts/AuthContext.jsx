import { createContext, useContext, useEffect, useState } from 'react';
import { api, getStoredToken, getStoredUser, setToken, setUser } from '../lib/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUserState] = useState(getStoredUser);
  const [loading, setLoading] = useState(!!getStoredToken());

  useEffect(() => {
    if (!getStoredToken()) {
      setLoading(false);
      return;
    }
    api.get('/me')
      .then((res) => {
        if (res.data?.success && res.data?.data) {
          setUserState(res.data.data);
          setUser(res.data.data);
        }
      })
      .catch(() => {
        setToken(null);
        setUser(null);
        setUserState(null);
      })
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    const onLogout = () => setUserState(null);
    window.addEventListener('auth:logout', onLogout);
    return () => window.removeEventListener('auth:logout', onLogout);
  }, []);

  const login = async (email, password) => {
    const res = await api.post('/login', { email, password });
    const { token, user: u } = res.data.data;
    setToken(token);
    setUser(u);
    setUserState(u);
    return u;
  };

  const registerUser = async (name, email, password, password_confirmation) => {
    const res = await api.post('/register', { name, email, password, password_confirmation });
    const { token, user: u } = res.data.data;
    setToken(token);
    setUser(u);
    setUserState(u);
    return u;
  };

  const logout = async () => {
    try {
      await api.post('/logout');
    } finally {
      setToken(null);
      setUser(null);
      setUserState(null);
    }
  };

  const updateUser = (userData) => {
    if (userData) {
      setUser(userData);
      setUserState(userData);
    }
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, register: registerUser, logout, updateUser }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
