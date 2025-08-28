<template>
  <div class="login-bg">
    <Toast />
    <img
      src="@/assets/images/Lines.svg"
      alt="Lines"
      class="bg-lines"
    />
    <img
      src="@/assets/images/Image.svg"
      alt="Illustration"
      class="bg-illustration"
    />
    <div class="login-card">
      <h2 class="login-title">Welcome Back</h2>
      <p class="login-subtitle">Please login to your account</p>
      <form @submit.prevent="handleLogin">
        <div class="input-group email-group">
          <span class="icon">
            <i class="fas fa-envelope"></i>
          </span>
          <input
            type="email"
            v-model="email"
            placeholder="Email ID"
            autocomplete="username"
            required
          />
        </div>
        <div class="input-group password-group">
          <span class="icon">
            <i class="fas fa-lock"></i>
          </span>
          <input
            :type="showPassword ? 'text' : 'password'"
            v-model="password"
            placeholder="Password"
            autocomplete="current-password"
            required
          />
          <span
            class="icon right"
            @click="togglePassword"
          >
            <i :class="showPassword ? 'fas fa-eye' : 'fas fa-eye-slash'"></i>
          </span>
        </div>
        <div class="options-row">
          <label class="remember-label">
            <input type="checkbox" /> Remember me
          </label>
          <router-link
            to="/forgot-password"
            class="forgot-password"
            >Forgot Password?</router-link
          >
        </div>
        <button
          type="submit"
          class="login-btn"
        >
          Login
        </button>
      </form>
      <div class="switch-auth">
        <span>Don't have an account?</span>
        <router-link
          to="/register"
          class="switch-link"
          >Register here</router-link
        >
      </div>
      <div class="footer">
        <img
          src="@/assets/images/Logo.svg"
          alt="Dolphin Logo"
          class="footer-logo"
        />
        <p class="copyright">
          &copy; {{ currentYear }} All Rights Reserved By Dolphin
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

import { ROLES } from '@/permissions';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import storage from '@/services/storage';
import { fetchCurrentUser } from '@/services/user';

const API_BASE_URL =
  process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

export default {
  name: 'Login',
  components: { Toast },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      email: '',
      password: '',
      showPassword: false,
      currentYear: new Date().getFullYear(),
    };
  },
  mounted() {
    if (this.$route.query.email) {
      this.email = this.$route.query.email;
    }

    if (this.$route.query.registrationSuccess) {
      this.toast.add({
        severity: 'success',
        summary: 'Registration Successful',
        detail: 'Please log in with your new account.',
        life: 3000,
      });
    }

    const query = { ...this.$route.query };
    delete query.email;
    delete query.registrationSuccess;
    this.$router.replace({ query }).catch(() => {});

    // If auth token exists in storage (shared across tabs via localStorage),
    // validate it by fetching the current user and redirect to dashboard.
    const token = storage.get('authToken');
    if (token) {
      fetchCurrentUser()
        .then((user) => {
          if (user) {
            if (user.role) storage.set('role', user.role);
            // navigate to dashboard
            this.$router.push('/dashboard').catch(() => {});
          } else {
            // token invalid — remove it so user can login normally
            storage.remove('authToken');
          }
        })
        .catch(() => {
          // ignore failures here; user stays on login
        });
    }
  },
  methods: {
    togglePassword() {
      this.showPassword = !this.showPassword;
    },
    async handleLogin() {
      try {
        const response = await axios.post(`${API_BASE_URL}/api/login`, {
          email: this.email,
          password: this.password,
        });

        const token = response.data.token;
        const role = response.data.user.role;
        const name = response.data.user.name;
        const firstName = response.data.user.first_name || '';
        const lastName = response.data.user.last_name || '';

        storage.set('authToken', token);
        storage.set('role', role);
        storage.set('first_name', firstName);
        storage.set('last_name', lastName);
        // Set userName as 'first_name last_name' if available, else fallback to name
        if (firstName || lastName) {
          storage.set(
            'userName',
            `${firstName}${lastName ? ' ' + lastName : ''}`.trim()
          );
        } else {
          storage.set('userName', name);
        }

        // Set login success flag for dashboard toast
        storage.set('showDashboardWelcome', '1');

        this.toast.add({
          severity: 'success',
          summary: 'Login Successful',
          detail: `Welcome, ${firstName || ''}${
            lastName ? ' ' + lastName : !firstName ? name : ''
          }!`,
          life: 3000,
        });

        this.$router.push('/dashboard');
      } catch (error) {
        console.error('Login failed:', error);
        let errorMessage = 'Login failed. Please check your credentials.';
        if (
          error.response &&
          error.response.data &&
          error.response.data.message
        ) {
          errorMessage = error.response.data.message;
        }
        this.toast.add({
          severity: 'error',
          summary: 'Login Error',
          detail: errorMessage,
          life: 4000,
        });
      }
    },
  },
};
</script>

<style scoped>
.login-bg {
  position: relative;
  width: 100vw;
  height: 100vh;
  background: #f8f9fb;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

.bg-lines {
  position: absolute;
  left: 0;
  top: 0;
  width: 250px;
  height: auto;
  z-index: 0;
}

.bg-illustration {
  position: absolute;
  right: 0;
  bottom: 0;
  width: 300px;
  height: auto;
  z-index: 0;
}

.login-card {
  position: relative;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  padding: 48px 48px 32px 48px;
  text-align: center;
  z-index: 1;
  max-width: 480px;
  width: 100%;
  box-sizing: border-box;
}

.login-title {
  font-size: 2rem;
  font-weight: 600;
  color: #234056;
  margin-bottom: 8px;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}

.login-subtitle {
  font-size: 1rem;
  color: #787878;
  margin-bottom: 32px;
  font-family: 'Inter', Arial, sans-serif;
}

.input-group {
  position: relative;
  margin-bottom: 24px;
}
.input-group input {
  width: 100%;
  padding: 12px 12px 12px 48px;
  border: 1.5px solid #e0e0e0;
  border-radius: 12px;
  font-size: 1rem;
  color: #222;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.18s;
}
.input-group input:focus {
  border-color: #0074c2;
}
.input-group .icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #787878;
  font-size: 1rem;
}
.input-group .icon.right {
  left: auto;
  right: 16px;
  cursor: pointer;
}

.options-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  font-size: 0.9rem;
  color: #787878;
  font-family: 'Inter', Arial, sans-serif;
}
.remember-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}
.remember-label input[type='checkbox'] {
  margin: 0;
  accent-color: #0074c2;
}
.forgot-password {
  color: #0164a5;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
}
.forgot-password:hover {
  color: #1690d1;
}

.login-btn {
  width: 100%;
  padding: 14px;
  background: #0074c2;
  color: #fff;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  margin-bottom: 32px;
  margin-top: 8px;
  transition: background 0.2s;
}
.login-btn:hover {
  background: #1690d1;
}

.switch-auth {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  font-size: 1rem;
  color: #787878;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}

.switch-link {
  color: #0164a5;
  text-decoration: underline;
  cursor: pointer;
  font-weight: 500;
}

.switch-link:hover {
  color: #1690d1;
}

.footer {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 8px;
}
.footer-logo {
  width: 28px;
  height: 28px;
  object-fit: contain;
  margin-bottom: 10px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
}
.copyright {
  color: #787878;
  font-size: 14px;
  font-family: 'Inter', Arial, sans-serif;
  text-align: center;
  margin-top: 4px;
}

@media (max-width: 1200px) {
  .bg-lines {
    width: 180px;
    left: 1vw;
    top: 8vh;
  }
  .bg-illustration {
    width: 220px;
    right: 1vw;
    bottom: 8vh;
  }
  .login-card {
    padding: 32px;
    max-width: 400px;
  }
}

@media (max-width: 768px) {
  .bg-lines {
    width: 120px;
    left: -20px;
    top: -20px;
  }
  .bg-illustration {
    width: 150px;
    right: -20px;
    bottom: -20px;
  }
  .login-card {
    padding: 24px;
    margin: 0 16px;
  }
  .login-title {
    font-size: 1.8rem;
  }
  .login-subtitle {
    font-size: 0.9rem;
  }
  .input-group input {
    font-size: 0.9rem;
  }
  .login-btn {
    font-size: 1rem;
    padding: 12px;
  }
}
</style>
