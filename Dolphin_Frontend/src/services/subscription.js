import axios from 'axios';
import storage from './storage';

export async function fetchSubscriptionStatus() {
  let authToken = storage.get('authToken');
  // support multiple token shapes: string, { token }, { access_token }
  if (authToken && typeof authToken === 'object') {
    if (authToken.token) authToken = authToken.token;
    else if (authToken.access_token) authToken = authToken.access_token;
    else authToken = null;
  }
  if (typeof authToken !== 'string') authToken = null;

  const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || '';
  const headers = authToken ? { Authorization: `Bearer ${authToken}` } : {};

  let res;
  try {
    res = await axios.get(`${API_BASE_URL}/api/subscription/status`, { headers });
  } catch (err) {
    // Bubble up 401 with a clearer console message for debugging
    if (err?.response?.status === 401) {
      console.warn('Subscription status request returned 401 Unauthorized. Missing or invalid auth token.');
    }
    throw err;
  }
  // Update subscription status in storage for router guards
  if (res.data && res.data.status) {
    storage.set('subscription_status', res.data.status);
  } else {
    // clear any existing status
    storage.remove('subscription_status');
  }
  return res.data;
}
