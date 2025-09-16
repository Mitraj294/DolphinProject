import axios from 'axios';
import storage from './storage';

export async function fetchSubscriptionStatus() {
  const authToken = storage.get('authToken');
  const API_BASE_URL = 'http://127.0.0.1:8000';
  const res = await axios.get(`${API_BASE_URL}/api/subscription/status`, {
    headers: { Authorization: `Bearer ${authToken}` },
  });
  // Update subscription status in storage for router guards
  if (res.data && res.data.status) {
    storage.set('subscription_status', res.data.status);
  } else {
    // clear any existing status
    storage.remove('subscription_status');
  }
  return res.data;
}
