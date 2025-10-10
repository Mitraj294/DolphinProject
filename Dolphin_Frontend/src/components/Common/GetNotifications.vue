<template>
  <MainLayout>
    <div class="page">
      <div class="notifications-table-outer">
        <div class="notifications-table-card">
          <div class="notifications-controls">
            <div class="notifications-date-wrapper">
              <input
                type="date"
                placeholder="Select Date"
                class="notifications-date"
                v-model="selectedDate"
                @change="onDateChange"
              />
              <button
                v-if="selectedDate"
                class="mark-all"
                style="margin-left: 8px; height: 36px"
                @click="clearDate"
              >
                Clear
              </button>
            </div>
            <div class="notifications-tabs">
              <button
                :class="[
                  'notifications-tab-btn-left',
                  { active: tab === 'unread' },
                ]"
                @click="switchTab('unread')"
              >
                Unread
              </button>
              <button
                :class="[
                  'notifications-tab-btn-right',
                  { active: tab === 'all' },
                ]"
                @click="switchTab('all')"
              >
                All
              </button>
            </div>
            <button
              v-if="tab === 'unread' && notifications.length > 0"
              class="mark-all"
              :disabled="markAllLoading"
              @click="markAllAsRead"
              style="
                margin-top: 10px;
                background: #fff;
                color: #0164a5;
                border: none;
                border-radius: 6px;
                padding: 4px 10px;
                font-size: 0.95rem;
                cursor: pointer;
              "
            >
              <i
                class="fas fa-check"
                style="margin-right: 6px"
              ></i>
              <span v-if="!markAllLoading">Mark All As Read</span>
              <span v-else>Marking...</span>
            </button>
          </div>
          <div class="notifications-list">
            <div
              v-for="(item, id) in paginatedNotifications"
              :key="id"
              class="notification-item"
            >
              <div
                class="notification-meta"
                style="
                  display: flex;
                  flex-direction: column;
                  align-items: center;
                "
              >
                <img
                  src="@/assets/images/Logo.svg"
                  class="notification-icon"
                  alt="Company logo"
                />
              </div>
              <div class="notification-body">
                <span class="notification-date">{{ item.date }}</span>
                <span class="notification-text">
                  <span class="notification-title">Dolphin.</span>
                  {{ item.body }}
                </span>
              </div>
              <div
                class="notification-meta"
                style="
                  display: flex;
                  flex-direction: column;
                  align-items: center;
                "
              >
                <button
                  v-if="tab === 'unread' && !item.read_at"
                  class="mark-all"
                  @click="markAsRead(item.id)"
                  style="
                    margin-top: 10px;
                    background: #fff;
                    color: #0164a5;
                    border: none;
                    border-radius: 6px;
                    padding: 4px 10px;
                    font-size: 0.95rem;
                    cursor: pointer;
                  "
                >
                  <i class="fas fa-check"></i>
                </button>
              </div>
            </div>

            <div
              v-if="paginatedNotifications.length === 0"
              class="no-data"
            >
              <span v-if="tab === 'unread'"
                >No unread notifications found.</span
              >
              <span v-else>No notification found.</span>
            </div>
          </div>
        </div>
        <Pagination
          :pageSize="pageSize"
          :pageSizes="[5, 10, 20]"
          :currentPage="page"
          :totalPages="totalPages"
          :isNotifications="true"
          :showPageDropdown="showPageDropdown"
          @goToPage="goToPage"
          @selectPageSize="selectPageSize"
          @togglePageDropdown="togglePageDropdown"
        />
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import Pagination from '@/components/layout/Pagination.vue';
import storage from '@/services/storage';
import authMiddleware from '@/middleware/authMiddleware';
import axios from 'axios';
import { parseISO, isValid, isSameDay } from 'date-fns';

export default {
  name: 'GetNotification',
  components: { Pagination, MainLayout },
  data() {
    return {
      tab: 'unread',
      page: 1,
      pageSize: 10,
      showPageDropdown: false,
      notifications: [],
      readNotifications: [],
      selectedDate: '',
      markAllLoading: false,
      notificationsReady: false,
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredNotifications.length / this.pageSize) || 1;
    },
    paginatedNotifications() {
      const start = (this.page - 1) * this.pageSize;
      return this.filteredNotifications.slice(start, start + this.pageSize);
    },
    filteredNotifications() {
      // base list depending on tab
      let list = [];
      if (this.tab === 'unread') {
        list = this.notifications.slice();
      } else if (this.tab === 'all') {
        list = [...this.notifications, ...this.readNotifications];
      } else {
        list = this.notifications.slice();
      }

      // if a date is selected, filter by created_at date (YYYY-MM-DD)
      if (this.selectedDate) {
        // selectedDate is in YYYY-MM-DD format from the input[type=date]
        const sel = parseISO(this.selectedDate);
        if (isValid(sel)) {
          list = list.filter((n) => {
            const createdAt =
              n.created_at ||
              (n._rawData && (n._rawData.created_at || n._rawData.createdAt)) ||
              '';
            if (!createdAt) return false;
            // try parsing ISO, if invalid fallback to Date
            let d =
              typeof createdAt === 'string'
                ? parseISO(createdAt)
                : new Date(createdAt);
            if (!isValid(d)) {
              d = new Date(createdAt);
            }
            if (!isValid(d)) return false;
            return isSameDay(d, sel);
          });
        }
      }

      return list;
    },
  },
  methods: {
    async switchTab(newTab) {
      // Only fetch if switching to a different tab
      if (this.tab !== newTab) {
        this.tab = newTab;
        this.page = 1; // Reset to first page when switching tabs
        await this.fetchNotifications();
      }
    },
    async fetchNotifications() {
      try {
        const config = this._getAuthHeaders();
  const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || '';
  const endpoint = this._getNotificationEndpoint();
  const fullEndpoint = endpoint.startsWith('/api/') ? `${API_BASE_URL}${endpoint}` : endpoint;
  const response = await this._fetchDataWithFallback(fullEndpoint, config);
        const notificationsArr = this._extractNotifications(response);

        const storedUserId = storage.get('userId') || storage.get('user_id');
        const currentUserId = storedUserId ? parseInt(storedUserId, 10) : 0;

        const mapped = notificationsArr
          .filter((n) => this._isNotificationForUser(n, currentUserId))
          .map((n) => this._normalizeNotification(n));

        this.notificationsReady = true;
        this.notifications = mapped.filter((m) => !m.read_at);
        this.readNotifications = mapped.filter((m) => !!m.read_at);
      } catch (error) {
        this._handleFetchError(error);
      }
    },

    _getAuthHeaders() {
      let token = storage.get('authToken');
      if (token && typeof token === 'object' && token.token) {
        token = token.token;
      }
      if (typeof token !== 'string') {
        return {};
      }
      return token ? { headers: { Authorization: `Bearer ${token}` } } : {};
    },

    _getNotificationEndpoint() {
      if (this.tab === 'unread') {
        return '/api/notifications/unread';
      }
      const role = authMiddleware.getRole();
      if (role === 'superadmin') {
        const storedUserIdParam =
          storage.get('userId') || storage.get('user_id');
        const uid = storedUserIdParam ? parseInt(storedUserIdParam, 10) : 0;
        if (uid) {
          return `/api/notifications?notifiable_type=${encodeURIComponent(
            'App\\Models\\User'
          )}&notifiable_id=${uid}`;
        }
        return '/api/notifications';
      }
      return '/api/notifications/user';
    },

    async _fetchDataWithFallback(endpoint, config) {
      try {
        return await axios.get(endpoint, config);
      } catch (err) {
        if (
          err.response &&
          err.response.status === 403 &&
          endpoint !== '/api/notifications/user'
        ) {
          const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || '';
          return axios.get(`${API_BASE_URL}/api/notifications/user`, config);
        }
        throw err;
      }
    },

    _extractNotifications(response) {
      if (!response || !response.data) return [];
      if (Array.isArray(response.data)) return response.data;
      if (Array.isArray(response.data.notifications))
        return response.data.notifications;
      if (Array.isArray(response.data.unread)) return response.data.unread;
      console.warn('Unexpected notifications response format:', response.data);
      return [];
    },

    _isNotificationForUser(n, currentUserId) {
      if (!currentUserId) return true;
      if (parseInt(n.notifiable_id, 10) === currentUserId) return true;
      if (!n.data) return false;
      try {
        const d = typeof n.data === 'string' ? JSON.parse(n.data) : n.data;
        return (
          (d.user_id && parseInt(d.user_id, 10) === currentUserId) ||
          (d.userId && parseInt(d.userId, 10) === currentUserId) ||
          (d.recipient_id && parseInt(d.recipient_id, 10) === currentUserId)
        );
      } catch (e) {
        console.error('Error parsing notification data:', e);
        return false;
      }
    },

    _normalizeNotification(n) {
      let d = n.data;
      if (typeof d === 'string') {
        try {
          d = JSON.parse(d);
        } catch (e) {
          console.error('Error parsing notification data:', e);
        }
      }

      const bodyKeys = [
        'body',
        'message',
        'text',
        'details',
        'description',
        'content',
        'msg',
      ];
      let bodyDisplay = this._pickString(d, bodyKeys) || n.body || '';

      if (!bodyDisplay && d && typeof d === 'object') {
        bodyDisplay =
          this._pickString(d, ['user_message', 'notification', 'payload']) ||
          (Object.keys(d).length ? JSON.stringify(d) : '');
      }

      return {
        id: n.id,
        created_at: n.created_at,
        date: n.created_at ? this.formatDate(n.created_at) : '',
        body: bodyDisplay,
        read_at: n.read_at,
        _rawData: d,
      };
    },

    _pickString(obj, keys) {
      if (!obj) return '';
      for (const k of keys) {
        if (Object.hasOwn(obj, k)) {
          const v = obj[k];
          if (typeof v === 'string' && v.trim()) return v.trim();
          if (typeof v === 'number') return String(v);
          if (v && typeof v === 'object' && v.message) return String(v.message);
        }
      }
      return '';
    },

    _handleFetchError(error) {
      this.notifications = [];
      this.readNotifications = [];
      console.error(
        'Failed to fetch notifications:',
        error,
        error?.response?.data
      );
      const serverMsg =
        error?.response?.data?.message || error?.response?.data?.error || null;
      this.$nextTick(() => {
        if (this.$notify) {
          this.$notify({
            type: 'error',
            message: serverMsg || 'Failed to fetch notifications.',
          });
        }
      });
    },
    onDateChange() {
      // reset to first page when date filter changes
      this.page = 1;
      // If needed, you could fetch fresh data from server for the date here.
      // For now, we filter client-side using already fetched notifications.
    },
    clearDate() {
      this.selectedDate = '';
      this.page = 1;
    },
    formatDate(dateStr) {
      // Format MySQL datetime to 'MMM DD, YYYY at hh:mm A'
      const d = new Date(dateStr);
      if (isNaN(d)) return dateStr;
      const options = {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
      };
      return d
        .toLocaleString('en-US', options)
        .replace(',', '')
        .replace(/(\d{2}:\d{2}) (AM|PM)/, 'at $1 $2');
    },
    async markAllAsRead() {
      try {
        let token = storage.get('authToken');
        if (token && typeof token === 'object' && token.token) {
          token = token.token;
        }
        if (typeof token !== 'string') {
          token = '';
        }
        const config = token
          ? { headers: { Authorization: `Bearer ${token}` } }
          : {};
        await axios.post('/api/notifications/mark-all-read', {}, config);
        // Refresh notifications
        await this.fetchNotifications();
        // Ensure badge updates immediately: update internal count and notify navbar
        try {
          this.updateNotificationCount();
        } catch (e) {
          console.error('Error updating notification count:', e);
        }
        // Broadcast events for in-window and cross-tab listeners
        window.dispatchEvent(new Event('notification-updated'));
        window.dispatchEvent(new Event('storage'));
      } catch (error) {
        console.error('Error marking all as read:', error);
        if (this.$notify) {
          this.$notify({
            type: 'error',
            message: 'Failed to mark all notifications as read.',
          });
        }
      }
    },
    async markAsRead(id) {
      const notif = this.paginatedNotifications.find((n) => n.id === id);
      let token = storage.get('authToken');
      if (token && typeof token === 'object' && token.token) {
        token = token.token;
      }
      if (typeof token !== 'string') {
        token = '';
      }
      const config = token
        ? { headers: { Authorization: `Bearer ${token}` } }
        : {};
      if (notif && notif.id) {
        try {
          await axios.post(`/api/announcements/${notif.id}/read`, {}, config);
          await this.fetchNotifications();
          // Ensure badge updates immediately: update internal count and notify navbar
          try {
            this.updateNotificationCount();
          } catch (e) {
            console.error('Error updating notification count:', e);
          }
          window.dispatchEvent(new Event('notification-updated'));
          window.dispatchEvent(new Event('storage'));
        } catch (error) {
          console.error('Failed to mark notification as read:', error);
          if (this.$notify) {
            this.$notify({
              type: 'error',
              message: 'Failed to mark notification as read.',
            });
          }
        }
      }
    },
    prevPage() {
      if (this.page > 1) this.page--;
    },
    nextPage() {
      if (this.page < this.totalPages) this.page++;
    },
    goToPage(n) {
      if (n >= 1 && n <= this.totalPages) this.page = n;
    },
    selectPageSize(size) {
      this.pageSize = size;
      this.page = 1;
      this.showPageDropdown = false;
    },
    togglePageDropdown() {
      this.showPageDropdown = !this.showPageDropdown;
    },
    // single authoritative method for updating stored notification count
    updateNotificationCount() {
      if (!this.notificationsReady) {
        return;
      }
      const unreadCount = Array.isArray(this.notifications)
        ? this.notifications.length
        : 0;
      storage.set('notificationCount', String(unreadCount));
      // Broadcast a storage event for cross-tab listeners
      window.dispatchEvent(new Event('storage'));
      // Broadcast a domain event for in-window subscribers (Navbar)
      window.dispatchEvent(new Event('notification-updated'));
      // Provide direct count payload for listeners to avoid refetch flicker
      window.dispatchEvent(
        new CustomEvent('notification-count-sync', {
          detail: { count: unreadCount },
        })
      );
    },
    markAllRead() {
      this.readNotifications = [
        ...this.readNotifications,
        ...this.notifications,
      ];
      this.notifications = [];
      this.updateNotificationCount();
    },
  },
  watch: {
    notifications: {
      handler() {
        this.updateNotificationCount();
      },
      deep: true,
    },
    readNotifications: {
      handler() {
        this.updateNotificationCount();
      },
      deep: true,
    },
  },
  mounted() {
    // Fetch notifications only when arriving at notifications page or after login
    if (
      storage.get('showDashboardWelcome') ||
      this.$route.name === 'GetNotification' ||
      this.$route.name === 'Notifications'
    ) {
      this.fetchNotifications();
      storage.remove('showDashboardWelcome');
    }
  },
};
</script>
<style scoped>
.notifications-table-outer {
  width: 100%;
  min-width: 260px;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
}
.notifications-table-card {
  width: 100%;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  overflow: visible;

  box-sizing: border-box;
  min-width: 0;

  display: flex;
  flex-direction: column;
  gap: 0;
  position: relative;
  padding: 0;
}
.notifications-controls {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 24px;
  padding: 24px 24px 0 24px;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  min-height: 64px;
  box-sizing: border-box;
}
.notifications-date-wrapper {
  display: flex;
  align-items: center;
  background: #f6f6f6;
  border-radius: 32px;
  padding: 0 18px;
  height: 36px;
  min-width: 140px;
}

.notifications-date {
  border: none;
  outline: none;
  background: transparent;
  font-size: 20px;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
  font-weight: 400;
  color: #888;
  width: 100%;
  padding: 0;
  height: 56px;
}
.notifications-tabs {
  display: flex;
  border-radius: 32px;
  background: #f8f8f8;
  overflow: hidden;
  min-width: 240px;
  height: 36px;
}
.notifications-tab-btn-left {
  border: none;
  border-radius: 32px;
  outline: none;
  background: #f8f8f8;
  color: #0f0f0f;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
  font-size: 20px;
  font-weight: 400;
  line-height: 26px;
  letter-spacing: 0.02em;
  padding: 0 50px;
  flex: 1;
  min-width: 0;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.18s, color 0.18s, border 0.18s, font-weight 0.18s;
  cursor: pointer;
  box-sizing: border-box;
}
.notifications-tab-btn-right {
  border: none;
  border-radius: 32px;
  outline: none;
  background: #f8f8f8;
  color: #0f0f0f;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
  font-size: 20px;
  font-weight: 400;
  line-height: 26px;
  letter-spacing: 0.02em;
  padding: 0 50px;
  flex: 1;
  min-width: 0;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.18s, color 0.18s, border 0.18s, font-weight 0.18s;
  cursor: pointer;
  box-sizing: border-box;
}
.notifications-tab-btn-left.active {
  background: #f6f6f6;
  border: 1px solid #dcdcdc;
  border-radius: 32px 0 0 32px;
  color: #0f0f0f;
  font-weight: 500;
  z-index: 1;
}
.notifications-tab-btn-right.active {
  background: #f6f6f6;
  border: 1px solid #dcdcdc;
  border-radius: 0 32px 32px 0;
  color: #0f0f0f;
  font-weight: 500;
  z-index: 1;
}

.notifications-tab-btn:not(.active) {
  background: #f8f8f8;
  border: none;
  border-radius: 32px;
  color: #0f0f0f;
  font-weight: 400;
}
.mark-all {
  margin-left: auto;
  background: none;
  border: none;
  color: #222;
  font-weight: 500;
  font-size: 1rem;
  cursor: pointer;
}
.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 0 24px 24px 24px;
  background: #fff;
  border-bottom-left-radius: 24px;
  border-bottom-right-radius: 24px;
}
.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px 0;
  border-bottom: 1px solid #f0f0f0;
}
.notification-meta {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-width: 56px;
  height: 100%;
}
.notification-icon {
  width: 32px;
  height: 32px;
  margin-top: 8px;
  margin-bottom: 0;
  background: none;
  border-radius: 0;
  display: block;
  box-shadow: none;
  padding: 0;
  object-fit: contain;
}
.notification-date {
  font-size: 0.95rem;
  color: #888;
  text-align: left;
  margin-bottom: 2px;
  display: block;
}
.notification-body {
  flex: 1;
  text-align: left;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  gap: 2px;
}
.notification-title {
  font-weight: 700;
  color: #0164a5;
  margin-right: 8px;
  text-align: left;
  display: inline;
}
.notification-text {
  color: #222;
  text-align: left;
  display: block;
  line-height: 1.6;
}
.no-data {
  text-align: center;
  color: #888;
  font-size: 16px;
  padding: 32px 0;
}

@media (max-width: 1400px) {
  .notifications-controls {
    padding: 8px 8px 0 8px;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
  }
  .notifications-list {
    padding: 0 8px 8px 8px;
    border-bottom-left-radius: 14px;
    border-bottom-right-radius: 14px;
  }
}
@media (max-width: 900px) {
  .notifications-controls {
    padding: 8px 4px 0 4px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
  }
  .notifications-list {
    padding: 0 4px 4px 4px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
  }
}
</style>
