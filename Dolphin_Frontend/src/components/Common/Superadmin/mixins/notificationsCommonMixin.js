import axios from 'axios';
import storage from '@/services/storage';

export default {
  data() {
    return {
      // lifecycle guard to prevent state updates after unmount
      isAlive: false,
      showPageDropdown: false,
      showSendModal: false,
      showDetailModal: false,
      selectedNotification: null,
      pageSize: 10,
      currentPage: 1,
      sortKey: '',
      sortAsc: true,
      selectedOrganizations: [],
      selectedAdmin: '',
      selectedGroups: [],
      scheduledDate: '',
      scheduledTime: '',
      organizations: [],
      groups: [],
      notifications: [],
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.notifications.length / this.pageSize) || 1;
    },
    paginatedNotifications() {
      let notifications = [...this.notifications];
      if (this.sortKey) {
        notifications.sort((a, b) => {
          const aVal = a[this.sortKey] || '';
          const bVal = b[this.sortKey] || '';
          if (aVal < bVal) return this.sortAsc ? -1 : 1;
          if (aVal > bVal) return this.sortAsc ? 1 : -1;
          return 0;
        });
      }
      const start = (this.currentPage - 1) * this.pageSize;
      return notifications.slice(start, start + this.pageSize);
    },
    paginationPages() {
      const total = this.totalPages;
      if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
      } else {
        const pages = [1];
        if (this.currentPage > 4) pages.push('...');
        for (
          let i = Math.max(2, this.currentPage - 1);
          i <= Math.min(total - 1, this.currentPage + 1);
          i++
        ) {
          pages.push(i);
        }
        if (this.currentPage < total - 3) pages.push('...');
        pages.push(total);
        return pages;
      }
    },
  },
  methods: {
    goToPage(page) {
      if (page === '...' || page < 1 || page > this.totalPages) return;
      this.currentPage = page;
    },
    selectPageSize(size) {
      this.pageSize = size;
      this.currentPage = 1;
      this.showPageDropdown = false;
    },
    sortBy(key) {
      if (this.sortKey === key) {
        this.sortAsc = !this.sortAsc;
      } else {
        this.sortKey = key;
        this.sortAsc = true;
      }
    },
    formatLocalDateTime(dateStr) {
      if (!dateStr) return '';
      // If ISO with timezone or 'Z', let Date parse it
      let d = null;
      try {
        if (/[T].*Z$/.test(dateStr) || /[T].*[+-]\d{2}:?\d{2}$/.test(dateStr)) {
          d = new Date(dateStr);
        } else {
          // Try to parse 'YYYY-MM-DD HH:MM:SS' (assume it's UTC from DB)
          const m = (dateStr || '').match(
            /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/
          );
          if (m) {
            const year = parseInt(m[1], 10);
            const month = parseInt(m[2], 10) - 1;
            const day = parseInt(m[3], 10);
            const hour = parseInt(m[4], 10);
            const minute = parseInt(m[5], 10);
            const second = m[6] ? parseInt(m[6], 10) : 0;
            // create Date from UTC components, then use local getters for display
            const utcMillis = Date.UTC(year, month, day, hour, minute, second);
            d = new Date(utcMillis);
          } else {
            d = new Date(dateStr);
          }
        }
      } catch (e) {
        console.warn('Date parse error:', e);
        d = new Date(dateStr);
      }
      if (!d || isNaN(d.getTime())) return dateStr || '';

      // Use local date/time getters so the shown value is in user's local timezone
      const day = String(d.getDate()).padStart(2, '0');
      const months = [
        'JAN',
        'FEB',
        'MAR',
        'APR',
        'MAY',
        'JUN',
        'JUL',
        'AUG',
        'SEP',
        'OCT',
        'NOV',
        'DEC',
      ];
      const mon = months[d.getMonth()];
      const yr = d.getFullYear();

      let hr = d.getHours();
      const min = String(d.getMinutes()).padStart(2, '0');
      const ampm = hr >= 12 ? 'PM' : 'AM';
      hr = hr % 12;
      hr = hr ? hr : 12; // convert 0 -> 12
      const strTime = `${hr}:${min} ${ampm}`;
      return `${day} ${mon},${yr} ${strTime}`;
    },
    async fetchOrganizations() {
      try {
        const apiUrl = process.env.VUE_APP_API_URL || '/api';
        const token = storage.get('authToken');
        const res = await axios.get(apiUrl + '/organizations', {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (this.isAlive) this.organizations = res.data;
      } catch (err) {
        if (this.isAlive) this.organizations = [];
      }
    },
    async fetchGroups() {
      try {
        const apiUrl = process.env.VUE_APP_API_URL || '/api';
        const token = storage.get('authToken');
        const res = await axios.get(apiUrl + '/groups', {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (this.isAlive) this.groups = res.data;
      } catch (err) {
        if (this.isAlive) this.groups = [];
      }
    },
    async fetchNotifications() {
      try {
        const apiUrl = process.env.VUE_APP_API_URL || '/api';
        const token = storage.get('authToken');
        const res = await axios.get(apiUrl + '/announcements', {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (this.isAlive) {
          this.notifications = Array.isArray(res.data)
            ? res.data
            : res.data.notifications || [];
        }
      } catch (err) {
        if (this.isAlive) this.notifications = [];
      }
    },
    // Show detail modal for a notification
    openDetail(notification) {
      this.selectedNotification = notification || null;
      this.showDetailModal = true;
    },
    // Close detail modal
    closeDetail() {
      this.selectedNotification = null;
      this.showDetailModal = false;
    },
  },
  mounted() {
    this.isAlive = true;
    this.fetchOrganizations();
    this.fetchGroups();
    this.fetchNotifications();
  },
  beforeUnmount() {
    this.isAlive = false;
  },
};
