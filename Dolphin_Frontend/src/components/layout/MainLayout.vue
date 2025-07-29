<template>
  <div class="main-layout">
    <div
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
      if (this.windowWidth <= 900) {
        return this.sidebarExpanded
          ? `calc(120px - 15px)`
          : `calc(45px - 15px)`;
      }
      return this.sidebarExpanded ? `calc(200px - 15px)` : `calc(65px - 15px)`;
    },
  },
  methods: {
    toggleSidebar() {
      this.sidebarExpanded = !this.sidebarExpanded;
      storage.set('sidebarExpanded', this.sidebarExpanded ? '1' : '0');
    },
    collapseSidebar() {
      this.sidebarExpanded = false;
      storage.set('sidebarExpanded', '0');
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
  /* Remove fixed height and overflow to allow page to grow naturally */
  /* height: auto; */
  margin: 0;
  overflow: visible;
}

.sidebar-circle-btn {
  position: fixed;
  width: 30px;
  height: 30px;
  top: 55px;
  /* left is now set dynamically */
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

.main-content.sidebar-expanded {
  margin-left: 200px; /* Sidebar width when expanded */
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

.main-content.sidebar-expanded .fixed-navbar {
  left: 200px;
  width: calc(100vw - 200px);
}

.page-content {
  flex: 1 1 auto;
  background: #fff;
  min-height: 0;
  padding: 0 32px; /* Add horizontal margin */
  box-sizing: border-box;
  width: 100%;
  max-width: 100vw;
  overflow: visible !important;
  height: auto;
}

/* Responsive: reduce margin on smaller screens */
@media (max-width: 1400px) {
  .page-content {
    padding: 0 12px;
  }
}
@media (max-width: 900px) {
  .page-content {
    padding: 0 4px;
    max-width: 100vw;
    overflow-x: hidden;
  }
  .main-content {
    margin-left: 45px; /* Sidebar width on small screens */
    padding-top: 54px; /* Navbar height on small screens */
    max-width: 100vw;
    overflow-x: hidden;
  }
  .main-content.sidebar-expanded {
    margin-left: 120px; /* Expanded sidebar width on small screens */
  }
  .fixed-navbar {
    left: 45px;
    width: calc(100vw - 45px);
    height: 54px;
    min-height: 54px;
    max-height: 54px;
  }
  .main-content.sidebar-expanded .fixed-navbar {
    left: 120px;
    width: calc(100vw - 120px);
  }
  .sidebar-circle-btn {
    top: calc(54px - 15px); /* Navbar height - half button size */
  }
}

/* Remove unwanted horizontal scrollbar for the whole app */
:global(html),
:global(body),
:global(#app) {
  overflow-x: hidden !important;
  width: 100% !important;
  max-width: 100vw !important;
}
</style>
