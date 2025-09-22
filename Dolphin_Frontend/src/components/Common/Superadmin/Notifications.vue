<template>
  <MainLayout>
    <Toast />
    <div class="page">
      <div class="table-outer">
        <div class="table-card">
          <div class="table-header-bar">
            <button
              class="btn btn-primary"
              @click="showSendModal = true"
            >
              <img
                src="@/assets/images/SendNotification.svg"
                alt="Send"
                class="notifications-add-btn-icon"
              />
              Send Notification
            </button>
          </div>
          <div class="table-container">
            <table class="table">
              <TableHeader
                :columns="[
                  {
                    label: 'Notification Title',
                    key: 'body',
                    minWidth: '225px',
                    sortable: true,
                  },
                  {
                    label: 'Scheduled Date & Time',
                    key: 'scheduled_at',
                    minWidth: '225px',
                    sortable: true,
                  },

                  {
                    label: 'Sent Date & Time',
                    key: 'sent_at',
                    minWidth: '225px',
                    sortable: true,
                  },
                  { label: 'Action', key: 'action', minWidth: '200px' },
                ]"
                :activeSortKey="sortKey"
                :sortAsc="sortAsc"
                @sort="sortBy"
              />
              <tbody>
                <tr
                  v-for="item in paginatedNotifications"
                  :key="item.id"
                >
                  <td class="notification-body-cell">
                    <span
                      class="notification-body-truncate"
                      :title="item.body"
                      style="
                        max-width: 200px;
                        transition: max-width 0.18s ease, white-space 0.18s ease;
                        overflow: hidden;
                        white-space: nowrap;
                        text-overflow: ellipsis;
                        display: inline-block;
                      "
                    >
                      {{ item.body }}
                    </span>
                  </td>
                  <td>{{ formatLocalDateTime(item.scheduled_at) }}</td>
                  <td>
                    {{ item.sent_at ? formatLocalDateTime(item.sent_at) : '-' }}
                  </td>
                  <td>
                    <button
                      class="btn-view"
                      @click="openDetail(item)"
                    >
                      <img
                        src="@/assets/images/Detail.svg"
                        alt="View"
                        class="btn-view-icon"
                      />
                      View Detail
                    </button>
                  </td>
                </tr>
                <tr v-if="paginatedNotifications.length === 0">
                  <td
                    colspan="4"
                    class="no-data"
                  >
                    No notifications found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <Pagination
      :pageSize="pageSize"
      :pageSizes="[10, 25, 100]"
      :showPageDropdown="showPageDropdown"
      :currentPage="currentPage"
      :totalPages="totalPages"
      :paginationPages="paginationPages"
      @goToPage="goToPage"
      @selectPageSize="selectPageSize"
      @togglePageDropdown="showPageDropdown = !showPageDropdown"
    />
    <div
      v-if="showSendModal"
      class="modal-overlay"
      @click.self="showSendModal = false"
    >
      <div class="modal-card">
        <button
          class="modal-close-btn"
          @click="showSendModal = false"
        >
          &times;
        </button>
        <div class="modal-title">Send Notifications</div>
        <div class="modal-desc">
          Send a notification to selected organizations, admins, or groups. You
          can also schedule it for later.
        </div>
        <textarea
          class="modal-textarea"
          placeholder="Type your notification here..."
        ></textarea>
        <div class="modal-row">
          <div class="modal-field">
            <FormLabel>Select Organizations</FormLabel>
            <MultiSelectDropdown
              :options="organizations || []"
              :selectedItems="selectedOrganizations"
              @update:selectedItems="selectedOrganizations = $event"
              option-label="organization_name"
              option-value="id"
              placeholder="Select organizations"
              :enableSelectAll="true"
            />
          </div>
          <div class="modal-field">
            <FormLabel>Select Group</FormLabel>
            <MultiSelectDropdown
              :options="groups || []"
              :selectedItems="selectedGroups"
              @update:selectedItems="selectedGroups = $event"
              placeholder="Select groups"
              :enableSelectAll="true"
            />
          </div>
        </div>
        <div class="modal-row">
          <div class="modal-field">
            <FormLabel>Select Admin</FormLabel>
            <MultiSelectDropdown
              :options="admins || []"
              :selectedItems="selectedAdmins"
              @update:selectedItems="selectedAdmins = $event"
              option-label="name"
              option-value="id"
              placeholder="Select admins"
              :enableSelectAll="true"
            />
          </div>
          <div class="modal-field"></div>
        </div>
        <div class="modal-row">
          <div class="schedule-demo-field schedule-demo-schedule-field">
            <FormLabel>Schedule</FormLabel>
            <div class="modal-row">
              <div class="modal-field">
                <div class="form-box">
                  <FormInput
                    v-model="scheduledDate"
                    type="date"
                    placeholder="MM/DD/YYYY"
                  />
                </div>
              </div>

              <div class="modal-field">
                <div class="form-box">
                  <FormInput
                    v-model="scheduledTime"
                    type="time"
                    placeholder="00:00"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
        <button
          class="btn btn-primary"
          @click="sendNotification"
        >
          Send Notification
        </button>
      </div>
    </div>

    <!-- Notification Detail Modal -->
    <NotificationDetail
      :visible="showDetailModal"
      :announcement="detailData && detailData.announcement"
      :groups="detailData && detailData.groups"
      :organizations="detailData && detailData.organizations"
      :notifications="detailData && detailData.notifications"
      @close="closeDetail"
    />
  </MainLayout>
</template>

<script>
import MainLayout from '../../layout/MainLayout.vue';
import axios from 'axios';
import storage from '@/services/storage';
import Pagination from '../../layout/Pagination.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
import NotificationDetail from './NotificationDetail.vue';
import {
  FormDropdown,
  FormRow,
  FormLabel,
  FormDateTime,
} from '@/components/Common/Common_UI/Form';
import FormInput from '../Common_UI/Form/FormInput.vue';
import MultiSelectDropdown from '../Common_UI/Form/MultiSelectDropdown.vue';
import Toast from 'primevue/toast';

export default {
  name: 'Notifications',
  components: {
    MainLayout,
    Pagination,
    TableHeader,
    NotificationDetail,
    FormDropdown,
    FormRow,
    FormInput,
    FormLabel,
    FormDateTime,
    MultiSelectDropdown,
    Toast,
  },

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
      selectedAdmins: [],
      selectedGroups: [],
      scheduledDate: '',
      scheduledTime: '',
      organizations: [],
      groups: [],
      admins: [],
      notifications: [],
      announcements: [],
      // local component state
      detailData: null,
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
    async openDetail(item) {
      if (!item || !item.id) {
        console.error('Invalid item for detail view:', item);
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Cannot load details for invalid item.',
          life: 3000,
        });
        return;
      }
      try {
        const authToken = storage.get('authToken');
        const res = await axios.get(
          `${
            process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000'
          }/api/announcements/${item.id}`,
          { headers: { Authorization: `Bearer ${authToken}` } }
        );
        if (res.data) {
          this.detailData = res.data;
          this.selectedNotification = res.data.announcement;
          this.showDetailModal = true;
        }
      } catch (error) {
        console.error('Failed to fetch notification details', error);
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to fetch notification details. Please try again.',
          life: 3000,
        });
      }
    },
    closeDetail() {
      this.showDetailModal = false;
      this.selectedNotification = null;
      this.detailData = null;
    },
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
        if (/T.*Z$/.test(dateStr) || /T.*[+-]\d{2}:?\d{2}$/.test(dateStr)) {
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
            const second = parseInt(m[6], 10) || 0;
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
      const dayOfMonth = String(d.getDate()).padStart(2, '0');
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

      const strTime = `${hr}:${min} ${ampm}`;
      return `${dayOfMonth} ${mon},${yr} ${strTime}`;
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
        console.error('Error fetching organizations:', err);
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
        console.error('Error fetching groups:', err);
        if (this.isAlive) this.groups = [];
      }
    },
    async fetchAdmins() {
      try {
        const apiUrl = process.env.VUE_APP_API_URL || '/api';
        const token = storage.get('authToken');
        const res = await axios.get(apiUrl + '/users', {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (this.isAlive) this.admins = res.data;
      } catch (err) {
        console.error('Error fetching admins:', err);
        if (this.isAlive) this.admins = [];
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
          // API may return { data: [...] } or an array directly
          if (Array.isArray(res.data)) {
            this.notifications = res.data;
          } else if (res.data && Array.isArray(res.data.data)) {
            this.notifications = res.data.data;
          } else if (res.data && Array.isArray(res.data.notifications)) {
            this.notifications = res.data.notifications;
          } else {
            this.notifications = [];
          }
        }
      } catch (err) {
        console.error('Error fetching notifications:', err);
        if (this.isAlive) this.notifications = [];
      }
    },
    async sendNotification() {
      try {
        const apiUrl = process.env.VUE_APP_API_URL || '/api';
        const token = storage.get('authToken');
        // Collect data from modal
        let scheduled_at;
        if (this.scheduledDate && this.scheduledTime) {
          let time = this.scheduledTime;
          if (time.length === 5) time += ':00';
          const local = new Date(`${this.scheduledDate}T${time}`);
          const pad = (n) => String(n).padStart(2, '0');
          const YYYY = local.getUTCFullYear();
          const MM = pad(local.getUTCMonth() + 1);
          const DD = pad(local.getUTCDate());
          const hh = pad(local.getUTCHours());
          const mm = pad(local.getUTCMinutes());
          const ss = pad(local.getUTCSeconds());
          scheduled_at = `${YYYY}-${MM}-${DD} ${hh}:${mm}:${ss}`;
        }
        const payload = {
          organization_ids: this.selectedOrganizations.map((org) => org.id),
          group_ids: this.selectedGroups.map((group) => group.id),
          admin_ids: this.selectedAdmins.map((admin) => admin.id),
          body: this.$el.querySelector('.modal-textarea')
            ? this.$el.querySelector('.modal-textarea').value
            : '',
        };
        if (scheduled_at) payload.scheduled_at = scheduled_at;
        await axios.post(apiUrl + '/notifications/send', payload, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (this.isAlive) {
          this.showSendModal = false;
          this.resetForm();
          this.$toast &&
            this.$toast.add &&
            this.$toast.add({
              severity: 'success',
              summary: 'Success',
              detail: 'Announcement sent!',
              life: 3000,
            });
        }
      } catch (err) {
        console.error('Error sending announcement:', err);
        if (this.isAlive && this.$toast && this.$toast.add) {
          this.$toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to send announcement',
            life: 4000,
          });
        }
      }
    },
    resetForm() {
      this.selectedOrganizations = [];
      this.selectedAdmins = [];
      this.selectedGroups = [];
      this.scheduledDate = '';
      this.scheduledTime = '';
      // Clear the textarea
      const textarea = this.$el.querySelector('.modal-textarea');
      if (textarea) {
        textarea.value = '';
      }
    },
  },
  mounted() {
    this.isAlive = true;
    this.fetchOrganizations();
    this.fetchGroups();
    this.fetchAdmins();
    this.fetchNotifications();
  },
  beforeUnmount() {
    this.isAlive = false;
  },
};
</script>

<style>
@import '@/assets/global.css';
@import '@/assets/modelcssnotificationandassesment.css';
</style>

<!-- Unified modal styles for consistency across components -->
<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.13);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-card {
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 4px 32px rgba(33, 150, 243, 0.08);
  padding: 36px 44px;
  max-width: 720px;
  width: 100%;
  box-sizing: border-box;
  position: relative;
}

.modal-close-btn {
  position: absolute;
  top: 18px;
  right: 18px;
  background: none;
  border: none;
  font-size: 28px;
  color: #888;
  cursor: pointer;
  z-index: 10;
}

.modal-title {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 12px;
  color: #222;
}

.modal-desc {
  margin-bottom: 12px;
  color: #000000;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.modal-form-row {
  display: flex;
  gap: 12px;
}

.modal-form-actions {
  display: flex;
  justify-content: flex-end;
}

.modal-save-btn {
  padding: 10px 28px;
  border-radius: 20px;
}

.modal-textarea {
  width: 100%;
  min-height: 80px;
  border-radius: 10px;
  border: 1.5px solid #e0e0e0;
  padding: 12px 16px;
  font-size: 16px;
  color: #222;
  margin-bottom: 18px;
  resize: vertical;
  background: #fafafa;
  outline: none;
  font-family: inherit;
}

.modal-row {
  display: flex;
  gap: 18px;
  width: 100%;
  margin-bottom: 18px;
}

.modal-field {
  flex: 1 1 0;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.modal-field label {
  color: #222;
  font-size: 15px;
  font-weight: 400;
  text-align: left;
}

.modal-field select,
.modal-date-input,
.modal-time-input {
  background: #fff;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 15px;
  color: #222;
  outline: none;
  transition: border 0.2s;
}

.schedule-demo-field {
  flex: 1 1 0;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.schedule-demo-field label {
  color: #222;
  font-size: 15px;
  font-weight: 400;
  text-align: left;
}

.schedule-demo-field input,
.schedule-demo-field select {
  background: #fafafa;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 15px;
  color: #222;
  outline: none;
  transition: border 0.2s;
}

.no-data {
  text-align: center;
  color: #888;
  font-size: 16px;
  padding: 32px 0;
}

.table-container .table {
  width: 100%;
  table-layout: fixed;
}

.notification-body-cell {
  width: 60%;
  max-width: 100%;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.notification-body-truncate {
  display: inline-block;
  max-width: 100%;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  cursor: pointer;
}

.notifications-add-btn-icon {
  width: 18px;
  height: 18px;
  margin-right: 6px;
  display: inline-block;
  vertical-align: middle;
}

.page {
  padding: 0 32px 32px 32px;
  display: flex;
  background-color: #fff;
  justify-content: center;
  box-sizing: border-box;
}

@media (max-width: 1400px) {
  .page {
    padding: 16px;
  }
}

@media (max-width: 900px) {
  .page {
    padding: 4px;
  }

  .modal-row {
    flex-direction: column;
    gap: 12px;
  }
}

@media (max-width: 700px) {
  .modal-card {
    min-width: 0;
    max-width: calc(100vw - 32px);
    width: calc(98vw - 32px);
    padding: 20px 16px 20px 16px;
    border-radius: 14px;
    margin: 16px;
  }
}

@media (max-width: 500px) {
  .modal-card {
    min-width: 0;
    max-width: calc(100vw - 24px);
    width: calc(98vw - 24px);
    padding: 18px 12px 18px 12px;
    border-radius: 12px;
    margin: 12px;
  }

  .modal-title {
    font-size: 20px;
    margin-bottom: 18px;
  }

  .modal-form {
    gap: 10px;
    padding: 0;
  }

  .modal-form-row {
    flex-direction: column;
    gap: 10px;
    width: 100%;
  }

  .modal-save-btn {
    padding: 8px 18px;
    font-size: 15px;
    border-radius: 14px;
  }

  .modal-close-btn {
    top: 10px;
    right: 12px;
    font-size: 26px;
  }

  .modal-form-actions {
    margin-top: 10px;
  }
}
</style>
