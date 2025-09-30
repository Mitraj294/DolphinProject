<template>
  <div class="magic-login-page">
    <div
      class="magic-login-card"
      v-if="!error"
    >
      <div class="spinner" />
      <h2>Preparing your account…</h2>
      <p>We're signing you in and getting your subscription plans ready.</p>
    </div>
    <div
      class="magic-login-card"
      v-else
    >
      <h2>Link Expired</h2>
      <p>{{ error }}</p>
      <button @click="goToLogin">Go to Login</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import storage from '@/services/storage';

const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;

export default {
  name: 'MagicLoginRedirect',
  data() {
    return {
      error: null,
    };
  },
  async mounted() {
    const {
      token,
      email,
      lead_id: leadId,
      price_id: priceId,
    } = this.$route.query;

    if (!token) {
      this.error = 'The login link is missing required information.';
      return;
    }

    try {
      const payload = { token };
      if (email) payload.email = email;
      if (leadId) payload.lead_id = leadId;
      if (priceId) payload.price_id = priceId;

      const response = await axios.post(
        `${API_BASE_URL}/api/leads/magic-login`,
        payload
      );
      await this.persistSession(response.data);

      const query = {};
      if (email) query.email = email;
      if (leadId) query.lead_id = leadId;
      if (priceId) query.price_id = priceId;

      this.$router.replace({ name: 'SubscriptionPlans', query });
    } catch (err) {
      console.error('Magic login failed:', err);
      this.error =
        'This link has expired. Please request a new agreement email.';
      storage.clear();
      setTimeout(() => {
        this.goToLogin();
      }, 3500);
    }
  },
  methods: {
    async persistSession(payload = {}) {
      const accessToken = payload.access_token || null;
      const refreshToken = payload.refresh_token || null;
      const expiresAt = payload.expires_at || null;
      const user = payload.user || null;

      if (accessToken) {
        storage.set('authToken', accessToken);
        axios.defaults.headers.common[
          'Authorization'
        ] = `Bearer ${accessToken}`;
      }

      if (refreshToken) {
        storage.set('refreshToken', refreshToken);
      } else {
        storage.remove('refreshToken');
      }

      if (expiresAt) {
        storage.set('tokenExpiry', expiresAt);
      }

      if (user) {
        storage.set('user', user);
        if (user.role) storage.set('role', user.role);
        if (user.first_name) storage.set('first_name', user.first_name);
        if (user.last_name) storage.set('last_name', user.last_name);

        const fullName = `${user.first_name || ''} ${
          user.last_name || ''
        }`.trim();
        if (fullName) {
          storage.set('userName', fullName);
        }
        if (user.organization_name) {
          storage.set('organization_name', user.organization_name);
        }
      }
    },
    goToLogin() {
      this.$router.replace({ name: 'Login' }).catch(() => {});
    },
  },
};
</script>

<style scoped>
.magic-login-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f5f7fb;
  padding: 32px;
}

.magic-login-card {
  width: 100%;
  max-width: 420px;
  background: #fff;
  padding: 32px;
  border-radius: 16px;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
  text-align: center;
}

.magic-login-card h2 {
  margin-bottom: 16px;
  font-size: 24px;
  color: #1f2937;
}

.magic-login-card p {
  color: #4b5563;
  margin-bottom: 16px;
}

.magic-login-card button {
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 18px;
  font-weight: 600;
  cursor: pointer;
}

.magic-login-card button:hover {
  background: #1d4ed8;
}

.spinner {
  width: 48px;
  height: 48px;
  margin: 0 auto 24px;
  border-radius: 50%;
  border: 4px solid #dbeafe;
  border-top-color: #2563eb;
  animation: spin 0.9s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
