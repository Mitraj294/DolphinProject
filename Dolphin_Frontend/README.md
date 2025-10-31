# Dolphin_Frontend — Vue.js application

This folder contains the Vue.js single-page application (SPA) that is the front-end for Dolphin. The project uses Vue + PrimeVue (and common Vue tooling). The app talks to the Laravel backend APIs.

## What it contains
- `src/` — Vue components, router, services and app entry points.
- `public/` — static assets (including an embedded copy of TinyMCE under `public/tinymce/`).
- `package.json` — npm scripts and dependencies.
- `netlify.toml` — Netlify configuration for deployment.

## Requirements
- Node.js 16+ (or matching the project's package.json engines)
- npm or yarn

## Quick setup

```bash
cd Dolphin_Frontend
npm install
cp .env.example .env    # if present; otherwise edit `public/env.json` or runtime env files
npm run serve           # start dev server (the exact script may be `serve`, `dev` or `start` — check package.json)
```

If you intend to build a production bundle:

```bash
npm run build
# The build output will be in `dist/` (or as configured) and can be served by Netlify or a static server.
```

## Environment & runtime
- There are environment files in the repo (`.env`, `.env.example`). The front-end also uses `public/env.json` and `src/env.js` to load runtime settings.
- Configure the backend API base URL and any Stripe/public keys used by subscription features.

## Local development tips
- The project contains `permission.js`, `tokenMonitor.js`, and `tokenInterceptor.js` under `src/` — these handle auth and token refresh flows. Make sure the backend provides the matching auth endpoints.
- TinyMCE is vendored into `public/tinymce/` and referenced by the front-end.

## Deployment
- Netlify configuration is in `netlify.toml` and `public/` contains `env.json` used by runtime.
- For production behind a different domain, update API base URLs and CORS settings on the backend (`Dolphin_Backend/config/cors.php`).

## Troubleshooting
- If the front-end cannot reach the backend, open the browser console and network tab to see requests and CORS errors. Update `config/cors.php` and `.env` accordingly on the backend.

---
Generated on: October 31, 2025
