# App folder structure

Conventions for the Hotel Booking backend (Clean Architecture). Controllers orchestrate; Services hold business rules; Repositories handle data access.

| Folder | Purpose |
|--------|--------|
| **Actions** | Single-purpose, reusable operations (e.g. create booking, apply coupon). Keeps controllers thin and logic testable. |
| **DTOs** | Data Transfer Objects for request/response and between layers. Typed, immutable where possible; no business logic. |
| **Enums** | PHP 8.1+ enums for roles, booking status, payment status, etc. Single source of truth for allowed values. |
| **Exceptions** | Domain and HTTP exceptions. Custom handlers for consistent API and dashboard error responses. |
| **Http/Controllers** | Orchestration only: validate input, call services/actions, return responses. No queries or business rules here. |
| **Http/Requests** | Form Requests for validation and authorization. Reuse in API and Blade where applicable. |
| **Http/Resources** | API Resources for consistent JSON shape and optional conditional attributes. |
| **Models** | Eloquent models: relationships, casts, fillable/guarded. No business logic; use Services or Actions. |
| **Policies** | Authorization logic per model (Hotel, Room, Booking, Payment, Review). Used in controllers and Blade. |
| **Repositories/Contracts** | Interfaces for data access (e.g. `BookingRepositoryInterface`). Keeps services DB-agnostic. |
| **Repositories/Eloquent** | Concrete repository implementations using Eloquent. All queries and scopes here. |
| **Services** | Domain services: booking, availability, pricing, payment, commission. Contain the core business rules. |
| **Traits** | Reusable behavior (e.g. HasUuid, BelongsToVendor) to keep models and classes DRY. |
