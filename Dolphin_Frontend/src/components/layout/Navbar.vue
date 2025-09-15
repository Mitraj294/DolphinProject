<template>
  <ConfirmDialog />
  <nav
    v-bind="$attrs"
    class="navbar"
  >
    <div class="navbar-left">
      <span class="navbar-page">{{ pageTitle }}</span>
    </div>
    <div class="navbar-actions">
      <!-- Show bell only if not superadmin -->
      <router-link
        v-if="!['superadmin', 'salesperson'].includes(roleName)"
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
              v-if="roleName != 'superadmin'"
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
  </nav>
</template>

<script>
import authMiddleware from '@/middleware/authMiddleware';
import '@/assets/global.css';
import storage from '@/services/storage';
import axios from 'axios';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
export default {
  name: 'Navbar',
  inheritAttrs: false,
  props: {
    sidebarExpanded: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    ConfirmDialog,
  },
  data() {
    return {
      dropdownOpen: false,
      // showLogoutConfirm removed — PrimeVue Confirm will be used
      overridePageTitle: null,
      roleName: authMiddleware.getRole(),
      isVerySmallScreen: false,
      notificationCount: 0,

      leadNameCache: {},
      leadNameFetching: {},

      orgNameCache: {},
      orgNameFetching: {},
      assessmentNameCache: {},
      assessmentNameFetching: {},
      // lifecycle flag to avoid mutating state after unmount
      isNavbarAlive: false,
      // bound handlers for window events (so `this` stays correct)
      _boundFetchUnread: null,
      _boundUpdateNotificationCount: null,
      _boundAuthUpdated: null,
    };
  },
  setup() {
    const confirm = useConfirm();
    return { confirm };
  },
  computed: {
    pageTitle() {
      if (this.overridePageTitle) return this.overridePageTitle;
      const routeName = this.$route.name;
      if (routeName === 'UserPermission') {
        return 'Users + Permission';
      }
      if (routeName === 'AddUser') {
        return 'Add User';
      }
      if (routeName === 'ScheduleClassTraining') {
        return 'Schedule Classes/Training';
      }
      if (routeName === 'LeadDetail') {
        let contact = this.$route.query.contact || '';
        if (contact) return `${contact} Details`;
        return 'Lead Details';
      }
      if (routeName === 'EditLead') {
        // Try to display the lead's name when route param id is present
        const leadId = this.$route.params.id || this.$route.query.id;
        if (leadId) {
          const cached = this.leadNameCache[leadId];
          if (cached) return `Edit Lead : ${cached}`;
          // If we're already fetching, show a placeholder
          if (this.leadNameFetching[leadId]) return 'Edit Lead';
          // Trigger async fetch (non-blocking) and return base title for now
          if (this.isNavbarAlive) this.fetchLeadName(leadId);
          return 'Edit Lead';
        }
        return 'Edit Lead';
      }
      if (routeName === 'OrganizationDetail') {
        return 'Organization Details';
      }
      if (routeName === 'OrganizationEdit') {
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
      if (routeName === 'AssessmentSummary') {
        // Prefer an assessment object passed via route params/query (SPA navigation)
        const assessmentParam =
          this.$route.params.assessment || this.$route.query.assessment || null;
        if (assessmentParam && assessmentParam.name) {
          console.debug(
            'Navbar: using assessment from route params for title',
            assessmentParam
          );
          return `Assessment Summary :  ${assessmentParam.name} `;
        }
        // Try cached name
        const assessmentId =
          this.$route.params.assessmentId || this.$route.params.id;
        if (assessmentId) {
          const cached = this.assessmentNameCache[assessmentId];
          if (cached) {
            console.debug(
              `Navbar: using cached assessment name for id=${assessmentId}`
            );
            return `Assessment Summary :  ${cached}`;
          }
          if (this.assessmentNameFetching[assessmentId]) {
            console.debug(
              `Navbar: assessment name fetch already in progress for id=${assessmentId}`
            );
            return 'Assessment Summary';
          }
          // trigger async fetch (non-blocking)
          if (this.isNavbarAlive) this.fetchAssessmentName(assessmentId);
          return 'Assessment Summary';
        }
        return 'Assessment Summary';
      }
      if (routeName === 'TrainingResources') {
        return 'Training & Resources';
      }
      if (routeName === 'MyOrganization') {
        const orgName = storage.get('organization_name');
        return `My Organization${orgName ? ': ' + orgName : ''}`;
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
      if (routeName === 'MemberListing') {
        return 'Member Listing';
      }
      if (routeName === 'Members') {
        return 'Member Listing';
      }

      if (routeName === 'Profile') {
        return 'Profile';
      }
      return this.$route.name || '';
    },
    displayName() {
      // Always show the CURRENT (impersonated or real) user's info
      const firstName = storage.get('first_name');
      const lastName = storage.get('last_name');
      if ((firstName && firstName.trim()) || (lastName && lastName.trim())) {
        return `${firstName ? firstName.trim() : ''}${
          lastName && lastName.trim() ? ' ' + lastName.trim() : ''
        }`.trim();
      }
      const userName = storage.get('userName');
      if (userName && userName.trim()) return userName.trim();
      // Fallback: try email if available
      const email = storage.get('email');
      if (email && email.trim()) return email.trim();
      return 'User';
    },
    role() {
      return authMiddleware.getRole();
    },
  },

  methods: {
    // simple debounce helper for methods invoked by window events
    _debounce(fn, wait = 200) {
      let t = null;
      return (...args) => {
        if (t) clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
      };
    },
    async fetchUnreadCount() {
      try {
        let token = storage.get('authToken');
        if (token && typeof token === 'object' && token.token)
          token = token.token;
        if (typeof token !== 'string') token = '';
        const config = token
          ? { headers: { Authorization: `Bearer ${token}` } }
          : {};
        const res = await axios.get('/api/notifications/unread', config);
        let unread = 0;
        if (res && res.data) {
          if (Array.isArray(res.data)) unread = res.data.length;
          else if (Array.isArray(res.data.unread))
            unread = res.data.unread.length;
          else if (Array.isArray(res.data.notifications)) {
            unread = res.data.notifications.filter((n) => !n.read_at).length;
          } else {
            console.warn(
              'Unexpected response format for unread notifications',
              res.data
            );
          }
        }
        this.notificationCount = unread;
        storage.set('notificationCount', String(unread));
      } catch (e) {
        console.warn('Failed to fetch initial unread count', e);
      }
    },

    async fetchLeadName(leadId) {
      if (!leadId) return null;
      // prevent duplicate concurrent fetches per id
      if (this.leadNameFetching[leadId]) return;
      this.leadNameFetching[leadId] = true;
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        let token = storage.get('authToken');
        if (token && typeof token === 'object' && token.token)
          token = token.token;
        const config = token
          ? { headers: { Authorization: `Bearer ${token}` } }
          : {};
        const res = await axios.get(
          `${API_BASE_URL}/api/leads/${leadId}`,
          config
        );
        const payload = res && res.data ? res.data : null;
        const leadObj = payload && payload.lead ? payload.lead : payload;
        if (leadObj) {
          const name =
            (
              (leadObj.first_name || '') +
              ' ' +
              (leadObj.last_name || '')
            ).trim() ||
            leadObj.contact ||
            leadObj.email ||
            '';
          if (name && this.isNavbarAlive) {
            // assign directly to reactive object
            this.leadNameCache[leadId] = name;
          }
        }
      } catch (e) {
        console.warn(`Failed to fetch lead name for id=${leadId}`, e);
      } finally {
        if (this.isNavbarAlive) {
          delete this.leadNameFetching[leadId];
        }
      }
      return null;
    },
    async fetchSummary() {
      if (!this.assessmentId) return;
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const res = await axios.get(
          `${API_BASE_URL}/api/assessment/${this.assessmentId}/summary`
        );
        const data = res.data;

        // Set navbar title from the fetched data
        if (data.assessment && data.assessment.name) {
          const assessmentName = data.assessment.name;
          // Use the event bus to update the navbar title
          if (this.$root && this.$root.$emit) {
            this.$root.$emit(
              'page-title-override',
              `Assessment ${assessmentName} Summary`
            );
          }
        }

        // Transform backend data to frontend rows
        this.rows = (data.members || []).map((member) => ({
          name:
            member.name ||
            (member.member_id ? `Member #${member.member_id}` : 'Unknown'),
          result:
            member.answers && member.answers.length > 0
              ? 'Submitted'
              : 'Pending',
          assessment: (member.answers || []).map((a) => ({
            question: a.question,
            answer: a.answer,
          })),
        }));
        this.summary = data.summary || {
          total_sent: 0,
          submitted: 0,
          pending: 0,
        };
      } catch (e) {
        this.rows = [];
        this.summary = { total_sent: 0, submitted: 0, pending: 0 };
        console.error('Failed to fetch assessment summary:', e);
      }
    },

    async fetchAssessmentName(assessmentId) {
      if (!assessmentId) return null;
      if (this.assessmentNameFetching[assessmentId]) return;
      this.assessmentNameFetching[assessmentId] = true;
      console.debug(`Navbar: fetchAssessmentName start id=${assessmentId}`);
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        let token = storage.get('authToken');
        if (token && typeof token === 'object' && token.token)
          token = token.token;
        const config = token
          ? { headers: { Authorization: `Bearer ${token}` } }
          : {};

        // Try summary endpoint which usually contains assessment.name
        const res = await axios.get(
          `${API_BASE_URL}/api/assessment/${assessmentId}/summary`,
          config
        );
        const data = res && res.data ? res.data : null;
        const name =
          data && data.assessment && data.assessment.name
            ? data.assessment.name
            : null;
        if (name && this.isNavbarAlive) {
          this.assessmentNameCache[assessmentId] = name;
          console.debug(
            `Navbar: cached assessment name for id=${assessmentId} -> ${name}`
          );
          // Emit override so pages depending on event update immediately
          if (this.$root && this.$root.$emit) {
            this.$root.$emit(
              'page-title-override',
              `Assessment ${name} Summary`
            );
          }
        }
      } catch (e) {
        console.warn(
          `Navbar: fetchAssessmentName failed for id=${assessmentId}`,
          e && e.message ? e.message : e
        );
      } finally {
        if (this.isNavbarAlive)
          delete this.assessmentNameFetching[assessmentId];
      }
      return null;
    },
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
      this.dropdownOpen = false;
      this.confirm.require({
        message: 'Are you sure you want to logout?',
        header: 'Confirm Logout',
        icon: 'pi pi-sign-out',
        acceptLabel: 'Yes',
        rejectLabel: 'Cancel',
        acceptProps: {
          style: 'background-color: red; color: white; font-weight: bold;',
        },
        rejectProps: {
          style: 'background-color: gray;',
        },
        accept: () => {
          this.handleLogoutYes();
        },
        reject: () => {
          // No action needed on cancel
          this.dropdownOpen = false;
        },
      });
    },
    handleLogoutYes() {
      // If impersonating, revert to superadmin instead of full logout
      if (storage.get('superAuthToken')) {
        storage.set('authToken', storage.get('superAuthToken'));
        storage.set('role', storage.get('superRole'));
        storage.set('userName', storage.get('superUserName'));
        storage.set('userId', storage.get('superUserId'));
        // Restore first_name and last_name from superFirstName/superLastName
        storage.set('first_name', storage.get('superFirstName') || '');
        storage.set('last_name', storage.get('superLastName') || '');
        // Remove superadmin impersonation keys
        storage.remove('superAuthToken');
        storage.remove('superRole');
        storage.remove('superUserName');
        storage.remove('superUserId');
        storage.remove('superFirstName');
        storage.remove('superLastName');
        this.$router.push('/user-permission');
      } else {
        // Normal logout
        storage.clear();
        this.$router.push({ name: 'Login' });
      }
    },
    // handleLogoutCancel and logout removed: PrimeVue confirm used instead
    checkScreen() {
      this.isVerySmallScreen = window.innerWidth <= 420;
    },
    updateNotificationCount() {
      const count = Number(storage.get('notificationCount'));
      this.notificationCount = isNaN(count) ? 0 : count;
    },
    goToProfile() {
      this.dropdownOpen = false;
      // Use router push with name 'Profile'
      this.$router.push({ name: 'Profile' });
    },
  },

  mounted() {
    // mark alive so async fetches can safely write to state
    this.isNavbarAlive = true;
    document.addEventListener('mousedown', this.handleClickOutside);
    window.addEventListener('resize', this.checkScreen);
    this.checkScreen();
    this.updateNotificationCount();
    // initial fetch of unread count: if we have an auth token (normal login
    // or impersonation), fetch unread notifications immediately so the
    // badge is correct after a reload/impersonation.
    try {
      const token = storage.get('authToken');
      if (token) {
        this.fetchUnreadCount();
      }
    } catch (e) {
      console.warn('Failed to fetch initial unread count', e);
    }
    // bind handlers so `this` is preserved when invoked by window events
    this._boundFetchUnread = this.fetchUnreadCount.bind(this);
    this._boundUpdateNotificationCount =
      this.updateNotificationCount.bind(this);
    // When auth context changes (impersonation), refresh role and badge.
    this._boundAuthUpdated = this._debounce(() => {
      this.roleName = authMiddleware.getRole();
      this.updateNotificationCount();
      try {
        this.fetchUnreadCount();
      } catch (e) {
        console.warn('Failed to fetch unread count after auth update', e);
      }
      this.$forceUpdate && this.$forceUpdate();
    }, 500);
    // refresh when other components dispatch a notification update (mark read actions)
    // Use a debounced wrapper to avoid multiple simultaneous network calls
    this._boundFetchUnread = this._debounce(
      this.fetchUnreadCount.bind(this),
      500
    );
    window.addEventListener('notification-updated', this._boundFetchUnread);
    // refresh when auth context changes (impersonation/revert)
    window.addEventListener('auth-updated', this._boundAuthUpdated);
    // storage event (cross-tab) should update local count
    window.addEventListener('storage', this._boundUpdateNotificationCount);

    // no-op: logout overlay removed in favor of PrimeVue Confirm

    // Listen for page title overrides from pages
    if (this.$root && this.$root.$on) {
      this.$root.$on('page-title-override', (val) => {
        this.overridePageTitle = val;
      });
    }

    // If we landed directly on an AssessmentSummary route, attempt to fetch its name
    try {
      const rn = this.$route && this.$route.name;
      if (rn === 'AssessmentSummary') {
        const assessmentId =
          this.$route.params.assessmentId || this.$route.params.id;
        if (assessmentId && this.isNavbarAlive)
          this.fetchAssessmentName(assessmentId);
      }
    } catch (e) {
      console.warn('Navbar: failed to fetch assessment name on mount', e);
    }

    // Watch route changes to trigger fetches for AssessmentSummary pages
    this.$watch(
      () => this.$route && this.$route.fullPath,
      (newVal, oldVal) => {
        try {
          const rn = this.$route && this.$route.name;
          if (rn === 'AssessmentSummary') {
            const aid =
              this.$route.params.assessmentId || this.$route.params.id;
            if (aid && this.isNavbarAlive) this.fetchAssessmentName(aid);
          }
        } catch (e) {
          console.warn(
            'Navbar: failed to fetch assessment name on route change',
            e
          );
        }
      }
    );
  },
  beforeDestroy() {
    // prevent background async callbacks from mutating state after unmount
    this.isNavbarAlive = false;
    document.removeEventListener('mousedown', this.handleClickOutside);
    window.removeEventListener('resize', this.checkScreen);
    if (this._boundUpdateNotificationCount) {
      window.removeEventListener('storage', this._boundUpdateNotificationCount);
    }
    if (this._boundFetchUnread) {
      window.removeEventListener(
        'notification-updated',
        this._boundFetchUnread
      );
    }
    if (this._boundAuthUpdated) {
      window.removeEventListener('auth-updated', this._boundAuthUpdated);
    }
    document.body.classList.remove('logout-overlay-active');
    if (this.$root && this.$root.$off) this.$root.$off('page-title-override');
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
  min-width: 260px;
  max-width: 100vw;
  overflow-x: auto;
}
@media (max-width: 425px) {
  .navbar {
    min-width: 425px;
    padding-right: 10px;
    padding-left: 7px;
    justify-content: space-between;
  }
}
.navbar-left {
  display: flex;
  align-items: center;
  gap: 16px;
  min-width: 130px;
}
.navbar-actions {
  display: flex;
  align-items: center;
  gap: 0;
  min-width: 180px;
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
@media (max-width: 675px) {
  .navbar-page {
    font-size: 24px;
    line-height: 24px;
  }
}
@media (max-width: 425px) {
  .navbar-page {
    font-size: 18px;
    line-height: 24px;
    max-width: 180px;
  }
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

  cursor: pointer;
  border-radius: 24px;
  padding: 2px 8px 2px 2px;
  transition: background 0.13s;
  user-select: none;
}
@media (max-width: 420px) {
  .navbar-profile-btn {
    padding: 2px 4px 2px 2px;
  }
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

@media (max-width: 420px) {
  .navbar-username {
    display: none !important;
  }
}

.p-button {
  color: #ffffff;
  background: #e53935;
  border: 1px solid #e53935;
  padding: 0.75rem 1.25rem;
  font-size: 1rem;
  transition: background-color 0.2s, color 0.2s, border-color 0.2s,
    box-shadow 0.2s;
  border-radius: 6px;
  outline-color: transparent;
}
.p-button:not(:disabled):hover {
  background: #ff0000;
  color: #ffffff;
  border-color: #e00f0f;
}
.p-button:disabled {
  background: #ff0000;
  opacity: 0.6;
  cursor: not-allowed;
}
.p-button.p-button-text {
  background: transparent;
  color: #e00f0f;
  border: none;
  padding: 0.75rem 1.25rem;
}
.p-button:hover {
  background: #ff0000;
  color: #ffffff;
  border-color: #e00f0f;
}
</style>

<style>
body,
#app {
  margin: 0 !important;
  padding: 0 !important;
}
</style>
