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

// Add a response interceptor to handle 401 errors
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
    return Promise.reject(error);
  }
);

export default axios;
