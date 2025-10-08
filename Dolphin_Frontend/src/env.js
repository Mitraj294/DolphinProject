export function getApiBase() {
  return (window.__env && window.__env.VUE_APP_API_BASE_URL) || window.VUE_APP_API_BASE_URL || process.env.VUE_APP_API_BASE_URL || '';
}
