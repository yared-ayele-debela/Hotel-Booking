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
    <footer className="bg-[#1a1a1a] text-white border-t border-[#2d2a28]">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 py-14 sm:py-20">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-10">
          <div className="flex items-center gap-3">
            <Link
              to="/"
              className="flex items-center gap-2.5 text-xl font-serif font-semibold text-white hover:text-[#c9a227] transition-colors"
            >
              {settings.site_logo ? (
                <img src={settings.site_logo} alt={settings.site_name} className="h-9 w-auto object-contain brightness-0 invert opacity-90" />
              ) : (
                <div className="w-9 h-9 rounded-lg bg-[#b8860b] flex items-center justify-center">
                  <MapPin className="w-5 h-5 text-white" />
                </div>
              )}
              {settings.site_name}
            </Link>
          </div>
          <div className="flex flex-wrap gap-8">
            <Link
              to="/hotels"
              className="text-[#a39e94] hover:text-[#c9a227] transition-colors text-sm font-medium"
            >
              Hotels
            </Link>
            <Link
              to="/support"
              className="text-[#a39e94] hover:text-[#c9a227] transition-colors text-sm font-medium"
            >
              Support
            </Link>
          </div>
        </div>

        {(settings.site_email || settings.site_phone || settings.site_address || Object.values(socialLinks).some(Boolean)) && (
          <div className="mt-10 pt-10 border-t border-[#2d2a28] flex flex-wrap gap-6 sm:gap-8">
            {settings.site_email && (
              <a
                href={`mailto:${settings.site_email}`}
                className="flex items-center gap-2 text-[#a39e94] hover:text-[#c9a227] transition-colors text-sm"
              >
                <Mail className="w-4 h-4" />
                {settings.site_email}
              </a>
            )}
            {settings.site_phone && (
              <a
                href={`tel:${settings.site_phone}`}
                className="flex items-center gap-2 text-[#a39e94] hover:text-[#c9a227] transition-colors text-sm"
              >
                <Phone className="w-4 h-4" />
                {settings.site_phone}
              </a>
            )}
            {settings.site_address && (
              <span className="flex items-center gap-2 text-[#a39e94] text-sm">
                <MapPin className="w-4 h-4 flex-shrink-0" />
                {settings.site_address}
              </span>
            )}
            <div className="flex gap-4">
              {socialLinks.facebook && (
                <a href={socialLinks.facebook} target="_blank" rel="noopener noreferrer" className="text-[#a39e94] hover:text-[#c9a227] transition-colors text-sm" aria-label="Facebook">Facebook</a>
              )}
              {socialLinks.twitter && (
                <a href={socialLinks.twitter} target="_blank" rel="noopener noreferrer" className="text-[#a39e94] hover:text-[#c9a227] transition-colors text-sm" aria-label="Twitter">Twitter</a>
              )}
              {socialLinks.instagram && (
                <a href={socialLinks.instagram} target="_blank" rel="noopener noreferrer" className="text-[#a39e94] hover:text-[#c9a227] transition-colors text-sm" aria-label="Instagram">Instagram</a>
              )}
              {socialLinks.linkedin && (
                <a href={socialLinks.linkedin} target="_blank" rel="noopener noreferrer" className="text-[#a39e94] hover:text-[#c9a227] transition-colors text-sm" aria-label="LinkedIn">LinkedIn</a>
              )}
            </div>
          </div>
        )}

        <div className="border-t border-[#2d2a28] mt-10 pt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
          <div className="flex flex-wrap gap-8">
            {trustBadges.map((badge, index) => {
              const Icon = badge.icon;
              return (
                <div key={index} className="flex items-center gap-2">
                  <Icon className="w-5 h-5 text-[#b8860b]" />
                  <span className="text-sm text-[#a39e94]">{badge.text}</span>
                </div>
              );
            })}
          </div>
          <p className="text-sm text-[#7a756d]">
            © {currentYear} {settings.site_name}. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
