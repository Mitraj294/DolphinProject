<template>
  <div class="thankyou-layout">
    <div class="main-content">
      <img
        src="@/assets/images/Lines.svg"
        alt=""
        class="bg-lines"
      />
      <img
        src="@/assets/images/Image.svg"
        alt=""
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

          <h2 class="thankyou-title">
            Congratulations, {{ userName || 'Valued Customer' }}!
          </h2>
          <p class="thankyou-desc">
            Your subscription is active. A confirmation receipt has been sent to
            your email.
          </p>

          <div class="key-details">
            <div class="key-item">
              <div class="key-label">Payment Date</div>
              <div class="key-value">{{ formattedPaymentDate }}</div>
            </div>
            <div class="key-item">
              <div class="key-label">Subscription Ends</div>
              <div class="key-value">{{ formattedEnd }}</div>
            </div>
            <div class="key-item">
              <div class="key-label">Next Renewal</div>
              <div class="key-value">{{ formattedNextRenewal }}</div>
            </div>
          </div>

          <div
            v-if="shouldShowPlanSummary"
            class="plan-summary"
          >
            <div class="plan-summary-left">
              <div class="plan-name">{{ plan_name }}</div>
              <div class="plan-price">
                <span class="price">${{ formattedAmount }}</span>
                <span class="period">/{{ planPeriodDisplay }}</span>
              </div>
              <div class="plan-meta">
                <span
                  class="status"
                  :class="subscriptionStatusClass"
                >
                  {{ subscriptionStatusLabel }}
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

          <div class="whats-next">
            <h3 class="section-title">What's Next?</h3>
            <div class="onboarding-steps">
              <a @click.prevent="() => router.push('/profile')"
                >Set up your profile</a
              >
              <a @click.prevent="() => router.push('/my-organization')"
                >Set up your organization - Groups & Members</a
              >
            </div>
          </div>

          <div class="success-actions">
            <button
              class="btn btn-primary"
              @click="goToDashboard"
            >
              Go to Your Dashboard
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

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import storage from '@/services/storage';

const route = useRoute();
const router = useRouter();

// --- State ---
const userName = ref(storage.get('userName') || '');
const checkoutSessionId = ref(null);
const email = ref(null);
const planAmount = ref(null);
const planName = ref(null);
const planPeriod = ref(null);

// Prefer the lightweight status endpoint when available as it often contains
// the human-friendly `plan_name` field.
const statusInfo = ref(null);
const plan_name = computed(() => {
  return (
    statusInfo.value?.plan_name ||
    subscription.value?.plan_name ||
    planName.value ||
    subscription.value?.plan ||
    ''
  );
});
const subscription_end = ref(null);
const nextBilling = ref(null);
const nextPayment = ref(null);
const subscriptionStatus = ref(null);
const loadingSession = ref(false);
const subscription = ref(null);
const isLoading = ref(false);

// --- Helpers ---
const formatDate = (dateStr) => {
  if (!dateStr) return null;
  const d = new Date(dateStr);
  return isNaN(d.getTime())
    ? dateStr
    : d.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
};

const API_BASE_URL = computed(() => {
  return (
    (typeof window !== 'undefined' &&
      (window.__env?.VUE_APP_API_BASE_URL || window.VUE_APP_API_BASE_URL)) ||
    ''
  );
});

const shouldShowPlanSummary = computed(
  () =>
    (plan_name.value ||
      planAmount.value ||
      subscriptionStatus.value ||
      subscription.value) &&
    !loadingSession.value &&
    !isLoading.value
);

const formattedAmount = computed(() => {
  const amount = subscription.value?.plan_amount || planAmount.value;
  if (!amount) return '';
  const n = parseFloat(String(amount).replace(/,/g, ''));
  if (!isFinite(n)) return amount;
  const amountStr = n % 1 === 0 ? n.toFixed(0) : n.toFixed(2);
  return `${amountStr}`;
});

const formattedEnd = computed(() =>
  formatDate(subscription.value?.ends_at || subscription_end.value)
);

const formattedPaymentDate = computed(() => {
  const raw =
    subscription.value?.created_at ||
    nextPayment.value ||
    nextBilling.value ||
    new Date().toISOString();
  return formatDate(raw);
});

const formattedNextRenewal = computed(() => {
  const raw =
    subscription.value?.next_billing ||
    nextBilling.value ||
    nextPayment.value ||
    subscription.value?.ends_at ||
    subscription_end.value ||
    null;
  return formatDate(raw) || '—';
});

const planPeriodDisplay = computed(() => {
  const period = subscription.value?.plan_period || planPeriod.value || '';
  const p = String(period).toLowerCase();
  if (p.includes('month')) return 'Month';
  if (p.includes('ann') || p.includes('year')) return 'Annual';

  const amount = subscription.value?.plan_amount || planAmount.value;
  if (amount) {
    const n = parseFloat(String(amount).replace(/[,\s]/g, ''));
    if (isFinite(n)) return n >= 1000 ? 'Annual' : 'Month';
  }
  return 'Month';
});

const subscriptionStatusLabel = computed(() => {
  const status = subscription.value?.status || subscriptionStatus.value;
  if (!status) return 'Active';
  const s = String(status);
  return s.charAt(0).toUpperCase() + s.slice(1);
});

const subscriptionStatusClass = computed(() => {
  const status =
    subscription.value?.status || subscriptionStatus.value || 'active';
  const s = String(status).toLowerCase();
  if (s === 'active' || s === 'success') return 'status-active';
  if (s === 'expired' || s === 'canceled') return 'status-expired';
  return 'status-unknown';
});

// --- Methods ---
const goToDashboard = () => router.push('/dashboard');
const goToBilling = () => router.push('/organizations/billing-details');

const fetchSubscriptionDetails = async () => {
  // 1. Try to get from the current user's active subscription
  try {
    const resp = await axios.get(`${API_BASE_URL.value}/api/subscription`);
    const d = resp.data || null;
    if (d) {
      // normalize backend response into subscription shape used by template
      // Note: intentionally do NOT treat `plan` as the human-friendly
      // `plan_name`. Some endpoints return an internal `plan` id only.
      subscription.value = {
        plan_amount: d.plan_amount || d.amount || d.planAmount || null,
        plan_name: d.plan_name || null, // only set when backend provided it
        plan: d.plan || null,
        plan_period: d.period || d.plan_period || null,
        ends_at: d.subscription_end || d.end || d.ends_at || null,
        next_billing: d.next_billing || d.nextBill || d.next_bill_date || null,
        created_at: d.created_at || d.paymentDate || d.payment_date || null,
        status: d.status || null,
        pdfUrl: d.pdfUrl || d.receipt_url || null,
      };
      // If the backend explicitly provided a human-friendly `plan_name`, we can stop.
      if (d.plan_name) return;
      // otherwise fall through to try the status endpoint which often has `plan_name`
    }
  } catch (err) {
    console.debug(
      'Could not fetch active subscription, will try checkout session next.',
      err?.message || err
    );
  }

  // try the simpler status endpoint which some flows use
  try {
    const resp2 = await axios.get(
      `${API_BASE_URL.value}/api/subscription/status`
    );
    const s = resp2.data || null;
    if (s) {
      subscription.value = {
        plan_amount: s.plan_amount || s.amount || null,
        plan_name: s.plan_name || null,
        plan_period: s.period || null,
        ends_at: s.subscription_end || null,
        next_billing: s.next_billing || s.nextBill || null,
        created_at: s.paymentDate || s.created_at || null,
        status: s.status || s.state || null,
      };
      return;
    }
  } catch {
    // ignore and continue to checkout session fallback
  }

  // 2. Fallback to fetching from checkout session ID
  if (checkoutSessionId.value) {
    loadingSession.value = true;
    try {
      const url = `${API_BASE_URL.value}/api/stripe/session`;
      const resp = await axios.get(url, {
        params: { session_id: checkoutSessionId.value },
      });
      const d = resp.data || {};
      subscription.value = {
        plan_amount: d.amount_total ? (d.amount_total / 100).toFixed(2) : null,
        plan_name: d.line_items?.[0]?.description || null,
        ends_at: d.subscription_end || d.subscriptionEnd || null,
        next_billing:
          d.next_billing || d.nextBill || d.next_billing_date || null,
        created_at: d.created ? new Date(d.created * 1000).toISOString() : null,
        status: d.status || 'active',
      };
    } catch (err) {
      console.debug('Could not fetch session details', err?.message || err);
    } finally {
      loadingSession.value = false;
    }
  }
};

// --- Lifecycle ---
onMounted(async () => {
  const q = route.query || {};
  checkoutSessionId.value = q.checkout_session_id || null;
  email.value = q.email || null;

  // Try to fetch the lightweight status endpoint first since it usually
  // contains the human-friendly `plan_name` used on the success page.
  try {
    const resp = await axios.get(
      `${API_BASE_URL.value}/api/subscription/status`
    );
    statusInfo.value = resp.data || null;
  } catch (e) {
    console.debug(
      'Could not fetch subscription status endpoint, will fallback',
      e?.message || e
    );
  }

  await fetchSubscriptionDetails();
});
</script>

<style scoped>
/* Layout */
.thankyou-layout {
  display: flex;
  min-height: 100vh;
}
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #f6faff;
  position: relative;
  overflow: hidden;
  /* keep a small horizontal padding so the centered card doesn't appear clipped
     when devtools or scrollbars reduce available viewport width */
  padding: 0 20px;
}

/* Card */
.thankyou-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  padding: 40px;
  width: 100%;
  max-width: 650px;
  /* ensure the card is centered inside its parent wrapper */
  margin: 0 auto;
  text-align: center;
  z-index: 2;
}

/* Wrapper that sits behind the card; make it a flex container so the
   .thankyou-card can be perfectly centered regardless of surrounding
   decorations (bg-lines/bg-illustration) or page gutters. */
.thankyou-bg {
  display: flex;
  justify-content: center;
  width: 100%;
  box-sizing: border-box;
  /* keep the pale background area constrained and centered so the inner
     .thankyou-card has equal left/right gutters even when the viewport
     changes (devtools open, scrollbars present, etc.) */
  max-width: 820px;
  margin: 0 auto;
  padding: 24px;
}

/* Typography */
.check-circle {
  margin-bottom: 24px;
}
.thankyou-title {
  font-size: 2rem;
  font-weight: 600;
  color: #111;
  margin: 0 0 12px 0;
}
.thankyou-desc {
  color: #555;
  font-size: 1.1rem;
  margin-bottom: 32px;
}

/* Key Billing */
.key-details {
  display: flex;
  gap: 18px;
  justify-content: space-around;
  margin: 16px 0;
  flex-direction: row;
  flex-wrap: nowrap;
}
.key-item {
  text-align: center;
}
.key-label {
  color: #888;
  font-size: 0.85rem;
}
.key-value {
  font-weight: 600;
  color: #111;
}

/* Plan Summary */
.plan-summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin: 24px 0;
  padding: 16px;
  border-radius: 12px;
  background: #f7faff;
  border: 1px solid #e0e8f3;
}
.plan-name {
  font-weight: 700;
  font-size: 1.1rem;
  color: #0b4666;
}
.plan-price {
  margin-top: 4px;
  font-size: 1.2rem;
  color: #111;
}
.plan-meta {
  margin-top: 8px;
  font-size: 0.9rem;
  color: #555;
  display: flex;
  gap: 12px;
  align-items: center;
  justify-content: center;
}
.status-active {
  color: #2e7d32;
  font-weight: 600;
}
.status-expired {
  color: #d32f2f;
  font-weight: 600;
}

/* Sections */
.features-unlocked,
.whats-next {
  margin-top: 32px;
  text-align: left;
  border-top: 1px solid #f0f0f0;
  padding-top: 24px;
}
.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  margin: 0 0 16px 0;
}
.features-unlocked ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.features-unlocked li {
  color: #444;
  display: flex;
  align-items: center;
}
.features-unlocked .fa-check-circle {
  color: #2ecc40;
  margin-right: 10px;
  font-size: 1.2em;
}
.onboarding-steps {
  display: flex;
  gap: 12px;
  justify-content: space-between;
  flex-direction: column;
}
.onboarding-steps a {
  color: #0074c2;
  text-decoration: none;
  font-weight: 500;
  cursor: pointer;
}
.onboarding-steps a:hover {
  text-decoration: underline;
}

/* Buttons */
.btn {
  border-radius: 8px;
  padding: 10px 20px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
  font-size: 1rem;
}
.btn-primary {
  background: #0074c2;
  color: white;
  border: 1px solid #0074c2;
  width: 100%;
  padding: 14px;
}
.btn-primary:hover {
  background: #1976d2;
}
.btn-outline {
  background: white;
  color: #0074c2;
  border: 1px solid #dcdcdc;
}
.btn-outline:hover {
  background: #f6faff;
  border-color: #0074c2;
}
.success-actions {
  margin-top: 24px;
  margin-bottom: 24px;
}

/* Footer */
.thankyou-footer {
  margin-top: 32px;
  border-top: 1px solid #f0f0f0;
  padding-top: 24px;
  text-align: center;
}
.footer-logo {
  width: 28px;
  height: 28px;
  margin-bottom: 8px;
}
.copyright {
  color: #999;
  font-size: 0.8rem;
}

/* Background */
.bg-lines,
.bg-illustration {
  position: absolute;
  opacity: 0.5;
  z-index: 1;
}
.bg-lines {
  left: 0;
  top: 0;
  width: 250px;
}
.bg-illustration {
  right: 0;
  bottom: 0;
  width: 300px;
}

/* Responsive */
@media (max-width: 768px) {
  .thankyou-card {
    margin: 12px;
    padding: 18px 16px;
    max-width: 100%;
    border-radius: 12px;
  }

  .bg-lines,
  .bg-illustration {
    display: none;
  }

  .thankyou-title {
    font-size: 1.4rem;
  }

  .thankyou-desc {
    font-size: 1rem;
    margin-bottom: 20px;
  }

  .key-details {
    flex-direction: column;
    gap: 10px;
    align-items: stretch;
    justify-content: center;
    margin: 12px 0;
  }

  .key-item {
    width: 100%;
    padding: 6px 0;
  }

  .plan-summary {
    flex-direction: column;
    align-items: stretch;
    text-align: left;
    gap: 10px;
    padding: 12px;
  }

  .plan-summary-left {
    width: 100%;
    /* keep left column stacked but center its contents on small screens */
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .plan-summary-right {
    width: 100%;
    display: flex;
    justify-content: center;
  }

  .plan-name {
    font-size: 1.05rem;
    word-break: break-word;
  }

  .plan-price {
    font-size: 1rem;
  }

  .plan-meta {
    justify-content: center;
  }

  .btn-primary,
  .btn-outline {
    width: 100%;
    box-sizing: border-box;
  }

  .success-actions {
    margin-top: 16px;
  }

  .onboarding-steps {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
