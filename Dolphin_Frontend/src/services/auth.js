import axios from 'axios';

const AUTH_TOKEN_KEY = 'auth_token';

const authService = {
    setToken(token) {
        localStorage.setItem(AUTH_TOKEN_KEY, token);
        this.setAxiosAuthHeader(token);
    },

    getToken() {
        return localStorage.getItem(AUTH_TOKEN_KEY);
    },

    removeToken() {
        localStorage.removeItem(AUTH_TOKEN_KEY);
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
    }
};

// Initialize auth service
authService.init();

export default authService;
