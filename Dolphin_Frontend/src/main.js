// src/main.js
import './assets/global.css';
import './assets/table.css';
import './assets/modelcssnotificationandassesment.css';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { fetchCurrentUser } from './services/user';

import storage from './services/storage';
import tokenMonitor from './services/tokenMonitor';

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
const app = createApp(App);


// Install PrimeVue and its services
app.use(PrimeVue); // Initialize PrimeVue
app.use(ToastService); // Install the ToastService globally
app.use(ConfirmService); // Install the ConfirmService globally for ConfirmDialog
import Toast from 'primevue/toast';
app.component('Toast', Toast); // Register Toast globally
import ConfirmDialog from 'primevue/confirmdialog';
app.component('ConfirmDialog', ConfirmDialog);
// PrimeVue v3 uses the Calendar component for date selection. "primevue/datepicker" is not available
// in this PrimeVue version, so import the Calendar component instead and register it globally.
import Calendar from 'primevue/calendar';
app.component('Calendar', Calendar); // Register Calendar globally


// Sync encrypted storage role with backend user role on app start



const authToken = storage.get('authToken');
if (authToken) {
  fetchCurrentUser().then(user => {
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