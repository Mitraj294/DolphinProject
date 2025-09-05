
import axios from 'axios';
import storage from './storage';

const AUTH_TOKEN_KEY = 'authToken';
const TOKEN_EXPIRY_KEY = 'tokenExpiry';

const authService = {

    setToken(token, expiresAt = null) {
        storage.set(AUTH_TOKEN_KEY, token);
        if (expiresAt) {
            storage.set(TOKEN_EXPIRY_KEY, expiresAt);
        }
        this.setAxiosAuthHeader(token);
    },

    getToken() {
        const token = storage.get(AUTH_TOKEN_KEY);
        // Check if token is expired before returning it
        if (token && this.isTokenExpired()) {
            this.removeToken();
            return null;
        }
        return token;
    },

    isTokenExpired() {
        const expiryTime = storage.get(TOKEN_EXPIRY_KEY);
        if (!expiryTime) return false;
        
        const now = new Date().getTime();
        const expiry = new Date(expiryTime).getTime();
        return now >= expiry;
    },

    removeToken() {
        storage.remove(AUTH_TOKEN_KEY);
        storage.remove(TOKEN_EXPIRY_KEY);
        this.setAxiosAuthHeader(null);
    },

    setAxiosAuthHeader(token) {
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        } else {
            delete axios.defaults.headers.common['Authorization'];
        }
    },

    async login(email, password) {
        try {
            const response = await axios.post('/api/login', {
                email,
                password
            });
            const { token, user, expires_at } = response.data;
            this.setToken(token, expires_at);
            if (user && user.id) {
                storage.set('user_id', user.id);
            }
            return { user, token };
        } catch (error) {
            console.error   ('Login error:', error);    
            throw error;
        }

    },

    async logout() {
        try {
            await axios.post('/api/logout');
            this.removeToken();
        } catch (error) {
            console.error('Logout error:', error);
            // Still remove token even if API call fails
            this.removeToken();
        }
    },

    isAuthenticated() {
        const token = this.getToken(); // This will check expiry automatically
        return !!token;
    },

    // Check if token will expire within the next 5 minutes
    isTokenExpiringSoon() {
        const expiryTime = storage.get(TOKEN_EXPIRY_KEY);
        if (!expiryTime) return false;
        
        const now = new Date().getTime();
        const expiry = new Date(expiryTime).getTime();
        const oneMinute = 1 * 60 * 1000; // 1 minute in milliseconds

        return (expiry - now) <= oneMinute;
    },

    // Initialize axios with token if it exists
    init() {
        const token = this.getToken();
        if (token) {
            this.setAxiosAuthHeader(token);
        }
    },

    getUserId() {
        return storage.get('user_id');
    }
};

// Initialize auth service
authService.init();

export default authService;
