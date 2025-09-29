import { createRouter, createWebHistory } from 'vue-router';
import storage from '../services/storage';
import { ROLES, canAccess } from '@/permissions.js';
import { fetchSubscriptionStatus } from '@/services/subscription.js';
import axios from 'axios';

/*
 Route Component Imports
 Using a hybrid strategy for optimal performance.
 - STATIC IMPORTS: For core, frequently-used components (e.g., Login, Dashboard).
 - DYNAMIC IMPORTS (Lazy Loading): For feature-specific, heavier components.

*/

// Static Imports (Core Components)
import Login from '@/components/auth/Login.vue';
import Register from '@/components/auth/Register.vue';
import ForgotPassword from '@/components/auth/ForgotPassword.vue';
import ResetPassword from '@/components/auth/ResetPassword.vue';
import Dashboard from '@/components/Common/Dashboard/Dashboard.vue';
import Profile from '@/components/Common/Profile.vue';
import AssessmentAnswerPage from '@/components/Common/AssessmentAnswerPage.vue';

// Dynamic Imports (Lazy-Loaded Feature Components)
const ThankYou = () => import('@/components/auth/ThankYou.vue');
const ThanksPage = () => import('@/components/Common/ThanksPage.vue');
const TrainingResources = () => import('@/components/Common/TrainingResources.vue');
const GetNotifications = () => import('@/components/Common/GetNotifications.vue');

// Subscription
const SubscriptionSuccess = () => import('@/components/Common/SubscriptionSuccess.vue');
const ManageSubscription = () => import('@/components/Common/ManageSubscription.vue');
const SubscriptionPlans = () => import('@/components/Common/SubscriptionPlans.vue');
const BillingDetails = () => import('@/components/Common/BillingDetails.vue');

// Superadmin
const UserPermission = () => import('@/components/Common/Superadmin/UserPermission.vue');
const AddUser = () => import('@/components/Common/Superadmin/AddUser.vue');
const Notifications = () => import('@/components/Common/Superadmin/Notifications.vue');

// Leads & Assessments
const Leads = () => import('@/components/Common/Leads_Assessment/Leads.vue');
const LeadDetail = () => import('@/components/Common/Leads_Assessment/LeadDetail.vue');
const EditLead = () => import('@/components/Common/Leads_Assessment/EditLead.vue');
const LeadCapture = () => import('@/components/Common/Leads_Assessment/LeadCapture.vue');
const SendAssessment = () => import('@/components/Common/Leads_Assessment/SendAssessment.vue');
const SendAgreement = () => import('@/components/Common/Leads_Assessment/SendAgreement.vue');
const ScheduleDemo = () => import('@/components/Common/Leads_Assessment/ScheduleDemo.vue');
const ScheduleClassTraining = () => import('@/components/Common/Leads_Assessment/ScheduleClassTraining.vue');
const Assessments = () => import('@/components/Common/Leads_Assessment/Assessments.vue');
const AssessmentSummary = () => import('@/components/Common/Leads_Assessment/AssessmentSummary.vue');

// Organization
const Organizations = () => import('@/components/Common/Organizations/Organizations.vue');
const OrganizationDetail = () => import('@/components/Common/Organizations/OrganizationDetail.vue');
const OrganizationEdit = () => import('@/components/Common/Organizations/OrganizationEdit.vue');
const MyOrganization = () => import('@/components/Common/MyOrganization/MyOrganization.vue');
const MemberListing = () => import('@/components/Common/MyOrganization/MemberListing.vue');


// Route Definitions

const routes = [
  // Public Routes
  {
    path: '/',
    name: 'Login',
    component: Login,
    meta: { public: true, guestOnly: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { public: true, guestOnly: true }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword,
    meta: { public: true }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword,
    meta: { public: true }
  },
  {
    path: '/thankyou',
    name: 'ThankYou',
    component: ThankYou,
    meta: { public: true }
  },
  {
    path: '/thanks',
    name: 'ThanksPage',
    component: ThanksPage,
    meta: { public: true }
  },
  {
    path: '/assessment/answer/:token',
    name: 'AssessmentAnswerPage',
    component: AssessmentAnswerPage,
    meta: { public: true }
  },

  // Authenticated Routes
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  },

  // Subscription & Billing
  {
    path: '/manage-subscription',
    name: 'ManageSubscription',
    component: ManageSubscription,
    meta: { requiresAuth: true, roles: [ROLES.USER, ROLES.ORGANIZATIONADMIN] }
  },
  {
    path: '/subscriptions/plans',
    name: 'SubscriptionPlans',
    meta: { requiresAuth: true, roles: [ROLES.USER, ROLES.ORGANIZATIONADMIN] }
  },
  {
    path: '/subscriptions/success',
    name: 'SubscriptionSuccess',
    component: SubscriptionSuccess,
    meta: { public: true }
  },
  {
    path: '/organizations/billing-details',
    name: 'BillingDetails',
    component: BillingDetails,
    props: true,
    meta: { requiresAuth: true }
  },
  
  // Organizations
  {
    path: '/organizations',
    name: 'Organizations',
    component: Organizations,
    meta: { requiresAuth: true }
  },
  {
    path: '/organizations/:id',
    name: 'OrganizationDetail',
    component: OrganizationDetail,
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/organizations/:id/edit',
    name: 'OrganizationEdit',
    component: OrganizationEdit,
    props: true,
    meta: { requiresAuth: true }
  },
  
  // My Organization
  {
    path: '/my-organization',
    name: 'MyOrganization',
    component: MyOrganization,
    meta: { requiresAuth: true }
  },
  {
    path: '/my-organization/members',
    name: 'MemberListing',
    component: MemberListing,
    props: true,
    meta: { requiresAuth: true }
  },

  // Leads
  {
    path: '/leads',
    name: 'Leads',
    component: Leads,
    meta: { requiresAuth: true }
  },
  {
    path: '/leads/lead-capture',
    name: 'LeadCapture',
    component: LeadCapture,
    meta: { requiresAuth: true }
  },
  {
    path: '/leads/:id',
    name: 'LeadDetail',
    component: LeadDetail,
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/leads/:id/edit',
    name: 'EditLead',
    component: EditLead,
    props: true,
    meta: { requiresAuth: true }
  },

  // Assessments
  {
    path: '/assessments',
    name: 'Assessments',
    component: Assessments,
    meta: { requiresAuth: true }
  },
  {
    path: '/assessments/send-assessment/:id?',
    name: 'SendAssessment',
    component: SendAssessment,
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/assessments/send-agreement/:id?',
    name: 'SendAgreement',
    component: SendAgreement,
    props: true,
    meta: { requiresAuth: true }
  },
  {
    path: '/assessments/:assessmentId/summary',
    name: 'AssessmentSummary',
    component: AssessmentSummary,
    props: true,
    meta: { requiresAuth: true }
  },

  // Superadmin
  {
    path: '/user-permission',
    name: 'UserPermission',
    component: UserPermission,
    meta: { requiresAuth: true, roles: [ROLES.SUPERADMIN] }
  },
  {
    path: '/user-permission/add',
    name: 'AddUser',
    component: AddUser,
    meta: { requiresAuth: true, roles: [ROLES.SUPERADMIN] }
  },
  {
    path: '/notifications',
    name: 'Notifications',
    component: Notifications,
    meta: { requiresAuth: true, roles: [ROLES.SUPERADMIN] }
  },
  
  // Other Authenticated Routes
  {
    path: '/training-resources',
    name: 'TrainingResources',
    component: TrainingResources,
    meta: { requiresAuth: true }
  },
  {
    path: '/get-notification',
    name: 'GetNotification',
    component: GetNotifications,
    meta: { requiresAuth: true }
  },
  {
    path: '/leads/schedule-demo',
    name: 'ScheduleDemo',
    component: ScheduleDemo,
    meta: { requiresAuth: true }
  },
  {
    path: '/leads/schedule-class-training',
    name: 'ScheduleClassTraining',
    component: ScheduleClassTraining,
    meta: { requiresAuth: true }
  },

  // Catch-all Route
  {
    path: '/:catchAll(.*)',
    redirect: '/dashboard'
  }
];


const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
});

// Refactored Navigation Guard Logic

const handlePublicRoutes = (to, authToken, next) => {
  if (!to.meta.public) return false;

  if (authToken && to.meta.guestOnly) {
    next('/dashboard');
  } else {
    next();
  }
  return true;
};

// Validate guest token by calling the backend. If valid, store a temporary guest session.
const validateGuestToken = async (token) => {
  try {
    const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
    const res = await axios.get(`${API_BASE_URL}/api/leads/guest-validate`, { params: { token } });
    if (res?.data?.valid) {
      // store minimal guest info for the session
      storage.set('guest_token', token);
      storage.set('guest_user', res.data.user);
      return true;
    }
  } catch (e) {
    console.error('Guest validation failed', e);
  }
  return false;
};

const handleExpiredSubscription = (to, next) => {
  const allowedRoutesForExpired = [
    'Profile',
    'ManageSubscription',
    'SubscriptionPlans',
    'BillingDetails'
  ];
  if (allowedRoutesForExpired.includes(to.name)) {
    next();
  } else {
    next('/manage-subscription');
  }
};

const checkPermissionsAndNavigate = (to, role, next) => {
  if (to.meta.roles && !to.meta.roles.includes(role)) {
    return next('/dashboard');
  }
  
  if (canAccess(role, 'routes', to.path)) {
    return next();
  }
  
  return next('/dashboard');
};


const handleAuthenticatedRoutes = async (to, role, next) => {
  const subscriptionPages = ['ManageSubscription', 'SubscriptionPlans', 'BillingDetails'];
  if (subscriptionPages.includes(to.name)) {
    return next();
  }

  try {
    const subscriptionStatus = await fetchSubscriptionStatus();
    storage.set('subscription_status', subscriptionStatus.status);

    if (subscriptionStatus.status === 'expired') {
      // Expired applies to everyone
      handleExpiredSubscription(to, next);
      return;
    }

    // Treat 'none' like expired only for organization admins
    if (subscriptionStatus.status === 'none' && role === ROLES.ORGANIZATIONADMIN) {
      handleExpiredSubscription(to, next);
      return;
    }

    checkPermissionsAndNavigate(to, role, next);
  } catch (error) {
    console.error("Error fetching subscription status:", error);
    storage.clear();
    next('/');
  }
};

router.beforeEach(async (to, from, next) => {
  const authToken = storage.get('authToken');
  const role = storage.get('role');

  if (handlePublicRoutes(to, authToken, next)) {
    return;
  }

  if (authToken) {
    await handleAuthenticatedRoutes(to, role, next);
  } else {
    // If a guest_token query param is present and user is navigating to SubscriptionPlans,
    // validate the token and allow the visit (guest flow) without requiring login.
    const guestToken = to.query?.guest_token || null;
    if (guestToken && to.name === 'SubscriptionPlans') {
      const ok = await validateGuestToken(guestToken);
      if (ok) {
        // allow visiting plans and mapping to the guest flow
        next();
        return;
      }
    }

    // Allow unauthenticated access to the plans page when the link contains
    // lead-identifying query params (email, lead_id, price_id) — this enables
    // the emailed links to open the plans page directly without requiring
    // a prior login. If a guest_token is supplied, we still validate it.
    const isPlansWithData = to.name === 'SubscriptionPlans' && (
      Boolean(to.query?.email) || Boolean(to.query?.lead_id) || Boolean(to.query?.price_id) || Boolean(to.query?.guest_token)
    );
    if (isPlansWithData) {
      // If a guest_token is present, prefer validating it (already handled above),
      // otherwise allow the visit and let the frontend render the minimal guest view.
      next();
      return;
    }

    // Default: redirect to login
    next('/');
  }
});

export default router;
