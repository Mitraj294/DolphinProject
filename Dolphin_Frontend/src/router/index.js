
import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/components/auth/Login.vue'
import Register from '@/components/auth/Register.vue'
import ThankYou from '@/components/auth/ThankYou.vue'
import ThanksPage from '@/components/Common/ThanksPage.vue';
import Dashboard from '@/components/Common/Dashboard/Dashboard.vue'
import Organizations from '@/components/Common/Organizations/Organizations.vue'
import Notifications from '@/components/Common/Superadmin/Notifications.vue'
import Leads from '@/components/Common/Leads_Assessment/Leads.vue'
import TrainingResources from '@/components/Common/TrainingResources.vue'
import Assessments from '@/components/Common/Leads_Assessment/Assessments.vue'
import MyOrganization from '@/components/Common/MyOrganization/MyOrganization.vue'
import LeadCapture from '@/components/Common/Leads_Assessment/LeadCapture.vue'
import SendAssessment from '@/components/Common/Leads_Assessment/SendAssessment.vue'
import ScheduleDemo from '@/components/Common/Leads_Assessment/ScheduleDemo.vue'
import ManageSubscription from '@/components/Common/ManageSubscription.vue';
import GetNotifications from '@/components/Common/GetNotifications.vue'
import UserPermission from '@/components/Common/Superadmin/UserPermission.vue'
import ForgotPassword from '@/components/auth/ForgotPassword.vue'
import Profile from '@/components/Common/Profile.vue'
import AssessmentAnswerPage from '@/components/Common/AssessmentAnswerPage.vue'
import ResetPassword from '@/components/auth/ResetPassword.vue' 
import AddUser from '@/components/Common/Superadmin/AddUser.vue'  
import SubscriptionPlans from '@/components/Common/SubscriptionPlans.vue'
import LeadDetail from '@/components/Common/Leads_Assessment/LeadDetail.vue'
import EditLead from '@/components/Common/Leads_Assessment/EditLead.vue'
import OrganizationDetail from '@/components/Common/Organizations/OrganizationDetail.vue'
import OrganizationEdit from '@/components/Common/Organizations/OrganizationEdit.vue'
import ScheduleClassTraining from '@/components/Common/Leads_Assessment/ScheduleClassTraining.vue'
import BillingDetails from '@/components/Common/BillingDetails.vue'
import MemberListing from '@/components/Common/MyOrganization/MemberListing.vue'  
import AssessmentSummary from '@/components/Common/Leads_Assessment/AssessmentSummary.vue'
import { ROLES, PERMISSIONS, canAccess } from '@/permissions.js'
import storage from '../services/storage';

const routes = [
  {
    path: '/thanks',
    name: 'ThanksPage',
    component: ThanksPage,
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
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword,
    meta: { public: true }
  },
  {
    path: '/user-permission/add', 
    name: 'AddUser',
    component:   AddUser,
  
  },
  {
    path: '/',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/thankyou',
    name: 'ThankYou',
    component: ThankYou
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/organizations',
    name: 'Organizations',
    component: Organizations
  },
  {
    path: '/notifications',
    name: 'Notifications',
    component: Notifications
  },
  {
    path: '/leads',
    name: 'Leads',
    component: Leads
  },
  {
    path: '/training-resources',
    name: 'TrainingResources',
    component: TrainingResources
  },
  {
    path: '/assessments',
    name: 'Assessments',
    component: Assessments
  },
  {
    path: '/my-organization',
    name: 'MyOrganization',
    component: MyOrganization
  },
  {
    path: '/manage-subscription',
    name: 'ManageSubscription',
    component: ManageSubscription
  },
  {
    path: '/get-notification',
    name: 'GetNotification',
    component: GetNotifications,
    meta: { layout: 'main' }
  },
  {
    path: '/assessments/send-assessment/:id?',
    name: 'SendAssessmentAssessments',
    component: SendAssessment,
    props: true,
  },
  {
    path: '/user-permission',
    name: 'UserPermission',
    component: UserPermission
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword,
    meta: { public: true }
  },
  {
    path: '/subscriptions/plans',
    name: 'SubscriptionPlans',
    component: SubscriptionPlans,
    meta: {
      requiresAuth: true,
    }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  },

  {
    path: '/leads/:id',
    name: 'LeadDetail',
    component: LeadDetail,
    props: true
  },
  {
    path: '/leads/:id/edit',
    name: 'EditLead',
    component: EditLead,
    props: true
  },
  {
    path: '/organizations/:id',
    name: 'OrganizationDetail',
    component: OrganizationDetail,
    props: true
  },

  {
    path: '/organizations/:orgName',
    name: 'OrganizationDetailByName',
    component: OrganizationDetail,  
    props: true
  },
  {
    path: '/organizations/billing-details',
    name: 'BillingDetails',
    component: BillingDetails,
    props: true
  },
  {
    path: '/organizations/:id/edit',
    name: 'OrganizationEdit',
    component: OrganizationEdit,
    props: true
  },

  {
    path: '/organizations/:orgName/edit',
    name: 'OrganizationEditByName',
    component: OrganizationEdit,
    props: true
  },
  {
    path: '/leads/lead-capture',
    name: 'LeadCapture',
    component: LeadCapture
  },
  {
    path: '/leads/send-assessment/:id?',
    name: 'SendAssessment',
    component: SendAssessment,
    props: true,
  },
  {
    path: '/leads/schedule-demo',
    name: 'ScheduleDemo',
    component: ScheduleDemo
  },
  {
    path: '/leads/schedule-class-training',
    name: 'ScheduleClassTraining',
    component: ScheduleClassTraining,
  },
  {
    path: '/my-organization/members',
    name: 'MemberListing',
    component: MemberListing,
    props: true
  },
  {
    path: '/assessments/:assessmentId/summary',
    name: 'AssessmentSummary',
    component: AssessmentSummary,
    props: true
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.addRoute({
  path: '/:catchAll(.*)',
  redirect: '/dashboard'
});

//if no route matches, redirect to /dashboard
router.beforeEach((to, from, next) => {

  // If user already has role saved and valid, redirect root/login to dashboard
  const authToken = storage.get('authToken');
  const role = storage.get('role');
  if ((to.path === '/' || to.path === '/login') && role && PERMISSIONS[role]) {
    return next('/dashboard');
  }

  // Allow public routes (e.g., forgot-password) for everyone
  if (to.meta && to.meta.public) {
    return next();
  }

  // login / thank you / register for everyone (thankyou/register are public entry points)
  if (to.path === '/thankyou' || to.path === '/register') {
    return next();
  }

  // If no role, redirect to login.
  // But if the target is already the login page ('/'), allow it to proceed
  // to avoid repeatedly redirecting to the same route which causes an
  // infinite redirect loop.
  if (!role || !PERMISSIONS[role]) {
    if (to.path === '/' || to.path === '/login') {
      return next();
    }
    return next('/');
  }

  // Check subscription status for users with expired subscriptions
  const subscriptionStatus = storage.get('subscription_status');
  const allowedRoutesForExpired = [
    '/profile',
    '/manage-subscription', 
    '/subscriptions/plans',
    '/organizations/billing-details'
  ];

  // If subscription is expired, only allow specific routes
  if (subscriptionStatus === 'expired') {
    if (allowedRoutesForExpired.includes(to.path)) {
      // Allow access to these specific routes even with expired subscription
      if (to.path === '/manage-subscription' || to.path === '/subscriptions/plans') {
        if (role === ROLES.USER || role === ROLES.ORGANIZATIONADMIN) {
          return next();
        }
        return next('/dashboard');
      }
      // For profile and billing-details, check normal permissions
      if (canAccess(role, 'routes', to.path)) {
        return next();
      } else {
        return next('/dashboard');
      }
    } else {
      // Redirect to manage-subscription for all other routes when subscription is expired
      return next('/manage-subscription');
    }
  }

  // Allow manage-subscription and subscription plans only for USER and ORGANIZATIONADMIN
  if (to.path === '/manage-subscription' || to.path === '/subscriptions/plans') {
    if (role === ROLES.USER || role === ROLES.ORGANIZATIONADMIN) {
      return next();
    }
    // other roles not allowed
    return next('/dashboard');
  }

  // Check if the role can access the route (normal flow for active subscriptions)
  if (canAccess(role, 'routes', to.path)) {
    return next();
  } else {
    // unauthorized users to dashboard
    return next('/dashboard');
  }
});

export default router