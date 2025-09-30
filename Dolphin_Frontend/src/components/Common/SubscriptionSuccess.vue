<template>
  <div class="subscription-success page">
    <div class="success-card">
      <h2>Subscription Successful!</h2>
      <p>Your subscription has been processed successfully.</p>
      <p>You will be redirected to the login page shortly.</p>

      <div class="success-actions">
        <button
          class="btn btn-primary"
          @click="goHome"
        >
          Go to Home
        </button>
        <button
          class="btn btn-secondary"
          @click="goLogin"
        >
          Go to Login
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SubscriptionSuccess',
  mounted() {
    // keep existing auto-redirect to login after a short delay
    setTimeout(() => {
      this.$router.push('/login');
    }, 5000);
  },
  methods: {
    goHome() {
      this.$router.push('/');
    },
    goLogin() {
      this.$router.push('/login');
    },
  },
};
</script>

<style scoped>
.subscription-success {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 60vh;
}
.success-card {
  background: #fff;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  text-align: center;
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
.btn-secondary {
  background: #f0f0f0;
  color: #333;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
}
</style>
