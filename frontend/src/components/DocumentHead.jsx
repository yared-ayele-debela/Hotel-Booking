import { useEffect } from 'react';
import { useWebsiteSettings } from '../contexts/WebsiteSettingsContext';

/**
 * Updates document title and favicon based on website settings.
 * Call once at app root (e.g. in Layout).
 */
export default function DocumentHead() {
  const { settings } = useWebsiteSettings();

  useEffect(() => {
    document.title = settings.meta_title || settings.site_name || 'Hotel Booking';
  }, [settings.meta_title, settings.site_name]);

  useEffect(() => {
    const metaDesc = document.querySelector('meta[name="description"]');
    if (metaDesc && settings.meta_description) {
      metaDesc.setAttribute('content', settings.meta_description);
    }
  }, [settings.meta_description]);

  useEffect(() => {
    const metaKeywords = document.querySelector('meta[name="keywords"]');
    if (metaKeywords && settings.meta_keywords) {
      metaKeywords.setAttribute('content', settings.meta_keywords);
    }
  }, [settings.meta_keywords]);

  useEffect(() => {
    const href = settings.site_favicon;
    if (!href) return;
    let link = document.querySelector('link[rel="icon"]') || document.querySelector('link[rel="shortcut icon"]');
    if (!link) {
      link = document.createElement('link');
      link.rel = 'icon';
      document.head.appendChild(link);
    }
    link.rel = 'icon';
    link.href = href;
    if (href.endsWith('.ico')) link.type = 'image/x-icon';
    else if (href.endsWith('.png')) link.type = 'image/png';
    else if (href.endsWith('.svg')) link.type = 'image/svg+xml';
    else link.removeAttribute('type');
  }, [settings.site_favicon]);

  return null;
}
