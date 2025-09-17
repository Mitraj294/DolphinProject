export const findUsOptions = [
  'Google',
  'Colleague',
  'Facebook',
  'LinkedIn',
  'Twitter',
  'Instagram',
  'Friend',
  'Website',
  'Other',
];
export function normalizeFindUs(value) {
  if (!value) return '';
  const lowercasedValue = String(value).toLowerCase();
  if (lowercasedValue.includes('google')) return 'Google';
  if (lowercasedValue.includes('colleague')) return 'Colleague';
  if (lowercasedValue.includes('facebook')) return 'Facebook';
  if (lowercasedValue.includes('linkedin')) return 'LinkedIn';
  if (lowercasedValue.includes('twitter')) return 'Twitter';
  if (lowercasedValue.includes('instagram')) return 'Instagram';
  if (lowercasedValue.includes('friend') || lowercasedValue.includes('referral')) return 'Friend';
  if (lowercasedValue.includes('website')) return 'Website';
  if (lowercasedValue.includes('other')) return 'Other';
  return value;
}
export const orgSizeOptions = [
  '1-99 Employees (Small)',
  '100-249 Employees (Medium)',
  '250+ Employees (Large)',
  'Not Decided Yet',
  'Prefer Not to Say',
   'Other',
];
export function normalizeOrgSize(value) {
  if (!value) return '';
  const lowercasedValue = String(value).toLowerCase();
  if (lowercasedValue.includes('1-99 employees (small)')) return '1-99 Employees (Small)';
  if (lowercasedValue.includes('100-249 employees (medium)')) return '100-249 Employees (Medium)';
  if (lowercasedValue.includes('250+ employees (large)')) return '250+ Employees (Large)';
  if (lowercasedValue.includes('not diceded yet')) return 'Not Decided Yet';
  if (lowercasedValue.includes('other')) return 'Other';
  if (lowercasedValue.includes('prefer not to say')) return 'Prefer Not to Say';

  return value;
}