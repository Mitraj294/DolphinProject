import axios from 'axios';
import storage from './storage';
import router from '../router';

// Add a request interceptor to check token expiry before each request
axios.interceptors.request.use(
  (config) => {
    const authToken = storage.get('authToken');
    const tokenExpiry = storage.get('tokenExpiry');
    
    if (authToken && tokenExpiry) {
      const now = new Date().getTime();
      const expiry = new Date(tokenExpiry).getTime();
      
      // If token is expired, clear storage and redirect to login
      if (now >= expiry) {
        console.log('Token expired, clearing storage and redirecting to login');
        storage.clear();
        router.push('/login');
        return Promise.reject(new Error('Token expired'));
      }

  
    }
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add a response interceptor to handle 401 errors and subscription expiry
axios.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response && error.response.status === 401) {
      console.log('Received 401 response, clearing storage and redirecting to login');
      storage.clear();
      router.push('/login');
    }
    
    // Handle subscription expired responses (403 with specific structure)
    if (error.response && error.response.status === 403 && error.response.data) {
      const data = error.response.data;
      if (data.status === 'expired' && data.redirect_url) {
        console.log('Subscription expired, updating storage');
        
        // Update subscription status in storage
        storage.set('subscription_status', 'expired');
        if (data.subscription_end) {
          storage.set('subscription_end', data.subscription_end);
        }
        if (data.subscription_id) {
          storage.set('subscription_id', data.subscription_id);
        }
        
        // Only redirect if not already on an allowed page for expired subscriptions
        const currentPath = router.currentRoute.value.path;
        const allowedPagesForExpired = [
          '/manage-subscription',
          '/subscriptions/plans',
          '/profile',
          '/organizations/billing-details'
        ];
        
        const isOnAllowedPage = allowedPagesForExpired.some(page => 
          currentPath === page || currentPath.startsWith(page)
        );
        
        if (!isOnAllowedPage) {
          console.log('Redirecting to manage subscription page');
          router.push('/manage-subscription');
        } else {
          console.log('Already on allowed page, not redirecting');
        }
        
        return Promise.reject(error); // Still reject to prevent continued processing
      }
    }
    
    return Promise.reject(error);
  }
);

export default axios;
