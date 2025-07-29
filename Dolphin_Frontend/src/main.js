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

// PrimeVue Styles (choose a theme and include primevue.min.css)
import 'primevue/resources/themes/lara-light-blue/theme.css'; // Recommended theme, or choose another
import 'primevue/resources/primevue.min.css'; // Core PrimeVue styles

// Font Awesome and PrimeIcons
import 'primeicons/primeicons.css'; // PrimeIcons for PrimeVue components
import '@fortawesome/fontawesome-free/css/all.min.css'; // Font Awesome for your custom icons

const app = createApp(App);


// Install PrimeVue and its services
app.use(PrimeVue); // Initialize PrimeVue
app.use(ToastService); // Install the ToastService globally
import Toast from 'primevue/toast';
app.component('Toast', Toast); // Register Toast globally


// Sync localStorage role with backend user role on app start
fetchCurrentUser().then(user => {
  if (user && user.role) {
    const localRole = localStorage.getItem('role');
    if (user.role !== localRole) {
      localStorage.setItem('role', user.role);
    }
  }
}).finally(() => {
  app.use(router);
  app.mount('#app');
});