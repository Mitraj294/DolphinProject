<template>
  <MainLayout>
    <div class="page">
      <div class="manage-subscription-outer">
        <div class="thankyou-layout small">
          <div class="thankyou-bg">
            <div class="manage-card">
              <div class="manage-top">
                <div class="manage-illustration">
                  <img
                    src="@/assets/images/Group 14.svg"
                    alt="Subscription"
                  />
                </div>
                <h1
                  class="welcome-line"
                  v-if="!loading && userName"
                >
                  Welcome back, {{ userName }}!
                </h1>
                <h2 class="manage-title">
                  <span v-if="loading">Checking subscription…</span>
                  <span v-else-if="status === 'active'">You're subscribed</span>
                  <span v-else-if="status === 'expired'"
                    >Subscription expired</span
                  >
                  <span v-else>Get started with a plan</span>
                </h2>
                <p
                  class="manage-subtitle"
                  v-if="!loading"
                >
                  <template v-if="status === 'active'">
                    You are subscribed to the
                    <span
                      class="pill status-pill"
                      :class="{
                        'status-pill-active': status === 'active',
                        'status-pill-expired': status === 'expired',
                      }"
                    >
                      {{ status }}
                    </span>
                    <strong>{{ plan_name }}</strong>
                  </template>
                  <template v-else-if="status === 'expired'">
                    Your <strong>{{ plan_name || '—' }}</strong>

                    <span
                      class="pill status-pill"
                      :class="{
                        'status-pill-active': status === 'active',
                        'status-pill-expired': status === 'expired',
                      }"
                    >
                      {{ status }}
                    </span>
                    on <strong>{{ subscriptionEnd || '—' }}</strong
                    >. Please renew to continue access.
                  </template>
                  <template v-else>
                    Choose from our plans and start using Dolphin today.
                  </template>
                </p>
              </div>

              <div class="manage-actions">
                <button
                  class="btn btn-primary"
                  @click="handleButton"
                  :disabled="loading"
                >
                  <template v-if="loading">Checking...</template>
                  <template v-else-if="isSubscribed"
                    >Manage Subscription</template
                  >
                  <template v-else>Explore Subscriptions</template>
                </button>

                <button
                  v-if="hasBillingHistory"
                  class="btn btn-outline"
                  @click="goToBillingDetails"
                  :disabled="loading"
                >
                  <template v-if="loading">Checking...</template>
                  <template v-else>Billing Details</template>
                </button>
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
import { fetchSubscriptionStatus } from '@/services/subscription.js';

export default {
  name: 'ManageSubscription',
  components: { MainLayout },
  data() {
    return {
      status: null,
      plan_name: null,
      subscriptionEnd: null,
      userName: null,
      loading: true,
      isSubscribed: false,
      hasBillingHistory: false,
    };
  },
  async mounted() {
    try {
      const res = await fetchSubscriptionStatus();
      this.status = res.status || 'none';
      this.plan_name = res.plan_name || null;
      this.subscriptionEnd = res.subscription_end;
    } catch (e) {
      console.error(e);
      this.status = 'none';
      this.plan_name = null;
      this.subscriptionEnd = null;
    } finally {
      this.loading = false;
    }

    // load user display name if available
    try {
      const storage = (await import('@/services/storage.js')).default;
      this.userName = storage.get('userName') || null;
    } catch {
      this.userName = null;
    }

    // fetch billing history to determine whether Billing Details button should be shown
    try {
      const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || '';
      const orgId = this.$route.query.orgId || null;
      const url = orgId
        ? `${API_BASE_URL}/api/billing/history?org_id=${orgId}`
        : `${API_BASE_URL}/api/billing/history`;
      const axios = require('axios');
      const storage = require('@/services/storage').default;
      const token = storage.get('authToken');
      const headers = token ? { Authorization: `Bearer ${token}` } : {};
      const histRes = await axios.get(url, { headers });
      const historyData = Array.isArray(histRes.data) ? histRes.data : [];
      this.hasBillingHistory = historyData.length > 0;
    } catch (e) {
      console.warn('Failed to fetch billing history:', e);
      this.hasBillingHistory = false;
    }
  },
  methods: {
    handleButton() {
      this.$router.push({ name: 'SubscriptionPlans' });
    },
    goToBillingDetails() {
      const orgId = this.$route.query.orgId || null;
      this.$router.push({
        name: 'BillingDetails',
        query: orgId ? { orgId } : {},
      });
    },
  },
};
</script>

<style scoped>
/* Base styles */
.manage-subscription-outer {
  width: 100%;

  min-width: 0;

  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
}

.manage-subscription-card {
  width: 100%;

  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  overflow: visible;
  margin: 0 auto;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  position: relative;
  padding: 0;
}

.manage-subscription-header {
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

.manage-subscription-header-spacer {
  height: 18px;
  width: 100%;
  background: transparent;
  display: block;
}

.manage-subscription-container {
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
  gap: 36px;
}

.manage-subscription-img-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 240px;
  height: 240px;
  background: #f8f8f8;
  border-radius: 18px;
  box-sizing: border-box;
}

.manage-subscription-img {
  width: 240px;
  height: 240px;
  padding: 8px;
}

.manage-subscription-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 16px;
}

.manage-subscription-msg {
  font-size: 1.5rem;
  color: #222;
  margin: 0;
  font-weight: 500;
  text-align: center;
}
.manage-subscription-msg.green {
  color: rgb(29, 121, 29) !important;
}
.manage-subscription-msg.red {
  color: red !important;
}

.manage-subscription-btn {
  font-size: 1.1rem;
  font-weight: 500;
  padding: 10px 36px;
  margin: 8px 18px;
  border-radius: 22px;
}

/* Tablet styles */
@media (max-width: 1400px) {
  .manage-subscription-card {
    border-radius: 18px;
    max-width: 100%;
  }

  .manage-subscription-header {
    padding: 8px 8px 0 8px;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
  }

  .manage-subscription-container {
    padding: 0 8px 24px 8px;
    border-bottom-left-radius: 18px;
    border-bottom-right-radius: 18px;
    gap: 24px;
  }

  .manage-subscription-img-box {
    border-radius: 10px;
  }

  .manage-subscription-content {
    gap: 12px;
  }

  .manage-subscription-btn {
    min-width: 120px;
    font-size: 1rem;
    padding: 8px 18px;
    margin: 8px 18px;
    border-radius: 16px;
  }
}

/* Mobile landscape */
@media (max-width: 900px) {
  .manage-subscription-card {
    border-radius: 10px;
  }

  .manage-subscription-container {
    gap: 20px;
  }

  .manage-subscription-img-box {
    border-radius: 8px;
  }

  .manage-subscription-content {
    gap: 10px;
  }

  .manage-subscription-btn {
    min-width: 100px;
    font-size: 0.9rem;
    padding: 7px 14px;
    margin: 7px 14px;
    border-radius: 14px;
  }
}

/* Mobile portrait */
@media (max-width: 600px) {
  .manage-subscription-container {
    min-height: 240px;
    padding: 0 2vw 16px 2vw;
    gap: 16px;
  }

  .manage-subscription-img-box {
    width: 160px;
    height: 160px;
    border-radius: 6px;
  }

  .manage-subscription-img {
    width: 160px;
    height: 160px;
    padding: 4px;
  }

  .manage-subscription-content {
    gap: 8px;
  }

  .manage-subscription-btn {
    min-width: 80px;
    font-size: 0.9rem;
    padding: 6px 12px;
    border-radius: 12px;
  }
}
</style>

<style scoped>
/* Improved card-style layout matching SubscriptionSuccess look */
.thankyou-layout.small {
  display: flex;
  --manage-card-min-height: 420px; /* tune this value to match SubscriptionSuccess */
  min-height: var(--manage-card-min-height);
  align-items: center;
  justify-content: center;
  /* remove outer padding so card can reach container edges */
  padding: 0;
  width: 100%;
  box-sizing: border-box;
}
.thankyou-bg {
  display: flex;
  justify-content: center;
  width: 100%;
  max-width: 100%;
  padding: 0;
  box-sizing: border-box;
  /* visible boundary for the card */
  border: 1px solid #ebebeb;
  border-radius: 24px;
  background: #fff;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
  overflow: hidden;
  min-height: var(--manage-card-min-height);
}
.manage-card {
  width: 100%;
  background: transparent; /* let the thankyou-bg show as the card */
  border-radius: 0;
  box-shadow: none;
  padding: 0;
  text-align: center;
  min-height: var(--manage-card-min-height);
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.manage-top {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 24px 28px; /* internal padding for content inside the boundary */
}
.manage-illustration img {
  width: 160px;
  height: auto;
}
.manage-title {
  font-size: 1.5rem;
  margin: 0;
  color: #0b4666;
}
.welcome-line {
  font-size: 1.25rem;
  margin: 0 0 10px 0;
  color: #2b587a;
  font-weight: 700;
  letter-spacing: 0.2px;
}
.manage-subtitle {
  color: #555;
  margin: 0 0 8px 0;
  max-width: 72ch;
}
.status-pill {
  display: inline-block;

  padding: 4px 10px;
  border-radius: 999px;
  background: #e8f5e9;
  color: #196f3d;
  font-weight: 600;
  text-transform: capitalize;
}
.status-pill-active {
  background: #e8f5e9;
  color: #196f3d;
}
.status-pill-expired {
  background: #fdecea;
  color: #a72b2b;
}
.manage-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 18px;
  flex-wrap: wrap;
  padding: 12px 28px 20px 28px; /* keep actions visually separated from content */
}
.btn {
  border-radius: 10px;
  padding: 10px 22px;
  cursor: pointer;
  font-weight: 600;
}
.btn-outline {
  background: white;
  border: 1px solid #dcdcdc;
  color: #0074c2;
}

@media (max-width: 768px) {
  .manage-illustration img {
    width: 120px;
  }
  .manage-title {
    font-size: 1.25rem;
  }
  .manage-subtitle {
    font-size: 0.95rem;
  }
  .thankyou-layout.small {
    --manage-card-min-height: 360px;
    min-height: var(--manage-card-min-height);
  }
}
</style>
