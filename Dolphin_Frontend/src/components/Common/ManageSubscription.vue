<template>
  <MainLayout>
    <div class="page">
      <div class="manage-subscription-outer">
        <div class="manage-subscription-card">
          <div class="manage-subscription-header">
            <!-- Reserved for future actions, keep for layout consistency -->
          </div>
          <div class="manage-subscription-header-spacer"></div>
          <div class="manage-subscription-container">
            <img
              src="@/assets/images/Group 14.svg"
              alt="No Plans"
              class="manage-subscription-img"
            />
            <div class="manage-subscription-msg">
              <template v-if="loading">
                Loading subscription status...
              </template>
              <template v-else-if="isSubscribed">
                You are subscribed to a plan.
              </template>
              <template v-else> You have not selected any plans yet. </template>
            </div>
            <button
              class="btn btn-primary"
              @click="handleButton"
              :disabled="loading"
            >
              <template v-if="loading">Checking...</template>
              <template v-else-if="isSubscribed">Manage Subscription</template>
              <template v-else>Explore Subscriptions</template>
            </button>
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
      isSubscribed: false,
      loading: true,
    };
  },
  async mounted() {
    try {
      const status = await fetchSubscriptionStatus();
      this.isSubscribed = status && status.status === 'active';
    } catch (e) {
      this.isSubscribed = false;
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
.manage-subscription-outer {
  width: 100%;
  max-width: 1400px;
  min-width: 0;

  font-size: 17px;
  color: #222;
  margin-bottom: 28px;
  text-align: center;
  font-family: 'Inter', Arial, sans-serif;
  font-weight: 500;
}

/* Responsive styles to match base pages */
@media (max-width: 1400px) {
  .manage-subscription-outer {
    margin: 12px;
    max-width: 100%;
  }
  .manage-subscription-card {
    border-radius: 14px;
    max-width: 100%;
  }
  .manage-subscription-header {
    padding: 8px 8px 0 8px;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
  }
  .manage-subscription-container {
    padding: 0 8px 24px 8px;
    border-bottom-left-radius: 14px;
    border-bottom-right-radius: 14px;
  }
}
@media (max-width: 900px) {
  .manage-subscription-outer {
    margin: 4px;
    max-width: 100%;
  }
  .manage-subscription-card {
    border-radius: 10px;
  }
  .manage-subscription-header {
    padding: 8px 4px 0 4px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
  }
  .manage-subscription-container {
    padding: 0 4px 12px 4px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
  }
}
@media (max-width: 600px) {
  .manage-subscription-container {
    padding: 0 2vw 8px 2vw;
    min-height: 180px;
  }
  .manage-subscription-img {
    width: 120px;
    margin-top: 12px;
    margin-bottom: 16px;
  }
  .manage-subscription-msg {
    font-size: 15px;
    margin-bottom: 18px;
  }
}
</style>
