import CryptoJS from 'crypto-js';

const STORAGE_KEY = process.env.VUE_APP_STORAGE_KEY || 'dolphin_secret_key';

const storage = {
  set(key, value) {
    try {
      const stringValue = JSON.stringify(value);
      const encrypted = CryptoJS.AES.encrypt(stringValue, STORAGE_KEY).toString();
      localStorage.setItem(key, encrypted);
    } catch (e) {
      // fallback: store as plain text
      localStorage.setItem(key, value);
    }
  },
  get(key) {
    const raw = localStorage.getItem(key);
    if (!raw) return null;

    // Detect CryptoJS AES output (it commonly starts with 'U2FsdGVk') and only
    // attempt decryption when appropriate. Otherwise try to parse plain JSON
    // or return the raw string.
    const looksEncrypted = typeof raw === 'string' && raw.indexOf('U2FsdGVk') === 0;

    if (looksEncrypted) {
      try {
        const bytes = CryptoJS.AES.decrypt(raw, STORAGE_KEY);
        const decrypted = bytes.toString(CryptoJS.enc.Utf8);
        const value = JSON.parse(decrypted);
        // If value is a string, return as is
        if (typeof value === 'string') return value;
        // If value is an object with a token property, return value.token;
        if (value && typeof value === 'object') {
          if (value.token) return value.token;
          // If the object itself is the token string
          const firstKey = Object.keys(value)[0];
          if (firstKey && Object.keys(value).length === 1 && value[firstKey] && value[firstKey].length > 30) {
            return value[firstKey];
          }
        }
        return value;
      } catch (e) {
        // Decryption/parsing failed — return the raw stored value so caller can
        // decide how to handle it.
        return raw;
      }
    }

    // Not encrypted: try to parse JSON, otherwise return raw string
    try {
      return JSON.parse(raw);
    } catch (e) {
      return raw;
    }
  },
  remove(key) {
    localStorage.removeItem(key);
  },
  clear() {
    localStorage.clear();
  }
};

export default storage;