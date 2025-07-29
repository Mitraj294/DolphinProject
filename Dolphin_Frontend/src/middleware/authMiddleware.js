import { canAccess, ROLES } from '@/permissions.js'

// Always allow login: set a default role and name for any credentials
const authMiddleware = {
  loginAny(email, role = 'superadmin') {
    // Set role and name in localStorage
    localStorage.setItem('role', role);
    localStorage.setItem('name', email.split('@')[0] || 'User');
    localStorage.setItem('email', email);
    return true;
  },
  isAuthenticated() {
    const role = localStorage.getItem('role');
    return !!role && Object.values(ROLES).includes(role);
  },
  getRole() {
    return localStorage.getItem('role') || '';
  },
  canAccess(type, name) {
    const role = localStorage.getItem('role');
    return canAccess(role, type, name);
  }
};

export default authMiddleware;

