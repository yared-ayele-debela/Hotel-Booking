# Hotel Booking Platform

A **production-ready, enterprise-grade multi-hotel booking system** built with Laravel, Blade, and React. This platform enables hotels to manage properties and bookings while providing customers with a seamless booking experience.

## 🎯 Overview

Hotel Booking is a complete SaaS solution for managing hotel reservations at scale. It supports multiple independent hotels (vendors), dynamic pricing, integrated payments, and comprehensive admin dashboards—all built with clean architecture principles and modern web technologies.

**Build as if real hotels and customers will use it tomorrow—no shortcuts, no toy logic.**

## ✨ Key Features

- **Multi-Hotel Management** — Independent vendors with complete isolation and custom branding
- **Dynamic Room Management** — Create, update, and manage multiple room types per hotel
- **Smart Availability & Pricing** — Real-time availability tracking with dynamic pricing rules
- **Integrated Payments** — Stripe and PayPal integration with webhooks and async confirmation
- **Admin Dashboards** — Vendor dashboards for analytics, bookings, and revenue tracking
- **Customer Portal** — Modern search, filtering, and booking experience
- **Booking Management** — Full lifecycle management with status tracking and refunds
- **Commission System** — Automatic calculation and tracking of platform commissions
- **Role-Based Access** — Hotels managers, platform admins, and customers with granular permissions
- **Redis Queues** — Async processing for emails, webhooks, and background jobs
- **API-First Design** — RESTful API for both customer and admin operations

## 🏗️ Architecture

The project follows **Clean Architecture** principles with clear separation of concerns:

```
backend/
├── app/
│   ├── Actions/          # Single-purpose, reusable operations
│   ├── DTOs/             # Type-safe data transfer objects
│   ├── Enums/            # Domain enums (BookingStatus, PaymentStatus, etc.)
│   ├── Services/         # Core business logic & domain services
│   ├── Repositories/     # Data access layer (interfaces + implementations)
│   ├── Models/           # Eloquent models (no business logic)
│   ├── Http/Controllers/ # Thin orchestration layer
│   ├── Http/Requests/    # Form request validation
│   ├── Http/Resources/   # API response formatting
│   └── Policies/         # Authorization rules per model
├── database/
│   └── migrations/       # Database schema
└── routes/
    └── api.php           # API endpoints (/api/v1)

frontend/
├── src/
│   ├── components/       # Reusable React components
│   ├── pages/            # Page components
│   ├── hooks/            # Custom React hooks
│   └── services/         # API client services
└── public/               # Static assets
```

## 🛠️ Tech Stack

### Backend
- **Framework:** Laravel 11 (LTS)
- **Authentication:** Laravel Sanctum (token-based API auth)
- **Database:** MySQL 8.0+ / PostgreSQL 13+
- **Cache & Queues:** Redis
- **Payments:** Stripe & PayPal SDKs
- **Task Scheduling:** Laravel Scheduler & Queue Workers

### Admin Dashboards
- **Template Engine:** Blade components
- **UI Framework:** Tailwind CSS
- **Charts:** Chart.js
- **Layout:** Responsive sidebar navigation

### Customer Frontend
- **Framework:** React 18+
- **Build Tool:** Vite
- **Styling:** Tailwind CSS
- **State Management & Data Fetching:** React Query
- **HTTP Client:** Axios
- **Routing:** React Router

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+ or PostgreSQL 13+
- Redis (for queues and caching)

### Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env
# DATABASE_URL=mysql://user:password@localhost/hotel_booking

# Run migrations
php artisan migrate

# Create admin user (optional)
php artisan tinker
# Then in tinker: User::factory()->admin()->create(['email' => 'admin@example.com'])

# Start Laravel development server
php artisan serve

# In another terminal, start queue worker
php artisan queue:work

# In another terminal, start scheduler (for production)
php artisan schedule:work
```

**API Base URL:** `http://localhost:8000/api/v1`

### Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Configure API endpoint in .env
echo "VITE_API_URL=http://localhost:8000" > .env.local

# Start development server
npm run dev
```

**Frontend URL:** `http://localhost:5173`

### Admin Dashboard

The admin dashboard is served by Laravel/Blade and is accessible at:
- `http://localhost:8000/admin` (requires authentication)

## 📚 Implementation Documentation

This repository includes comprehensive implementation guides in the root directory:

- **IMPLEMENTATION-GUIDE.md** — Detailed 11-phase implementation plan covering:
  - Project foundation & setup
  - Authentication & authorization
  - Database design
  - Business logic services
  - API development
  - Admin dashboard implementation
  - React frontend development
  - Payment integration
  - Event handling
  - Quality & security
  - Deployment

- **IMPLEMENTATION-CHECKLIST.md** — Phase-by-phase checklist for tracking development progress

- **FEATURE-SUGGESTIONS.md** — Post-MVP feature ideas including:
  - Wishlist functionality
  - Guest checkout
  - Cancellation policies
  - Multi-room cart
  - Amenities management
  - Support tickets
  - Multi-currency support
  - And more...

## 🔑 Core Entities

### Hotels (Vendors)
- Hotel profile with contact details
- Multiple properties per hotel
- Custom branding options
- Commission rate configuration

### Rooms
- Multiple room types per hotel
- Amenities and features
- Base pricing and occupancy rules
- Availability calendar

### Bookings
- Complete booking lifecycle (pending → confirmed → completed/cancelled)
- Guest details and preferences
- Multiple room selections
- Payment tracking

### Payments
- Payment method storage (Stripe, PayPal)
- Transaction recording
- Refund management
- Commission calculation

### Reviews & Ratings
- Guest reviews post-booking
- Hotel ratings and feedback
- Review management by vendors

## 🔐 Authentication & Authorization

- **Guest Users:** Public access to search and book
- **Registered Customers:** Account creation, booking history, reviews
- **Hotel Managers:** Manage own hotel properties, bookings, analytics
- **Platform Admins:** Full platform management, vendor oversight, payouts

Uses **Laravel Sanctum** for API token authentication and **Laravel Policies** for authorization.

## 💳 Payment Integration

- **Stripe & PayPal** integration for secure payments
- **Webhook handling** for payment confirmations
- **Async confirmation** with webhook verification
- **Refund processing** with proper reversal
- **Commission tracking** and vendor payouts

## 🔄 Async Processing

Background jobs handled via **Redis queues** for:
- Sending booking confirmation emails
- Payment webhook processing
- Commission calculations
- Analytics aggregation
- Report generation

## 📊 Database Schema Highlights

- **Multi-tenancy** with vendor isolation via `hotel_id` foreign keys
- **Polymorphic relationships** for flexible auditing and logging
- **UUID primary keys** for enhanced security
- **Soft deletes** for data preservation
- **Timestamps** for audit trails

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter TestName
```

## 📦 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Generate application key
- [ ] Run migrations with `--force` flag
- [ ] Set up Redis for caching and queues
- [ ] Configure Stripe/PayPal credentials
- [ ] Set up queue worker supervisor
- [ ] Configure task scheduler with cron
- [ ] Set up SSL/TLS certificates
- [ ] Configure CORS for frontend domain
- [ ] Set up monitoring and logging
- [ ] Configure backups for database

### Recommended Hosting
- **Backend:** Laravel-optimized hosting (Laravel Forge, Ploi, AWS)
- **Frontend:** Static hosting (Vercel, Netlify, AWS S3 + CloudFront)
- **Database:** Managed database service (AWS RDS, DigitalOcean)
- **Cache/Queue:** Managed Redis (AWS ElastiCache, DigitalOcean)

## 🎓 Absolute Rules

These principles guide all development:

1. **Clean Architecture** — Strict separation of concerns; business logic never in controllers
2. **Multi-Vendor Isolation** — Complete data separation between vendors; no data leaks
3. **Security First** — Input validation, output escaping, CSRF protection, rate limiting
4. **Scalability** — Async processing, caching, database optimization, stateless APIs
5. **Testability** — Unit tests for services and actions, integration tests for workflows
6. **Documentation** — Self-documenting code with clear naming and PHPDoc comments

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👤 Author

Created by Yared Debela
## 📞 Support

For issues, questions, or suggestions:
- Open an issue on GitHub
- Check existing documentation in the root directory
- Review Laravel/React documentation

## 🗺️ Roadmap

- [x] Core booking system
- [x] Multi-hotel support
- [x] Payment integration
- [ ] Advanced analytics
- [ ] Mobile app
- [ ] AI-powered recommendations
- [ ] Multi-language support
- [ ] Advanced reporting

## 🙏 Acknowledgments

- Laravel community and documentation
- React and Vue.js communities
- Stripe and PayPal for payment infrastructure
- All contributors and supporters

---