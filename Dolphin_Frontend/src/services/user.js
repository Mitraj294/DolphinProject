
import axios from 'axios';
import storage from './storage';

const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

export async function fetchCurrentUser() {
  try {
    const authToken = storage.get('authToken');
    const headers = {};
    if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
    const response = await axios.get(`${API_BASE_URL}/api/user`, { headers });
    return response.data;
  } catch (e) {
    console.error('Error fetching current user:', e);
    return null;
  }
}
