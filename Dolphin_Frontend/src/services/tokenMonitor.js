import axios from 'axios';
import storage from './storage';

const API_BASE_URL = process.env.VUE_APP_API_BASE_URL  ;

let tokenCheckInterval = null;
let lastExpiryWarning = 0;

const tokenMonitor = {
  
  /**
   * Start monitoring token expiry
   * @param {Object} options - Configuration options
   * @param {number} options.checkInterval - How often to check in milliseconds (default: 5 minutes)
   * @param {number} options.warningThreshold - Warn when token expires within this many minutes (default: 10)
   * @param {Function} options.onExpiringSoon - Callback when token is expiring soon
   * @param {Function} options.onExpired - Callback when token is expired
   */
  startMonitoring(options = {}) {
    const {
      checkInterval = 5 * 60 * 1000, // 5 minutes
      warningThreshold = 10 * 60 * 1000, // 10 minutes
      onExpiringSoon = null,
      onExpired = null
    } = options;

    // Clear any existing interval
    this.stopMonitoring();

    const normalizeToken = (raw) => {
      let token = raw;
      if (token && typeof token === 'object') {
        if (token.token) return token.token;
        if (token.access_token) return token.access_token;
        return null;
      }
      return token;
    };

    const fetchStatus = async (token) => {
      return axios.get(`${API_BASE_URL}/api/token/status`, {
        headers: { Authorization: `Bearer ${token}` }
      });
    };

    const handleExpired = () => {
      console.log('Token has expired');
      storage.clear();
      if (onExpired && typeof onExpired === 'function') onExpired();
      this.stopMonitoring();
    };

    tokenCheckInterval = setInterval(async () => {
      let authToken = normalizeToken(storage.get('authToken'));
      if (!authToken) return;

      try {
        const response = await fetchStatus(authToken);
        const { expires_in_seconds } = response.data;
        const expiresInMs = expires_in_seconds * 1000;

        if (expires_in_seconds <= 0) {
          handleExpired();
          return;
        }

        if (expiresInMs <= warningThreshold) {
          const now = Date.now();
          if (now - lastExpiryWarning > 5 * 60 * 1000) {
            console.warn(`Token will expire in ${Math.round(expires_in_seconds / 60)} minutes`);
            lastExpiryWarning = now;
            if (onExpiringSoon && typeof onExpiringSoon === 'function') onExpiringSoon(expires_in_seconds);
          }
        }
      } catch (error) {
        if (error.response && error.response.status === 401) {
          console.log('Token validation failed with 401');
          handleExpired();
        } else {
          console.error('Token status check failed:', error);
        }
      }
    }, checkInterval);

    console.log('Token monitoring started');
  },

  /**
   * Stop monitoring token expiry
   */
  stopMonitoring() {
    if (tokenCheckInterval) {
      clearInterval(tokenCheckInterval);
      tokenCheckInterval = null;
      console.log('Token monitoring stopped');
    }
  },

  /**
   * Check token status immediately (one-time check)
   * @returns {Promise<Object|null>} Token status or null if failed
   */
  async checkTokenNow() {
    try {
      let authToken = storage.get('authToken');
      if (!authToken) {
        return null;
      }
      if (authToken && typeof authToken === 'object') {
        if (authToken.token) authToken = authToken.token;
        else if (authToken.access_token) authToken = authToken.access_token;
        else authToken = null;
      }
      if (!authToken) return null;

      const response = await axios.get(`${API_BASE_URL}/api/token/status`, {
        headers: { Authorization: `Bearer ${authToken}` }
      });

      return response.data;
    } catch (error) {
      console.error('Token status check failed:', error);
      if (error.response && error.response.status === 401) {
        storage.clear();
      }
      return null;
    }
  }
};

export default tokenMonitor;
