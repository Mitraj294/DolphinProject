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
      <h2 class="login-title">Forgot Password</h2>
      <p class="login-subtitle">Enter your email to reset your password</p>
      <form @submit.prevent="handleForgotPassword">
        <div class="input-group email-group">
          <span class="icon">
            <i class="fas fa-envelope"></i>
          </span>
          <input
            type="email"
            v-model="email"
            placeholder="Email ID"
            required
          />
        </div>
        <button
          type="submit"
          class="login-btn"
          :disabled="loading"
        >
          {{ loading ? 'Sending...' : 'Send Reset Link' }}
        </button>
      </form>
      <div class="switch-auth">
        <span>Remembered your password?</span>
        <router-link
          to="/login"
          class="switch-link"
          >Login here</router-link
        >
      </div>
      <div class="footer">
        <img
          src="@/assets/images/Logo.svg"
          alt="Dolphin Logo"
          class="footer-logo"
        />
        <p class="copyright">©2025 Dolphin | All Rights Reserved</p>
      </div>
    </div>
  </div>
</template>

<script>
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

export default {
  name: 'ForgotPassword',
  components: { Toast },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      email: '',
      loading: false,
      cooldown: 0,
      cooldownInterval: null,
    };
  },
  methods: {
    handleForgotPassword() {
      if (this.cooldown > 0) {
        this.toast.add({
          severity: 'warn',
          summary: 'Please wait',
          detail: `You can request another reset link in ${this.cooldown}s`,
          life: 3000,
        });
        return;
      }
      if (!this.email) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Please enter your email address.',
          life: 3000,
        });
        return;
      }
      this.loading = true;
      fetch('http://127.0.0.1:8000/api/password/email', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: this.email }),
      })
        .then(async (res) => {
          const data = await res.json();
          this.loading = false;
          if (
            res.status === 429 ||
            (data.message && data.message.toLowerCase().includes('throttle'))
          ) {
            this.cooldown = 30;
            this.startCooldown();
            this.toast.add({
              severity: 'warn',
              summary: 'Please wait',
              detail: `You can request another reset link in 3 minutes`,
              life: 3500,
            });
            return;
          }
          this.toast.add({
            severity: 'success',
            summary: 'Reset Link Sent',
            detail:
              data.message ||
              'If this email is registered, a reset link has been sent.',
            life: 3500,
          });

          this.email = '';
          this.cooldown = 30;
          this.startCooldown();
          /*setTimeout(() => {
            this.$router.push('/login');
          }, 1000);*/
        })
        .catch(() => {
          this.loading = false;
          this.toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to send reset link. Try again.',
            life: 3500,
          });
        });
    },
    startCooldown() {
      if (this.cooldownInterval) clearInterval(this.cooldownInterval);
      this.cooldownInterval = setInterval(() => {
        if (this.cooldown > 0) {
          this.cooldown--;
        } else {
          clearInterval(this.cooldownInterval);
          this.cooldownInterval = null;
        }
      }, 1000);
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
@media (max-height: 900px) {
  .login-card {
    padding-top: 16px;
    padding-bottom: 16px;
    /* constrain card height and allow internal scrolling when vertical space is limited */
    max-height: calc(100vh - 32px);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }

  .login-card::-webkit-scrollbar {
    width: 4px;
  }
  .login-card::-webkit-scrollbar-track {
    background: transparent;
  }
  .login-card::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.12);
    border-radius: 8px;
  }
}
</style>
