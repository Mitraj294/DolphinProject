//Role & Permission Configuration for DolphinProject

//Contains:
// - ROLES: All available user roles in the system
// - PERMISSIONS: Allowed routes for each role
// - canAccess(): Utility for permission checking by route/component

//Usage:
// - Import ROLES and PERMISSIONS where needed
// - Use canAccess(role, type, name) to check route/component access



// 1. Role Definitions

export const ROLES = {
  SUPERADMIN: 'superadmin',
  DOLPHINADMIN: 'dolphinadmin',
  USER: 'user',
  ORGANIZATIONADMIN: 'organizationadmin',
  SALESPERSON: 'salesperson',
};


// 2. Permissions Mapping (Allowed Routes By Role)

export const PERMISSIONS = {
  [ROLES.SUPERADMIN]: {
    routes: [
      '/dashboard',
      '/organizations',
      '/notifications',
      '/leads',
      '/leads/send-assessment',
      '/leads/send-assessment/:id',
      '/leads/schedule-demo',
      '/leads/schedule-class-training',
      '/organizations/:orgName',
      '/organizations/:orgName',
      '/organizations/:id',
      '/organizations/:orgName/edit',
      '/organizations/:id/edit',
      '/leads/lead-capture',
      '/leads/:id/edit',
      '/leads/:email',
      '/members',
      '/my-organization/members',
      '/assessments/send-assessment',
      '/assessments/send-assessment/:id',
      '/assessments/send-agreement',
      '/assessments/send-agreement/:id',
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
      '/leads/send-assessment/:id',
      '/leads/schedule-demo',
      '/leads/schedule-class-training',
      '/leads/lead-capture',
      '/leads/:id/edit',
      '/leads/:email',
      '/assessments/send-assessment',
      '/assessments/send-assessment/:id',
      '/assessments/send-agreement',
      '/assessments/send-agreement/:id',
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
      '/leads/send-assessment/:id',
      '/assessments/send-assessment',
      '/assessments/send-assessment/:id',
      '/assessments/send-agreement',
      '/assessments/send-agreement/:id',
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

/**

 * Utility: canAccess

 * Checks if a given role has permission to access a route/component.
 * Supports dynamic route matching (e.g. /organizations/:orgName).
 *
 * @param {string} role - Role identifier from ROLES
 * @param {string} type - Permission type (e.g., 'routes')
 * @param {string} name - Route path or component name
 * @returns {boolean}

 */
export function canAccess(role, type, name) {
  if (!PERMISSIONS[role]) return false;
  if (type === 'routes') {
    // Dynamic route matching (e.g. /organizations/:orgName)
    return PERMISSIONS[role][type].some(pattern => {
      if (pattern === name) return true;
      // Convert /organizations/:orgName to regex
      const regex = new RegExp('^' + pattern.replace(/:[^/]+/g, '[^/]+') + '$');
      return regex.test(name);
    });
  }
  return PERMISSIONS[role][type] && PERMISSIONS[role][type].includes(name);
}
