# Dolphin_Backend — Laravel application

This folder contains the server-side Laravel application for Dolphin: API endpoints, scheduled jobs, mail templates, background jobs, observers, and the database layer.

## What it contains
- `artisan` — Laravel CLI entrypoint.
- `app/` — application code (Controllers, Models, Jobs, Mail, Services, Observers).
- `bootstrap/`, `config/`, `database/` — framework and DB configuration, migrations, factories, seeders.
- `public/` — frontend entry for server-hosted pages and static assets.
- `storage/` — logs, file storage and OAuth keys (private/public keys are present in `storage/`).
- `tests/` — PHPUnit tests (Unit and Feature).

## Requirements
- PHP 8.0+ (check `composer.json` for exact requirement)
- Composer
- MySQL or MariaDB
- Node.js & npm (for frontend assets, if built here)

## Quick setup

1. Install composer dependencies

```bash
cd Dolphin_Backend
composer install --no-interaction --prefer-dist
```

2. Environment

```bash
cp .env.example .env
# Edit .env to set DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL and mail/redis settings
php artisan key:generate
```

3. Database & seed

```bash
php artisan migrate --seed
# or import provided backup: mysql -u user -p database < backup.sql
```

4. Storage link and oauth keys

```bash
php artisan storage:link
# If oauth keys are missing, generate Passport keys or copy provided keys from storage/
```

5. Run the app

```bash
php artisan serve --port=8000
# Or use `start.sh` for a more production-like setup if provided
```

## Queues and background workers

- The repository includes `supervisor-dolphin-queue-worker.conf` to run queue workers under Supervisor.
- Locally, run `php artisan queue:work` or `php artisan horizon` (if Horizon is configured).

## Tests

Run PHPUnit tests:

```bash
composer install --dev
./vendor/bin/phpunit
```

## Common tasks and troubleshooting
- If migrations fail, verify DB credentials in `.env` and that the DB server is reachable.
- If email isn't sending, check `config/mail.php` and credentials in `.env`.
- OAuth: Storage contains `oauth-private.key` and `oauth-public.key`. If missing, run `php artisan passport:install`.

## Deployment notes
- See `build.sh`, `start.sh` at repo root for build and startup steps used by this project.
- `supervisor-dolphin-queue-worker.conf` is an example Supervisor unit.

---
Generated on: October 31, 2025
