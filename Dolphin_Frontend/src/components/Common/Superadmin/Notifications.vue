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
                  { label: 'Notification Title', key: 'body' },
                  { label: 'Date & Time', key: 'sent_at', width: '225px' },
                  { label: 'Action', key: 'action' },
                ]"
                @sort="sortBy"
              />
              <tbody>
                <tr
                  v-for="(item, idx) in paginatedNotifications"
                  :key="idx"
                >
                  <td class="notification-body-cell">
                    <span
                      class="notification-body-truncate"
                      :title="item.body"
                    >
                      {{ item.body }}
                    </span>
                  </td>
                  <td>{{ formatLocalDateTime(item.sent_at) }}</td>
                  <td>
                    <button class="btn-view">
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
                    colspan="3"
                    class="no-data"
                  >
                    No notifications found.
                  </td>
                </tr>
              </tbody>
            </table>
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
          class="send-notification-modal-overlay"
          @click.self="showSendModal = false"
        >
          <div class="send-notification-modal">
            <button
              class="modal-close-btn"
              @click="showSendModal = false"
            >
              &times;
            </button>
            <div class="modal-title">Send Notifications</div>
            <div class="modal-desc">
              Send a notification to selected organizations, admins, or groups.
              You can also schedule it for later.
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
                  option-label="org_name"
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
              <!--
                This section renders a dropdown menu for selecting an admin user.
                - The dropdown is bound to the `selectedAdmin` model.
                - Options include a default "Select" prompt and two admin choices ("Admin 1" and "Admin 2").
                - The label "Select Admin" is displayed above the dropdown.
                This part will be used later for admin selection functionality.
            
              <div class="modal-field">
                <FormLabel>Select Admin</FormLabel>
                <FormDropdown v-model="selectedAdmin">
                  <option value="">Select</option>
                  <option value="admin1">Admin 1</option>
                  <option value="admin2">Admin 2</option>
                </FormDropdown>
              </div>
                -->
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
      </div>
    </div>
  </MainLayout>
</template>

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
      showPageDropdown: false,
      showSendModal: false,
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
      const date = new Date(dateStr.replace(' ', 'T') + 'Z');
      return date.toLocaleString();
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
          // Combine date and time as local, then convert to UTC ISO string
          const localDateTime = new Date(`${this.scheduledDate}T${time}`);
          scheduled_at = localDateTime
            .toISOString()
            .replace('T', ' ')
            .replace(/\.\d+Z$/, 'Z');
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
        this.showSendModal = false;
        this.$toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Announcement sent!',
          life: 3000,
        });
        console.log('Announcement sent successfully');
      } catch (err) {
        console.error('Error sending notification:', err);
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to send announcement',
          life: 4000,
        });
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
        this.organizations = res.data;
      } catch (err) {
        console.error('Error fetching organizations:', err);
        this.organizations = [];
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
        this.groups = res.data;
      } catch (err) {
        console.error('Error fetching groups:', err);
        this.groups = [];
      }
    },
    async fetchNotifications() {
      try {
        const apiUrl = process.env.VUE_APP_API_URL || '/api';
        const token = storage.get('authToken');
        const res = await axios.get(apiUrl + '/announcements', {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.notifications = Array.isArray(res.data)
          ? res.data
          : res.data.notifications || [];
      } catch (err) {
        console.error('Error fetching notifications:', err);
        this.notifications = [];
      }
    },
  },
  watch: {
    // No watchers - selections are independent
  },
  mounted() {
    this.fetchOrganizations();
    this.fetchGroups();
    this.fetchNotifications();
    console.log('Mounted: fetching organizations, groups, notifications');
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
</style>
