// Client-side guard to reduce noise from browser extensions (Grammarly, others)
// This will intercept unhandledrejection and global error events originating
// from chrome-extension:// URLs and prevent them from being logged to the
// application's console handlers. It does NOT stop the browser from reporting
// the failed network requests in DevTools Network tab. To fully prevent
// extension errors, disable the extension in the browser.

function isExtensionResource(url) {
  if (!url || typeof url !== 'string') return false;
  return url.startsWith('chrome-extension://') || url.startsWith('moz-extension://') || url.startsWith('ms-browser-extension://');
}

// Suppress errors where the source is a browser extension link element
window.addEventListener('error', (ev) => {
  try {
    const src = ev?.target?.src || ev?.filename || '';
    if (isExtensionResource(src)) {
      // Prevent default logging and stop propagation to app-level handlers
      ev.stopImmediatePropagation?.();
      ev.preventDefault?.();
      return true;
    }
  } catch (e) {
    console.warn('Error in extension error filter', e);
  }
  return false;
}, true);

// Suppress Promise rejection warnings coming from extension-injected scripts
window.addEventListener('unhandledrejection', (ev) => {
  try {
    const reason = ev?.reason;
    // If the rejection contains a stack or message referencing chrome-extension://,
    // ignore it.
    const msg = (reason && (reason.message || reason.stack || String(reason))) || '';
    if (isExtensionResource(msg) || msg.includes('chrome-extension://')) {
      ev.stopImmediatePropagation?.();
      ev.preventDefault?.();
      return true;
    }
  } catch (e) {
    console.warn('Error in extension error filter', e);
  }
  return false;
}, true);

// Optionally override console.error to filter specific extension error messages
const origConsoleError = console.error.bind(console);
console.error = function filteredConsoleError(...args) {
  try {
    const joined = args.map(a => (typeof a === 'string' ? a : JSON.stringify(a))).join(' ');
    if (joined.includes('chrome-extension://') || joined.includes('Grammarly')) {
      // drop noisy Grammarly/extension error
      return;
    }
  } catch (e) {
    console.warn('Error in extension error filter', e);
  }
  origConsoleError(...args);
};

export default function installIgnoreExtensionErrors() {
  // no-op; side effects above are enough
}
