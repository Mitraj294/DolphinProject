
import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/components/auth/Login.vue'
import Register from '@/components/auth/Register.vue'
import ThankYou from '@/components/auth/ThankYou.vue'
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
import { ROLES, PERMISSIONS, canAccess } from '@/permissions.js'
import storage from '../services/storage';

const routes = [
 
  {
    path: '/assessment/answer/:token',
    name: 'AssessmentAnswerPage',
    component: () => import('@/components/Common/AssessmentAnswerPage.vue'),
    meta: { public: true }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: () => import('@/components/auth/ResetPassword.vue'),
    meta: { public: true }
  },
  {
    path: '/user-permission/add',
    name: 'AddPermission',
    component: () => import('@/components/Common/Superadmin/AddPermission.vue'),
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
    path: '/assessments/send-assessment',
    name: 'SendAssessmentAssessments',
    component: SendAssessment
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
    meta: { public: true } // Mark as public if you use route guards
  },
  {
    path: '/subscriptions/plans',
    name: 'SubscriptionPlans',
    component: () => import('@/components/Common/SubscriptionPlans.vue'),
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
    path: '/leads/:email',
    name: 'LeadDetail',
    component: () => import('@/components/Common/Leads_Assessment/LeadDetail.vue'),
    props: true
  },
  {
    path: '/leads/edit-lead',
    name: 'EditLead',
    component: () => import('@/components/Common/Leads_Assessment/EditLead.vue'),
    props: true
  },
  {
    path: '/organizations/:orgName',
    name: 'OrganizationDetail',
    component: () => import('@/components/Common/Organizations/OrganizationDetail.vue'),
    props: true
  },
  {
    path: '/organizations/billing-details',
    name: 'BillingDetails',
    component: () => import('@/components/Common/BillingDetails.vue')
  },
  {
    path: '/organizations/:orgName/edit',
    name: 'OrganizationEdit',
    component: () => import('@/components/Common/Organizations/OrganizationEdit.vue'),
    props: true
  },
  {
    path: '/leads/lead-capture',
    name: 'LeadCapture',
    component: () => import('@/components/Common/Leads_Assessment/LeadCapture.vue'),
  },
  {
    path: '/leads/send-assessment',
    name: 'SendAssessment',
    component: SendAssessment
  },
  {
    path: '/leads/schedule-demo',
    name: 'ScheduleDemo',
    component: ScheduleDemo
  },
  {
    path: '/leads/schedule-class-training',
    name: 'ScheduleClassTraining',
    component: () => import('@/components/Common/Leads_Assessment/ScheduleClassTraining.vue')
  },
  {
    path: '/my-organization/members',
    name: 'MemberListing',
    component: () => import('@/components/Common/MyOrganization/MemberListing.vue')
  },
  {
    path: '/assessments/:assessmentId/summary',
    name: 'AssessmentSummary',
    component: () => import('@/components/Common/Leads_Assessment/AssessmentSummary.vue'),
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
  // Allow public routes (e.g., forgot-password) for everyone
  if (to.meta && to.meta.public) {
    return next();
  }

  // Get user role from encrypted storage
  const role = storage.get('role');

  // login / thank you / register for everyone
  if (to.path === '/' || to.path === '/thankyou' || to.path === '/register') {
    return next();
  }

  // If no role, redirect to login
  if (!role || !PERMISSIONS[role]) {
    return next('/');
  }

  // Allow manage-subscription and subscription plans for all roles
  if (to.path === '/manage-subscription' || to.path === '/subscriptions/plans') {
    return next();
  }

  // Check if the role can access the route
  if (canAccess(role, 'routes', to.path)) {
    return next();
  } else {
    // unauthorized users to dashboard
    return next('/dashboard');
  }
});

export default router