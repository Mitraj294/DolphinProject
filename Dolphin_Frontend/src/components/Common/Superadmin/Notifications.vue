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
                  { label: 'Notification Title', key: 'body', sortable: true },
                  {
                    label: 'Date & Time',
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

                    <!-- Send modal already defined earlier -->

                    <!-- Improved Notification Detail Modal -->
                    <div
                      v-if="showDetailModal && selectedNotification"
                      class="send-notification-modal-overlay"
                      @click.self="closeDetail"
                    >
                      <div class="send-notification-modal notification-detail-modal">
                        <button class="modal-close-btn" @click="closeDetail">&times;</button>
                        <div class="notification-detail-header">
                          <div>
                            <div class="modal-title">{{ selectedNotification.title || 'Notification Detail' }}</div>
                            <div class="modal-desc">{{ selectedNotification.subtitle || 'Details for the selected notification.' }}</div>
                          </div>
                          <div class="notification-badges">
                            <div class="status-badge">{{ announcementStatus }}</div>
                            <div class="recipient-count">{{ recipientCountText }}</div>
                          </div>
                        </div>

                        <div class="notification-detail-grid">
                          <div class="left-col">
                            <div class="section">
                              <div class="section-title">Title</div>
                              <div class="section-body">{{ selectedNotification.body }}</div>
                            </div>

                            <div class="section two-cols">
                              <div>
                                <div class="section-title">Sender</div>
                                <div class="section-body">{{ senderName || 'System' }}</div>
                              </div>
                              <div>
                                <div class="section-title">Date & Time</div>
                                <div class="section-body">
                                  <div v-if="selectedNotification.sent_at">Sent: {{ formatLocalDateTime(selectedNotification.sent_at) }}</div>
                                  <div v-if="selectedNotification.scheduled_at">Scheduled: {{ formatLocalDateTime(selectedNotification.scheduled_at) }}</div>
                                  <div v-if="!selectedNotification.sent_at && !selectedNotification.scheduled_at">Created: {{ formatLocalDateTime(selectedNotification.created_at) }}</div>
                                </div>
                              </div>
                            </div>

                            <div class="section">
                              <div class="section-title">Organizations</div>
                              <div class="section-body">
                                <div v-if="orgChips.length">
                                  <span v-for="(o, i) in orgChips" :key="i" class="chip">{{ o }}</span>
                                </div>
                                <div v-else class="muted">—</div>
                              </div>
                            </div>

                            <div class="section">
                              <div class="section-title">Groups</div>
                              <div class="section-body">
                                <div v-if="groupChips.length">
                                  <span v-for="(g, i) in groupChips" :key="i" class="chip chip-secondary">{{ g }}</span>
                                </div>
                                <div v-else class="muted">—</div>
                              </div>
                            </div>

                            <div class="section">
                              <div class="section-title">Admin Emails</div>
                              <div class="section-body">
                                <div v-if="adminEmails.length">
                                  <div v-for="(e, i) in adminEmails" :key="i" class="small-muted">{{ e }}</div>
                                </div>
                                <div v-else class="muted">—</div>
                              </div>
                            </div>
                          </div>

                          <div class="right-col">
                            <div class="section">
                              <div class="section-title">Recipients</div>
                              <div class="recipient-list">
                                <div v-for="(r, ri) in detailRecipients" :key="ri" class="recipient-row">
                                  <div class="recipient-info">
                                    <div class="recipient-name">{{ r.name }}</div>
                                    <div class="recipient-email" v-if="r.email">{{ r.email }}</div>
                                  </div>
                                  <div class="recipient-status">
                                    <span v-if="r.status === 'delivered'" class="status-icon delivered" v-html="tickSvg"></span>
                                    <span v-else-if="r.status === 'failed'" class="status-icon failed" v-html="crossSvg"></span>
                                    <span v-else class="status-icon pending">Pending</span>
                                  </div>
                                </div>
                                <div v-if="detailRecipients.length === 0" class="muted">All</div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="modal-actions">
                          <button class="btn btn-secondary" @click="closeDetail">Close</button>
                        </div>
                      </div>
                    </div>

                  </div> <!-- .table-outer -->
                </div> <!-- .page -->
              </MainLayout>
            </template>
                                <div class="section-body">
                                  <div v-if="groupChips.length">
                                    <span v-for="(g, i) in groupChips" :key="i" class="chip chip-secondary">{{ g }}</span>
                                  </div>
                                  <div v-else class="muted">—</div>
                                </div>
                              </div>

                              <div class="section">
                                <div class="section-title">Admin Emails</div>
                                <div class="section-body">
                                  <div v-if="adminEmails.length">
                                    <div v-for="(e, i) in adminEmails" :key="i" class="small-muted">{{ e }}</div>
                                  </div>
                                  <div v-else class="muted">—</div>
                                </div>
                              </div>
                            </div>

                            <div class="right-col">
                              <div class="section">
                                <div class="section-title">Recipients</div>
                                <div class="recipient-list">
                                  <div
                                    v-for="(r, ri) in detailRecipients"
                                    :key="ri"
                                    class="recipient-row"
                                  >
                                    <div class="recipient-info">
                                      <div class="recipient-name">{{ r.name }}</div>
                                      <div class="recipient-email" v-if="r.email">{{ r.email }}</div>
                                    </div>
                                    <div class="recipient-status">
                                      <span v-if="r.status === 'delivered'" class="status-icon delivered" v-html="tickSvg"></span>
                                      <span v-else-if="r.status === 'failed'" class="status-icon failed" v-html="crossSvg"></span>
                                      <span v-else class="status-icon pending">Pending</span>
                                    </div>
                                  </div>
                                  <div v-if="detailRecipients.length === 0" class="muted">All</div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="modal-actions">
                            <button class="btn btn-secondary" @click="closeDetail">Close</button>
                          </div>
                        </div>
                      </div>
<script>
import MainLayout from '../../layout/MainLayout.vue';
import Pagination from '../../layout/Pagination.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
import {
  FormDropdown,
  FormRow,
  FormLabel,
  FormDateTime,
} from '@/components/Common/Common_UI/Form';
import FormInput from '../Common_UI/Form/FormInput.vue';
import MultiSelectDropdown from '../Common_UI/Form/MultiSelectDropdown.vue';
import axios from 'axios';
import storage from '@/services/storage';
import Toast from 'primevue/toast';
export default {
  name: 'Notifications',
  components: {
    MainLayout,
    Pagination,
    TableHeader,
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
      selectedGroups: [],
      scheduledDate: '',
      scheduledTime: '',
      organizations: [],
      groups: [],
      // orgGroupsMap and syncing logic removed to keep selections independent
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
    // detailRecipients and svg getters
    detailRecipients() {
      const n = this.selectedNotification || {};
      const list = [];
      // If backend supplied explicit recipients array, use it.
      if (Array.isArray(n.recipients) && n.recipients.length) {
        n.recipients.forEach((r) => {
          list.push({
            name: r.name || r.full_name || r.email || 'Unknown',
            email: r.email || '',
            status: r.status || r.delivery_status || r.state || 'pending',
          });
        });
        return list;
      }
      // Fallback: build from organizations/groups/admins info
      if (Array.isArray(n.organization_names) && n.organization_names.length) {
        n.organization_names.forEach((on) => {
          list.push({ name: on, email: '', status: 'pending' });
        });
      }
      if (Array.isArray(n.group_names) && n.group_names.length) {
        n.group_names.forEach((gn) => {
          list.push({ name: gn, email: '', status: 'pending' });
        });
      }
      return list;
    },
    tickSvg() {
      return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17L4 12" stroke="#16A34A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    },
    crossSvg() {
      return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 6L6 18M6 6l12 12" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
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
    openDetail(item) {
      if (!this.isAlive) return;
      this.selectedNotification = item || null;
      this.showDetailModal = !!item;
    },
    closeDetail() {
      if (!this.isAlive) return;
      this.showDetailModal = false;
      this.selectedNotification = null;
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
          // Convert local date+time to an explicit UTC datetime string so backend stores UTC only.
          // Build a Date from local inputs, then extract UTC components.
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
          body: this.$el.querySelector('.modal-textarea').value,
        };
        if (scheduled_at) {
          payload.scheduled_at = scheduled_at;
        }
        console.log('Sending notification payload:', payload);
        await axios.post(apiUrl + '/announcements/send', payload, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (this.isAlive) {
          this.showSendModal = false;
          this.$toast &&
            this.$toast.add &&
            this.$toast.add({
              severity: 'success',
              summary: 'Success',
              detail: 'Announcement sent!',
              life: 3000,
            });
        }
        console.log('Announcement sent successfully');
      } catch (err) {
        console.error('Error sending notification:', err);
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
    async fetchOrganizations() {
      try {
        const apiUrl = process.env.VUE_APP_API_URL || '/api';
        const token = storage.get('authToken');
        const res = await axios.get(apiUrl + '/organizations', {
          headers: { Authorization: `Bearer ${token}` },
        });
        console.log('Organizations response:', res.data);
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
        console.log('Groups response:', res.data);
        if (this.isAlive) this.groups = res.data;
      } catch (err) {
        console.error('Error fetching groups:', err);
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
        console.error('Error fetching notifications:', err);
        if (this.isAlive) this.notifications = [];
      }
    },
  },
  watch: {
    // No watchers - selections are independent
  },
  mounted() {
    this.isAlive = true;
    this.fetchOrganizations();
    this.fetchGroups();
    this.fetchNotifications();
    console.log('Mounted: fetching organizations, groups, notifications');
  },
  beforeUnmount() {
    // lifecycle guard
    this.isAlive = false;
  },
};
</script>

<style>
@import '@/assets/global.css';
</style>
<style scoped>
.no-data {
  text-align: center;
  color: #888;
  font-size: 16px;
  padding: 32px 0;
}

.table-container .table {
  width: 100%;
  table-layout: fixed; /* respect column widths */
}

.notification-body-cell {
  /* make body column the dominant column */
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
.send-notification-modal {
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 4px 32px 0 rgba(33, 150, 243, 0.1);
  padding: 40px 48px 32px 48px;
  min-width: 320px;
  max-width: 700px;
  width: 100%;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
.send-notification-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(34, 34, 34, 0.18);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-close-btn {
  position: absolute;
  top: 24px;
  right: 32px;
  background: none;
  border: none;
  font-size: 32px;
  color: #888;
  cursor: pointer;
  z-index: 10;
}
.modal-title {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #222;
}
.modal-desc {
  font-size: 16px;
  color: #555;
  margin-bottom: 18px;
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
.modal-field-schedule .modal-schedule-fields {
  display: flex;
  gap: 8px;
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
@media (max-width: 900px) {
  .send-notification-modal {
    padding: 18px 8px;
    border-radius: 14px;
  }
  .modal-row {
    flex-direction: column;
    gap: 12px;
  }
}
@media (max-width: 600px) {
  .send-notification-modal {
    padding: 12px 2vw;
    border-radius: 10px;
    min-width: 0;
    max-width: 98vw;
  }
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
}

.recipient-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 240px;
  overflow: auto;
}
.recipient-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 10px;
  border-radius: 8px;
  background: #fafafa;
}
.recipient-info {
  display: flex;
  flex-direction: column;
}
.recipient-name {
  font-weight: 600;
  color: #222;
}
.recipient-email {
  font-size: 13px;
  color: #666;
}
.recipient-status .status-icon {
  display: inline-block;
  vertical-align: middle;
}
.recipient-status .delivered svg {
  filter: none;
}
.recipient-status .failed svg {
  filter: none;
}
.recipient-status .pending {
  color: #999;
}
</style>
