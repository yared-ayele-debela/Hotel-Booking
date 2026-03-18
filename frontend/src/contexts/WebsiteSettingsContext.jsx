import React, { createContext, useContext } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';

const defaultSettings = {
  site_name: 'Hotel Booking',
  site_description: 'Find and book the perfect hotel for your next trip',
  site_logo: null,
  site_favicon: null,
  site_email: null,
  site_phone: null,
  site_address: null,
  social_links: {},
  meta_title: 'Hotel Booking',
  meta_description: 'Find and book the perfect hotel for your next trip',
  meta_keywords: 'hotel, booking, travel, accommodation',
};

const WebsiteSettingsContext = createContext(defaultSettings);

export function WebsiteSettingsProvider({ children }) {
  const { data, isLoading } = useQuery({
    queryKey: ['website-settings'],
    queryFn: async () => {
      const res = await api.get('/website-settings');
      if (!res.data?.success) throw new Error('Failed to load website settings');
      return res.data.data;
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
    retry: 1,
  });

  const settings = data ?? defaultSettings;

  return (
    <WebsiteSettingsContext.Provider value={{ settings, isLoading }}>
      {children}
    </WebsiteSettingsContext.Provider>
  );
}

export function useWebsiteSettings() {
  const context = useContext(WebsiteSettingsContext);
  if (!context) {
    return { settings: defaultSettings, isLoading: false };
  }
  return context;
}
