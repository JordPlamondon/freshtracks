# FreshTracks

A time tracking application for freelancers and small teams. Track billable hours, manage clients and projects, generate invoices, and view analytics.

**Live Demo:** https://getfreshtracks.com
**Credentials:** `demo@freshtracks.test` / `password`

## Tech Stack

**Backend:** Laravel 12, Laravel Reverb (WebSockets), Laravel Sanctum, SQLite
**Frontend:** Nuxt 3, Vue 3 Composition API, TypeScript, Tailwind CSS, ApexCharts

## Features

- **Timer** - Start, stop, pause, and resume timers. Timers sync in real-time across browser tabs via WebSockets.
- **Weekly Timesheet** - View and edit entries by day with keyboard navigation (arrow keys, T for today).
- **Analytics** - Hours worked, billable ratio, revenue breakdowns with trend comparisons. Period filters (7/30/90 days).
- **Reports** - Filter by date range, client, project, billable status. Group by day/client/project. CSV export.
- **Clients & Projects** - Manage clients with hourly rates, organize projects per client.
- **Invoices** - Generate invoices from unbilled time entries.
- **Keyboard Shortcuts** - Press `?` to see all shortcuts. `S` toggles timer, `G+H/T/A/R/P/C/I` navigates pages, `Cmd+K` opens command palette.
- **Live Revenue** - Optional real-time revenue display. Toggle in Settings.
- **Mobile** - Responsive design with bottom navigation.

## Installation

### Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- npm

### Setup

```bash
# Clone and enter directory
cd fresh-tracks

# Install dependencies
composer install
npm install --prefix client

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate:fresh --seed

# Start servers (runs Laravel, Reverb, and Nuxt concurrently)
composer dev
```

Backend runs at `http://localhost:8000`, frontend at `http://localhost:3000`.

For production, you'll also need to run `php artisan reverb:start` for WebSocket support.

## Sample Data

The seeder creates a demo user with:
- 4 clients (Acme Corporation, Tech Startup Inc, Creative Design Co, Legacy Systems Ltd)
- 2 projects per client
- Time entries spread across the past few weeks

Reset anytime with `php artisan migrate:fresh --seed`.

## API

All endpoints require authentication via Laravel Sanctum (cookie-based for SPA, token for API).

### Auth
- `POST /api/login` - Login
- `POST /api/register` - Register
- `POST /api/logout` - Logout

### Time Entries
- `GET /api/time-entries` - List entries
- `POST /api/time-entries` - Start timer
- `POST /api/time-entries/{id}/stop` - Stop timer
- `POST /api/time-entries/{id}/restart` - Resume a stopped entry
- `GET /api/active-timer` - Get running timer
- `PUT /api/time-entries/{id}` - Update entry
- `DELETE /api/time-entries/{id}` - Delete entry

### Resources
- `GET|POST /api/clients` - List/create clients
- `GET|PUT|DELETE /api/clients/{id}` - Read/update/delete client
- `GET|POST /api/projects` - List/create projects
- `GET|PUT|DELETE /api/projects/{id}` - Read/update/delete project
- `GET|POST /api/invoices` - List/create invoices
- `POST /api/invoices/generate` - Generate invoice from entries
- `GET|PUT|DELETE /api/invoices/{id}` - Read/update/delete invoice

### Settings
- `GET /api/settings` - Get user settings
- `PUT /api/settings` - Update settings

## Project Structure

```
fresh-tracks/
├── app/                    # Laravel application
│   ├── Http/Controllers/   # API controllers
│   ├── Models/             # Eloquent models
│   └── Events/             # WebSocket events
├── database/
│   ├── migrations/         # Database schema
│   └── seeders/            # Demo data
├── routes/
│   └── api.php             # API routes
└── client/                 # Nuxt frontend
    ├── components/         # Vue components
    ├── composables/        # Shared logic (useApi, useEcho, useKeyboardShortcuts)
    ├── layouts/            # App layouts
    └── pages/              # Route pages
```

## License

MIT
