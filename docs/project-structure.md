# Healthcare Management System – Project Structure

This document describes the purpose of each folder and notable sub-folder in the `health-care/` Laravel project. Use it as a guide when navigating or extending the codebase.

## Top-Level Overview

| Path | Purpose |
| --- | --- |
| `.claude/` | IDE/assistant configuration (Claude/Cascade tooling). Not part of runtime. |
| `app/` | Core Laravel application code: controllers, models, services, jobs, observers, repositories, etc. |
| `bootstrap/` | Framework bootstrapping files and cache metadata loaded on every request. |
| `config/` | PHP configuration files for framework and custom services. |
| `database/` | Migrations, seeders, and factories defining database schema and seed data. |
| `docs/` | Project documentation assets (this file and related references). |
| `node_modules/` | Node.js dependencies installed via npm. Generated; do not edit manually. |
| `optimization/` | Utility scripts and assets related to performance or build optimization. |
| `public/` | Web server document root (entry `index.php`, compiled assets, publicly accessible files). |
| `resources/` | Blade templates and uncompiled frontend assets (Tailwind CSS, JavaScript). |
| `routes/` | HTTP and console route definitions that map URLs or commands to controllers. |
| `src/` | Additional PHP source (e.g., bespoke libraries or support classes outside `app/`). |
| `storage/` | Runtime storage for logs, cache, compiled views, file uploads, and queue data. |
| `tests/` | PHPUnit test suites (feature/unit). |
| `vendor/` | Composer-managed PHP dependencies. Generated; do not edit manually. |
| Root files (`artisan`, `composer.json`, `package.json`, `.env`, `vite.config.js`, etc.) | Command entry points and dependency/build configuration. |

## Detailed Directory Notes

### `.claude/`
- Holds assistant configuration such as `settings.local.json`. Enables IDE integrations; not deployed.

### `app/`
Core business logic for the Laravel application. Key sub-folders:

- `Channels/` – Custom notification channels.
- `Console/` – Artisan commands and scheduling configuration.
- `Enums/` – PHP enum definitions for shared constants.
- `Http/` – All HTTP-layer concerns:
  - `Controllers/` – Request handlers (authentication, prenatal care, backups, reports, etc.).
  - `Middleware/` – Filters wrapping requests (auth, role enforcement).
  - `Requests/` – Form request validators centralizing input rules.
  - `Resources/` – API resource transformers for JSON responses.
- `Jobs/` – Queued jobs for background processing (e.g., `ProcessRestoreJob` handles restore workflows).
- `Models/` – Eloquent models for domain entities (users, patients, prenatal records, vaccines, etc.).
- `Notifications/` – Notifications (email/SMS/in-app) sent via the Laravel notification system.
- `Observers/` – Model observers registering automatic side effects (bound in `AppServiceProvider`).
- `Providers/` – Service providers that bind services, observers, Blade directives, and repositories.
- `Repositories/` – Repository interfaces and implementations abstracting data access.
- `Rules/` – Custom validation rules.
- `Services/` – Domain services (e.g., `DatabaseBackupService` handles backups, `GoogleDriveService` integrates cloud storage).
- `Testing/` – Support utilities for test suites.
- `Traits/` – Reusable behavior shared across classes.
- `Utils/` – Helper utilities (formatters, adapters, etc.).
- `View/` – View composers and related presentation helpers.

### `bootstrap/`
- `app.php` boots the Laravel framework.
- `cache/` caches compiled service manifests and routes for performance.
- Ensures the application autoloader and configuration are prepared for every request/CLI run.

### `config/`
- Contains configuration arrays for core and third-party services (`app.php`, `auth.php`, `queue.php`, `services.php`, etc.).
- Determines connection details (database, mail, caching) and integrates external APIs like Google.
- Consumed across the app via helper functions (`config('…')`).

### `database/`
- `migrations/` define schema evolution for MySQL tables.
- `seeders/` populate sample data (e.g., default users, master data).
- `factories/` generate model instances for testing/seeding.

### `docs/`
- Stores project documentation. Useful for onboarding and architectural reference.

### `node_modules/`
- Install target for npm packages (Tailwind, Vite tooling, etc.). Generated via `npm install`; excluded from version control typically.

### `optimization/`
- Contains scripts or resources aimed at improving build/runtime performance (e.g., SQL optimizations, task automation). Review contents before altering deployment processes.

### `public/`
- Entry point `index.php` receives HTTP requests.
- Asset directories (`css`, `js`, `images`) hold built files output by Vite or uploaded content.
- Only directory that should be exposed to the web server (`Apache`/`Nginx`).

### `resources/`
- `views/` – Blade templates for midwife and BHW dashboards, layouts, shared components, and reports.
- `css/` – Tailwind and custom CSS source files compiled via Vite.
- `js/` – JavaScript modules (interactivity, charting, form logic) compiled via Vite.
- Changes here require running `npm run dev`/`build` to regenerate assets.

### `routes/`
- `web.php` – Web routes mapping URLs to controllers (midwife dashboard, cloud backup operations, etc.).
- Additional files (e.g., `api.php`, `console.php`) define API routes and Artisan command routes if present.

### `src/`
- Holds supplementary PHP code outside standard Laravel conventions (project-specific libraries, helpers). Check before adding to avoid duplication with `app/` utilities.

### `storage/`
- `app/` – Application files (backups, uploads). Often symlinked to `public/storage`.
- `framework/` – Cache, sessions, compiled views.
- `logs/` – Application logs (e.g., `laravel.log`).
- Should be writable; managed via `php artisan storage:link` and permission commands.

### `tests/`
- Contains PHPUnit test cases verifying features and units of logic. Run via `phpunit` or `php artisan test`.

### `vendor/`
- Composer-managed PHP libraries (Laravel framework, Sanctum, Socialite, Google API client, etc.).
- Never edit directly; regenerate via `composer install`/`update`.

### Root-Level Files
- `.env` / `.env.example` – Environment configuration (DB credentials, Google OAuth, mail). Copy/adjust for deployments.
- `artisan` – CLI entry point for Laravel Artisan commands.
- `composer.json` / `composer.lock` – PHP dependency definitions/lockfile.
- `package.json` / `package-lock.json` – Node dependency definitions/lockfile.
- `vite.config.js`, `postcss.config.js`, `tailwind.config.js` – Frontend build configuration.
- `.editorconfig`, `.eslintrc.json`, `.prettierrc.json` – Code style/linting configuration.
- `.gitignore`, `.gitattributes` – Version control settings.
- `README.md` – High-level project overview and setup instructions.

### Generated or Temporary Files
- `.phpunit.result.cache`, `storage/framework/cache`, etc., improve runtime performance or cache test results. Safe to clear when troubleshooting.

## Usage Notes

- Service bindings within `app/Providers/AppServiceProvider.php` wire repositories and services together (e.g., `DatabaseBackupService` receives an optional `GoogleDriveService`).
- Observers registered in the same provider ensure domain events (patient created, vaccine updated) trigger notifications automatically.
- Queueable jobs in `app/Jobs/` work with Laravel’s queue system configured through `config/queue.php`.
- Frontend assets flow: author in `resources/`, compile with Vite, serve from `public/`.
- Keep runtime directories (`storage`, `bootstrap/cache`) writable for smooth operations.

Refer back to this document when adding features to ensure new code lands in the appropriate layer and follows existing architectural patterns.
