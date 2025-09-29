<template>
  <ConfirmDialog />
  <nav
    v-bind="$attrs"
    class="navbar"
    :class="{ 'sidebar-expanded': sidebarExpanded }"
  >
    <div class="navbar-left">
      <div class="sidebar-logo1">
        <img
          src="@/assets/images/Logo.svg"
          alt="Logo"
          style="width: 25px; height: 25px; object-fit: contain"
        />
      </div>
      <span class="navbar-page">{{ pageTitle }}</span>
    </div>
    <div class="navbar-actions">
      <!-- Show bell for every role except superadmin -->
      <router-link
        v-if="roleName !== 'superadmin'"
        to="/get-notification"
        style="display: flex; align-items: center; position: relative"
      >
        <span
          :style="{
            height: isVerySmallScreen ? '28px' : '36px',
            width: isVerySmallScreen ? '26px' : '34px',
            verticalAlign: 'middle',
            marginRight: '8px',
            cursor: 'pointer',
            display: 'inline-block',
            position: 'relative',
          }"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            :width="isVerySmallScreen ? 26 : 34"
            :height="isVerySmallScreen ? 28 : 36"
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
            <div class="navbar-dropdown-item0">
              <i class="fas fa-user"></i>
              <div
                class="navbar-dropdown-item"
                v-if="roleName"
                @click="goToProfile"
              >
                Profile
              </div>
            </div>
            <div
              class="navbar-dropdown-item0"
              v-if="
                !['superadmin', 'dolphinadmin', 'salesperson'].includes(
                  roleName
                )
              "
              @click="
                $router.push({ name: 'ManageSubscription' });
                dropdownOpen = false;
              "
            >
              <i class="fas fa-credit-card"></i>
              <div
                class="navbar-dropdown-item"
                v-if="
                  !['superadmin', 'dolphinadmin', 'salesperson'].includes(
                    roleName
                  )
                "
                @click="
                  $router.push({ name: 'ManageSubscription' });
                  dropdownOpen = false;
                "
              >
                Manage Subscriptions
              </div>
            </div>
            <div class="navbar-dropdown-item0">
              <i class="fas fa-sign-out-alt"></i>
              <div
                class="navbar-dropdown-item"
                @click="confirmLogout"
              >
                Logout
              </div>
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
      overridePageTitle: null,
      roleName: authMiddleware.getRole(),
      isVerySmallScreen: false,
      notificationCount: 0,
      userFirstName: storage.get('first_name') || '',
      userLastName: storage.get('last_name') || '',
      userName: storage.get('userName') || '',
      userEmail: storage.get('email') || '',
      leadNameCache: {},
      leadNameFetching: {},
      orgNameCache: {},
      orgNameFetching: {},
      assessmentNameCache: {},
      assessmentNameFetching: {},
      isNavbarAlive: false,
      _boundFetchUnread: null,
      _boundUpdateNotificationCount: null,
      _boundAuthUpdated: null,
      _boundCountSync: null,
    };
  },
  setup() {
    const confirm = useConfirm();
    return { confirm };
  },
  computed: {
    pageTitle() {
      if (this.overridePageTitle) return this.overridePageTitle;
      const routeName = this.$route && this.$route.name;
      return this.titleForRoute(routeName);
    },
    displayName() {
      if (
        (this.userFirstName && this.userFirstName.trim()) ||
        (this.userLastName && this.userLastName.trim())
      ) {
        return `${this.userFirstName ? this.userFirstName.trim() : ''}${
          this.userLastName && this.userLastName.trim()
            ? ' ' + this.userLastName.trim()
            : ''
        }`.trim();
      }
      if (this.userName && this.userName.trim()) return this.userName.trim();

      if (this.userEmail && this.userEmail.trim()) return this.userEmail.trim();
      return 'User';
    },
    role() {
      return authMiddleware.getRole();
    },
  },
  methods: {
    titleForRoute(routeName) {
      if (!routeName)
        return this.$route && this.$route.name ? this.$route.name : '';

      const simpleMap = {
        UserPermission: 'Users + Permission',
        AddUser: 'Add User',
        ScheduleClassTraining: 'Schedule Classes/Training',
        SendAssessment: 'Send Assessment', // This was duplicated, but it's fine.
        SendAgreement: 'Send Agreement/Payment Link',
        ScheduleDemo: 'Schedule Demo',
        Assessments: 'Assessments',
        TrainingResources: 'Training & Resources',
        Notifications: 'Notification',
        GetNotification: 'Notification',
        Organizations: 'Organizations',
        Leads: 'Leads',
        ThankYou: 'Thank You',
        Login: 'Login',
        LeadCapture: 'Lead Capture',
        ManageSubscription: 'Manage Subscription',
        SubscriptionPlans: 'Subscription Plans',
        MemberListing: 'Member Listing',
        Members: 'Member Listing',
        Profile: 'Profile',
      };

      // Special-case: if ScheduleDemo and mode=followup, show 'Schedule Follow up'
      if (routeName === 'ScheduleDemo') {
        try {
          const mode =
            this.$route && this.$route.query && this.$route.query.mode;
          if (mode === 'followup') return 'Schedule Follow up';
        } catch (e) {
          console.debug('Navbar: error checking mode for ScheduleDemo', e);
        }
      }

      if (simpleMap[routeName]) return simpleMap[routeName];

      if (routeName === 'OrganizationDetail')
        return this.handleOrganizationDetailTitle();
      if (routeName === 'OrganizationEdit')
        return this.handleOrganizationEditTitle();
      if (routeName === 'LeadDetail') return this.handleLeadDetailTitle();
      if (routeName === 'EditLead') return this.handleEditLeadTitle();
      if (routeName === 'BillingDetails')
        return this.handleBillingDetailsTitle();
      if (routeName === 'AssessmentSummary')
        return this.handleAssessmentSummaryTitle();
      if (routeName === 'MyOrganization')
        return this.handleMyOrganizationTitle();

      return this.$route && this.$route.name ? this.$route.name : '';
    },
    handleOrganizationDetailTitle() {
      const organization_name =
        (this.$route && this.$route.query && this.$route.query.orgName) || '';
      if (organization_name)
        return `Organization Details : ${organization_name} `;

      // try to resolve from route params/query orgId and cache
      const orgId =
        (this.$route &&
          ((this.$route.params && this.$route.params.id) ||
            (this.$route.query && this.$route.query.orgId))) ||
        null;

      if (orgId) {
        const cached = this.orgNameCache[orgId];
        if (cached) return ` Organization Details : ${cached}`;
        if (this.orgNameFetching[orgId]) return 'Organization Details';
        if (this.isNavbarAlive) this.fetchOrgName(orgId);
      }

      return 'Organization Details';
    },
    handleOrganizationEditTitle() {
      const organization_name =
        (this.$route && this.$route.query && this.$route.query.orgName) || '';
      if (organization_name) return `Edit Organization : ${organization_name}`;

      const orgId =
        (this.$route &&
          ((this.$route.params && this.$route.params.id) ||
            (this.$route.query && this.$route.query.orgId))) ||
        null;

      if (orgId) {
        const cached = this.orgNameCache[orgId];
        if (cached) return `Edit Organization : ${cached}`;
        if (this.orgNameFetching[orgId]) return 'Edit Organization';
        if (this.isNavbarAlive) this.fetchOrgName(orgId);
      }

      return 'Edit Organization';
    },
    handleLeadDetailTitle() {
      const leadId =
        (this.$route &&
          ((this.$route.params && this.$route.params.id) ||
            (this.$route.query && this.$route.query.id))) ||
        null;
      if (!leadId) return 'Lead Detail';
      const cached = this.leadNameCache[leadId];
      if (cached) return `Lead Detail : ${cached}`;
      if (this.leadNameFetching[leadId]) return 'Lead Detail';
      if (this.isNavbarAlive) this.fetchLeadName(leadId);
      return 'Lead Detail';
    },
    handleEditLeadTitle() {
      const leadId =
        (this.$route &&
          ((this.$route.params && this.$route.params.id) ||
            (this.$route.query && this.$route.query.id))) ||
        null;
      if (!leadId) return 'Edit Lead';

      const cached = this.leadNameCache[leadId];
      if (cached) return `Edit Lead : ${cached}`;
      if (this.leadNameFetching[leadId]) return 'Edit Lead';
      if (this.isNavbarAlive) this.fetchLeadName(leadId);
      return 'Edit Lead';
    },
    handleBillingDetailsTitle() {
      const orgName =
        (this.$route && this.$route.query && this.$route.query.orgName) || '';
      if (orgName) return ` Organization Details :${orgName}`;

      const orgId =
        (this.$route &&
          ((this.$route.params && this.$route.params.id) ||
            (this.$route.query && this.$route.query.orgId))) ||
        null;

      if (orgId) {
        const cached = this.orgNameCache[orgId];
        if (cached) return `Organization Details : ${cached}`;
        if (this.orgNameFetching[orgId]) return 'Organization Details';
        if (this.isNavbarAlive) this.fetchOrgName(orgId);
      }

      return 'Organization Details';
    },
    handleAssessmentSummaryTitle() {
      const assessmentParam =
        (this.$route && this.$route.params && this.$route.params.assessment) ||
        (this.$route && this.$route.query && this.$route.query.assessment) ||
        null;
      if (assessmentParam && assessmentParam.name) {
        console.debug(
          'Navbar: using assessment from route params for title',
          assessmentParam
        );
        return `Assessment Summary :  ${assessmentParam.name} `;
      }

      const assessmentId =
        (this.$route &&
          ((this.$route.params && this.$route.params.assessmentId) ||
            (this.$route.params && this.$route.params.id))) ||
        null;
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
        if (this.isNavbarAlive) this.fetchAssessmentName(assessmentId);
        return 'Assessment Summary';
      }

      return 'Assessment Summary';
    },
    handleMyOrganizationTitle() {
      const orgName = storage.get('organization_name');
      return `My Organization${orgName ? ': ' + orgName : ''}`;
    },
    _debounce(fn, wait = 200) {
      let t = null;
      return (...args) => {
        if (t) clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
      };
    },
    async fetchUnreadCount() {
      try {
        // Only fetch unread notifications when the subscription is active (or unknown)
        const subStatus = storage.get('subscription_status');
        const currentRole = this.roleName || authMiddleware.getRole();
        const bypassRoles = ['dolphinadmin', 'superadmin', 'salesperson'];
        if (
          subStatus &&
          subStatus !== 'active' &&
          !bypassRoles.includes(currentRole)
        ) {
          // subscription not active: do not call notifications endpoint for billable roles
          this.notificationCount = 0;
          storage.set('notificationCount', String(0));
          return;
        }
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
    updateUserInfo() {
      this.userFirstName = storage.get('first_name') || '';
      this.userLastName = storage.get('last_name') || '';
      this.userName = storage.get('userName') || '';
      this.userEmail = storage.get('email') || '';
    },
    async fetchCurrentUser() {
      try {
        let token = storage.get('authToken');
        if (token && typeof token === 'object' && token.token)
          token = token.token;
        const config = token
          ? { headers: { Authorization: `Bearer ${token}` } }
          : {};

        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const res = await axios.get(`${API_BASE_URL}/api/user`, config);
        const user = res?.data || null;
        if (!user) return;

        // persist organization id/name if present on the user object so titles can use it
        if (user.organization_id) {
          const orgIdStr = String(user.organization_id);
          storage.set('organization_id', orgIdStr);
        }

        // if API returned an organization name, persist it (single source of truth)
        if (user.organization_name || user.organization) {
          const orgName = user.organization_name || user.organization;
          storage.set('organization_name', orgName);
          // prime simple in-memory cache for current org id if available
          if (user.organization_id)
            this.orgNameCache[String(user.organization_id)] = orgName;
        }

        if (this.isNavbarAlive) this.updateUserInfo();
      } catch (e) {
        console.debug(
          'Navbar: fetchCurrentUser failed',
          e && e.message ? e.message : e
        );
      }
    },
    async fetchLeadName(leadId) {
      if (!leadId) return null;
      if (this.leadNameFetching[leadId]) return;
      this.leadNameFetching[leadId] = true;
      try {
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
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
    async fetchOrgName(orgId) {
      if (!orgId) return null;
      if (this.orgNameFetching[orgId]) return null;
      this.orgNameFetching[orgId] = true;
      let name = null;
      try {
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const config = this._getAuthConfig();
        const res = await axios.get(
          `${API_BASE_URL}/api/organizations/${orgId}`,
          config
        );
        const data = res && res.data ? res.data : null;
        name =
          (data && (data.organization_name || data.name || data.orgName)) ||
          null;
        if (name && this.isNavbarAlive) this.orgNameCache[orgId] = name;
      } catch (e) {
        console.debug(`Navbar: fetchOrgName failed for id=${orgId}`, e);
      } finally {
        if (this.isNavbarAlive) delete this.orgNameFetching[orgId];
      }
      return name;
    },
    async fetchSummary() {
      if (!this.assessmentId) return;
      try {
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const res = await axios.get(
          `${API_BASE_URL}/api/assessment/${this.assessmentId}/summary`
        );
        const data = res.data;

        if (data.assessment && data.assessment.name) {
          const assessmentName = data.assessment.name;
          if (this.$root && this.$root.$emit) {
            this.$root.$emit(
              'page-title-override',
              `Assessment ${assessmentName} Summary`
            );
          }
        }

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
    _getAuthConfig() {
      let token = storage.get('authToken');
      if (token && typeof token === 'object' && token.token)
        token = token.token;
      if (typeof token !== 'string') token = '';
      return token ? { headers: { Authorization: `Bearer ${token}` } } : {};
    },
    async _fetchAssessmentSummary(assessmentId, config) {
      const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
      const res = await axios.get(
        `${API_BASE_URL}/api/assessment/${assessmentId}/summary`,
        config
      );
      return res && res.data ? res.data : null;
    },
    _processAssessmentName(assessmentId, name) {
      if (!name || !this.isNavbarAlive) return;
      this.assessmentNameCache[assessmentId] = name;
      console.debug(
        `Navbar: cached assessment name for id=${assessmentId} -> ${name}`
      );
      if (this.$root && this.$root.$emit) {
        this.$root.$emit('page-title-override', `Assessment ${name} Summary`);
      }
    },
    async fetchAssessmentName(assessmentId) {
      if (!assessmentId) return null;
      if (this.assessmentNameFetching[assessmentId]) return;
      this.assessmentNameFetching[assessmentId] = true;
      console.debug(`Navbar: fetchAssessmentName start id=${assessmentId}`);
      try {
        const config = this._getAuthConfig();
        const data = await this._fetchAssessmentSummary(assessmentId, config);
        const name =
          data && data.assessment && data.assessment.name
            ? data.assessment.name
            : null;
        this._processAssessmentName(assessmentId, name);
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
          this.dropdownOpen = false;
        },
      });
    },
    handleLogoutYes() {
      if (storage.get('superAuthToken')) {
        storage.set('authToken', storage.get('superAuthToken'));
        storage.set('role', storage.get('superRole'));
        storage.set('userName', storage.get('superUserName'));
        storage.set('userId', storage.get('superUserId'));
        storage.set('first_name', storage.get('superFirstName') || '');
        storage.set('last_name', storage.get('superLastName') || '');
        storage.remove('superAuthToken');
        storage.remove('superRole');
        storage.remove('superUserName');
        storage.remove('superUserId');
        storage.remove('superFirstName');
        storage.remove('superLastName');
        this.$router.push('/user-permission');
      } else {
        storage.clear();
        this.$router.push({ name: 'Login' });
      }
    },
    checkScreen() {
      this.isVerySmallScreen = window.innerWidth <= 425;
    },
    updateNotificationCount() {
      const count = Number(storage.get('notificationCount'));
      this.notificationCount = isNaN(count) ? 0 : count;
    },
    goToProfile() {
      this.dropdownOpen = false;
      this.$router.push({ name: 'Profile' });
    },
  },
  mounted() {
    this.isNavbarAlive = true;
    document.addEventListener('mousedown', this.handleClickOutside);
    window.addEventListener('resize', this.checkScreen);
    this.checkScreen();
    this.updateNotificationCount();
    this.updateUserInfo();
    try {
      const token = storage.get('authToken');
      if (token) {
        this.fetchUnreadCount();
      }
    } catch (e) {
      console.warn('Failed to fetch initial unread count', e);
    }
    this.fetchCurrentUser();
    this._boundFetchUnread = this.fetchUnreadCount.bind(this);
    this._boundUpdateNotificationCount =
      this.updateNotificationCount.bind(this);
    this._boundAuthUpdated = this._debounce(() => {
      this.roleName = authMiddleware.getRole();
      this.updateUserInfo();
      this.updateNotificationCount();
      try {
        this.fetchUnreadCount();
        this.fetchCurrentUser();
      } catch (e) {
        console.warn('Failed to fetch unread count after auth update', e);
      }
      this.$forceUpdate && this.$forceUpdate();
    }, 500);
    this._boundFetchUnread = this._debounce(
      this.fetchUnreadCount.bind(this),
      500
    );
    window.addEventListener('notification-updated', this._boundFetchUnread);
    window.addEventListener('auth-updated', this._boundAuthUpdated);
    window.addEventListener('storage', this._boundUpdateNotificationCount);
    this._boundCountSync = (event) => {
      const incoming = event?.detail?.count;
      if (typeof incoming !== 'number' || Number.isNaN(incoming)) return;
      this.notificationCount = incoming;
      storage.set('notificationCount', String(incoming));
    };
    window.addEventListener('notification-count-sync', this._boundCountSync);

    if (this.$root && this.$root.$on) {
      this.$root.$on('page-title-override', (val) => {
        this.overridePageTitle = val;
      });
    }

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
    if (this._boundCountSync) {
      window.removeEventListener(
        'notification-count-sync',
        this._boundCountSync
      );
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

.navbar.sidebar-expanded {
  width: calc(100vw - 200px);
}

@media (max-width: 425px) {
  .navbar.sidebar-expanded {
    width: calc(100vw + 1px);
  }
}

@media (max-width: 425px) {
  .navbar {
    width: calc(100vw + 1px);
    min-width: 320px;
    max-width: 100vw;
    margin: 0 1px 0 1px;
    padding: 0 0 0 8px;
    height: 70px;
    justify-content: space-between;
  }
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 130px;
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: 0;
  min-width: 80px;
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

@media (max-width: 425px) {
  .navbar-avatar {
    width: 28px;
    height: 28px;
    font-size: 1rem;
    margin-right: 4px;
  }
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

@media (max-width: 425px) {
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

@media (max-width: 425px) {
  .navbar-chevron {
    width: 14px;
    height: 14px;
    margin-left: 2px;
  }
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

@media (max-width: 425px) {
  .navbar-dropdown {
    top: 68px;
    right: 16px;
    min-width: 140px;
    max-width: 160px;
    border-radius: 16px;
    padding: 12px 0;
  }
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
  justify-content: center;
  /* horizontally center icon + label */
  text-align: center;
  /* center text fallback */
  width: 100%;
  /* make items fill dropdown width so centering is apparent */
}

.navbar-dropdown-item0 {
  padding: 0 20px;
  font-size: 1rem;
  color: #222;
  cursor: default;
  user-select: none;
  transition: background 0.15s;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
  justify-content: center;
  /* horizontally center icon + label */
  text-align: center;
  /* center text fallback */
  width: 100%;
  /* make items fill dropdown width so centering is apparent */
}

@media (max-width: 425px) {
  .navbar-dropdown-item {
    padding: 10px 16px;
    font-size: 0.8rem;
    text-align: center;
    gap: 8px;
    justify-content: center;
    /* ensure mobile keeps centered layout */
    width: 100%;
  }
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

@media (max-width: 450px) {
  .navbar-badge {
    top: -4px;
    left: 10px;
    width: 14px;
    height: 16px;
    font-size: 0.75rem;
    padding: 0 4px;
  }
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

.sidebar-logo1 {
  display: none;
}

@media (max-width: 425px) {
  .sidebar-logo1 {
    width: auto;
    min-width: unset;
    max-width: unset;
    height: 65px;
    min-height: 65px;
    max-height: 65px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fafafa;
    position: relative;
    flex-direction: column;
  }
}
</style>

<style>
body,
#app {
  margin: 0 !important;
  padding: 0 !important;
}
</style>
