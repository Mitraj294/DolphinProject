
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
    const encrypted = localStorage.getItem(key);
    if (!encrypted) return null;
    try {
      const bytes = CryptoJS.AES.decrypt(encrypted, STORAGE_KEY);
      const decrypted = bytes.toString(CryptoJS.enc.Utf8);
      const value = JSON.parse(decrypted);
      // If value is a string, return as is
      if (typeof value === 'string') return value;
      // If value is an object with a token property, return value.token;
      if (value && typeof value === 'object') {
        if (value.token) return value.token;
        // If the object itself is the token string
        if (Object.keys(value).length === 1 && value[Object.keys(value)[0]].length > 30) {
          return value[Object.keys(value)[0]];
        }
      }
      return value;
    } catch (e) {
      // fallback: return as is
      return encrypted;
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
