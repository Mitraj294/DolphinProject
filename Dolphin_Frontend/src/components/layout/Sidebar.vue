<template>
  <aside
    :class="['sidebar', { expanded, 'sidebar-disabled': sidebarDisabled }]"
    :aria-hidden="sidebarDisabled ? 'true' : null"
    :tabindex="sidebarDisabled ? -1 : null"
    role="navigation"
    aria-label="Sidebar"
  >
    <div
      class="sidebar-logo"
      :class="{ expanded }"
    >
      <img
        src="@/assets/images/Logo.svg"
        alt="Logo"
      />
      <span
        v-if="expanded"
        class="sidebar-logo-label"
        >DOLPHIN</span
      >
    </div>
    <ul
      class="sidebar-menu"
      v-if="!hideMenu"
    >
      <li
        v-for="(item, idx) in menuOptions"
        :key="idx"
        :style="
          expanded
            ? {
                width: '100%',
                display: 'flex',
                alignItems: 'center',
                padding: '6px',
                margin: 0,
                listStyle: 'none',
              }
            : getCircleStyle(idx)
        "
      >
        <router-link
          @click="handleLinkClick"
          :to="item.route"
          :class="[
            'sidebar-link',
            expanded ? 'sidebar-link-expanded' : '',
            isActive(item.route) ? 'active' : '',
          ]"
          style="
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
          "
        >
          <span
            :class="[
              'sidebar-icon',
              expanded ? 'sidebar-icon-expanded' : '',
              isActive(item.route) ? 'active' : '',
            ]"
            :style="expanded ? {} : {}"
          >
            <img
              v-if="item.route === '/dashboard'"
              :src="
                isActive('/dashboard')
                  ? require('@/assets/images/DashboardOn.svg')
                  : require('@/assets/images/DashboardOff.svg')
              "
              alt="Dashboard"
              class="sidebar-svg"
            />
            <img
              v-else-if="item.route === '/organizations'"
              :src="
                isActive('/organizations')
                  ? require('@/assets/images/Organizations+AdminsOn.svg')
                  : require('@/assets/images/Organizations+AdminsOff.svg')
              "
              alt="Organizations"
              class="sidebar-svg"
            />
            <img
              v-else-if="item.route === '/notifications'"
              :src="
                isActive('/notifications')
                  ? require('@/assets/images/AnnouncementsOn.svg')
                  : require('@/assets/images/AnnouncementsOff.svg')
              "
              alt="Notifications"
              class="sidebar-svg"
            />
            <img
              v-else-if="item.route === '/user-permission'"
              :src="
                isActive('/user-permission')
                  ? require('@/assets/images/Users+PermissionsOn.svg')
                  : require('@/assets/images/Users+PermissionsOff.svg')
              "
              alt="User + Permission"
              class="sidebar-svg"
            />
            <img
              v-else-if="item.route === '/leads'"
              :src="
                isActive('/leads')
                  ? require('@/assets/images/LeadOn.svg')
                  : require('@/assets/images/LeadOff.svg')
              "
              alt="Leads"
              class="sidebar-svg"
            />
            <img
              v-else-if="item.route === '/my-organization'"
              :src="
                isActive('/my-organization')
                  ? require('@/assets/images/MyOrganizationOn.svg')
                  : require('@/assets/images/MyOrganizationOff.svg')
              "
              alt="My Organization"
              class="sidebar-svg"
            />
            <img
              v-else-if="item.route === '/training-resources'"
              :src="
                isActive('/training-resources')
                  ? require('@/assets/images/Training&ResourcesOn.svg')
                  : require('@/assets/images/Training&ResourcesOff.svg')
              "
              alt="Training & Resources"
              class="sidebar-svg"
            />
            <img
              v-else-if="item.route === '/assessments'"
              :src="
                isActive('/assessments')
                  ? require('@/assets/images/AssessmentsOn.svg')
                  : require('@/assets/images/AssessmentsOff.svg')
              "
              alt="Assessments"
              class="sidebar-svg"
            />
          </span>
          <span
            v-if="expanded"
            :class="[
              'sidebar-menu-label',
              'sidebar-menu-label-expanded',
              isActive(item.route) ? 'active-label' : '',
            ]"
            >{{ item.label }}</span
          >
        </router-link>
      </li>
    </ul>
  </aside>
</template>

<script>
import '@/assets/global.css';
import { canAccess } from '@/permissions.js';

export default {
  name: 'Sidebar',
  props: {
    hideMenu: {
      type: Boolean,
      default: false,
    },
    expanded: {
      type: Boolean,
      default: false,
    },
    sidebarDisabled: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    role() {
      // Get role from encrypted storage for consistency
      const storage = require('@/services/storage').default;
      return storage.get('role') || '';
    },
    menuOptions() {
      if (this.hideMenu) return [];
      const allMenus = [
        { route: '/dashboard', label: 'Dashboard' },
        { route: '/organizations', label: 'Organizations' },
        { route: '/notifications', label: 'Notification' },
        { route: '/user-permission', label: 'User + Permission' },
        { route: '/leads', label: 'Leads' },
        { route: '/my-organization', label: 'My Organization' },
        {
          route: '/training-resources',
          label: 'Training & Resources',
          style: { fontSize: '0.8rem !important' },
        },
        { route: '/assessments', label: 'Assessments' },
      ];
      // Filter menus based on permissions
      return allMenus.filter((menu) =>
        canAccess(this.role, 'routes', menu.route)
      );
    },
  },
  methods: {
    handleLinkClick() {
      this.$emit('menu-item-clicked');
    },
    getCircleStyle(idx) {
      const top = idx === 0 ? 24 : 54 * idx + 24;
      return {
        position: 'absolute',
        left: 'calc(50% - 22.5px)',
        top: `${top}px`,
        width: '45px',
        height: '45px',
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        padding: 0,
        margin: 0,
        listStyle: 'none',
      };
    },
    isActive(route) {
      // Highlight for /organizations and all subroutes
      if (route === '/organizations') {
        return this.$route.path.startsWith('/organizations');
      }
      if (route === '/leads') {
        return this.$route.path.startsWith('/leads');
      }
      if (route === '/my-organization') {
        return this.$route.path.startsWith('/my-organization');
      }
      if (route === '/dashboard') {
        return this.$route.path.startsWith('/dashboard');
      }
      if (route === '/notifications') {
        return this.$route.path.startsWith('/notifications');
      }
      if (route === '/assessments') {
        return this.$route.path.startsWith('/assessments');
      }
      if (route === '/training-resources') {
        return this.$route.path.startsWith('/training-resources');
      }
      return this.$route.path === route;
    },
  },
};
</script>

<style scoped>
:root {
  --sidebar-navbar-height: 56px;
}
.sidebar {
  box-sizing: border-box;
  position: fixed;
  width: 65px;
  min-width: 65px;
  max-width: 65px;
  height: 100vh;
  left: 0;
  top: 0;
  background: #fafafa;
  border-right: 0.6px solid #f0f0f0;
  border-radius: 0 1px 1px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  overflow-y: auto;
  overflow-x: hidden;
  padding-left: 0;
  padding-top: 0;
  margin-left: 0;
  margin-top: 0;
  z-index: 11;
}
.sidebar.expanded {
  width: 200px;
  min-width: 200px;
  max-width: 200px;
}
.sidebar-logo {
  width: auto;
  min-width: unset;
  max-width: unset;
  height: 70px;
  min-height: 70px;
  max-height: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fafafa;
  position: relative;
  flex-direction: column;
}
.sidebar-logo.expanded {
  width: auto;
  max-width: unset;
  min-width: unset;
  height: 70px;
  flex-direction: row;
  justify-content: flex-start;
  align-items: center;
  padding-left: 16px;
}
.sidebar-logo img {
  width: Zpx;
  height: Zpx;
  min-width: Zpx;
  min-height: Zpx;
  max-width: Zpx;
  max-height: Zpx;
  object-fit: contain;
  display: block;
  position: static;
  left: unset;
  top: unset;
  transform: none;
  margin: 0;
}
.sidebar-logo-label {
  display: inline-block;
  font-size: 1.1rem;
  font-weight: bold;
  color: #0164a5;
  letter-spacing: 2px;
  text-align: left;
  margin-left: 12px;
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  white-space: nowrap;
}

.sidebar-menu {
  position: relative;
  width: 100%;
  max-width: 100%;
  height: 100%;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.sidebar.expanded ul.sidebar-menu {
  margin-top: 32px;
  padding: 0;
  display: flex;
  flex-direction: column;
  align-items: center !important;
}

.sidebar.expanded li {
  height: 56px;
  margin-top: 0;
  margin-bottom: 0;
  padding: 0 0 0 0;
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  justify-content: flex-start !important;
  gap: 12px;
  width: 100%;
  min-width: 0;
  max-width: 100%;
}

.sidebar.expanded li:first-child {
  margin-top: 16px;
}

.sidebar.expanded li:last-child {
  margin-bottom: 0;
}
.sidebar.expanded .router-link-active,
.sidebar.expanded .router-link-exact-active,
.sidebar.expanded .router-link-active:visited,
.sidebar.expanded .router-link-exact-active:visited {
  text-decoration: none !important;
}
.sidebar-menu-label {
  margin-left: 0;
  margin-top: 8px;
  margin-bottom: 8px;
  font-size: 1rem;
  color: #222;
  font-weight: 500;
  white-space: nowrap;
  text-decoration: none !important;
  padding: 0;
  text-align: center;
}

.router-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.sidebar-icon {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: #fff;
  border: 1px solid #dcdcdc;
  display: flex;
  align-items: center;
  justify-content: center;

  cursor: pointer;
  position: relative;
}

.sidebar-icon.sidebar-icon-expanded {
  border: none !important;
  margin-left: 10px !important; /* Increased left margin */
}

.sidebar-icon.active {
  background: #0164a5;
  border: none;
  color: #fff;
}

.sidebar-icon-expanded {
  width: 22px !important;
  height: 22px !important;
  min-width: 22px !important;
  min-height: 22px !important;
  max-width: 22px !important;
  max-height: 22px !important;
  margin-left: 8px !important;

  background: #fff;

  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar-svg {
  position: absolute;
  width: 20px;
  height: 18px;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  pointer-events: none;
}

.sidebar-menu li {
  margin-bottom: 32px;
}
.sidebar-menu li:last-child {
  margin-bottom: 0;
}

.sidebar-link-expanded {
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  justify-content: flex-start !important;
  width: 100%; /* Ensure 100% width, not more */
  border-radius: 16px;

  min-height: 48px;

  text-decoration: none !important;
  background: #fff;
  color: #0164a5;
}
.sidebar-link-expanded.active {
  background: #0164a5 !important;
  color: #fff !important;
}
.sidebar-link-expanded .sidebar-menu-label-expanded {
  margin-left: 6px;
  margin-right: 4px;
  font-size: 0.95rem;
  font-weight: 500;
  color: #0164a5;
}
.sidebar-link-expanded.active .sidebar-menu-label-expanded,
.sidebar-link-expanded.active .sidebar-icon-expanded {
  color: #fff !important;
}
.sidebar-link-expanded.active .sidebar-menu-label-expanded {
  color: #fff !important;
}
.sidebar-link-expanded .sidebar-icon-expanded {
  color: #0164a5;
}
.sidebar-link-expanded.active .sidebar-icon-expanded {
  color: #fff !important;
}
.sidebar-link-expanded .sidebar-svg {
  filter: none;
}
.sidebar-link-expanded.active .sidebar-svg {
  filter: none;
}
.sidebar-menu-label-expanded {
  /* Ensures label is always visible in expanded mode */
  display: inline-block;

  font-size: 1.08rem;
  font-weight: 500;
  color: #0164a5;
}

.sidebar-link-expanded:not(.active):hover {
  background: #fff;
  color: #0164a5;
}
.sidebar-link-expanded:not(.active):hover .sidebar-menu-label-expanded {
  color: #0164a5;
}
.sidebar-link-expanded:not(.active):hover .sidebar-icon-expanded {
  color: #0164a5;
}
.sidebar-link-expanded:not(.active):hover .sidebar-svg {
  filter: none;
}

.logout-confirm-overlay {
  z-index: 1000;
}

@media (max-width: 425px) {
  .sidebar:not(.expanded) {
    display: none;
  }
}
</style>
