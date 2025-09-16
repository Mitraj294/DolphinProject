// 1. ROLES
// Defines all possible user roles as constants to avoid magic strings.
export const ROLES = {
  SUPERADMIN: 'superadmin',
  DOLPHINADMIN: 'dolphinadmin',
  ORGANIZATIONADMIN: 'organizationadmin',
  SALESPERSON: 'salesperson',
  USER: 'user',
};


// 2. SHARED PERMISSION SETS
// Define common groups of routes to keep permissions DRY and easy to manage.

const PERMISSIONS_BASE = [
  '/dashboard',
  '/profile',
  '/get-notification',
];

const PERMISSIONS_SUBSCRIPTION = [
  '/manage-subscription',
  '/subscriptions/plans',
  '/organizations/billing-details',
];

const PERMISSIONS_LEAD_MANAGEMENT = [
  '/leads',
  '/leads/lead-capture',
  '/leads/send-assessment',
  '/leads/send-assessment/:id',
  '/leads/schedule-demo',
  '/leads/schedule-class-training',
  '/leads/:id/edit',
  '/leads/:email', // Note: Consider using /leads/:id for consistency
];

const PERMISSIONS_ASSESSMENT_USER = [
  '/assessments',
  '/assessments/:assessmentId/summary',
];

const PERMISSIONS_ORGANIZATION_ADMIN = [
  '/my-organization',
  '/my-organization/members',
  '/training-resources',
];

const PERMISSIONS_SUPERADMIN_ORGS = [
  '/organizations',
  '/organizations/:id',
  '/organizations/:id/edit',
  // Note: /:orgName routes are functionally similar to /:id and could potentially be consolidated
  '/organizations/:orgName',
  '/organizations/:orgName/edit',
];

const PERMISSIONS_SUPERADMIN_USERS = [
  '/user-permission',
  '/user-permission/add',
];


// 3. ROLE TO PERMISSION MAPPING
// Assign permissions to each role by combining the shared sets.
// Using a Set ensures that the final list of routes is unique.

export const PERMISSIONS = {
  [ROLES.SUPERADMIN]: {
    routes: [...new Set([
      ...PERMISSIONS_BASE,
      ...PERMISSIONS_SUBSCRIPTION,
      ...PERMISSIONS_LEAD_MANAGEMENT,
      ...PERMISSIONS_ASSESSMENT_USER,
      ...PERMISSIONS_ORGANIZATION_ADMIN, // Superadmin can do everything an org admin can
      ...PERMISSIONS_SUPERADMIN_ORGS,
      ...PERMISSIONS_SUPERADMIN_USERS,
      '/notifications',
    ])],
  },

  [ROLES.DOLPHINADMIN]: {
    routes: [...new Set([
      ...PERMISSIONS_BASE,
      ...PERMISSIONS_SUBSCRIPTION,
      ...PERMISSIONS_LEAD_MANAGEMENT,
      '/assessments/:assessmentId/summary', // Can view summaries
    ])],
  },

  [ROLES.ORGANIZATIONADMIN]: {
    routes: [...new Set([
      ...PERMISSIONS_BASE,
      ...PERMISSIONS_SUBSCRIPTION,
      ...PERMISSIONS_ASSESSMENT_USER,
      ...PERMISSIONS_ORGANIZATION_ADMIN,
    ])],
  },

  [ROLES.SALESPERSON]: {
    routes: [...new Set([
      ...PERMISSIONS_BASE,
      ...PERMISSIONS_SUBSCRIPTION,
      '/leads', // Can access the main leads page
      '/assessments/:assessmentId/summary', // Can view summaries
    ])],
  },

  [ROLES.USER]: {
    routes: [...new Set([
      ...PERMISSIONS_BASE,
      ...PERMISSIONS_SUBSCRIPTION,
      ...PERMISSIONS_ASSESSMENT_USER,
    ])],
  },
};


// 4. UTILITY FUNCTION
/**
 * Checks if a given role has permission to access a specific route.
 * This function supports dynamic route segments (e.g., /leads/:id).
 *
 * @param {string} role The user's role (e.g., 'superadmin').
 * @param {string} type The type of permission to check (currently only 'routes').
 * @param {string} name The path of the route to check (e.g., '/leads/123').
 * @returns {boolean} True if the user has permission, otherwise false.
 */
export function canAccess(role, type, name) {
  const permissionsForRole = PERMISSIONS[role];
  if (!permissionsForRole || !permissionsForRole[type]) {
    return false;
  }

  // Use .some() to find if any defined route pattern matches the current route name.
  return permissionsForRole[type].some(pattern => {
    // Direct match (e.g., '/dashboard' === '/dashboard')
    if (pattern === name) {
      return true;
    }
    
    // Dynamic match for routes with params like '/leads/:id'
    // This converts the pattern into a regular expression.
    // e.g., '/leads/:id' becomes /^/leads/[^/]+$/
    if (pattern.includes(':')) {
      const regex = new RegExp('^' + pattern.replace(/:[^/]+/g, '[^/]+') + '$');
      return regex.test(name);
    }

    return false;
  });
}