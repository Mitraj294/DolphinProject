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
                    width: '400px',
                    sortable: true,
                  },
                  {
                    label: 'Date & Time',
                    key: 'sent_at',
                    width: '225px',
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
                  v-for="(item, idx) in paginatedNotifications"
                  :key="idx"
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
                  <td>{{ formatLocalDateTime(item.sent_at) }}</td>
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

// mixins
import notificationsCommonMixin from './mixins/notificationsCommonMixin';
import notificationsSendMixin from './mixins/notificationsSendMixin';
import notificationsDetailMixin from './mixins/notificationsDetailMixin';

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
  mixins: [
    notificationsCommonMixin,
    notificationsSendMixin,
    // notificationsDetailMixin, // Re-implementing detail logic here
  ],
  data() {
    return {
      showDetailModal: false,
      selectedNotification: null, // Kept for compatibility if needed elsewhere
      detailData: null,
    };
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
