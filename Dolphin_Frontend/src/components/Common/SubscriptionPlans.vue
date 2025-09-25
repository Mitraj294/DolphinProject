<template>
  <MainLayout>
    <div class="page">
      <div class="subscription-plans-outer">
        <div class="subscription-plans-card">
          <div class="subscription-plans-header">
            <!-- Reserved for future actions, keep for layout consistency -->
          </div>
          <div class="subscription-plans-header-spacer"></div>
          <div class="subscription-plans-container">
            <div class="subscription-plans-title">Subscription Plans</div>
            <div
              class="subscription-plans-desc"
              style="max-width: 600px"
            >
              Choose the plan that fits your needs. Whether you’re just starting
              or looking for long-term value, we’ve got flexible options to help
              you grow without limits.
            </div>
            <div class="subscription-plans-options">
              <div
                class="plan-card"
                :class="{ 'plan-card--current': userPlan === 250 }"
              >
                <div class="plan-card-header">
                  <span class="plan-card-name">Basic</span>
                </div>
                <div class="plan-card-price">
                  $250 <span class="plan-card-period">/month</span>
                </div>
                <button
                  :class="[
                    'plan-card-btn',
                    { 'plan-card-btn--current': userPlan === 250 },
                  ]"
                  :disabled="isLoading"
                  @click="basicBtnAction()"
                >
                  <span v-if="isLoading && userPlan !== 250"
                    >Redirecting...</span
                  >
                  <span v-else>{{ basicBtnText }}</span>
                </button>
              </div>
              <div
                class="plan-card"
                :class="{ 'plan-card--current': userPlan === 2500 }"
              >
                <span class="plan-card-badge">Save 2 Months</span>
                <div class="plan-card-header">
                  <span class="plan-card-name">Standard</span>
                </div>
                <div class="plan-card-price">
                  $2500 <span class="plan-card-period">/annual</span>
                </div>
                <button
                  :class="[
                    'plan-card-btn',
                    { 'plan-card-btn--current': userPlan === 2500 },
                  ]"
                  :disabled="isLoading"
                  @click="standardBtnAction()"
                >
                  <span v-if="isLoading && userPlan !== 2500"
                    >Redirecting...</span
                  >
                  <span v-else>{{ standardBtnText }}</span>
                </button>
              </div>
            </div>
            <div
              class="subscription-plans-footer"
              style="max-width: 600px"
            >
              Upgrade anytime, cancel anytime. No hidden fees – just simple,
              transparent pricing. industry.
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import axios from 'axios';
import storage from '@/services/storage';
export default {
  name: 'SubscriptionPlans',
  components: { MainLayout },
  data() {
    return {
      planPeriod: 'annually',
      isAnnually: true,
      isLoading: false,
      stripePriceIds: {
        monthly: 'price_1RqAOwPnfSZSgS1X7vLNRdmX',
        annually: 'price_1RqAPlPnfSZSgS1X2zY3qP4K',
      },
      userPlan: null, // will hold user's current plan amount (250 or 2500)
    };
  },
  computed: {
    basicBtnText() {
      if (this.userPlan === 250) return 'Current Plan';
      if (this.userPlan === 2500) return 'Change Plan';
      return 'Get Started';
    },
    basicBtnAction() {
      if (this.userPlan === 250) return this.goToBillingDetails;
      if (this.userPlan === 2500)
        return this.startStripeCheckout.bind(this, 'monthly');
      return this.startStripeCheckout.bind(this, 'monthly');
    },
    standardBtnText() {
      if (this.userPlan === 2500) return 'Current Plan';
      if (this.userPlan === 250) return 'Upgrade Plan';
      return 'Get Started';
    },
    standardBtnAction() {
      if (this.userPlan === 2500) return this.goToBillingDetails;
      if (this.userPlan === 250)
        return this.startStripeCheckout.bind(this, 'annually');
      return this.startStripeCheckout.bind(this, 'annually');
    },
  },
  watch: {
    isAnnually(val) {
      this.planPeriod = val ? 'annually' : 'monthly';
    },
  },
  methods: {
    async fetchUserPlan() {
      try {
        const authToken = storage.get('authToken');
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const res = await axios.get(`${API_BASE_URL}/api/subscription`, {
          headers: { Authorization: `Bearer ${authToken}` },
        });
        // API may return either `amount` or `plan_amount` depending on endpoint/format.
        // Backend returns amount as a string like "250.00" — parse to numeric value
        const rawAmount = res.data?.amount ?? res.data?.plan_amount ?? null;
        if (rawAmount !== null && rawAmount !== undefined) {
          // remove commas and parse float, then round to integer (250.00 -> 250)
          const parsed = parseFloat(String(rawAmount).replace(/,/g, ''));
          this.userPlan = Number.isFinite(parsed) ? Math.round(parsed) : null;
        } else {
          this.userPlan = null;
        }

        // Set billing period (some APIs return 'Monthly' or 'Annual')
        const period = res.data?.period || res.data?.plan_period || null;
        if (period) {
          const p = String(period).toLowerCase();
          this.planPeriod = p.includes('ann') ? 'annually' : 'monthly';
          this.isAnnually = this.planPeriod === 'annually';
        }

        // After fetching plan, also refresh user role in storage (in case role changed)
        const { fetchCurrentUser } = await import('@/services/user');
        const user = await fetchCurrentUser();
        const oldRole = storage.get('role');

        if (user && user.role) {
          console.log('[Subscription] New role from backend:', user.role);
        } else if (user.role !== oldRole) {
          storage.set('role', user.role);

          window.location.reload();
        } else {
          console.warn('[Subscription] Could not fetch user or user.role');
        }
      } catch (e) {
        this.userPlan = null;
        console.error(
          '[Subscription] Error fetching user plan or updating role:',
          e
        );
      }
    },
    async startStripeCheckout(period) {
      this.isLoading = true;
      try {
        const priceId =
          this.stripePriceIds[period] || this.stripePriceIds.annually;
        const authToken = storage.get('authToken');
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const res = await axios.post(
          `${API_BASE_URL}/api/stripe/checkout-session`,
          { price_id: priceId },
          { headers: { Authorization: `Bearer ${authToken}` } }
        );
        if (res.data && res.data.url) {
          window.location.href = res.data.url;
        } else if (this.$toast && typeof this.$toast.add === 'function') {
          this.$toast.add({
            severity: 'error',
            summary: 'Checkout Error',
            detail: 'Could not start Stripe Checkout.',
            life: 4000,
          });
        } else {
          console.warn('Toast not available: Could not start Stripe Checkout.');
        }
      } catch (e) {
        if (this.$toast && typeof this.$toast.add === 'function') {
          this.$toast.add({
            severity: 'error',
            summary: 'Checkout Error',
            detail: 'Stripe Checkout failed.',
            life: 4000,
          });
        } else {
          console.error('Toast not available: Stripe Checkout failed.');
        }
        console.error('[Subscription] Stripe checkout error:', e);
      } finally {
        this.isLoading = false;
      }
    },
    goToBillingDetails() {
      this.$router.push({ name: 'BillingDetails' });
    },
  },
  mounted() {
    this.fetchUserPlan();

    // If redirected from Stripe Checkout, the URL will contain checkout_session_id
    // Poll subscription status until webhook processed and DB shows active subscription.
    const qs = new URLSearchParams(window.location.search);
    const checkoutSessionId = qs.get('checkout_session_id');
    if (checkoutSessionId) {
      // poll every 2s for up to 30s
      const maxAttempts = 15;
      let attempts = 0;
      const poll = async () => {
        attempts++;
        try {
          const { fetchSubscriptionStatus } = await import(
            '@/services/subscription'
          );
          const res = await fetchSubscriptionStatus();
          if (res && res.status === 'active') {
            // update local state and refresh user data to pick up roles
            storage.set('subscriptionStatus', res);
            const { fetchCurrentUser } = await import('@/services/user');
            await fetchCurrentUser();
            // Remove checkout param to avoid re-polling on further reloads
            const url = new URL(window.location.href);
            url.searchParams.delete('checkout_session_id');
            window.history.replaceState({}, document.title, url.toString());
            return; // stop polling
          }
        } catch (e) {
          console.warn('Polling subscription status failed', e);
        }
        if (attempts < maxAttempts) {
          setTimeout(poll, 2000);
        }
      };
      poll();
    }
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');

.subscription-plans-outer {
  width: 100%;

  min-width: 0;

  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
}
.subscription-plans-card {
  width: 100%;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  overflow: visible;
  margin: 0 auto;
  box-sizing: border-box;
  min-width: 0;

  display: flex;
  flex-direction: column;
  gap: 0;
  position: relative;
}
.subscription-plans-header {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 24px 46px 0 24px;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  min-height: 64px;
  box-sizing: border-box;
}
.subscription-plans-header-spacer {
  height: 18px;
  width: 100%;
  background: transparent;
  display: block;
}
.subscription-plans-container {
  width: 100%;
  min-height: 320px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  padding: 0 24px 48px 24px;
  background: #fff;
  border-bottom-left-radius: 24px;
  border-bottom-right-radius: 24px;
}
.subscription-plans-title {
  font-size: 2rem;
  font-weight: 600;
  margin-bottom: 8px;
  text-align: center;
}
.subscription-plans-desc {
  font-size: 1rem;
  color: #444;
  margin-bottom: 24px;
  text-align: center;
}
.subscription-plans-options {
  display: flex;
  gap: 36px; /* increased gap */
  justify-content: center;
  margin-bottom: 18px;
  flex-wrap: wrap;
  margin-top: 32px; /* add some top margin for spacing */
}
.plan-card {
  background: #fff;
  border-radius: 12px;
  border: 2.5px solid #e3eaf3;
  min-width: 260px;
  min-height: 260px;
  width: 260px;
  height: 260px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  box-shadow: none;
  position: relative;
  padding: 0;
  margin: 0;
  overflow: hidden;
  transition: background 0.18s, border 0.18s;
}
.plan-card--current {
  background: #f5faff;
  border: 2.5px solid #0074c2;
  z-index: 2;
}
.plan-card:hover,
.plan-card:focus-within {
  border-color: #0074c2; /* only change color, not thickness */
  background: #f5faff;
  z-index: 2;
}
.plan-card-header {
  width: 100%;
  display: flex;
  align-items: flex-start;
  justify-content: flex-start;
  position: relative;
  margin-bottom: 0;
  padding: 0 0 0 18px;
  min-height: 38px;
}
.plan-card-name {
  font-size: 1.6rem; /* increased size */
  font-weight: 600; /* bolder */
  color: #222;
  margin-top: -18px; /* move label up into purple space */
  margin-bottom: 0;
  z-index: 2;
}
.plan-card-badge {
  position: absolute;
  top: 35px;
  right: -55px;
  background: #0074c2;
  color: #fff;
  font-size: 0.95rem;
  font-weight: 400;
  padding: 4px 44px;
  border-radius: 0;
  transform: rotate(45deg);
  box-shadow: none;
  z-index: 3;
  letter-spacing: 0.5px;
  border-top-right-radius: 4px;
  border-bottom-left-radius: 4px;
  white-space: nowrap;
  border-right: 1.5px solid #0074c2;
  border-left: 1.5px solid #0074c2;
}
.plan-card-price {
  font-size: 2.2rem;
  font-weight: 700;
  color: #111;
  margin: 18px 0 18px 0;
  display: flex;
  align-items: baseline;
  justify-content: center;
  width: 100%;
  letter-spacing: 0.5px;
}
.plan-card-price::before {
  font-size: 1.4rem;
  font-weight: 500;
  margin-right: 2px;
  color: #111;
}
.plan-card-period {
  font-size: 1.1rem;
  color: #222;
  font-weight: 400;
  margin-left: 4px;
}
.plan-card-btn {
  background: #fff;
  color: #0074c2;
  border: 2px solid #0074c2;
  border-radius: 22px;
  padding: 10px 36px;
  font-size: 1.15rem;
  font-weight: 500;
  cursor: pointer;
  margin-top: 18px;
  margin-bottom: 0;

  box-shadow: none;
  outline: none;
  display: block;
}
.plan-card:hover .plan-card-btn,
.plan-card:focus-within .plan-card-btn {
  background: #0074c2;
  color: #fff;
  border: 2px solid #0074c2;
}
.plan-card-btn--current {
  background: #e3eaf3;
  color: #0074c2;
  border: 2px solid #e3eaf3;
  cursor: default;
  font-weight: 500;
  font-size: 1.15rem;
  box-shadow: none;
}
.plan-card-btn--current:hover,
.plan-card-btn--current:focus {
  background: #e3eaf3 !important;
  color: #0074c2 !important;
  border: 2px solid #e3eaf3 !important;

  box-shadow: none !important;
}
.subscription-plans-footer {
  font-size: 0.95rem;
  color: #888;
  text-align: center;
  margin-top: 8px;
}

.input-group input[placeholder='0000 0000 0000 0000'] {
  letter-spacing: 2px;
}
.input-group:last-child .input-icon {
  margin-left: 8px;
  margin-right: 0;
}
.plan-info-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 8px;
  margin-top: 0;
}
.plan-info-toggle {
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 400;
}

.switch {
  position: relative;
  display: inline-block;
  width: 38px;
  height: 22px;
  margin: 0 8px;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.switch-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #e0e0e0;
  border-radius: 22px;
  transition: background 0.2s;
}
.switch-slider:before {
  position: absolute;
  content: '';
  height: 16px;
  width: 16px;
  left: 3px;
  bottom: 3px;
  background-color: #fff;
  border-radius: 50%;
  transition: transform 0.2s;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}
input:checked + .switch-slider {
  background-color: #888;
}
input:checked + .switch-slider:before {
  transform: translateX(16px);
}
.plan-info-benefits {
  font-size: 0.98rem;
  margin-bottom: 10px;
  margin-top: 0;
}
.plan-info-benefits-title {
  font-size: 1.05rem;
  font-weight: 500;
  margin-bottom: 8px;
}
.plan-info-benefits ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.plan-info-benefits li {
  display: flex;
  align-items: center;
  margin-bottom: 7px;
  color: #222;
  font-size: 0.98rem;
}
.checkmark {
  color: #2e7d32;
  font-weight: bold;
  margin-right: 8px;
  font-size: 1.1rem;
}
.plan-info-divider {
  border: none;
  border-top: 1px solid #e0e0e0;
  margin: 14px 0 14px 0;
}
.promo-input {
  width: 100%;
  background: #f6f6f6;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 8px;
  font-size: 0.98rem;
  margin-bottom: 14px;
  outline: none;
  color: #888;
}
.plan-info-summary {
  margin-bottom: 0;
}
.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.98rem;
  margin-bottom: 7px;
  color: #222;
}
.summary-row-total {
  font-weight: 700;
  font-size: 1.08rem;
  margin-top: 12px;
  margin-bottom: 12px;
}
.billed-now {
  color: #005fa3;
  font-weight: 700;
}
.confirm-btn {
  width: 100%;
  background: #0074c2;
  color: #fff;
  border: none;
  border-radius: 999px;
  padding: 10px 0;
  font-size: 1.05rem;
  font-weight: 600;
  cursor: pointer;
  margin: 0 0 12px 0;

  box-shadow: none;
}
.confirm-btn:hover {
  background: #005fa3;
}
.plan-info-note {
  font-size: 0.97rem;
  color: #222;
  text-align: left;
  margin-top: 0;
  margin-bottom: 0;
  line-height: 1.5;
}
.plan-info-note a {
  color: #0074c2;
  text-decoration: underline;
  cursor: pointer;
}
</style>
