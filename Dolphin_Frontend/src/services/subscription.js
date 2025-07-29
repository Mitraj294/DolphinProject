import axios from 'axios';

export async function fetchSubscriptionStatus() {
  const authToken = localStorage.getItem('authToken');
  const API_BASE_URL = 'http://127.0.0.1:8000';
  const res = await axios.get(`${API_BASE_URL}/api/subscription/status`, {
    headers: { Authorization: `Bearer ${authToken}` },
  });
  return res.data;
}
