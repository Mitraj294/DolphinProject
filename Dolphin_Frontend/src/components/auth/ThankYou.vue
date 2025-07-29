<template>
  <div class="thankyou-layout">
    <Sidebar />
    <div class="main-content">
      <Navbar />
      <div class="thankyou-bg">
        <div class="left-illustration">
          <!-- ...existing code... -->
        </div>
        <div class="thankyou-card">
          <div class="check-circle">
            <svg width="56" height="56" viewBox="0 0 56 56">
              <circle cx="28" cy="28" r="28" fill="#2ecc40"/>
              <polyline points="18,30 26,38 38,20" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h2 class="thankyou-title">Thank You for your interest!</h2>
          <div class="thankyou-desc">
            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard.
          </div>
          <div class="thankyou-touch">We will be in touch.</div>
          <div class="thankyou-footer">
            <img :src="require('@/assets/images/Logo.svg')" alt="Dolphin Logo" class="footer-logo" />
            <div class="copyright">©2025 Dolphin | All Rights Reserved</div>
          </div>
        </div>
        <div class="right-illustration">
          <!-- ...existing code... -->
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from '@/components/layout/Navbar.vue'
import Sidebar from '@/components/layout/Sidebar.vue'
export default {
  name: 'ThankYou',
  components: { Sidebar, Navbar }
}
</script>

<style scoped>
.thankyou-layout {
  display: flex;
 min-height: 0;
  height: auto;
}
.main-content {
  flex: 1;
  margin-left: 64px;
  display: flex;
  flex-direction: column;
 min-height: 0;
  height: auto;
}
.thankyou-bg {
  flex: 1;
  background: #f6faff;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}
.thankyou-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 8px 24px 0 rgba(0,0,0,0.08);
  padding: 48px 40px 32px 40px;
  max-width: 420px;
  width: 100%;
  text-align: center;
  z-index: 2;
  position: relative;
}
.check-circle {
  margin-bottom: 24px;
}
.thankyou-title {
  font-size: 1.7rem;
  font-weight: 600;
  color: #111;
  margin-bottom: 16px;
  margin-top: 0;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}
.thankyou-desc {
  color: #888;
  font-size: 1.08rem;
  margin-bottom: 18px;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}
.thankyou-touch {
  color: #444;
  font-size: 1.08rem;
  margin-bottom: 24px;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}
.thankyou-footer {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 12px;
}
.footer-logo {
  width: 28px;
  height: 28px;
  object-fit: contain;
  margin-bottom: 8px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.07);
}
.copyright {
  color: #787878;
  font-size: 14px;
  font-family: 'Inter', Arial, sans-serif;
  text-align: center;
  margin-top: 2px;
}

@media (max-width: 900px) {
  
  .thankyou-card {
    margin: 0 8vw;
    padding: 32px 8vw 24px 8vw;
  }
}
</style>
