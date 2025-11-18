# TallyHo - Time Tracking Application

A modern, full-stack time tracking application built with Laravel 11 and Nuxt 3.

## Features

- **Timer Functionality**: Start/stop timer with real-time duration display
- **Client Management**: Full CRUD for clients with hourly rates
- **Project Management**: Organize work by clients and projects
- **Time Entries**: Track and manage all time entries
- **Invoice Generation**: Generate invoices from billable time entries
- **Authentication**: Secure API authentication with Laravel Sanctum
- **Dark Theme**: Clean, minimal UI with dark mode (#0a0f1f background)

## Tech Stack

### Backend
- Laravel 11
- SQLite database
- Laravel Sanctum for API authentication
- RESTful API architecture

### Frontend
- Nuxt 3
- Vue 3 Composition API
- Tailwind CSS
- TypeScript

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- npm or pnpm

### Backend Setup

1. Navigate to the project root:
```bash
cd tally-ho
```

2. Install PHP dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run migrations and seed database:
```bash
php artisan migrate:fresh --seed
```

6. Start the Laravel development server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

### Frontend Setup

1. Navigate to the client directory:
```bash
cd client
```

2. Install dependencies:
```bash
npm install
```

3. Start the development server:
```bash
npm run dev
```

The frontend will be available at `http://localhost:3000`

## Default Credentials

- **Email**: demo@tallyho.test
- **Password**: password

## Sample Data

The seeder creates:
- 1 demo user
- 3 clients (Acme Corporation, Tech Startup Inc, Design Studio)
- 2 projects per client
- Multiple time entries for each project

## API Endpoints

### Authentication
- `POST /api/login` - Login
- `POST /api/register` - Register new user
- `POST /api/logout` - Logout

### Clients
- `GET /api/clients` - List all clients
- `POST /api/clients` - Create client
- `GET /api/clients/{id}` - Get client details
- `PUT /api/clients/{id}` - Update client
- `DELETE /api/clients/{id}` - Delete client

### Projects
- `GET /api/projects` - List all projects
- `POST /api/projects` - Create project
- `GET /api/projects/{id}` - Get project details
- `PUT /api/projects/{id}` - Update project
- `DELETE /api/projects/{id}` - Delete project

### Time Entries
- `GET /api/time-entries` - List all time entries
- `POST /api/time-entries` - Start timer
- `POST /api/time-entries/{id}/stop` - Stop timer
- `GET /api/active-timer` - Get currently running timer
- `PUT /api/time-entries/{id}` - Update time entry
- `DELETE /api/time-entries/{id}` - Delete time entry

### Invoices
- `GET /api/invoices` - List all invoices
- `POST /api/invoices/generate` - Generate invoice from time entries
- `GET /api/invoices/{id}` - Get invoice details
- `PUT /api/invoices/{id}` - Update invoice
- `DELETE /api/invoices/{id}` - Delete invoice

## Project Structure

```
tally-ho/
├── app/
│   ├── Http/Controllers/    # API Controllers
│   ├── Models/              # Eloquent Models
│   └── Policies/            # Authorization Policies
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── routes/
│   └── api.php             # API routes
└── client/                 # Nuxt frontend
    ├── components/         # Vue components
    ├── composables/        # Composables (useApi)
    ├── layouts/           # Layout components
    └── pages/             # Page components
```

## Development

### Running Tests
```bash
php artisan test
```

### Database Reset
```bash
php artisan migrate:fresh --seed
```

## License

MIT
