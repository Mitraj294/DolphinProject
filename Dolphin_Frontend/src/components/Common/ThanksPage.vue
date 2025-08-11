<template>
  <div class="thanks-page">
    <img
      src="@/assets/images/Lines.svg"
      alt="Lines"
      class="bg-lines"
    />
    <img
      src="@/assets/images/Image.svg"
      alt="Illustration"
      class="bg-illustration"
    />
    <div class="thanks-card">
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
      <h2 class="thanks-title">Thank You!</h2>
      <div class="thanks-desc">
        <template v-if="$route.query.already === '1'">
          You have already submitted this assessment.<br />
          Duplicate submissions are not allowed.
        </template>
        <template v-else>
          Thank you for completing the assessment.<br />
          Your responses have been submitted successfully.
        </template>
      </div>
      <div class="thanks-footer">
        <img
          :src="require('@/assets/images/Logo.svg')"
          alt="Dolphin Logo"
          class="footer-logo"
        />
        <div class="copyright">©2025 Dolphin | All Rights Reserved</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ThanksPage',
};
</script>

<style scoped>
.thanks-page {
  min-height: 100vh;
  width: 100vw;
  position: relative;
  background: #f6faff;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.bg-lines {
  position: absolute;
  left: 0;
  top: 0;
  width: 250px;
  height: auto;
  z-index: 0;
}

.bg-illustration {
  position: absolute;
  right: 0;
  bottom: 0;
  width: 300px;
  height: auto;
  z-index: 0;
}
.thanks-card {
  position: relative;
  z-index: 1;
  background: #fff;
  padding: 2.5rem 2rem 2rem 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
  max-width: 540px;
  width: 100%;
  text-align: center;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.check-circle {
  margin-bottom: 1.5rem;
}
.thanks-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #1a237e;
  letter-spacing: 1px;
}
.thanks-desc {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 2rem;
  line-height: 1.5;
}
.thanks-footer {
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}
.footer-logo {
  width: 28px;
  height: auto;
}

@media (max-width: 1200px) {
  .bg-lines {
    width: 180px;
    left: 1vw;
    top: 8vh;
  }
  .bg-illustration {
    width: 220px;
    right: 1vw;
    bottom: 8vh;
  }
}
@media (max-width: 900px) {
  .bg-lines {
    width: 120px;
    left: -20px;
    top: -20px;
  }
  .bg-illustration {
    width: 150px;
    right: -20px;
    bottom: -20px;
  }
  .thanks-card {
    max-width: 98vw;
    padding: 1.5rem 0.5rem 1.5rem 0.5rem;
  }
}
@media (max-width: 600px) {
  .bg-lines {
    width: 120px;
    left: -20px;
    top: -20px;
  }
  .bg-illustration {
    width: 150px;
    right: -20px;
    bottom: -20px;
  }
  .thanks-card {
    max-width: 100vw;
    padding: 1rem 0.2rem 1rem 0.2rem;
  }
}
</style>
