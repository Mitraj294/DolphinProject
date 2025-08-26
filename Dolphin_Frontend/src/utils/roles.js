// Utility to map internal role keys to user-friendly labels
export function formatRole(role) {
  if (!role) return '';
  const key = String(role).toLowerCase();
  const map = {
    superadmin: 'Super Admin',
    organizationadmin: 'Organization Admin',
    dolphinadmin: 'Dolphin Admin',
    salesperson: 'Sales Person',
    user: 'User',
  };
  return map[key] || // try simple split on camel/case or underscores
    (String(role)
      // insert spaces before capitals
      .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
      // replace underscores/dashes with space
      .replace(/[_-]+/g, ' ')
      // capitalize words
      .split(' ')
      .map((w) => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase())
      .join(' '));
}
