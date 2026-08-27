# Reserveringssysteem

A reservation management system built with Laravel and Livewire. Users can browse products, reserve them for specific date ranges, and admins can manage the full reservation lifecycle through a dashboard.

**Live:** [app.hanger18.online](https://app.hanger18.online)

## Features

- **Product catalog** with search, filtering, and infinite scroll
- **Reservation system** with date-range selection and availability checking
- **Cart & checkout** with capacity validation and conflict detection
- **Admin dashboard** with status management, calendar view, and filtering
- **Reservation lifecycle** — pending → reserved → awaiting return → returned
- **Product inventory** with automatic quantity reconciliation
- **API endpoints** with Sanctum token authentication
- **Email notifications** for pickup reminders and status changes
- **Two-factor authentication** via Fortify
- **GDPR data export**

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13, PHP 8.3+ |
| Frontend | Livewire 4, Flux UI, Alpine.js, Vite |
| Database | SQLite |
| Auth | Fortify (register, login, 2FA, password reset) |
| API | Sanctum token auth, Spatie Query Builder |
| Media | Spatie Media Library |
| Testing | Pest / PHPUnit (35 test files, 190+ tests) |
| Deployment | Docker, GitHub Actions CI/CD, auto-deploy to VPS |

## Getting Started

```bash
git clone https://github.com/HajDragon/reserving.git
cd reserving
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan serve
```

## Testing

```bash
php artisan test
```

Tests run against an in-memory SQLite database with all infrastructure services (cache, session, queue, mail) stubbed for isolation.

## Project Structure

```
app/
├── Actions/          # Business logic (Fortify, Reservations)
├── Enums/            # ReservationStatus, AdminReservationStatus, ApiTokenAbility
├── Http/Controllers/ # Web + API controllers
├── Livewire/         # Cart form, admin pages
├── Models/           # Product, Reservation, Cart, User, etc.
├── Observers/        # Eloquent observers for Reservation and Product
└── Services/         # AvailabilityService (capacity calculations)
```

## License

This is a student project for Summa College — not open source.
