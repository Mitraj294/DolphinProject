export const ROLES = {
  SUPERADMIN: 'superadmin',
  DOLPHINADMIN: 'dolphinadmin',
  USER: 'user',
  ORGANIZATIONADMIN: 'organizationadmin',
  SALESPERSON: 'salesperson',
};

export const PERMISSIONS = {
  [ROLES.SUPERADMIN]: {
    routes: [
      '/dashboard',
      '/organizations',
      '/notifications',
      '/leads',
      '/leads/send-assessment',
      '/leads/schedule-demo',
      '/leads/schedule-class-training',
      '/organizations/:orgName',
      '/organizations/:orgName',
      '/organizations/:id',
      '/organizations/:orgName/edit',
      '/organizations/:id/edit',
      '/leads/lead-capture',
      '/leads/edit-lead',
      '/leads/:email',
      '/members', 
      '/my-organization/members', 
      '/assessments/send-assessment',
      '/assessments/:assessmentId/summary',
      '/user-permission',
      '/user-permission/add',
      '/organizations/billing-details',
      '/profile',
    ],

  },
  [ROLES.DOLPHINADMIN]: {
    routes: [
      '/dashboard',
      '/leads',
      '/leads/send-assessment',
      '/leads/schedule-demo',
      '/leads/schedule-class-training',
      '/leads/lead-capture',
      '/leads/edit-lead',
      '/leads/:email',
      '/assessments/send-assessment',
      '/assessments/:assessmentId/summary',
      '/get-notification',
      '/subscriptions/plans',
      '/organizations/billing-details',
      '/profile',
     
    ],
    
  },
  [ROLES.USER]: {
    routes: [
      '/dashboard',
      '/assessments',
 
      '/assessments/:assessmentId/summary',
      '/get-notification',
      '/subscriptions/plans',
      '/organizations/billing-details',
      '/profile',
      '/subscriptions/plans', 
  '/manage-subscription', 
    ],

  },
  [ROLES.ORGANIZATIONADMIN]: {
    routes: [
      '/dashboard',
      '/my-organization',
      '/training-resources',
      '/assessments',
      '/members',
      '/my-organization/members',

      '/assessments',
      '/assessments/:assessmentId/summary',
      '/get-notification',
      '/subscriptions/plans',
      '/organizations/billing-details',
      '/profile',
     
    ],

  },
  [ROLES.SALESPERSON]: {
    routes: [
      '/dashboard',
      '/leads',
      '/leads/send-assessment',
      '/assessments/send-assessment',
      '/assessments/:assessmentId/summary',
      '/get-notification',
      '/subscriptions/plans',
      '/organizations/billing-details',
      '/profile',
      '/subscriptions/plans', 
  '/manage-subscription', 
    ],

  },
};

// Utility to check if a role can access a route/component
export function canAccess(role, type, name) {
  if (!PERMISSIONS[role]) return false;
  if (type === 'routes') {
    // Allow dynamic route matching (e.g. /organizations/:orgName)
    return PERMISSIONS[role][type].some(pattern => {
      if (pattern === name) return true;
      // Convert /organizations/:orgName to regex
      const regex = new RegExp('^' + pattern.replace(/:[^/]+/g, '[^/]+') + '$');
      return regex.test(name);
    });
  }
  return PERMISSIONS[role][type] && PERMISSIONS[role][type].includes(name);
}
