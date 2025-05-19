
# Multi-Tenant Event Management API (Laravel Backend)

## Project Overview

This backend API is built with Laravel 12 and serves a multi-tenant event management system.  
Each organization has isolated data accessed via path-based routing using organization slugs.  
Admins can manage organizations, events, and attendees securely.

---

## Features

- Organizations management with data isolation  
- CRUD for Events and Attendees scoped to organizations  
- Admin authentication via email/password (Laravel Sanctum)  
- Multi-tenancy enforced through middleware and route scoping  
- Soft deletes support for events and attendees  
- Activity logging for event changes  
- RESTful API endpoints  
- Test coverage for core features  

---

## Technology Stack

- Laravel 8.2 or higher  
- PHP 7.4+ (preferably PHP 8+)  
- MySQL or PostgreSQL  
- Laravel Sanctum for authentication  
- PHPUnit for testing  

---

## Setup Instructions

1. Clone the repository to your local machine:  
   ```bash
   git clone <repository-url>
   cd <repository-folder>
   ```
2. Install PHP dependencies:  
   ```bash
   composer install
   ```
3. Copy the example environment file and update variables:  
   ```bash
   cp .env.example .env
   ```
   Update the `.env` file, especially database credentials.  
4. Generate the application key:  
   ```bash
   php artisan key:generate
   ```
5. Run database migrations:  
   ```bash
   php artisan migrate
   ```
6. Seed the database (optional):  
   ```bash
   php artisan db:seed
   ```
7. Start the development server:  
   ```bash
   php artisan serve
   ```
   
The backend API will be available at [http://localhost:8000](http://localhost:8000).

---

## Database Configuration

Ensure the following variables are set in your `.env` file:

```env
DB_CONNECTION=mysql        # or pgsql
DB_HOST=127.0.0.1
DB_PORT=3306               # or your PostgreSQL port
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

---

## Running Tests

Run all automated tests with:

```bash
php artisan test
```

Tests cover organization, event, attendee features, and multi-tenancy verification.

---

## API Routes Overview

| Route                                            | Description                     |
| ------------------------------------------------|--------------------------------|
| `/api/login`                                     | Admin login                    |
| `/api/logout`                                    | Admin logout                   |
| `/api/organizations`                             | Manage organizations           |
| `/api/{organization_slug}/events`                | Manage events within an organization |
| `/api/{organization_slug}/events/{event_id}/attendees` | Manage attendees for an event  |

---

## Authentication & Authorization

- Uses Laravel Sanctum for API token-based authentication.  
- Admins are scoped to their organization; access to data outside the organization returns 404.  

---

## Multi-Tenancy

- Path-based routing using organization slug in the URL to isolate data.  
- Middleware extracts organization slug and restricts queries accordingly.  

---

## Soft Deletes and Logging

- Events and attendees support soft deletes to allow recovery.  
- Event changes are logged for audit purposes.  

---

## Deployment

- Deploy on any PHP-compatible web server.  
- Configure `.env` appropriately for production.  
- Use HTTPS and proper CORS settings to connect with frontend.  

---


## License


