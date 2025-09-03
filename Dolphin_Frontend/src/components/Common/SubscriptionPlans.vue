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
            <div class="subscription-plans-desc">
              Lorem Ipsum is simply dummy text of the printing and typesetting
              industry. Lorem Ipsum has been the industry's standard dummy text
              ever since the 1500s.
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
            <div class="subscription-plans-footer">
              Lorem Ipsum is simply dummy text of the printing and typesetting
              industry.
            </div>
          </div>
        </div>
      </div>
      <!-- Purchase Modal -->
      <div
        v-if="showPurchaseModal"
        class="purchase-modal-overlay"
      >
        <div class="purchase-modal">
          <button
            class="purchase-modal-close"
            @click="showPurchaseModal = false"
          >
            &times;
          </button>
          <div class="purchase-modal-content">
            <div class="purchase-modal-left">
              <div class="purchase-modal-title">Get Essential</div>
              <div class="purchase-modal-section-label">
                Billing Information
              </div>
              <div class="purchase-modal-form">
                <div class="input-group">
                  <span class="input-icon material-icons">mail</span>
                  <input
                    type="email"
                    placeholder="abcd@gmail.com"
                  />
                </div>
                <div class="input-row">
                  <div class="input-group">
                    <span class="input-icon material-icons">person</span>
                    <input
                      type="text"
                      placeholder="First Name"
                    />
                  </div>
                  <div class="input-group">
                    <span class="input-icon material-icons">person</span>
                    <input
                      type="text"
                      placeholder="Last Name"
                    />
                  </div>
                </div>
                <div class="input-row">
                  <div class="input-group">
                    <select>
                      <option>Country</option>
                    </select>
                  </div>
                  <div class="input-group">
                    <select>
                      <option>State</option>
                    </select>
                  </div>
                </div>
                <div class="input-row">
                  <div class="input-group">
                    <input
                      type="text"
                      placeholder="City"
                    />
                  </div>
                  <div class="input-group">
                    <input
                      type="text"
                      placeholder="Zip code"
                    />
                  </div>
                </div>
                <div class="purchase-modal-section-label">Payment Method</div>
                <div class="payment-method-row">
                  <label class="payment-method-btn">
                    <input
                      type="radio"
                      name="payment"
                      checked
                    />
                    <span class="icon material-icons">credit_card</span>
                    Card
                  </label>
                  <label class="payment-method-btn">
                    <input
                      type="radio"
                      name="payment"
                    />
                    <span class="icon material-icons"
                      >account_balance_wallet</span
                    >
                    Paypal
                  </label>
                </div>
                <div class="input-group">
                  <input
                    type="text"
                    placeholder="Name on Card"
                  />
                </div>
                <div class="input-group">
                  <input
                    type="text"
                    placeholder="0000 0000 0000 0000"
                    maxlength="19"
                  />
                </div>
                <div class="input-row">
                  <div class="input-group">
                    <input
                      type="text"
                      placeholder="MM/YY"
                      maxlength="5"
                    />
                  </div>
                  <div class="input-group">
                    <input
                      type="text"
                      placeholder="CVV"
                      maxlength="4"
                    />
                    <span class="input-icon material-icons">help_outline</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="purchase-modal-right">
              <div class="plan-info-header">
                <span>Plan Info</span>
                <span class="plan-info-toggle">
                  Monthly
                  <label class="switch">
                    <input
                      type="checkbox"
                      v-model="isAnnually"
                    />
                    <span class="switch-slider"></span>
                  </label>
                  Annually
                </span>
              </div>
              <div class="plan-info-benefits">
                <div class="plan-info-benefits-title">
                  With Essential you get
                </div>
                <ul>
                  <li><span class="checkmark">&#10003;</span> Benefit no. 1</li>
                  <li><span class="checkmark">&#10003;</span> Benefit no. 2</li>
                  <li><span class="checkmark">&#10003;</span> Benefit no. 3</li>
                </ul>
              </div>
              <hr class="plan-info-divider" />
              <input
                class="promo-input"
                type="text"
                placeholder="Apply Promo Code"
              />
              <div class="plan-info-summary">
                <div class="summary-row">
                  <span>Subtotal</span>
                  <span>$2500.00/year</span>
                </div>
                <div class="summary-row">
                  <span>Yearly plan discount</span>
                  <span>-$20.00</span>
                </div>
                <div class="summary-row">
                  <span>Promo code discount</span>
                  <span>-$26.00</span>
                </div>
              </div>
              <hr class="plan-info-divider" />
              <div class="summary-row summary-row-total">
                <span>Billed Now</span>
                <span class="billed-now">-$2454.00</span>
              </div>
              <button class="btn btn-primary">Confirm and Pay</button>
              <div class="plan-info-note">
                Your subscription will renew automatically every year as Lorem
                Ipsum is simply dummy text of the printing and typesetting
                industry. Lorem Ipsum has been the industry's standard
                <a href="#">Terms and Conditions</a>.
              </div>
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
      showPurchaseModal: false,
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
        const API_BASE_URL = 'http://127.0.0.1:8000';
        const res = await axios.get(`${API_BASE_URL}/api/user/subscription`, {
          headers: { Authorization: `Bearer ${authToken}` },
        });
        // Assume API returns { plan_amount: 250 } or { plan_amount: 2500 }
        this.userPlan = res.data.plan_amount || null;

        // After fetching plan, also refresh user role in storage (in case role changed)
        const { fetchCurrentUser } = await import('@/services/user');
        const user = await fetchCurrentUser();
        const oldRole = storage.get('role');
        console.log('[Subscription] Old role:', oldRole);
        if (user && user.role) {
          console.log('[Subscription] New role from backend:', user.role);
          if (user.role !== oldRole) {
            storage.set('role', user.role);
            console.log(
              '[Subscription] Role updated in storage. Reloading page...'
            );
            window.location.reload();
          } else {
            console.log('[Subscription] Role unchanged, no reload needed.');
          }
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
        const API_BASE_URL = 'http://127.0.0.1:8000';
        const res = await axios.post(
          `${API_BASE_URL}/api/stripe/checkout-session`,
          { price_id: priceId },
          { headers: { Authorization: `Bearer ${authToken}` } }
        );
        if (res.data && res.data.url) {
          window.location.href = res.data.url;
        } else {
          if (this.$toast && typeof this.$toast.add === 'function') {
            this.$toast.add({
              severity: 'error',
              summary: 'Checkout Error',
              detail: 'Could not start Stripe Checkout.',
              life: 4000,
            });
          } else {
            console.warn(
              'Toast not available: Could not start Stripe Checkout.'
            );
          }
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
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');

.subscription-plans-outer {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
  margin: 64px auto 64px auto;
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
  max-width: 1400px;
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

/* Purchase Modal Styles */
.purchase-modal-overlay {
  position: fixed;
  z-index: 2000;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.13);
  display: flex;
  align-items: center;
  justify-content: center;
}
.purchase-modal {
  background: #fff;
  border-radius: 16px;
  max-width: 900px;
  width: 98vw;
  min-width: 320px;
  box-shadow: 0 4px 32px 0 rgba(33, 150, 243, 0.1);
  padding: 0;
  position: relative;
  display: flex;
  flex-direction: column;
}
.purchase-modal-close {
  position: absolute;
  top: 18px;
  right: 18px;
  background: none;
  border: none;
  font-size: 2rem;
  color: #888;
  cursor: pointer;
  z-index: 1;
}
.purchase-modal-content {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: stretch;
  padding: 64px 24px 24px 24px;
  width: 100%;
  box-sizing: border-box;
  gap: 0;
}
.purchase-modal-left,
.purchase-modal-right {
  flex: 1 1 0;
  min-width: 0;
  max-width: none;
  background: #fff;
  border-radius: 18px;
  box-sizing: border-box;
  margin: 0 18px;
  display: flex;
  flex-direction: column;
  padding: 18px 24px;
  /* Make both sides visually equal */
}
.purchase-modal-left {
  align-items: flex-start;
  gap: 12px;
  background: #fff;
}
.purchase-modal-right {
  background: #fafbfb;
  gap: 0;
  justify-content: flex-start;
}
@media (max-width: 900px) {
  .purchase-modal-content {
    flex-direction: column;
    padding: 0;
    align-items: stretch;
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
  }
  .purchase-modal-left,
  .purchase-modal-right {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    margin: 0;
    border-radius: 12px;
    padding: 16px 12px 0 12px;
    box-sizing: border-box;
    display: block;
  }
  .purchase-modal-right {
    padding: 8px 12px 12px 12px;
  }
}
@media (max-width: 600px) {
  .purchase-modal {
    width: 95vw;
    max-width: 420px;
    min-width: 0;
    margin: 0 auto;
    border-radius: 12px;
    box-sizing: border-box;
    max-height: 98vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    padding: 0;
  }
  .purchase-modal-content {
    flex-direction: column;
    padding: 0 0 8px 0;
    align-items: stretch;
    gap: 0;
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
  }
  .purchase-modal-left,
  .purchase-modal-right {
    max-width: 100%;
    min-width: 0;
    margin: 0;
    border-radius: 10px;
    padding: 16px 12px 0 12px;
    width: 100%;
    box-sizing: border-box;
  }
  .purchase-modal-right {
    padding: 8px 12px 12px 12px;
  }
  .purchase-modal-form .input-group,
  .purchase-modal-form .input-row {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }
  .purchase-modal-form input,
  .purchase-modal-form select {
    width: 100%;
    font-size: 1rem;
  }
  .payment-method-row {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }
  .plan-info-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
  .plan-info-summary,
  .plan-info-benefits,
  .plan-info-note {
    width: 100%;
  }
  .promo-input,
  .btn.btn-primary,
  .confirm-btn {
    width: 100%;
    box-sizing: border-box;
  }
}
.purchase-modal-title,
.purchase-modal-section-label {
  text-align: left;
  width: 100%;
}
.purchase-modal-title {
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 8px;
}
.purchase-modal-section-label {
  font-size: 1rem;
  font-weight: 500;
  margin: 12px 0 6px 0;
}
.purchase-modal-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.input-group {
  display: flex;
  align-items: center;
  background: #f6f6f6;
  border-radius: 8px;
  padding: 0 10px;
  margin-bottom: 0;
  flex: 1;
}
.input-group input,
.input-group select {
  border: none;
  background: transparent;
  padding: 10px 0;
  width: 100%;
  font-size: 1rem;
  outline: none;
}
.input-group select {
  appearance: none;
  background: transparent;
}
.input-icon {
  color: #bdbdbd;
  margin-right: 8px;
  font-size: 1.2rem;
}
.input-row {
  display: flex;
  gap: 10px;
}
.payment-method-row {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 6px;
}
.payment-method-btn {
  display: flex;
  align-items: center;
  background: #f6f6f6;
  border-radius: 8px;
  padding: 8px 18px;
  font-size: 1rem;
  font-weight: 500;
  border: 1.5px solid transparent;
  cursor: pointer;
  gap: 8px;
}
.payment-method-btn input[type='radio'] {
  accent-color: #0074c2;
  margin-right: 4px;
}
.payment-method-btn .icon {
  font-size: 1.2rem;
  color: #888;
}
.payment-method-btn input[type='radio']:checked + .icon,
.payment-method-btn input[type='radio']:checked ~ .icon {
  color: #0074c2;
}
.payment-method-btn input[type='radio']:checked ~ span,
.payment-method-btn input[type='radio']:checked ~ .icon {
  font-weight: 600;
}
.payment-method-btn input[type='radio']:checked ~ * {
  color: #0074c2;
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

/* Responsive styles */
@media (max-width: 1400px) {
  .subscription-plans-outer {
    margin: 12px;
    max-width: 100%;
  }
  .subscription-plans-card {
    border-radius: 14px;
    max-width: 100%;
  }
  .subscription-plans-header {
    padding: 8px 8px 0 8px;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
  }
  .subscription-plans-container {
    padding: 0 8px 24px 8px;
    border-bottom-left-radius: 14px;
    border-bottom-right-radius: 14px;
  }
}
@media (max-width: 900px) {
  .subscription-plans-outer {
    margin: 4px;
    max-width: 100%;
  }
  .subscription-plans-card {
    border-radius: 10px;
  }
  .subscription-plans-header {
    padding: 8px 4px 0 4px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
  }
  .subscription-plans-container {
    padding: 0 4px 12px 4px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
  }
}
@media (max-width: 600px) {
  .subscription-plans-container {
    padding: 0 2vw 8px 2vw;
    min-height: 180px;
  }
  .subscription-plans-title {
    font-size: 1.1rem;
  }
  .subscription-plans-options {
    flex-direction: column;
    gap: 16px;
  }
  .plan-card {
    min-width: 0;
    width: 100%;
    height: auto;
    padding: 12px 4px;
  }
  .plan-card-header {
    padding-left: 8px;
  }
  .plan-card-badge {
    font-size: 0.85rem;
    padding: 2px 18px;
    top: 8px;
    right: -24px;
  }
  .plan-card-price {
    font-size: 1.3rem;
    margin: 10px 0 10px 0;
  }
  .plan-card-btn,
  .plan-card-btn--current {
    font-size: 1rem;
    padding: 8px 0;
  }
}
</style>
