// src/main.js
import './assets/global.css';
import './assets/table.css';
import './assets/modelcssnotificationandassesment.css';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { fetchCurrentUser } from './services/user';
import { fetchSubscriptionStatus } from './services/subscription';

import storage from './services/storage';
import tokenMonitor from './services/tokenMonitor';
import { loadRuntimeEnv } from './runtime-env';

import './services/tokenInterceptor';

// PrimeVue Imports
import PrimeVue from 'primevue/config'; // Import PrimeVue configuration
import ToastService from 'primevue/toastservice'; // Import ToastService
import ConfirmService from 'primevue/confirmationservice';

// PrimeVue Styles (choose a theme and include primevue.min.css)
import 'primevue/resources/themes/lara-light-blue/theme.css'; // Recommended theme, or choose another
import 'primevue/resources/primevue.min.css'; // Core PrimeVue styles

// Font Awesome and PrimeIcons
import 'primeicons/primeicons.css'; // PrimeIcons for PrimeVue components
import '@fortawesome/fontawesome-free/css/all.min.css'; // Font Awesome for your custom icons
import 'primeflex/primeflex.css';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
async function bootstrap() {
  await loadRuntimeEnv();
  const app = createApp(App);

  // Install PrimeVue and its services
  app.use(PrimeVue); // Initialize PrimeVue
  app.use(ToastService); // Install the ToastService globally
  app.use(ConfirmService); // Install the ConfirmService globally for ConfirmDialog
  app.component('Toast', Toast); // Register Toast globally
  app.component('ConfirmDialog', ConfirmDialog);
  app.component('Calendar', Calendar); // Register Calendar globally
  app.component('Button', Button); // Register PrimeVue Button globally

  return app;
}


// Sync encrypted storage role with backend user role on app start

// Check if this is a guest access scenario (like subscription plans with guest_code)
const isGuestAccess = () => {
  const urlParams = new URLSearchParams(window.location.search);
  const hasGuestParams = urlParams.has('guest_code') || urlParams.has('guest_token') || 
         urlParams.has('lead_id') || urlParams.has('email');
  console.log('isGuestAccess check:', {
    url: window.location.href,
    search: window.location.search,
    hasGuestParams,
    guest_code: urlParams.get('guest_code'),
    guest_token: urlParams.get('guest_token'),
    lead_id: urlParams.get('lead_id'),
    email: urlParams.get('email')
  });
  return hasGuestParams;
};

const authToken = storage.get('authToken');
console.log('Main.js startup:', { authToken: !!authToken, isGuest: isGuestAccess() });

if (authToken && !isGuestAccess()) {
  fetchCurrentUser().then(user => {
    // Also fetch subscription status so refreshing the page reflects current state immediately
    fetchSubscriptionStatus().then(status => {
      if (status) {
        storage.set('subscriptionStatus', status);
      }
    }).catch(err => {
      console.warn('Could not fetch subscription status on startup', err);
    });
    if (user?.role) {
      const localRole = storage.get('role');
      if (user.role !== localRole) {
        storage.set('role', user.role);
      }
      
      // Start token monitoring after successful authentication check
      tokenMonitor.startMonitoring({
        checkInterval: 5 * 60 * 1000, // Check every 5 minutes
        warningThreshold: 10 * 60 * 1000, // Warn when 10 minutes left
        onExpiringSoon: (seconds) => {
          console.warn(`Your session will expire in ${Math.round(seconds / 60)} minutes`);
          // You could show a toast notification here
        },
        onExpired: () => {
          console.log('Session expired, redirecting to login');
          // Force redirect to login page
          window.location.href = '/login';
        }
      });
    }
  }).finally(() => {
    app.use(router);
    app.mount('#app');
  });
} else {
  app.use(router);
  app.mount('#app');
}