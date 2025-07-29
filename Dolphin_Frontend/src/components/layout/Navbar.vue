<template>
  <nav class="navbar">
    <div class="navbar-left">
      <span class="navbar-page">{{ pageTitle }}</span>
    </div>
    <div class="navbar-actions">
      <!-- Show bell only if not superadmin -->
      <router-link
        v-if="roleName !== 'superadmin'"
        to="/get-notification"
        style="display: flex; align-items: center; position: relative"
      >
        <span
          style="
            height: 36px;
            width: 34px;
            vertical-align: middle;
            margin-right: 8px;
            cursor: pointer;
            display: inline-block;
            position: relative;
          "
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="34"
            height="36"
            viewBox="0 0 256 256"
          >
            <g
              style="stroke: none; fill: none"
              transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)"
            >
              <path
                d="M 83.25 74.548 H 6.75 c -1.536 0 -2.864 -0.988 -3.306 -2.457 c -0.441 -1.468 0.122 -3.022 1.401 -3.868 c 0.896 -0.594 1.954 -1.152 3.233 -1.707 c 5.52 -2.514 6.42 -16.025 7.144 -26.882 c 0.182 -2.74 0.355 -5.327 0.59 -7.664 c 1.926 -12.752 8.052 -20.942 18.223 -24.424 C 35.767 3.067 40.169 0 45 0 s 9.233 3.067 10.964 7.546 c 10.171 3.482 16.298 11.671 18.214 24.352 c 0.245 2.409 0.416 4.996 0.6 7.736 c 0.723 10.857 1.624 24.368 7.168 26.893 c 1.255 0.544 2.313 1.102 3.21 1.696 c 1.279 0.846 1.842 2.4 1.4 3.868 C 86.114 73.56 84.785 74.548 83.25 74.548 z M 45 2.934 c -3.818 0 -7.279 2.556 -8.416 6.215 l -0.228 0.733 l -0.732 0.231 c -9.568 3.018 -15.096 10.287 -16.9 22.224 c -0.221 2.216 -0.392 4.779 -0.573 7.493 c -0.816 12.242 -1.74 26.117 -8.88 29.368 c -1.129 0.49 -2.064 0.982 -2.806 1.473 c -0.265 0.175 -0.26 0.409 -0.21 0.575 c 0.051 0.168 0.177 0.368 0.496 0.368 h 76.5 c 0.318 0 0.445 -0.2 0.496 -0.368 c 0.05 -0.166 0.054 -0.4 -0.209 -0.575 h -0.001 c -0.741 -0.491 -1.677 -0.983 -2.782 -1.462 c -7.163 -3.261 -8.088 -17.137 -8.905 -29.379 c -0.181 -2.714 -0.352 -5.277 -0.582 -7.565 c -1.795 -11.864 -7.323 -19.134 -16.891 -22.151 l -0.732 -0.231 L 53.416 9.15 C 52.279 5.49 48.818 2.934 45 2.934 z"
                style="fill: rgb(0, 0, 0)"
              />
              <path
                d="M 33.257 78.292 C 33.277 84.75 38.536 90 45 90 c 6.463 0 11.723 -5.25 11.743 -11.708 H 33.257 z M 45 87.066 c -3.816 0 -7.063 -2.443 -8.285 -5.843 h 16.57 C 52.063 84.623 48.816 87.066 45 87.066 z"
                style="fill: rgb(0, 0, 0)"
              />
            </g>
          </svg>
          <span
            v-if="notificationCount > 0"
            class="navbar-badge"
            >{{ notificationCount }}</span
          >
        </span>
      </router-link>
      <!-- Make avatar, username, chevron a single clickable button -->
      <div
        class="navbar-profile-btn"
        ref="dropdownWrapper"
        @click="toggleDropdown"
        tabindex="0"
        @keydown.enter="toggleDropdown"
      >
        <span class="navbar-avatar">{{ displayName.charAt(0) }}</span>
        <span
          class="navbar-username"
          v-show="!isVerySmallScreen"
          >{{ displayName }}</span
        >
        <img
          v-if="!dropdownOpen"
          src="@/assets/images/VectorDown.svg"
          alt="Open"
          class="navbar-chevron"
        />
        <img
          v-else
          src="@/assets/images/VectorUp.svg"
          alt="Close"
          class="navbar-chevron"
        />
        <transition name="fade">
          <div
            v-if="dropdownOpen"
            class="navbar-dropdown"
            ref="dropdown"
          >
            <div
              class="navbar-dropdown-item"
              v-if="roleName"
              @click="goToProfile"
            >
              <i class="fas fa-user"></i>
              Profile
            </div>
            <div
              class="navbar-dropdown-item"
              @click="
                $router.push({ name: 'ManageSubscription' });
                dropdownOpen = false;
              "
            >
              <i class="fas fa-credit-card"></i>
              Manage Subscriptions
            </div>
            <div
              class="navbar-dropdown-item"
              @click="confirmLogout"
            >
              <i class="fas fa-sign-out-alt"></i>
              Logout
            </div>
          </div>
        </transition>
      </div>
    </div>
    <div
      v-if="showLogoutConfirm"
      class="logout-confirm-overlay"
    >
      <div class="logout-confirm-dialog">
        <div class="logout-confirm-title">Are you sure you want to logout?</div>
        <div class="logout-confirm-actions">
          <button
            class="btn btn-secondary"
            @click="handleLogoutYes"
          >
            Yes
          </button>
          <button
            class="btn btn-primary"
            @click="handleLogoutCancel"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import authMiddleware from '@/middleware/authMiddleware';
import '@/assets/global.css';

export default {
  name: 'Navbar',
  data() {
    return {
      dropdownOpen: false,
      showLogoutConfirm: false,
      roleName: authMiddleware.getRole(),
      isVerySmallScreen: false,
      notificationCount: 0,
    };
  },
  computed: {
    pageTitle() {
      const routeName = this.$route.name;
      if (routeName === 'UserPermission') {
        return 'Users + Permission';
      }
      if (routeName === 'ScheduleClassTraining') {
        return 'Schedule Classes/Training';
      }
      if (routeName === 'LeadDetail') {
        let contact = this.$route.query.contact || '';
        if (contact) return `${contact} Details`;
        return 'Lead Details';
      }
      if (routeName === 'OrganizationDetail') {
        const orgName = this.$route.params.orgName || '';
        if (orgName) return `${orgName} Organization Details`;
        return 'Organization Details';
      }
      if (routeName === 'OrganizationEdit') {
        const orgName = this.$route.params.orgName || '';
        if (orgName) return `${orgName} Organization Details`;
        return 'Organization Details';
      }
      if (routeName === 'BillingDetails') {
        const orgName = this.$route.query.orgName || '';
        if (orgName) return `${orgName} Organization Details`;
        return 'Organization Details';
      }
      if (routeName === 'SendAssessment') {
        return 'Send Assessment';
      }
      if (routeName === 'ScheduleDemo') {
        return 'Schedule Demo';
      }
      if (routeName === 'Assessments') {
        return 'Assessments';
      }
      if (routeName === 'TrainingResources') {
        return 'Training & Resources';
      }
      if (routeName === 'MyOrganization') {
        return 'My Organization';
      }
      if (routeName === 'Notifications' || routeName === 'GetNotification') {
        return 'Notification';
      }
      if (routeName === 'Organizations') {
        return 'Organizations';
      }
      if (routeName === 'Leads') {
        return 'Leads';
      }
      if (routeName === 'ThankYou') {
        return 'Thank You';
      }
      if (routeName === 'Login') {
        return 'Login';
      }
      if (routeName === 'LeadCapture') {
        return 'Lead Capture';
      }
      if (routeName === 'ManageSubscription') {
        return 'Manage Subscription';
      }
      if (routeName === 'SubscriptionPlans') {
        return 'Subscription Plans';
      }
      if (routeName === 'AssessmentSummary') {
        const assessmentId = this.$route.params.assessmentId;
        return assessmentId
          ? `Assessment ${assessmentId} Summary`
          : 'Assessment Summary';
      }
      if (routeName === 'Profile') {
        return 'Profile';
      }
      return this.$route.name || '';
    },
    displayName() {
      return localStorage.getItem('userName') || this.roleName;
    },
    role() {
      return authMiddleware.getRole();
    },
  },
  methods: {
    toggleDropdown() {
      this.dropdownOpen = !this.dropdownOpen;
    },
    handleClickOutside(event) {
      if (
        this.dropdownOpen &&
        this.$refs.dropdownWrapper &&
        !this.$refs.dropdownWrapper.contains(event.target)
      ) {
        this.dropdownOpen = false;
      }
    },
    confirmLogout(event) {
      event.stopPropagation();
      this.showLogoutConfirm = true;
      this.dropdownOpen = false;
    },
    handleLogoutYes() {
      // If impersonating, revert to superadmin instead of full logout
      if (localStorage.getItem('superAuthToken')) {
        localStorage.setItem(
          'authToken',
          localStorage.getItem('superAuthToken')
        );
        localStorage.setItem('role', localStorage.getItem('superRole'));
        localStorage.setItem('userName', localStorage.getItem('superUserName'));
        localStorage.setItem('userId', localStorage.getItem('superUserId'));
        localStorage.removeItem('superAuthToken');
        localStorage.removeItem('superRole');
        localStorage.removeItem('superUserName');
        localStorage.removeItem('superUserId');
        this.showLogoutConfirm = false;
        this.$router.push('/user-permission');
      } else {
        // Normal logout
        localStorage.clear();
        this.showLogoutConfirm = false;
        this.$router.push({ name: 'Login' });
      }
    },
    handleLogoutCancel() {
      this.showLogoutConfirm = false;
    },
    logout() {
      this.showLogoutConfirm = false;
      this.$router.push({ name: 'Login' });
    },
    checkScreen() {
      this.isVerySmallScreen = window.innerWidth <= 420;
    },
    updateNotificationCount() {
      const count = Number(localStorage.getItem('notificationCount'));
      this.notificationCount = isNaN(count) ? 0 : count;
    },
    goToProfile() {
      this.dropdownOpen = false;
      // Use router push with name 'Profile'
      this.$router.push({ name: 'Profile' });
    },
  },
  watch: {
    showLogoutConfirm(newValue) {
      if (newValue) {
        document.body.classList.add('logout-overlay-active');
      } else {
        document.body.classList.remove('logout-overlay-active');
      }
    },
  },
  mounted() {
    document.addEventListener('mousedown', this.handleClickOutside);
    window.addEventListener('resize', this.checkScreen);
    this.checkScreen();
    this.updateNotificationCount();
    window.addEventListener('storage', this.updateNotificationCount);
    window.addEventListener('focus', this.updateNotificationCount);
    if (this.showLogoutConfirm) {
      document.body.classList.add('logout-overlay-active');
    }
  },
  beforeDestroy() {
    document.removeEventListener('mousedown', this.handleClickOutside);
    window.removeEventListener('resize', this.checkScreen);
    window.removeEventListener('storage', this.updateNotificationCount);
    window.removeEventListener('focus', this.updateNotificationCount);
    document.body.classList.remove('logout-overlay-active');
  },
};
</script>

<style scoped>
.navbar,
.navbar-left,
.navbar-right,
.navbar-dropdown,
.navbar-dropdown-item {
  box-sizing: border-box;
}

.navbar {
  width: calc(100vw - 50px);
  height: 70px;
  left: 50px;
  border: 0.6px solid #f0f0f0;
  border-radius: 1px;
  background: #fafafa;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: fixed;
  top: 0;
  z-index: 11;
  margin: 0;
  padding: 0 24px;
  max-width: 100vw;
  overflow-x: auto;
}
.navbar-left {
  display: flex;
  align-items: center;
  gap: 16px;
}
.navbar-actions {
  display: flex;
  align-items: center;
  gap: 0; /* No gap between bell and right section */
}
.navbar-page {
  position: static;
  font-family: 'Helvetica Neue LT Std', sans-serif;
  font-style: normal;
  font-weight: 500;
  font-size: 28px;
  line-height: 32px;
  letter-spacing: 0.04em;
  color: #0f0f0f;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 60vw;
  min-width: 0;
}
.navbar-right {
  display: flex;
  align-items: center;
  gap: 14px;
  color: #646464;
  font-size: 1rem;
  position: relative;
  min-width: 0;
  flex-shrink: 1;
  flex-wrap: wrap;
}
.navbar-avatar {
  width: 38px;
  height: 38px;
  background: #0164a5;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.2rem;
  margin-right: 6px;
  margin-left: 6px;
  position: static;
  z-index: 1;
}
.navbar-username {
  font-weight: 500;
  color: #222;
  margin-right: 4px;
}
.navbar-profile-btn {
  display: flex;
  align-items: center;

  color: #646464;
  font-size: 1rem;
  position: relative;
  min-width: 0;
  flex-shrink: 1;
  flex-wrap: wrap;
  cursor: pointer;
  border-radius: 24px;
  padding: 2px 8px 2px 2px;
  transition: background 0.13s;
  user-select: none;
}
.navbar-profile-btn:focus,
.navbar-profile-btn:hover {
  background: #f5f5f5;
}
.navbar-chevron {
  width: 18px;
  height: 18px;
  margin-left: 4px;
  display: inline-block;
  vertical-align: middle;
}
.navbar-dropdown {
  position: fixed;
  top: 70px;
  right: 48px;
  min-width: 160px;
  background: #fff;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
  border-radius: 24px;
  padding: 16px 0;
  z-index: 2000;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  animation: dropdown-fade-in 0.18s;
}
.navbar-dropdown-item {
  padding: 12px 20px;
  font-size: 1rem;
  color: #222;
  cursor: pointer;
  user-select: none;
  transition: background 0.15s;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.navbar-dropdown-item:first-child {
  cursor: default;
}
.navbar-dropdown-item:hover {
  background: #f5f5f5;
}
@keyframes dropdown-fade-in {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}

/* Logout confirmation dialog styles */
.logout-confirm-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  width: 100vw;
  height: 100vh;
  pointer-events: auto;
}
.logout-confirm-dialog {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
  padding: 32px 32px 24px 32px;
  min-width: 280px;
  min-height: 120px;
  max-width: 90vw;
  max-height: 90vh;
  box-sizing: border-box;
  text-align: center;
  z-index: 9999;
}
.logout-confirm-title {
  font-size: 1.1rem;
  margin-bottom: 20px;
  color: #222;
}
.logout-confirm-actions {
  display: flex;
  justify-content: center;
  gap: 16px;
}
.btn {
  padding: 8px 20px;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
}
.btn-danger {
  background: #e53935;
  color: #fff;
}
.btn-secondary {
  background: #f5f5f5;
  color: #222;
}
.navbar-badge {
  position: absolute;
  top: 0;
  left: 15px;
  right: 0;
  min-width: 10px;
  height: 18px;
  background: #e53935;
  color: #fff;
  font-size: 0.85rem;
  font-weight: bold;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
  pointer-events: none;
  z-index: 1;
}

@media (max-width: 900px) {
  .navbar {
    width: calc(100vw - 38px);
    left: 38px;
    height: 54px;
    min-height: 54px;
    max-height: 54px;
    padding: 0 8px;
  }
  .navbar-avatar {
    width: 28px;
    height: 28px;
    font-size: 1rem;
  }
  .navbar-page {
    font-size: 18px;
    line-height: 22px;
  }
}
@media (max-width: 420px) {
  .navbar-username {
    display: none !important;
  }
}

/* New style to disable pointer events on main content when overlay is active */
body.logout-overlay-active .main-content {
  pointer-events: none;
}
</style>

<style>
body,
#app {
  margin: 0 !important;
  padding: 0 !important;
}
</style>
