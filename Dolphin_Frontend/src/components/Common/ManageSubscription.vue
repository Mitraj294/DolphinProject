<template>
  <MainLayout>
    <div class="page">
      <div class="manage-subscription-outer">
        <div class="manage-subscription-card">
          <div class="manage-subscription-header"></div>
          <div class="manage-subscription-header-spacer"></div>
          <div class="manage-subscription-container">
            <div class="manage-subscription-img-box">
              <img
                src="@/assets/images/Group 14.svg"
                alt="No Plans"
                class="manage-subscription-img"
              />
            </div>
            <div class="manage-subscription-content">
              <div class="manage-subscription-msg">
                <div v-if="loading">Loading subscription status...</div>
                <div
                  v-else-if="status === 'active'"
                  class="manage-subscription-msg green"
                >
                  You are subscribed to the {{ plan_name }} {{ status }} plan.
                </div>
                <div
                  v-else-if="status === 'expired'"
                  class="manage-subscription-msg red"
                >
                  Your Plan {{ plan_name }} expired on {{ subscriptionEnd }}.
                  Please renew.
                </div>
                <div
                  v-else
                  class="manage-subscription-msg"
                >
                  You have not selected any plans yet.
                </div>
              </div>
              <button
                class="btn btn-primary manage-subscription-btn"
                @click="handleButton"
                :disabled="loading"
              >
                <template v-if="loading">Checking...</template>
                <template v-else-if="isSubscribed"
                  >Manage Subscription</template
                >
                <template v-else>Explore Subscriptions</template>
              </button>
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
      loading: true,
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
  },
  methods: {
    handleButton() {
      this.$router.push({ name: 'SubscriptionPlans' });
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
  color: green !important;
}
.manage-subscription-msg.red {
  color: red !important;
}

.manage-subscription-btn {
  margin-top: 8px;
  min-width: 180px;
  font-size: 1.1rem;
  font-weight: 500;
  padding: 10px 36px;
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
