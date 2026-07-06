---
name: Technology Stack Decisions
description: Why specific technologies were chosen and their intended use
type: reference
---

# Technology Stack for RSG-CRM

## Backend: Laravel 10

**Why**: Laravel offers:
- Mature, well-documented framework
- Built-in ORM (Eloquent) for database operations
- Strong security features (CSRF, SQL injection protection)
- Excellent testing support
- Large ecosystem and community

**Key packages**:
- **Sanctum**: Token-based API authentication (for mobile/SPA clients)
- **Livewire**: Interactive components without heavy JavaScript
- **Pint**: Code formatter for consistency

## Frontend: Livewire + Blade + Vite

**Why this combination**:
- **Livewire**: Build dynamic UIs with PHP instead of JavaScript. Reduces context switching for the team.
- **Blade**: Laravel's templating engine—simple, powerful, integrates naturally
- **Vite**: Fast asset bundling and hot module replacement during development

Alternative NOT chosen:
- **Separate Vue/React SPA**: Would require more JS expertise and separate API/frontend deployments

## Database: MySQL

**Why**: 
- Reliable and proven for business applications
- Good support for transactions (important for CRM data consistency)
- Excellent tooling and hosting support

**Configuration**: See `.env` for connection details. Use migrations (in `database/migrations/`) to manage schema.

## Testing: PHPUnit

**Why**: Laravel's default. Built-in support for database testing (transactions, seeding).

## Code Quality: Laravel Pint

**Why**: Official Laravel code formatter. Ensures consistent style across the codebase.

---

## Development Environment Setup

Run `php artisan serve` for local development. Assets with `npm run dev`.

For detailed commands, see `CLAUDE.md`.
