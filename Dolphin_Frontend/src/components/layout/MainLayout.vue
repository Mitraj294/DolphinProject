<template>
  <div class="main-layout">
    <div
      v-if="isSubscriptionActive"
      class="sidebar-circle-btn"
      @click="toggleSidebar"
      :style="{ left: sidebarBtnLeft }"
    >
      <img
        src="@/assets/images/ExpandLines.svg"
        alt="Menu"
        class="sidebar-circle-icon"
      />
    </div>
    <div :class="['main-content', { 'sidebar-expanded': sidebarExpanded }]">
      <Navbar
        class="fixed-navbar"
        :sidebarExpanded="sidebarExpanded"
      />
      <Sidebar
        :role="userRole"
        :expanded="sidebarExpanded"
        @menu-item-clicked="handleSidebarClick"
      />

      <div class="page-content">
        <slot />
      </div>

      <Footer
        class="sticky-footer"
        style="
          z-index: 1;
          position: relative;
          pointer-events: none;
          background: transparent;
        "
      />
    </div>
  </div>
</template>

<script>
import Sidebar from './Sidebar.vue';
import Navbar from '@/components/layout/Navbar.vue';
import Footer from '@/components/layout/Footer.vue';
import ToastService from 'primevue/toastservice';
import Toast from 'primevue/toast';

import authMiddleware from '../../middleware/authMiddleware.js';
import storage from '@/services/storage';

export default {
  name: 'MainLayout',
  components: { Sidebar, Navbar, Footer, Toast },
  data() {
    return {
      userRole: authMiddleware.getRole() || 'User',
      sidebarExpanded: false,
      windowWidth: window.innerWidth,
    };
  },
  computed: {
    sidebarBtnLeft() {
      if (this.windowWidth <= 425) {
        return this.sidebarExpanded ? `calc(200px - 15px)` : '-15px';
      }
      return this.sidebarExpanded ? `calc(200px - 15px)` : `calc(65px - 15px)`;
    },
    isSubscriptionActive() {
      if (!storage || typeof storage.get !== 'function') return false;
      const role = storage.get('role') || '';
      const status = storage.get('subscription_status');
      if (role === 'organizationadmin') {
        return status === 'active';
      }
      return true;
    },
  },
  methods: {
    toggleSidebar() {
      this.sidebarExpanded = !this.sidebarExpanded;
      storage.set('sidebarExpanded', this.sidebarExpanded ? '1' : '0');
    },

    handleSidebarClick() {
      if (this.sidebarExpanded && this.windowWidth < 768) {
        this.toggleSidebar();
      }
    },

    handleResize() {
      this.windowWidth = window.innerWidth;
    },
  },
  mounted() {
    window.addEventListener('resize', this.handleResize);
    // Restore sidebar state from encrypted storage
    const saved = storage.get('sidebarExpanded');
    if (saved === '1') {
      this.sidebarExpanded = true;
    } else {
      this.sidebarExpanded = false;
    }
    // Provide ToastService for PrimeVue Toast
    if (this.$app && this.$app.use) {
      this.$app.use(ToastService);
    }
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.handleResize);
  },
};
</script>

<style scoped>
.main-layout {
  display: flex;
  min-height: 0;
  height: auto;
  width: 100vw;
  margin: 0;
  overflow: visible;
}

.sidebar-circle-btn {
  position: fixed;
  width: 30px;
  height: 30px;
  top: 55px;

  border-radius: 50%;
  border-width: 1px;
  background: #ffffff;
  border: 1px solid #dcdcdc;
  z-index: 12;
  overflow: visible !important;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
@media (max-width: 425px) {
  .sidebar-circle-btn {
    top: 55px;
  }
}

.sidebar-circle-icon {
  width: 16px;
  height: 10px;
  display: block;
}

.main-content {
  flex: 1;
  margin-left: 65px; /* Sidebar width when collapsed */
  padding-top: 70px; /* Navbar height */
  display: flex;
  flex-direction: column;
  min-height: 0;
  height: auto;
  box-sizing: border-box;
  background: #fff;
  width: 100vw;
  max-width: 100vw;
  overflow-x: hidden; /* Prevent horizontal scroll on main layout */
}
@media (max-width: 425px) {
  .main-content {
    margin-left: 0;
  }
}

.main-content.sidebar-expanded {
  margin-left: 200px; /* Sidebar width when expanded */
}
@media (max-width: 425px) {
  .main-content.sidebar-expanded {
    margin-left: 0;
  }
}

.fixed-navbar {
  margin-left: 0;
  border-radius: 0;
  box-shadow: none;
  border-bottom: 1px solid #f0f0f0;
  position: fixed;
  left: 65px;
  top: 0;
  width: calc(100vw - 65px);
  z-index: 11;
}
@media (max-width: 425px) {
  .fixed-navbar {
    left: 0;
    width: 100%;
    min-width: 320px;
    max-width: 425px;
  }
}

.main-content.sidebar-expanded .fixed-navbar {
  left: 200px;
  width: calc(100vw - 200px);
}

.page-content {
  flex: 1 1 auto;
  background: #fff;
  min-height: 0;

  box-sizing: border-box;
  width: 100%;
  max-width: 100vw;
  overflow: visible !important;
  height: auto;
}
</style>

<style scoped>
/* Responsive: reduce margin on smaller screens */

/* Remove unwanted horizontal scrollbar for the whole app */
:global(html),
:global(body),
:global(#app) {
  overflow-x: hidden !important;
  width: 100% !important;
  max-width: 100vw !important;
}
</style>
