// src/services/storage.js
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
      return JSON.parse(decrypted);
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
