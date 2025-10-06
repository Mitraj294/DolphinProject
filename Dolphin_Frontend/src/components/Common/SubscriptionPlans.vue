<template>
  <div>
    <MainLayout v-if="!isGuestView">
      <div class="page">
        <div class="subscription-plans-outer">
          <div class="subscription-plans-card">
            <div class="subscription-plans-header"></div>
            <div class="subscription-plans-header-spacer"></div>
            <div class="subscription-plans-container">
              <div class="subscription-plans-title">Subscription Plans</div>
              <div
                class="subscription-plans-desc"
                style="max-width: 600px"
              >
                Choose the plan that fits your needs. Whether you’re just
                starting or looking for long-term value, we’ve got flexible
                options to help you grow without limits.
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
                transparent pricing.
              </div>
            </div>
          </div>
        </div>
      </div>
    </MainLayout>

    <div
      v-else
      class="page guest-view"
    >
      <div class="subscription-plans-outer">
        <div class="subscription-plans-card">
          <div class="subscription-plans-header"></div>
          <div class="subscription-plans-header-spacer"></div>
          <div class="subscription-plans-container">
            <div class="subscription-plans-title">Subscription Plans</div>
            <div
              class="subscription-plans-desc"
              style="max-width: 600px"
            >
              Choose a plan to continue. This page was opened from an invitation
              and may be pre-filled.
            </div>
            <div class="subscription-plans-options">
              <div class="plan-card">
                <div class="plan-card-header">
                  <span class="plan-card-name">Basic</span>
                </div>
                <div class="plan-card-price">
                  $250 <span class="plan-card-period">/month</span>
                </div>
                <button
                  class="plan-card-btn"
                  :disabled="isLoading"
                  @click="startStripeCheckout('monthly')"
                >
                  <span v-if="isLoading">Redirecting...</span>
                  <span v-else>Get Started</span>
                </button>
              </div>

              <div class="plan-card">
                <span class="plan-card-badge">Save 2 Months</span>
                <div class="plan-card-header">
                  <span class="plan-card-name">Standard</span>
                </div>
                <div class="plan-card-price">
                  $2500 <span class="plan-card-period">/annual</span>
                </div>
                <button
                  class="plan-card-btn"
                  :disabled="isLoading"
                  @click="startStripeCheckout('annually')"
                >
                  <span v-if="isLoading">Redirecting...</span>
                  <span v-else>Get Started</span>
                </button>
              </div>
            </div>
            <div
              class="subscription-plans-footer"
              style="max-width: 600px"
            >
              After payment you'll be redirected to the login page.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// Component Imports

import MainLayout from '@/components/layout/MainLayout.vue';
import axios from 'axios';
import storage from '@/services/storage';

export default {
  name: 'SubscriptionPlans',
  components: { MainLayout },

  // Data

  data() {
    return {
      planPeriod: 'annually',
      isAnnually: true,
      isLoading: false,
      stripePriceIds: {
        monthly: 'price_1SERsJPnfSZSgS1XktSvPTQr',
        annually: 'price_1SERriPnfSZSgS1XYnWP4uM2',
      },
      userPlan: null,
      isGuestView: false,
      guestParams: {
        email: null,
        lead_id: null,
        price_id: null,
        guest_code: null,
        guest_token: null,
      },
    };
  },

  // Computed Properties

  computed: {
    /**
     * Determines the text for the Basic plan button based on the user's current plan.
     */
    basicBtnText() {
      if (this.userPlan === 250) return 'Current Plan';
      if (this.userPlan === 2500) return 'Change Plan';
      return 'Get Started';
    },

    /**
     * Determines the action for the Basic plan button.
     */
    basicBtnAction() {
      if (this.userPlan === 250) return this.goToBillingDetails;
      return () => this.startStripeCheckout('monthly');
    },

    /**
     * Determines the text for the Standard plan button.
     */
    standardBtnText() {
      if (this.userPlan === 2500) return 'Current Plan';
      if (this.userPlan === 250) return 'Upgrade Plan';
      return 'Get Started';
    },

    /**
     * Determines the action for the Standard plan button.
     */
    standardBtnAction() {
      if (this.userPlan === 2500) return this.goToBillingDetails;
      return () => this.startStripeCheckout('annually');
    },
  },

  // Watchers

  watch: {
    isAnnually(val) {
      this.planPeriod = val ? 'annually' : 'monthly';
    },
  },

  // Methods

  methods: {
    /**
     * Fetches the current user's subscription plan from the backend.
     */
    async fetchUserPlan() {
      try {
        const authToken = storage.get('authToken');
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const res = await axios.get(`${API_BASE_URL}/api/subscription`, {
          headers: { Authorization: `Bearer ${authToken}` },
        });

        const rawAmount = res.data?.amount ?? res.data?.plan_amount ?? null;
        if (rawAmount !== null && rawAmount !== undefined) {
          const parsed = parseFloat(String(rawAmount).replace(/,/g, ''));
          this.userPlan = Number.isFinite(parsed) ? Math.round(parsed) : null;
        } else {
          this.userPlan = null;
        }
      } catch (e) {
        this.userPlan = null;
        console.error('[Subscription] Error fetching user plan:', e);
      }
    },

    /**
     * Initiates a Stripe checkout session.
     * @param {string} period - The subscription period ('monthly' or 'annually').
     */
    async startStripeCheckout(period) {
      this.isLoading = true;
      try {
        const priceId =
          this.stripePriceIds[period] || this.stripePriceIds.annually;
        console.log('Starting checkout with period:', period, 'priceId:', priceId);
        const authToken = storage.get('authToken');
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const payload = this.buildCheckoutPayload(priceId);
        console.log('Checkout payload:', payload);

        const res = await axios.post(
          `${API_BASE_URL}/api/stripe/checkout-session`,
          payload,
          { headers: { Authorization: `Bearer ${authToken}` } }
        );

        if (res.data && res.data.url) {
          window.location.href = res.data.url;
        } else {
          // Handle error
        }
      } catch (e) {
        console.error('[Subscription] Stripe checkout error:', e);
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Builds the payload for the Stripe checkout session request.
     * @param {string} priceId - The ID of the Stripe price.
     * @returns {object} The payload for the request.
     */
    buildCheckoutPayload(priceId) {
      const payload = { price_id: priceId };
      if (this.isGuestView) {
        // Add guest params but exclude null/undefined values to avoid overriding valid priceId
        const filteredGuestParams = {};
        Object.keys(this.guestParams).forEach(key => {
          if (this.guestParams[key] !== null && this.guestParams[key] !== undefined) {
            // Don't override the priceId we already set
            if (key !== 'price_id') {
              filteredGuestParams[key] = this.guestParams[key];
            }
          }
        });
        Object.assign(payload, filteredGuestParams);
      }
      return payload;
    },

    /**
     * Validates guest token via backend, sets temporary session.
     */
    async validateGuestToken(opts) {
      try {
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const res = await axios.get(`${API_BASE_URL}/api/leads/guest-validate`, { params: opts });
        if (res?.data?.valid) {
          if (res.data.token) {
            storage.set('authToken', res.data.token);
            axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.token}`;
          }
          storage.set('guest_user', res.data.user || null);
          return true;
        }
      } catch (e) {
        console.error('Guest validation failed', e);
      }
      return false;
    },

    /**
     * Navigates to the billing details page.
     */
    goToBillingDetails() {
      this.$router.push({ name: 'BillingDetails' });
    },
  },

  // Lifecycle Hooks

  mounted() {
    console.log('SubscriptionPlans mounted, URL:', window.location.href);
    const qs = new URLSearchParams(window.location.search);
    console.log('URL search params:', qs.toString());
    
    const hasGuestParams = [
      'email',
      'lead_id',
      'price_id',
      'guest_code',
      'guest_token',
    ].some((p) => qs.has(p));

    console.log('Guest params check:', {
      hasGuestParams,
      email: qs.has('email'),
      lead_id: qs.has('lead_id'),
      price_id: qs.has('price_id'),
      guest_code: qs.has('guest_code'),
      guest_token: qs.has('guest_token')
    });

    if (hasGuestParams) {
      console.log('Setting guest view mode');
      this.isGuestView = true;
      this.guestParams = {
        email: qs.get('email'),
        lead_id: qs.get('lead_id'),
        price_id: qs.get('price_id'),
        guest_code: qs.get('guest_code'),
        guest_token: qs.get('guest_token'),
      };
      console.log('Guest params:', this.guestParams);

      // Validate guest token/code to get authentication
      if (this.guestParams.guest_code) {
        console.log('Validating guest code:', this.guestParams.guest_code);
        this.validateGuestToken({ guest_code: this.guestParams.guest_code }).then(success => {
          console.log('Guest code validation result:', success);
          if (success) {
            console.log('Guest authentication successful, auth token set');
          } else {
            console.log('Guest authentication failed');
          }
        });
      } else if (this.guestParams.guest_token) {
        console.log('Validating guest token:', this.guestParams.guest_token);
        this.validateGuestToken({ token: this.guestParams.guest_token }).then(success => {
          console.log('Guest token validation result:', success);
          if (success) {
            console.log('Guest authentication successful, auth token set');
          } else {
            console.log('Guest authentication failed');
          }
        });
      }
    } else {
      console.log('No guest params detected, fetching user plan');
      this.fetchUserPlan();
    }
  },
};
</script>

<style scoped>
/* All original styles are preserved here */
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
  gap: 36px;
  justify-content: center;
  margin-bottom: 18px;
  flex-wrap: wrap;
  margin-top: 32px;
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
  border-color: #0074c2;
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
  font-size: 1.6rem;
  font-weight: 600;
  color: #222;
  margin-top: -18px;
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
