<template>
  <MainLayout>
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
                  { label: 'Notification Title', key: 'title' },
                  { label: 'Date & Time', key: 'date' },
                  { label: 'Action', key: 'action' },
                ]"
                @sort="sortBy"
              />
              <tbody>
                <tr
                  v-for="(item, idx) in paginatedNotifications"
                  :key="idx"
                >
                  <td>{{ item.title }}</td>
                  <td>{{ item.date }}</td>
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
                <FormDropdown v-model="selectedOrganization">
                  <option value="">Select</option>
                  <option value="org1">Organization 1</option>
                  <option value="org2">Organization 2</option>
                </FormDropdown>
              </div>
              <div class="modal-field">
                <FormLabel>Select Admin</FormLabel>
                <FormDropdown v-model="selectedAdmin">
                  <option value="">Select</option>
                  <option value="admin1">Admin 1</option>
                  <option value="admin2">Admin 2</option>
                </FormDropdown>
              </div>
            </div>
            <div class="modal-row">
              <div class="modal-field">
                <FormLabel>Select Group</FormLabel>
                <FormDropdown v-model="selectedGroup">
                  <option value="">Select</option>
                  <option value="group1">Group 1</option>
                  <option value="group2">Group 2</option>
                </FormDropdown>
              </div>
              <div class="schedule-demo-field schedule-demo-schedule-field">
                <FormLabel>Schedule</FormLabel>
                <div class="schedule-demo-schedule-inputs">
                  <FormInput
                    v-model="date"
                    type="date"
                    placeholder="MM/DD/YYYY"
                  />
                  <FormInput
                    v-model="time"
                    type="time"
                    placeholder="00:00"
                  />
                </div>
              </div>
            </div>
            <button class="btn btn-primary">Send Notification</button>
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
  },
  data() {
    return {
      showPageDropdown: false,
      showSendModal: false,
      pageSize: 10,
      currentPage: 1,
      sortKey: '',
      sortAsc: true,
      selectedOrganization: '',
      selectedAdmin: '',
      selectedGroup: '',
      scheduledDate: '',
      scheduledTime: '',
      notifications: [
        { title: 'Lorem Ipsum is simply', date: 'Jan 22, 2025 at 02:00 PM' },
        {
          title: 'Dummy text of the printing',
          date: 'Jan 22, 2025 at 02:00 PM',
        },
        {
          title: 'And typesetting industry.',
          date: 'Jan 12, 2025 at 01:15 PM',
        },
        {
          title: 'Lorem Ipsum text has been',
          date: 'Jan 10, 2025 at 01:00 PM',
        },
        { title: 'The industry standard', date: 'Jan 6, 2025 at 12:00 PM' },
        { title: 'Dummy text ever since', date: 'Dec 24, 2024 at 10:00 AM' },
        { title: 'When an unknown printer', date: 'Dec 24, 2024 at 10:00 AM' },
        { title: 'took a galley of type', date: 'Dec 24, 2024 at 10:00 AM' },
        { title: 'And scrambled it to make', date: 'Dec 24, 2024 at 10:00 AM' },
        { title: 'And scrambled it to make', date: 'Dec 15, 2024 at 4:40 PM' },
      ],
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
    sendNotification() {
      this.showSendModal = false;
    },
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
.schedule-demo-schedule-inputs {
  display: flex;
  gap: 18px;
  width: 100%;
}
.schedule-demo-schedule-inputs input[type='date'],
.schedule-demo-schedule-inputs input[type='time'] {
  flex: 1 1 0;
  min-width: 0;
  background: #fafafa;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 14px 10px 44px;
  font-size: 15px;
  color: #222;
  outline: none;
  transition: border 0.2s;
  box-sizing: border-box;
}
.schedule-demo-schedule-inputs input[type='date']::placeholder,
.schedule-demo-schedule-inputs input[type='time']::placeholder {
  color: #888;
  opacity: 1;
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
