<template>
  <div class="page">
    <div class="notifications-table-outer">
      <div class="notifications-table-card">
        <div class="notifications-controls">
          <div class="notifications-date-wrapper">
            <img
              src="@/assets/images/Calendar.svg"
              class="calendar-icon"
            />
            <input
              type="text"
              placeholder="Select Date"
              class="notifications-date"
            />
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
            class="mark-all"
            @click="markAllRead"
          >
            Mark all as read
          </button>
        </div>
        <div class="notifications-list">
          <div
            v-for="(item, idx) in paginatedNotifications"
            :key="idx"
            class="notification-item"
          >
            <div class="notification-meta">
              <img
                src="@/assets/images/Logo.svg"
                class="notification-icon"
              />
            </div>
            <div class="notification-body">
              <span class="notification-date">{{ item.date }}</span>
              <span class="notification-text">
                <span class="notification-title">Dolphin.</span>
                {{ item.text }}
              </span>
            </div>
          </div>
          <div
            v-if="paginatedNotifications.length === 0"
            class="no-data"
          >
            No notifications found.
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
import axios from 'axios';
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
      if (this.tab === 'unread') {
        return this.notifications;
      } else if (this.tab === 'all') {
        return [...this.notifications, ...this.readNotifications];
      }
      return this.notifications;
    },
  },
  methods: {
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
      window.dispatchEvent(new Event('storage'));
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
    this.fetchNotifications();
    this.updateNotificationCount();
  },

  methods: {
    async fetchNotifications() {
      try {
        const response = await axios.get('/api/all-notifications');
        // Assuming response.data is an array of notifications with body and sent_at
        this.notifications = response.data.map((n) => ({
          date: n.sent_at ? this.formatDate(n.sent_at) : '',
          text: n.body || '',
        }));
      } catch (error) {
        this.notifications = [];
        this.$nextTick(() => {
          this.$notify &&
            this.$notify({
              type: 'error',
              message: 'Failed to fetch notifications.',
            });
        });
      }
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
      window.dispatchEvent(new Event('storage'));
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
  padding: 24px 46px 0 24px;
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
  height: 56px;
  min-width: 340px;
  margin-right: 24px;
}
.calendar-icon {
  width: 28px;
  height: 28px;
  margin-right: 14px;
  opacity: 0.7;
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
  height: 56px;
}
.notifications-tab-btn {
  border: none;
  outline: none;
  background: #f8f8f8;
  color: #0f0f0f;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
  font-size: 20px;
  font-weight: 400;
  line-height: 26px;
  letter-spacing: 0.02em;
  padding: 0 38px;
  height: 56px;
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
  color: #0f0f0f;
  font-weight: 500;
  z-index: 1;
}
.notifications-tab-btn:not(.active) {
  background: #f8f8f8;
  border: none;
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
