<template>
  <div>
    <h2>Subscription Successful!</h2>
    <p>Your subscription has been processed successfully.</p>
    <p>You will be redirected to the login page shortly.</p>
  </div>
</template>

<script>
export default {
  name: 'SubscriptionSuccess',
  mounted() {
    setTimeout(() => {
      this.$router.push('/login');
    }, 5000);
  },
};
</script>
