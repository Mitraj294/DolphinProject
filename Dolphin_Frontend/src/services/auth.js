import axios from 'axios';
import storage from './storage';

const AUTH_TOKEN_KEY = 'authToken';

const authService = {

    setToken(token) {
        storage.set(AUTH_TOKEN_KEY, token);
        this.setAxiosAuthHeader(token);
    },

    getToken() {
        return storage.get(AUTH_TOKEN_KEY);
    },

    removeToken() {
        storage.remove(AUTH_TOKEN_KEY);
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
            const { token, user } = response.data;
            this.setToken(token);
            if (user && user.id) {
                storage.set('user_id', user.id);
            }
            return { user, token };
        } catch (error) {
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
        return !!this.getToken();
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

// Add a global Axios response interceptor to catch 401 Unauthorized responses.
// On 401 we remove the saved token and redirect the user to the login page so
// they can't continue using the app without authenticating again.
axios.interceptors.response.use(
    response => response,
    error => {
        if (error && error.response && error.response.status === 401) {
            try {
                authService.removeToken();
            } catch (e) {
                // ignore
            }
            // Redirect to the login route. Update path if your app uses a different route.
            // Using window.location to ensure a full reload and clean state.
            window.location.href = '/';
        }
        return Promise.reject(error);
    }
);

export default authService;
