// runtime-env.js
// This loads runtime env into window.__env so the app can pick up VUE_APP_API_BASE_URL
// without rebuilding the bundle. It first looks for a global `window.__env` (injected by host),
// otherwise tries to fetch /env.json (served from public folder).
export async function loadRuntimeEnv() {
  if (window.__env) return;
  try {
    const res = await fetch('/env.json', { cache: 'no-store' });
    if (res.ok) {
      const obj = await res.json();
      window.__env = obj;
    } else {
      window.__env = {};
    }
  } catch (e) {
    window.__env = {};
  }
}
