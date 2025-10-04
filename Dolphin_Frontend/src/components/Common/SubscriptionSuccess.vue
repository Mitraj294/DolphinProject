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
          <h2 class="thankyou-title">Successfully Subscribed!</h2>
          <div class="thankyou-desc">
            Your subscription has been processed successfully
          </div>
          <div class="thankyou-touch">
            Thank you for subscribing. We will be in touch.
          </div>
          <!-- Plan summary: prefilled from query params when available -->
          <div
            v-if="shouldShowPlanSummary"
            class="plan-summary"
          >
            <div class="plan-summary-left">
              <div class="plan-name">{{ planName || defaultPlanLabel }}</div>
              <div class="plan-price">
                <span class="price">{{ formattedAmount }}</span>
                <span class="period">/{{ planPeriodDisplay }}</span>
              </div>
              <div class="plan-meta">
                <span
                  class="status"
                  :class="subscriptionStatusClass"
                  >{{ subscriptionStatusLabel }}</span
                >
                <span
                  v-if="subscriptionEnd"
                  class="ends"
                  >Ends: {{ formattedEnd }}</span
                >
                <span
                  v-if="nextBilling"
                  class="next-billing"
                >
                  Next bill: {{ formattedNextBilling }}
                </span>
                <span
                  v-else-if="nextPayment"
                  class="next-billing"
                >
                  Next payment: {{ formattedNextPayment }}
                </span>
              </div>
            </div>
            <div class="plan-summary-right">
              <button
                class="btn btn-outline"
                @click="goToBilling"
              >
                View Billing Details
              </button>
            </div>
          </div>
          <div
            v-else-if="loadingSession"
            class="plan-summary plan-summary--loading"
          >
            <div class="loader">Loading plan details…</div>
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
    // Plan details available from redirect query params (guest flow)
    this.planAmount = q.plan_amount || q.planAmount || null;
    this.planName = q.plan_name || q.plan || null;
    this.planPeriod = q.plan_period || q.period || null;
    this.subscriptionEnd = q.subscription_end || q.subscriptionEnd || null;
    this.subscriptionStatus =
      q.subscription_status || q.subscriptionStatus || null;
    // Next billing/payment info (multiple possible param names)
    this.nextBilling =
      q.next_billing ||
      q.next_billing_date ||
      q.nextBill ||
      q.next_bill_date ||
      null;
    this.nextPayment =
      q.next_payment || q.next_payment_date || q.nextPayment || null;

    // If we only have a checkout session id, fetch details from backend to fill plan info
    if (!this.planName && !this.planAmount && this.checkoutSessionId) {
      this.loadingSession = true;
      try {
        const axios = require('axios');
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || '';
        const url = `${API_BASE_URL}/api/stripe/session`;
        const params = { session_id: this.checkoutSessionId };
        axios
          .get(url, { params })
          .then((resp) => {
            const d = resp.data || {};
            // map available fields
            if (d.amount_total)
              this.planAmount = (d.amount_total / 100).toFixed(2);
            if (d.line_items && d.line_items[0] && d.line_items[0].description)
              this.planName = d.line_items[0].description;
            if (d.subscription_end) this.subscriptionEnd = d.subscription_end;
            if (d.next_billing) this.nextBilling = d.next_billing;
          })
          .catch((err) => {
            // not critical — don't block the UI
            // eslint-disable-next-line no-console
            console.debug(
              'Could not fetch session details',
              err?.message || err
            );
          })
          .finally(() => {
            this.loadingSession = false;
          });
      } catch (e) {
        // eslint-disable-next-line no-console
        console.debug(
          'SubscriptionSuccess mounted helper error',
          e?.message || e
        );
        this.loadingSession = false;
      }
    }
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
    goToBilling() {
      // navigate to billing details page
      this.$router.push({ path: '/organizations/billing-details' });
    },
  },

  data() {
    return {
      checkoutSessionId: null,
      email: null,
      guestCode: null,
      planAmount: null,
      planName: null,
      planPeriod: null,
      subscriptionEnd: null,
      nextBilling: null,
      nextPayment: null,
      loadingSession: false,
      subscriptionStatus: null,
    };
  },
  computed: {
    shouldShowPlanSummary() {
      // show summary when we have any plan info, or when not loading
      return (
        (this.planName || this.planAmount || this.subscriptionStatus) &&
        !this.loadingSession
      );
    },
    formattedAmount() {
      if (!this.planAmount) return '';
      // parse numeric strings like 250.00
      const n = parseFloat(String(this.planAmount).replace(/,/g, ''));
      if (!Number.isFinite(n)) return this.planAmount;
      // show without decimals if whole number
      return `$${n % 1 === 0 ? n.toFixed(0) : n.toFixed(2)}`;
    },
    formattedNextBilling() {
      if (!this.nextBilling) return null;
      const d = new Date(this.nextBilling);
      if (isNaN(d.getTime())) return this.nextBilling;
      return d.toLocaleString();
    },
    formattedNextPayment() {
      if (!this.nextPayment) return null;
      const d = new Date(this.nextPayment);
      if (isNaN(d.getTime())) return this.nextPayment;
      return d.toLocaleString();
    },
    planPeriodLabel() {
      const p = String(this.planPeriod || '').toLowerCase();
      if (p.includes('month')) return 'month';
      if (p.includes('ann') || p.includes('year')) return 'annual';
      return this.planPeriod || 'plan';
    },
    formattedEnd() {
      if (!this.subscriptionEnd) return null;
      const d = new Date(this.subscriptionEnd);
      if (isNaN(d.getTime())) return this.subscriptionEnd;
      return d.toLocaleString();
    },
    defaultPlanLabel() {
      // Prefer an explicit plan name when provided
      if (this.planName) {
        const name = String(this.planName).toLowerCase();
        if (name.includes('basic')) return 'Basic';
        if (name.includes('standard')) return 'Standard';
      }

      // Fallback to period-based mapping: Monthly -> Basic, Annual -> Standard
      const p = String(this.planPeriod || '').toLowerCase();
      if (p.includes('month')) return 'Basic';
      if (p.includes('ann') || p.includes('year')) return 'Standard';

      // As a last resort, infer from amount (common pricing heuristics used here)
      if (this.planAmount) {
        const n = parseFloat(String(this.planAmount).replace(/,/g, ''));
        if (Number.isFinite(n)) {
          if (n >= 1000) return 'Standard';
          return 'Basic';
        }
      }

      return 'Basic';
    },
    planPeriodDisplay() {
      const p = String(this.planPeriod || '').toLowerCase();
      if (p.includes('month')) return 'Month';
      if (p.includes('ann') || p.includes('year')) return 'Annual';

      // Infer from numeric amount when period isn't provided
      if (this.planAmount) {
        const n = parseFloat(String(this.planAmount).replace(/[,\s]/g, ''));
        if (Number.isFinite(n)) return n >= 1000 ? 'Annual' : 'Month';
      }

      // default
      return 'Month';
    },
    subscriptionStatusLabel() {
      if (!this.subscriptionStatus) return '';
      return (
        String(this.subscriptionStatus).charAt(0).toUpperCase() +
        String(this.subscriptionStatus).slice(1)
      );
    },
    subscriptionStatusClass() {
      if (!this.subscriptionStatus) return '';
      const s = String(this.subscriptionStatus).toLowerCase();
      if (s === 'active' || s === 'success') return 'status-active';
      if (s === 'expired') return 'status-expired';
      return 'status-unknown';
    },
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
.btn-outline {
  background: white;
  color: #2196f3;
  border: 2px solid #2196f3;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
}
.plan-summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin: 18px 0 8px 0;
  padding: 12px 16px;
  border-radius: 8px;
  background: #f6fbff;
  border: 1px solid #e6f0fb;
}
.plan-summary-left {
  text-align: left;
}
.plan-name {
  font-weight: 700;
  font-size: 1.05rem;
  color: #0b4666;
}
.plan-price {
  margin-top: 6px;
  font-size: 1.2rem;
  color: #111;
}
.plan-meta {
  margin-top: 6px;
  font-size: 0.9rem;
  color: #555;
  display: flex;
  gap: 10px;
  align-items: center;
}
.status-active {
  color: #2e7d32;
  font-weight: 600;
}
.status-expired {
  color: #d32f2f;
  font-weight: 600;
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
