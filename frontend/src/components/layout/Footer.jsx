import React from 'react';
import { Link } from 'react-router-dom';
import { MapPin, Shield, CreditCard, Headphones, Mail, Phone } from 'lucide-react';
import { useWebsiteSettings } from '../../contexts/WebsiteSettingsContext';

const Footer = () => {
  const currentYear = new Date().getFullYear();
  const { settings } = useWebsiteSettings();
  const socialLinks = settings.social_links || {};

  const trustBadges = [
    { icon: Shield, text: 'Secure Booking' },
    { icon: CreditCard, text: 'Safe Payment' },
    { icon: Headphones, text: '24/7 Support' },
  ];

  return (
    <footer className="bg-stone-900 text-white border-t border-stone-800">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 py-12 sm:py-16">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-8">
          <div className="flex items-center gap-3">
            <Link to="/" className="flex items-center gap-2 text-xl font-semibold text-white hover:text-amber-400 transition-colors">
              {settings.site_logo ? (
                <img src={settings.site_logo} alt={settings.site_name} className="h-9 w-auto object-contain" />
              ) : (
                <div className="w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center">
                  <MapPin className="w-5 h-5 text-white" />
                </div>
              )}
              {settings.site_name}
            </Link>
          </div>
          <div className="flex flex-wrap gap-6 sm:gap-8">
            <Link to="/hotels" className="text-stone-400 hover:text-amber-400 transition-colors text-sm font-medium">
              Hotels
            </Link>
            <Link to="/support" className="text-stone-400 hover:text-amber-400 transition-colors text-sm font-medium">
              Support
            </Link>
          </div>
        </div>

        {(settings.site_email || settings.site_phone || settings.site_address || Object.values(socialLinks).some(Boolean)) && (
          <div className="mt-8 pt-8 border-t border-stone-800 flex flex-wrap gap-6 sm:gap-8">
            {settings.site_email && (
              <a href={`mailto:${settings.site_email}`} className="flex items-center gap-2 text-stone-400 hover:text-amber-400 transition-colors text-sm">
                <Mail className="w-4 h-4" />
                {settings.site_email}
              </a>
            )}
            {settings.site_phone && (
              <a href={`tel:${settings.site_phone}`} className="flex items-center gap-2 text-stone-400 hover:text-amber-400 transition-colors text-sm">
                <Phone className="w-4 h-4" />
                {settings.site_phone}
              </a>
            )}
            {settings.site_address && (
              <span className="flex items-center gap-2 text-stone-400 text-sm">
                <MapPin className="w-4 h-4 flex-shrink-0" />
                {settings.site_address}
              </span>
            )}
            <div className="flex gap-4">
              {socialLinks.facebook && (
                <a href={socialLinks.facebook} target="_blank" rel="noopener noreferrer" className="text-stone-400 hover:text-amber-400 transition-colors" aria-label="Facebook">Facebook</a>
              )}
              {socialLinks.twitter && (
                <a href={socialLinks.twitter} target="_blank" rel="noopener noreferrer" className="text-stone-400 hover:text-amber-400 transition-colors" aria-label="Twitter">Twitter</a>
              )}
              {socialLinks.instagram && (
                <a href={socialLinks.instagram} target="_blank" rel="noopener noreferrer" className="text-stone-400 hover:text-amber-400 transition-colors" aria-label="Instagram">Instagram</a>
              )}
              {socialLinks.linkedin && (
                <a href={socialLinks.linkedin} target="_blank" rel="noopener noreferrer" className="text-stone-400 hover:text-amber-400 transition-colors" aria-label="LinkedIn">LinkedIn</a>
              )}
            </div>
          </div>
        )}

        <div className="border-t border-stone-800 mt-10 pt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
          <div className="flex flex-wrap gap-6 sm:gap-8">
            {trustBadges.map((badge, index) => {
              const Icon = badge.icon;
              return (
                <div key={index} className="flex items-center gap-2">
                  <Icon className="w-5 h-5 text-amber-400" />
                  <span className="text-sm text-stone-400">{badge.text}</span>
                </div>
              );
            })}
          </div>
          <p className="text-sm text-stone-500">
            © {currentYear} {settings.site_name}. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
