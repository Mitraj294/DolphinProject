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
                    key: 'sent_at',
                    width: '225px',
                    sortable: true,
                  },
                  { label: 'Action', key: 'action' },
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

    <!-- Notification Detail Modal -->
    <div
      v-if="showDetailModal && selectedNotification"
      class="modal-overlay"
      @click.self="closeDetail"
    >
      <div class="modal-card">
        <button
          class="modal-close-btn"
          @click="closeDetail"
        >
          &times;
        </button>
        <div class="modal-title2">Notification Detail</div>
        <div class="modal-desc">Details for the selected notification.</div>
        <div
          class="modal-title2 schedule-header"
          style="font-size: 20px; font-weight: 450"
        >
          <div class="schedule-header-left">
            <div>
              <div
                class="schedule-assessment-name"
                style="
                  display: inline-block;
                  vertical-align: middle;
                  max-width: 520px;
                  margin-right: 12px;
                "
              >
                {{ selectedNotification.body }}
              </div>
              -
              <div
                class="schedule-assessment-name"
                style="
                  display: inline-block;
                  vertical-align: middle;
                  margin-left: 12px;
                "
              >
                {{
                  formatLocalDateTime(
                    selectedNotification.sent_at ||
                      selectedNotification.created_at
                  )
                }}
              </div>
            </div>
          </div>
        </div>
        <div class="detail-row">
          <div class="detail-table">
            <div class="detail-value">
              <!-- Render recipients as a compact table grouped by group -->
              <div class="recipient-table-wrap">
                <table
                  class="recipient-table compact"
                  v-if="recipientTableRows.length"
                >
                  <thead>
                    <tr>
                      <th style="width: 20%">Category</th>
                      <th style="width: 30%">Name</th>
                      <th style="width: 30%">Email</th>
                      <th style="width: 20%">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(row, idx) in recipientTableRows"
                      :key="idx"
                    >
                      <td
                        class="group-cell"
                        v-if="row.showGroup"
                        :rowspan="row.rowspan"
                      >
                        {{ row.category || '' }}
                      </td>
                      <td>{{ row.name || '' }}</td>
                      <td>{{ row.email || '' }}</td>
                      <td>{{ row.status || '' }}</td>
                    </tr>
                  </tbody>
                </table>

                <!-- Fallback: use simple recipient list if no structured rows available -->
                <div
                  v-else
                  class="recipient-list"
                >
                  <div
                    v-for="(r, ri) in detailRecipients"
                    :key="ri"
                    class="recipient-row"
                  >
                    <div class="recipient-info">
                      <div class="recipient-name">{{ r.name }}</div>
                      <div
                        class="recipient-email"
                        v-if="r.email"
                      >
                        {{ r.email }}
                      </div>
                    </div>
                    <div class="recipient-status">
                      <span
                        class="recipient-badge"
                        :class="{
                          read: r.status === 'delivered' || r.status === 'read',
                          unread: !(
                            r.status === 'delivered' || r.status === 'read'
                          ),
                        }"
                        >{{
                          r.status === 'delivered' || r.status === 'read'
                            ? 'Read'
                            : 'Yet to read'
                        }}</span
                      >
                    </div>
                  </div>
                  <div
                    v-if="detailRecipients.length === 0"
                    class="all-recipients"
                  >
                    All
                  </div>
                </div>
              </div>
            </div>
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
    notificationsDetailMixin,
  ],
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
.modal-close-btn,
.modal-close {
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
.modal-title,
.modal-title2 {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 12px;
  color: #222;
}
.modal-desc {
  margin-bottom: 12px;
  color: #555;
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
  .modal-close {
    top: 10px;
    right: 12px;
    font-size: 26px;
  }
  .modal-form-actions {
    margin-top: 10px;
  }
}

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

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.13);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}
.modal-card {
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 4px 32px 0 rgba(33, 150, 243, 0.1);
  padding: 40px 48px 32px 48px;
  min-width: 400px;
  max-width: 700px;
  width: 100%;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
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
  .modal-row {
    flex-direction: column;
    gap: 12px;
  }
}
@media (max-width: 600px) {
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
.recipient-status {
  padding: 4px 8px;
  margin-right: 40px;
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

.recipient-badge {
  font-size: 14px;
  padding: 4px 8px;
  border-radius: 12px;
  color: #fff;
}
.recipient-badge.read {
  background: #16a34a; /* green */
}
.recipient-badge.unread {
  background: #dc2626; /* red */
}

.notification-detail-grid {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 20px;
  width: 100%;
  margin-top: 12px;
}
.detail-card {
  background: #fafafa;
  border-radius: 12px;
  padding: 18px;
}
.detail-label {
  font-weight: 600;
  color: #333;
  margin-bottom: 6px;
}
.detail-row > .detail-label {
  margin-top: 8px;
}
.detail-value {
  color: #222;
}
.detail-meta {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.detail-row {
  display: flex;
  flex-direction: column;
}
.recipient-list {
  max-height: 180px;
  overflow: auto;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.recipient-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 10px;
  border-radius: 8px;
  background: #fff;
  border: 1px solid #f0f0f0;
}
.all-recipients {
  color: #222;
}
.modal-actions {
  margin-top: 16px;
  display: flex;
  justify-content: flex-end;
}

@media (max-width: 900px) {
  .notification-detail-grid {
    grid-template-columns: 1fr;
  }
}

/* Table-like label/value layout */
.detail-table {
  display: grid;
  grid-template-columns: 200px 1fr;
  gap: 12px 24px;
  align-items: start;
  .detail-label {
    text-align: left;
  }
  .detail-row > .detail-label {
    margin-top: 8px;
    text-align: left;
  }
}
.detail-table .detail-label {
  font-weight: 600;
  color: #333;
}
.detail-table .detail-value {
  color: #222;
}
.detail-table .recipient-list {
  max-height: 220px;
  overflow: auto;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

@media (max-width: 700px) {
  .detail-table {
    grid-template-columns: 1fr;
  }
}

/* Simplified centered table-like rows (label left / value right) */

.modal-title {
  text-align: left;
}
.detail-table {
  display: block;
  width: 100%;
  margin: 14px auto 0 auto;
}
.detail-table .detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}
.detail-table .detail-row:last-child {
  border-bottom: none;
}
.detail-table .detail-label {
  flex: 0 0 38%;
  color: #666;
  font-weight: 600;
}
.detail-table .detail-value {
  flex: 1 1 60%;
  text-align: right;
  color: #222;
  word-break: break-word;
}
.recipient-list {
  max-height: 340px;
  overflow: auto;
}
.recipient-row {
  background: transparent;
  border: none;
  padding: 4px 0;
}
.recipient-name {
  font-weight: 500;
}
.modal-actions {
  margin-top: 18px;
  display: flex;
  justify-content: center;
}
.org-edit-update {
  padding: 8px 24px;
}

@media (max-width: 520px) {
  /* Keep label and value on the same line but allow wrapping if needed */
  .detail-table .detail-row {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
  }
  .detail-table .detail-label {
    flex: 0 0 35%;
    max-width: 25%;
    text-align: left;
    color: #666;
    font-weight: 600;
  }
  .detail-table .detail-value {
    flex: 1 1 60%;
    text-align: right;
    color: #222;
    word-break: break-word;
    padding-left: 8px;
  }
}

/* Fallback for very narrow screens: keep values readable by left-aligning them */
@media (max-width: 360px) {
  .detail-table .detail-value {
    text-align: left;
    margin-top: 4px;
    width: 100%;
  }
  .detail-table .detail-label {
    flex: 0 0 40%;
    max-width: 25%;
  }
}

/* Stronger selector to override earlier column rules and keep label/value on one line */
.detail-table .detail-row {
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 8px;
}
.detail-table .detail-label {
  flex: 0 0 36% !important;
  max-width: 25% !important;
  text-align: left !important;
}
.detail-table .detail-value {
  flex: 1 1 64% !important;
  text-align: left !important;
  padding-left: 8px !important;
}

/* Recipient compact table styles */
.recipient-table-wrap {
  width: 100%;
  overflow: auto;
}
.recipient-table.compact {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}
.recipient-table.compact th,
.recipient-table.compact td {
  padding: 8px 10px;
  border-bottom: 1px solid #eee;
  text-align: left;
}
.recipient-table.compact .group-cell {
  font-weight: 600;
  vertical-align: top;
}
</style>
