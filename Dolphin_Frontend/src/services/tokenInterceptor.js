import axios from 'axios';
import storage from './storage';
import router from '../router';

// Helpers
const isTokenExpired = (expiry) => {
  if (!expiry) return false;
  const now = Date.now();
  return now >= new Date(expiry).getTime();
};

const handleTokenExpiry = () => {
  console.log('Token expired, clearing storage and redirecting to login');
  storage.clear();
  router.push('/login');
  return Promise.reject(new Error('Token expired'));
};

const handleUnauthorized = (error) => {
  console.log('Received 401 response, clearing storage and redirecting to login');
  storage.clear();
  router.push('/login');
  return Promise.reject(error);
};

const handleSubscriptionExpired = (error, data) => {
  console.log('Subscription expired, updating storage');

  storage.set('subscription_status', 'expired');
  if (data.subscription_end) storage.set('subscription_end', data.subscription_end);
  if (data.subscription_id) storage.set('subscription_id', data.subscription_id);

  const currentPath = router.currentRoute.value.path;
  const allowedPages = [
    '/manage-subscription',
    '/subscriptions/plans',
    '/profile',
    '/organizations/billing-details'
  ];

  const isOnAllowedPage = allowedPages.some(
    (page) => currentPath === page || currentPath.startsWith(page)
  );

  if (!isOnAllowedPage) {
    console.log('Redirecting to manage subscription page');
    router.push('/manage-subscription');
  } else {
    console.log('Already on allowed page, not redirecting');
  }

  return Promise.reject(error);
};

// Interceptors

// Request interceptor
axios.interceptors.request.use(
  (config) => {
    // normalize auth token from storage (support string or object shapes)
    let authToken = storage.get('authToken');
    const tokenExpiry = storage.get('tokenExpiry');

    if (authToken && typeof authToken === 'object') {
      if (authToken.token) authToken = authToken.token;
      else if (authToken.access_token) authToken = authToken.access_token;
      else authToken = null;
    }

    if (authToken && isTokenExpired(tokenExpiry)) {
      return handleTokenExpiry();
    }

    if (authToken && typeof authToken === 'string') {
      config.headers = config.headers || {};
      // only set Authorization if not already set
      if (!config.headers.Authorization && !config.headers.authorization) {
        config.headers.Authorization = `Bearer ${authToken}`;
      }
    }

    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      return handleUnauthorized(error);
    }

    if (
      error.response?.status === 403 &&
      error.response.data?.status === 'expired' &&
      error.response.data.redirect_url
    ) {
      return handleSubscriptionExpired(error, error.response.data);
    }

    return Promise.reject(error);
  }
);

export default axios;
