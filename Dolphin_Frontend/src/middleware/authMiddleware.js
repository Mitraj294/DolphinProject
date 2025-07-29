
import { canAccess, ROLES } from '@/permissions.js';
import storage from '../services/storage';

const authMiddleware = {
  loginAny(email, role = 'superadmin') {
    storage.set('role', role);
    storage.set('name', email.split('@')[0] || 'User');
    storage.set('email', email);
    return true;
  },
  isAuthenticated() {
    const role = storage.get('role');
    return !!role && Object.values(ROLES).includes(role);
  },
  getRole() {
    return storage.get('role') || '';
  },
  canAccess(type, name) {
    const role = storage.get('role');
    return canAccess(role, type, name);
  }
};

export default authMiddleware;

