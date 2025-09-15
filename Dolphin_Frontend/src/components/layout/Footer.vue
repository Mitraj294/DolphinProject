<template>
  <footer class="footer-box">
    <div
      class="footer-controls"
      :class="{ 'footer-controls-flex': !withPagination }"
    >
      <template v-if="withPagination">
        <!-- Removed notifications-page-size-dropdown and notifications-pagination -->
        <div class="footer-logo-text-vertical center-footer-logo-block">
          <img
            src="@/assets/images/Logo.svg"
            alt="Logo"
            class="footer-logo-vertical responsive-footer-logo"
          />
          <span class="footer-text-vertical"
            >©2025 Dolphin | All Rights Reserved</span
          >
        </div>
      </template>
      <template v-else>
        <div class="footer-logo-text-vertical center-footer-logo-block">
          <img
            src="@/assets/images/Logo.svg"
            alt="Logo"
            class="footer-logo-vertical responsive-footer-logo"
          />
          <span class="footer-text-vertical"
            >©2025 Dolphin | All Rights Reserved</span
          >
        </div>
      </template>
    </div>
  </footer>
</template>

<script>
import '@/assets/global.css';
export default {
  name: 'Footer',
  props: {
    withPagination: {
      type: Boolean,
      default: false,
    },
    // Removed pageSize, pageSizes, showPageDropdown, currentPage, totalPages, paginationPages
  },
  // Removed computedPaginationPages
};
</script>

<style scoped>
.footer-box {
  width: 100%;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0 0 18px 18px;
  box-sizing: border-box;
  padding: 0;
  margin: 8px auto 24px auto;
  position: relative;
  min-height: 64px;

  box-shadow: none;
  overflow: visible !important;
  z-index: 1; /* Lower than dropdown overlay */
}
.footer-controls {
  width: 100%;

  display: flex !important;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 0;
  padding: 0 24px;
  box-sizing: border-box;
  overflow-x: visible !important;
}
/* Add this for flex centering when no pagination */
.footer-controls-flex {
  display: flex !important;
  justify-content: center;
  align-items: center;
  gap: 0;
  padding: 0 24px;
}
.footer-logo-text-vertical {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-width: 120px;
  margin: 12px 0 0 0;
}
.center-footer-logo-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin: 12px 0 0 0;
}
.footer-logo-vertical.responsive-footer-logo {
  width: 32px;
  height: 32px;
  object-fit: contain;
  margin-bottom: 0;
}
.footer-text-vertical {
  font-family: 'Inter', sans-serif;
  font-style: normal;
  font-weight: 400;
  font-size: 16px;
  line-height: 22px;
  letter-spacing: 0.02em;
  text-align: center;
  color: #787878;
  background: transparent;
  display: block;
}
/* Removed notifications-page-size-dropdown, notifications-pagination and related styles */
@media (max-width: 900px) {
  .footer-box {
    margin: 16px auto 0 auto;

    overflow: visible !important;
  }
  .footer-controls,
  .footer-controls-flex {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 16px 8px;
    overflow-x: visible !important;
  }
  .center-footer-logo-block {
    align-self: center;
    justify-content: center;
    width: 100%;
    display: flex;
    flex-direction: row;
  }
  .footer-logo-vertical.responsive-footer-logo {
    width: 22px;
    height: 22px;
  }
  .footer-text-vertical {
    font-size: 12px;
  }
  .center-footer-logo-block {
    margin: 8px 0 0 0;
  }
}
</style>
