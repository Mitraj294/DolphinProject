<template>
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
              :class="['notifications-tab-btn', { active: tab === 'unread' }]"
              @click="tab = 'unread'"
            >
              Unread
            </button>
            <button
              :class="['notifications-tab-btn', { active: tab === 'all' }]"
              @click="tab = 'all'"
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
            v-for="(item, idx) in paginatedNotifications"
            :key="idx"
            class="notification-item"
          >
            <div
              class="notification-meta"
              style="display: flex; flex-direction: column; align-items: center"
            >
              <img
                src="@/assets/images/Logo.svg"
                class="notification-icon"
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
              style="display: flex; flex-direction: column; align-items: center"
            >
              <button
                v-if="tab === 'unread' && !item.read_at"
                class="mark-all"
                @click="markAsRead(idx)"
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
            <span v-if="tab === 'unread'">No unread notifications found.</span>
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
</template>

<script>
import Pagination from '@/components/layout/Pagination.vue';
import storage from '@/services/storage';
import authMiddleware from '@/middleware/authMiddleware';
import axios from 'axios';
import { ref } from 'vue';

const date = ref();
export default {
  name: 'GetNotification',
  components: { Pagination },
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
        const sel = this.selectedDate;
        list = list.filter((n) => {
          // try multiple places for created timestamp
          const createdAt =
            n.created_at ||
            (n._rawData && (n._rawData.created_at || n._rawData.createdAt)) ||
            '';
          if (!createdAt) return false;
          const d = new Date(createdAt);
          if (isNaN(d)) return false;
          const yyyy = d.getFullYear();
          const mm = String(d.getMonth() + 1).padStart(2, '0');
          const dd = String(d.getDate()).padStart(2, '0');
          return `${yyyy}-${mm}-${dd}` === sel;
        });
      }

      return list;
    },
  },
  methods: {
    async fetchNotifications() {
      try {
        let endpoint =
          this.tab === 'unread'
            ? '/api/notifications/unread'
            : '/api/notifications';
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
        // If asking for "all" notifications, call the superadmin endpoint
        // only when the current role is superadmin. Otherwise use the
        // authenticated user's notifications endpoint to avoid 403.
        if (this.tab === 'all') {
          const role = authMiddleware.getRole();
          if (role === 'superadmin') {
            const storedUserId =
              storage.get('userId') ||
              storage.get('user_id') ||
              storage.get('userId');
            const uid = storedUserId ? parseInt(storedUserId, 10) : 0;
            if (uid) {
              endpoint = `/api/notifications?notifiable_type=${encodeURIComponent(
                'App\\Models\\User'
              )}&notifiable_id=${uid}`;
            } else {
              endpoint = '/api/notifications';
            }
          } else {
            endpoint = '/api/notifications/user';
          }
        }

        let response;
        try {
          response = await axios.get(endpoint, config);
        } catch (err) {
          // If server returns 403 for /api/notifications try the user-specific endpoint
          if (
            err.response &&
            err.response.status === 403 &&
            this.tab === 'all'
          ) {
            try {
              response = await axios.get('/api/notifications/user', config);
            } catch (err2) {
              throw err2;
            }
          } else {
            throw err;
          }
        }
        let notificationsArr = [];
        if (Array.isArray(response.data)) {
          notificationsArr = response.data;
        } else if (
          response.data &&
          Array.isArray(response.data.notifications)
        ) {
          notificationsArr = response.data.notifications;
        } else if (response.data && Array.isArray(response.data.unread)) {
          notificationsArr = response.data.unread;
        }
        // only show notifications related to the logged-in user
        const storedUserId =
          storage.get('userId') ||
          storage.get('user_id') ||
          storage.get('userId');
        const currentUserId = storedUserId ? parseInt(storedUserId, 10) : 0;

        const isForUser = (n) => {
          // If server already returned only user's notifications, allow.
          if (!currentUserId) return true;
          // check standard Laravel notifications table field
          if (
            n.notifiable_id &&
            parseInt(n.notifiable_id, 10) === currentUserId
          )
            return true;
          // check common payload shapes
          if (n.data) {
            try {
              const d =
                typeof n.data === 'string' ? JSON.parse(n.data) : n.data;
              if (d.user_id && parseInt(d.user_id, 10) === currentUserId)
                return true;
              if (d.userId && parseInt(d.userId, 10) === currentUserId)
                return true;
              if (
                d.recipient_id &&
                parseInt(d.recipient_id, 10) === currentUserId
              )
                return true;
            } catch (e) {
              // ignore parse errors
            }
          }
          return false;
        };

        const filtered = notificationsArr.filter(isForUser);

        this.notifications = filtered.map((n) => {
          // normalize data payload
          let d = n.data;
          if (typeof d === 'string') {
            try {
              d = JSON.parse(d);
            } catch (e) {
              // leave as string
            }
          }

          // helper to pick the first non-empty string from common fields
          const pickString = (obj, keys) => {
            if (!obj) return '';
            for (const k of keys) {
              if (Object.prototype.hasOwnProperty.call(obj, k)) {
                const v = obj[k];
                if (typeof v === 'string' && v.trim()) return v.trim();
                if (typeof v === 'number') return String(v);
                if (v && typeof v === 'object' && v.message)
                  return String(v.message);
              }
            }
            return '';
          };

          const bodyFromData = pickString(d, [
            'body',
            'message',
            'text',
            'details',
            'description',
            'content',
            'msg',
          ]);

          const fallbackBody = n.body || '';
          const finalBody = bodyFromData || fallbackBody;

          // If still empty and data is an object, stringify a helpful subset
          let bodyDisplay = finalBody;
          if (!bodyDisplay && d && typeof d === 'object') {
            // try common nested shapes
            bodyDisplay =
              pickString(d, ['user_message', 'notification', 'payload']) ||
              (Object.keys(d).length ? JSON.stringify(d) : '');
          }

          return {
            id: n.id,
            date: n.created_at ? this.formatDate(n.created_at) : '',
            body: bodyDisplay,
            read_at: n.read_at,
            _rawData: d,
          };
        });
        // Notifications have been updated; watchers will synchronize the
        // stored count and emit domain events (avoid duplicate emits here).
      } catch (error) {
        this.notifications = [];
        // Provide more detailed error info for debugging (403 body etc.)
        console.error(
          'Failed to fetch notifications:',
          error,
          error && error.response && error.response.data
        );
        const serverMsg =
          (error &&
            error.response &&
            (error.response.data.message || error.response.data.error)) ||
          null;
        this.$nextTick(() => {
          this.$notify &&
            this.$notify({
              type: 'error',
              message: serverMsg || 'Failed to fetch notifications.',
            });
        });
      }
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
        // Notification count will be synchronized via the watcher -> updateNotificationCount()
      } catch (error) {
        console.error('Error marking all as read:', error);
        this.$notify &&
          this.$notify({
            type: 'error',
            message: 'Failed to mark all notifications as read.',
          });
      }
    },
    async markAsRead(idx) {
      const notif = this.paginatedNotifications[idx];
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
          // Notification count update is handled by the notifications watcher -> updateNotificationCount()
        } catch (error) {
          console.error('Failed to mark notification as read:', error);
          this.$notify &&
            this.$notify({
              type: 'error',
              message: 'Failed to mark notification as read.',
            });
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
      storage.set('notificationCount', this.notifications.length);
      // Broadcast a storage event for cross-tab listeners
      window.dispatchEvent(new Event('storage'));
      // Broadcast a domain event for in-window subscribers (Navbar)
      window.dispatchEvent(new Event('notification-updated'));
    },
    // ...existing code...
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
    markAllRead() {
      this.readNotifications = [
        ...this.readNotifications,
        ...this.notifications,
      ];
      this.notifications = [];
      this.updateNotificationCount();
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
    updateNotificationCount() {
      storage.set('notificationCount', this.notifications.length);
      // Let other tabs/components know the count changed.
      window.dispatchEvent(new Event('storage'));
      // Also emit a domain-level event so listeners that rely on it
      // (Navbar.fetchUnreadCount) can react immediately.
      window.dispatchEvent(new Event('notification-updated'));
    },
  },
  watch: {
    notifications: {
      handler() {
        this.updateNotificationCount();
      },
      deep: true,
      immediate: true,
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
  watch: {
    tab() {
      this.fetchNotifications();
    },
  },
};
</script>

<style scoped>
/* --- Layout and spacing to match Leads/OrganizationTable --- */
.notifications-table-outer {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
  margin: 64px auto 64px auto;
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
  margin: 0 auto;
  box-sizing: border-box;
  min-width: 0;
  max-width: 1400px;
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
  padding: 0 0 0 18px;
  height: 36px;
  min-width: 340px;
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
.notifications-tab-btn {
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
.notifications-tab-btn.active {
  background: #f6f6f6;
  border: 1px solid #dcdcdc;
  border-radius: 32px;
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

/* Responsive styles to match base pages */
@media (max-width: 1400px) {
  .notifications-table-outer {
    margin: 12px;
    max-width: 100%;
  }
  .notifications-table-card {
    border-radius: 14px;
    max-width: 100%;
  }
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
  .notifications-table-outer {
    margin: 4px;
    max-width: 100%;
  }
  .notifications-table-card {
    border-radius: 10px;
  }
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
