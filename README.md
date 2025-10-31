# Dolphin

Monorepo containing the Dolphin web application: a Laravel (PHP) backend and a Vue.js frontend. This document gives a high-level overview, quick start instructions for local development, and links to the per-service READMEs.

## Contents
- `Dolphin_Backend/` — Laravel 8/9+ application (API + web controllers, mail, jobs, queues).
- `Dolphin_Frontend/` — Vue.js single-page application (SPA) that consumes the backend API.
- `Dockerfile`, `start-dev.sh`, and helper scripts — repo-level scripts to help with local/dev runs.

## High-level architecture

- Backend: Laravel, uses Passport/Sanctum (see `config/passport.php` and `storage/oauth-*` keys), has job queues, observers, scheduled tasks and a set of APIs under `routes/api.php`.
- Frontend: Vue.js + PrimeVue (look under `Dolphin_Frontend/src/`), single-page app served separately or via static hosting (Netlify config is included).
- Data: database migrations and seeders are in the backend `database/` folder. A `backup.sql` is included for reference.

## Quick start (local dev)

Prerequisites (typical):
- PHP 8.0+ (match project's composer.json), Composer, MySQL / MariaDB, Node 16+ / npm or pnpm, Git.

1. Backend (from repo root):

```bash
cd Dolphin_Backend
composer install
cp .env.example .env
# configure DB credentials in .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve --port=8000
```

2. Frontend (new terminal):

```bash
cd Dolphin_Frontend
npm install
# set env files (see .env.example)
npm run serve   # or npm run dev / npm run build depending on scripts in package.json
```

3. Open the frontend at the indicated dev URL and it should call the backend API.

## Docker / Production

This repo includes a `Dockerfile` and helper scripts; adapt to your environment. The backend has `start.sh` and `build.sh` scripts for build and deployment tasks — inspect them for CI/CD integration.

## Where to look next
- Backend README: `Dolphin_Backend/README.md` (Laravel details, env keys, queue worker/supervisor, tests).
- Frontend README: `Dolphin_Frontend/README.md` (Vue dev server, build, Netlify notes).

## Next steps
- Run the app locally, inspect `.env` files and `config/` to align services.
- If you want, I can: add Docker Compose for one-command local dev, add CI workflow, or run a quick smoke test.

---
Generated on: October 31, 2025
