// src/main.js
import './assets/global.css';
import './assets/table.css';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { fetchCurrentUser } from './services/user';

// PrimeVue Imports
import PrimeVue from 'primevue/config'; // Import PrimeVue configuration
import ToastService from 'primevue/toastservice'; // Import ToastService
import ConfirmService from 'primevue/confirmationservice';

// PrimeVue Styles (choose a theme and include primevue.min.css)
import 'primevue/resources/themes/lara-light-blue/theme.css'; // Recommended theme, or choose another
import 'primevue/resources/primevue.min.css'; // Core PrimeVue styles
// Quill editor styles (required by PrimeVue Editor)
import 'quill/dist/quill.snow.css';

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
// Register Editor globally so it's available in all components without local import
import Editor from 'primevue/editor';
app.component('Editor', Editor);
// PrimeVue v3 uses the Calendar component for date selection. "primevue/datepicker" is not available
// in this PrimeVue version, so import the Calendar component instead and register it globally.
import Calendar from 'primevue/calendar';
app.component('Calendar', Calendar); // Register Calendar globally


// Sync encrypted storage role with backend user role on app start

import storage from './services/storage';
const authToken = storage.get('authToken');
if (authToken) {
  fetchCurrentUser().then(user => {
    if (user && user.role) {
      const localRole = storage.get('role');
      if (user.role !== localRole) {
        storage.set('role', user.role);
      }
    }
  }).finally(() => {
    app.use(router);
    app.mount('#app');
  });
} else {
  app.use(router);
  app.mount('#app');
}