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
                <template v-if="loading">
                  Loading subscription status...
                </template>
                <template v-else-if="isSubscribed">
                  You are subscribed to a plan.
                </template>
                <template v-else>
                  You have not selected any plans yet.
                </template>
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
  margin: 64px auto 64px auto;
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
  min-width: 0;
  max-width: 1400px;
  display: flex;
  flex-direction: column;
  gap: 0;
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
  flex-direction: row;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  padding: 0 24px 48px 24px;
  background: #fff;
  border-bottom-left-radius: 24px;
  border-bottom-right-radius: 24px;
  gap: 36px;
}
manage-subscription-img.-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 160px;
  height: 160px;
  background: #f8f8f8;
  border-radius: 18px;
  margin-right: 32px;
  box-sizing: border-box;
}
.manage-subscription-img {
  width: 160px;
  height: 160px;
  padding: 8px;
}

.manage-subscription-msg {
  font-size: 1.2rem;
  color: #222;
  margin-bottom: 0;
  margin-top: 0;
  text-align: left;
  font-weight: 500;
}
.manage-subscription-btn {
  margin-top: 8px;
  min-width: 180px;
  font-size: 1.1rem;
  font-weight: 500;
  padding: 10px 36px;
  border-radius: 22px;
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
  .manage-subscription-img-box {
    width: 180px;
    height: 180px;
    margin-right: 16px;
    border-radius: 10px;
  }
  .manage-subscription-img {
    width: 140px;
    height: 140px;
    padding: 8px;
  }

  .manage-subscription-img {
    width: 60px;
    height: 60px;
    padding: 4px;
  }
  .manage-subscription-content {
    min-width: 120px;
    max-width: 320px;
    gap: 10px;
  }
  .manage-subscription-msg {
    font-size: 1rem;
  }
  .manage-subscription-btn {
    min-width: 120px;
    font-size: 1rem;
    padding: 8px 18px;
    border-radius: 16px;
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
  .manage-subscription-img-box {
    width: 120px;
    height: 120px;
    margin: 0 0 12px 0;
    border-radius: 8px;
  }
  .manage-subscription-img {
    width: 90px;
    height: 90px;
    padding: 4px;
  }
}
.manage-subscription-content {
  min-width: 80px;
  max-width: 100vw;
  gap: 6px;
  align-items: center;
  text-align: center;
}
.manage-subscription-msg {
  font-size: 0.95rem;
  text-align: center;
}
.manage-subscription-btn {
  min-width: 80px;
  font-size: 0.95rem;
  padding: 7px 12px;
  border-radius: 12px;
}

@media (max-width: 600px) {
  .manage-subscription-container {
    min-height: 80px;
    padding: 0 2vw 8px 2vw;
    gap: 6px;
  }
  .manage-subscription-content {
    min-width: 40px;
    max-width: 100vw;
    gap: 4px;
    align-items: center;
    text-align: center;
  }
  .manage-subscription-msg {
    font-size: 0.85rem;
    margin-bottom: 4px;
  }
  .manage-subscription-btn {
    min-width: 60px;
    font-size: 0.85rem;
    padding: 5px 8px;
    border-radius: 8px;
  }
}

/* Responsive styles to match base pages */
@media (max-width: 1400px) {
  .manage-subscription-outer {
    margin: 12px;
    max-width: 100%;
  }
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
  }
  .manage-subscription-img {
    width: 120px;
    max-width: 40vw;
    margin: 16px auto 12px auto;
    padding: 8px;
  }
  .manage-subscription-msg {
    font-size: 15px;
    margin-bottom: 14px;
  }
}
@media (max-width: 900px) {
  .manage-subscription-outer {
    margin: 4px;
    max-width: 100%;
  }
  .manage-subscription-img-box {
    width: 70px;
    height: 70px;
    margin: 0 0 8px 0;
    border-radius: 6px;
  }
  .manage-subscription-img {
    width: 50px;
    height: 50px;
    padding: 2px;
  }
  .manage-subscription-img {
    width: 90px;
    max-width: 30vw;
    margin: 10px auto 10px auto;
    padding: 6px;
  }
  .manage-subscription-msg {
    font-size: 13px;
    margin-bottom: 10px;
  }
}
@media (max-width: 600px) {
  .manage-subscription-container {
    padding: 0 2vw 8px 2vw;
    min-height: 80px;
  }
  .manage-subscription-img {
    width: 50px;
    max-width: 20vw;
    margin: 6px auto 6px auto;
    padding: 2px;
  }
  .manage-subscription-msg {
    font-size: 11px;
    margin-bottom: 6px;
  }
}
</style>
