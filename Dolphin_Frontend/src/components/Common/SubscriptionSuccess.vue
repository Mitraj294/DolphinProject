<template>
  <div class="thankyou-layout">
    <Sidebar />
    <div class="main-content">
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
      <div class="thankyou-bg">
        <div class="thankyou-card">
          <div class="check-circle">
            <svg
              width="56"
              height="56"
              viewBox="0 0 56 56"
            >
              <circle
                cx="28"
                cy="28"
                r="28"
                fill="#2ecc40"
              />
              <polyline
                points="18,30 26,38 38,20"
                fill="none"
                stroke="#fff"
                stroke-width="4"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
          <h2 class="thankyou-title">Subscription Successful!</h2>
          <div class="thankyou-desc">
            Your subscription has been processed successfully
          </div>
          <div class="thankyou-touch">
            Thank you for subscribing. We will be in touch.
          </div>
          <div class="success-actions">
            <button
              class="btn btn-primary"
              @click="goHome"
            >
              Go to Home
            </button>
          </div>
          <div class="thankyou-footer">
            <img
              :src="require('@/assets/images/Logo.svg')"
              alt="Dolphin Logo"
              class="footer-logo"
            />
            <div class="copyright">©2025 Dolphin | All Rights Reserved</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SubscriptionSuccess',
  mounted() {
    // Read query params to optionally display checkout/session information
    const q = this.$route.query || {};
    this.checkoutSessionId = q.checkout_session_id || null;
    this.email = q.email || null;
    this.guestCode = q.guest_code || q.guest_token || null;
  },
  methods: {
    goHome() {
      // Persist the email (if present) so Home or Login can prefill the email field.
      if (this.email) {
        try {
          const storage = require('@/services/storage').default;
          storage.set('prefill_email', this.email);
        } catch (e) {
          // eslint-disable-next-line no-console
          console.warn('Could not save prefill email', e);
        }
      }
      this.$router.push('/');
    },
    goLogin() {
      // Navigate to the named Login route to be resilient to path changes
      this.$router.push({ name: 'Login' });
    },
  },
  data() {
    return {
      checkoutSessionId: null,
      email: null,
      guestCode: null,
    };
  },
};
</script>

<style scoped>
.thankyou-layout {
  display: flex;
  align-items: stretch;
  /* Fill the viewport so inner flex centering can vertically center content */
  min-height: 100vh;
  box-sizing: border-box;
}
.main-content {
  flex: 1;
  align-items: center;
  display: flex;
  flex-direction: column;
  background-color: #f6faff;
  min-height: 100vh;
}
.thankyou-bg {
  flex: 1;
  background: #f6faff;
  display: flex;
  align-items: center;
  justify-content: center;

  overflow: hidden;
}
.thankyou-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 8px 24px 0 rgba(0, 0, 0, 0.08);
  padding: 48px 40px 32px 40px;
  max-width: 420px;
  width: 100%;
  text-align: center;
  z-index: 2;
  position: relative;
}
.check-circle {
  margin-bottom: 24px;
}
.thankyou-title {
  font-size: 1.7rem;
  font-weight: 600;
  color: #111;
  margin-bottom: 16px;
  margin-top: 0;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}
.thankyou-desc {
  color: #888;
  font-size: 1.08rem;
  margin-bottom: 18px;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}
.thankyou-touch {
  color: #444;
  font-size: 1.08rem;
  margin-bottom: 24px;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}
.thankyou-footer {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 12px;
}
.footer-logo {
  width: 28px;
  height: 28px;
  object-fit: contain;
  margin-bottom: 8px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
}
.copyright {
  color: #787878;
  font-size: 14px;
  font-family: 'Inter', Arial, sans-serif;
  text-align: center;
  margin-top: 2px;
}
.success-actions {
  margin-top: 16px;
  display: flex;
  gap: 12px;
  justify-content: center;
}
.btn-primary {
  background: #2196f3;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
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
}

@media (max-width: 900px) {
  .thankyou-card {
    margin: 0 8vw;
    padding: 32px 8vw 24px 8vw;
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
}
</style>
