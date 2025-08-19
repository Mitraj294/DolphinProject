<template>
  <div class="assessments-card">
    <div class="assessments-header-row">
      <div></div>
      <div class="assessments-header-actions">
        <button
          class="btn btn-primary"
          @click="showCreateModal = true"
        >
          <img
            src="@/assets/images/Add.svg"
            alt="Add"
            style="
              width: 18px;
              height: 18px;
              vertical-align: middle;
              margin-right: 8px;
            "
          />
          Create Assessments
        </button>
      </div>
    </div>
    <div class="table-container">
      <table class="table">
        <TableHeader
          :columns="[
            { label: 'Assessment Name', key: 'name' },

            { label: 'Actions', key: 'actions' },
          ]"
        />
        <tbody>
          <tr
            v-for="item in paginatedAssessments"
            :key="item.id"
          >
            <td>
              <button
                class="assessment-link"
                @click="goToSummary(item)"
              >
                {{ item.name }}
              </button>
            </td>
            <td>
              <template v-if="item.schedule">
                <div class="scheduled-details">
                  <span
                    style="color: #f7c948; font-weight: bold; font-size: 18px"
                    >Mail Scheduled</span
                  >
                  <div style="margin-top: 8px; font-size: 15px">
                    <div>
                      <strong>Subject:</strong>
                      {{ item.schedule.subject || 'Assessment Scheduled' }}
                    </div>
                    <div>
                      <strong>Send At:</strong> {{ item.schedule.send_at }}
                    </div>
                    <div>
                      <strong>Status:</strong>
                      {{ item.schedule.status || 'scheduled' }}
                    </div>
                  </div>
                  <button
                    class="schedule-btn"
                    @click="openScheduleDetails(item)"
                    style="margin-top: 10px"
                  >
                    <img
                      src="@/assets/images/Schedule.svg"
                      alt="Details"
                      style="
                        margin-right: 6px;
                        width: 18px;
                        height: 18px;
                        vertical-align: middle;
                        display: inline-block;
                      "
                    />
                    Details
                  </button>
                </div>
              </template>
              <template v-else>
                <button
                  class="schedule-btn"
                  @click="openScheduleModal(item)"
                >
                  <img
                    src="@/assets/images/Schedule.svg"
                    alt="Schedule"
                    style="
                      margin-right: 6px;
                      width: 18px;
                      height: 18px;
                      vertical-align: middle;
                      display: inline-block;
                    "
                  />
                  Schedule
                </button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- Create Assessment Modal -->
    <div
      v-if="showCreateModal"
      class="modal-overlay"
    >
      <div class="modal-card">
        <button
          class="modal-close"
          @click="closeCreateModal"
        >
          &times;
        </button>
        <div class="modal-title">Create Assessment</div>
        <form
          class="modal-form"
          @submit.prevent="submitAssessment"
        >
          <div class="modal-form-row">
            <div
              class="modal-form-group"
              style="
                padding: 0 0;
                background: none;
                border-radius: 0;
                height: auto;
              "
            >
              <input
                v-model="newAssessment.name"
                type="text"
                placeholder="Assessment Name"
                required
                style="
                  width: 100%;
                  background: #f6f6f6;
                  border-radius: 9px;
                  border: 1.5px solid #e0e0e0;
                  font-size: 20px;
                  padding: 16px 20px;
                  box-sizing: border-box;
                  font-weight: 500;
                  color: #222;
                "
              />
            </div>
          </div>
          <div
            class="modal-form-row"
            style="flex-direction: column; align-items: stretch; gap: 10px"
          >
            <label
              style="
                font-weight: 600;
                margin-bottom: 16px;
                font-size: 22px;
                text-align: left;
                display: block;
                align-self: flex-start;
              "
            >
              Questions
            </label>
            <div
              v-for="q in questions"
              :key="q.id"
              style="width: 100%; margin-bottom: 8px"
            >
              <label
                :for="'q-' + q.id"
                class="user-assessment-checkbox-label"
                :class="{
                  checked: newAssessment.selectedQuestionIds.includes(q.id),
                }"
                style="
                  justify-content: flex-start;
                  font-size: 18px;
                  padding: 18px 24px;
                  background: #f8f9fb;
                  border-radius: 12px;
                  margin-bottom: 0;
                  width: 100%;
                  text-align: left;
                "
              >
                <span class="user-assessment-checkbox-custom"></span>
                <input
                  type="checkbox"
                  :id="'q-' + q.id"
                  :value="q.id"
                  v-model="newAssessment.selectedQuestionIds"
                />
                <span
                  style="
                    flex: 1;
                    text-align: left;
                    font-size: 18px;
                    font-weight: 500;
                    color: #222;
                  "
                  >{{ q.text }}</span
                >
              </label>
            </div>
          </div>
          <div class="modal-form-actions">
            <button
              type="submit"
              class="modal-save-btn"
            >
              Create
            </button>
            <button
              type="button"
              class="btn btn-secondary"
              @click="closeCreateModal"
              style="margin-left: 12px"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
    <!-- Schedule Modal -->
    <div
      v-if="showScheduleModal"
      class="modal-overlay"
    >
      <ScheduleAssessmentModal
        :assessmentName="selectedAssessment && selectedAssessment.name"
        :assessment_id="selectedAssessment && selectedAssessment.id"
        @close="closeScheduleModal"
        @schedule="handleScheduleAssessment"
      />
    </div>
    <!-- Schedule Details Modal -->
    <div
      v-if="showScheduleDetailsModal"
      class="modal-overlay"
    >
      <div
        class="modal-card"
        style="max-width: 480px; text-align: center; align-items: center"
      >
        <button
          class="modal-close"
          @click="closeScheduleDetailsModal"
        >
          &times;
        </button>
        <div
          class="modal-title"
          style="margin-bottom: 18px"
        >
          Scheduled Assessment Details
        </div>
        <div v-if="scheduleDetails && scheduleDetails.schedule">
          <div style="margin-bottom: 8px">
            <strong>Assessment:</strong> {{ scheduleDetails.assessment.name }}
          </div>
          <div style="margin-bottom: 8px">
            <strong>Date:</strong> {{ scheduleDetails.schedule.date }}
          </div>
          <div style="margin-bottom: 8px">
            <strong>Time:</strong> {{ scheduleDetails.schedule.time }}
          </div>

          <div style="margin-top: 16px; margin-bottom: 6px">
            <strong>Emails:</strong>
          </div>
          <div>
            <span
              v-if="scheduleDetails.emails && scheduleDetails.emails.length"
            >
              <span
                v-if="
                  filteredEmails.length && filteredEmails.every((e) => e.sent)
                "
                class="status-green"
                >Sent</span
              >
              <span
                v-else-if="
                  filteredEmails.length &&
                  filteredEmails.some((e) => !e.sent) &&
                  filteredEmails.some((e) => e.sent)
                "
                class="status-yellow"
                >Scheduled</span
              >
              <span
                v-else-if="
                  filteredEmails.length && filteredEmails.every((e) => !e.sent)
                "
              >
                <span
                  v-if="
                    filteredEmails.some((e) => {
                      const [date, time] = (e.send_at || '').split(' ');
                      const [year, month, day] = date ? date.split('-') : [];
                      const [hour, min, sec] = time ? time.split(':') : [];
                      const sendAtUtc = Date.UTC(
                        Number(year),
                        Number(month) - 1,
                        Number(day),
                        Number(hour),
                        Number(min),
                        Number(sec)
                      );
                      const nowUtc = Date.now();
                      return sendAtUtc >= nowUtc;
                    })
                  "
                  class="status-yellow"
                  >Scheduled</span
                >
                <span
                  v-else
                  class="status-red"
                  >Failed</span
                >
              </span>
              <span
                v-else
                class="status-yellow"
                >Scheduled</span
              >
            </span>
            <span v-else>
              <span
                v-if="
                  (() => {
                    const [year, month, day] = (
                      scheduleDetails.schedule.date || ''
                    ).split('-');
                    const [hour, min, sec] = (
                      scheduleDetails.schedule.time || ''
                    ).split(':');
                    const schedAtUtc = Date.UTC(
                      Number(year),
                      Number(month) - 1,
                      Number(day),
                      Number(hour),
                      Number(min),
                      Number(sec)
                    );
                    const nowUtc = Date.now();
                    return schedAtUtc >= nowUtc;
                  })()
                "
                class="status-yellow"
                >Scheduled</span
              >
              <span
                v-else
                class="status-red"
                >Failed</span
              >
            </span>
          </div>
        </div>
        <div v-else>
          <em>No schedule details found.</em>
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
    @togglePageDropdown="showPageDropdown = !showPageDropdown"
    @selectPageSize="selectPageSize"
    @goToPage="goToPage"
  />
</template>

<script>
import Pagination from '@/components/layout/Pagination.vue';
import ScheduleAssessmentModal from '@/components/Common/MyOrganization/ScheduleAssessmentModal.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
import axios from 'axios';
import storage from '@/services/storage';
import { useToast } from 'primevue/usetoast';
export default {
  name: 'OrganizationAdminAssessmentsCard',
  components: { Pagination, ScheduleAssessmentModal, TableHeader },
  data() {
    return {
      assessments: [],
      showScheduleModal: false,
      selectedAssessment: null,
      showCreateModal: false,
      newAssessment: {
        name: '',
        selectedQuestionIds: [],
      },
      questions: [],
      // Pagination state
      pageSize: 10,
      currentPage: 1,
      showPageDropdown: false,
      // Dropdown state for modal (future use)
      showGroupsDropdown: false,
      showMembersDropdown: false,
      loading: false,
      toast: null,
      // Schedule details state
      showScheduleDetailsModal: false,
      scheduleDetails: null,
      allGroups: [],
      allMembers: [],
    };
  },
  created() {
    this.toast = useToast();
  },
  computed: {
    paginatedAssessments() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.assessments.slice(start, start + this.pageSize);
    },
    totalPages() {
      return Math.ceil(this.assessments.length / this.pageSize) || 1;
    },
    groupNameMap() {
      const map = {};
      (this.allGroups || []).forEach((g) => {
        map[g.id] = g.name;
      });
      return map;
    },
    memberNameMap() {
      const map = {};
      (this.allMembers || []).forEach((m) => {
        map[m.id] = m.name || m.email || m.id;
      });
      return map;
    },
    filteredEmails() {
      if (!this.scheduleDetails || !this.scheduleDetails.emails) return [];
      const schedule = this.scheduleDetails.schedule;
      if (!schedule) return [];
      // Only include emails for this assessment
      return this.scheduleDetails.emails.filter(
        (e) => e.assessment_id == this.scheduleDetails.assessment.id
      );
    },
  },
  methods: {
    async openScheduleDetails(item) {
      // Fetch schedule details from backend
      try {
        const res = await axios.get(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/scheduled-email/show',
          { params: { assessment_id: item.id } }
        );
        this.scheduleDetails = res.data;
      } catch (e) {
        this.scheduleDetails = null;
      }
      this.selectedAssessment = item;
      this.showScheduleDetailsModal = true;
    },
    closeScheduleDetailsModal() {
      this.showScheduleDetailsModal = false;
      this.scheduleDetails = null;
    },
    async openScheduleModal(item) {
      // Always check backend for schedule status
      try {
        const res = await axios.get(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/scheduled-email/show',
          { params: { assessment_id: item.id } }
        );
        // Debug log for backend response
        console.log('Scheduled check response:', res.data);
        if (
          res.data &&
          (res.data.scheduled === true || res.data.scheduled === 1)
        ) {
          // If scheduled, show details instead
          this.openScheduleDetails(item);
          return;
        } else if (
          res.data &&
          (res.data.scheduled === false || res.data.scheduled === 0)
        ) {
          this.selectedAssessment = item;
          this.showScheduleModal = true;
          return;
        } else {
          // Defensive: treat as scheduled if response is malformed
          this.openScheduleDetails(item);
          return;
        }
      } catch (e) {
        console.error('Schedule check error:', e);
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Could not check schedule status.',
          life: 3500,
        });
        return;
      }
    },
    closeScheduleModal() {
      this.showScheduleModal = false;
      this.selectedAssessment = null;
      this.showGroupsDropdown = false;
      this.showMembersDropdown = false;
    },
    goToSummary(item) {
      this.$router.push({
        name: 'AssessmentSummary',
        params: { assessmentId: item.id },
      });
    },

    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    selectPageSize(size) {
      this.pageSize = size;
      this.currentPage = 1;
      this.showPageDropdown = false;
    },
    closeCreateModal() {
      this.showCreateModal = false;
      this.newAssessment = { name: '', selectedQuestionIds: [] };
    },
    submitAssessment: async function () {
      // Use real questions from backend and store in DB
      const selectedQuestions = this.questions.filter((q) =>
        this.newAssessment.selectedQuestionIds.includes(q.id)
      );
      if (!this.newAssessment.name || selectedQuestions.length === 0) {
        alert('Please enter a name and select at least one question.');
        return;
      }
      try {
        const authToken = storage.get('authToken');
        const res = await axios.post(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/assessments',
          {
            name: this.newAssessment.name,
            question_ids: this.newAssessment.selectedQuestionIds,
          },
          { headers: { Authorization: `Bearer ${authToken}` } }
        );
        if (res.data && res.data.assessment) {
          this.assessments.unshift(res.data.assessment);
        }
        this.closeCreateModal();
      } catch (e) {
        alert('Failed to create assessment.');
      }
    },
    async handleScheduleAssessment({
      date,
      time,
      groupIds,
      memberIds,
      selectedMembers,
    }) {
      if (!this.selectedAssessment || !date || !time) {
        this.toast.add({
          severity: 'warn',
          summary: 'Missing Data',
          detail: 'Please select assessment, date, and time.',
          life: 3500,
        });
        return;
      }
      try {
        const authToken = storage.get('authToken');
        await axios.post(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/assessment-schedules',
          {
            assessment_id: this.selectedAssessment.id,
            date,
            time,
            group_ids: groupIds,
            member_ids: memberIds,
          },
          { headers: { Authorization: `Bearer ${authToken}` } }
        );

        // Send scheduled emails to selected members (using selectedMembers for emails)
        // Convert local date+time to UTC ISO string for send_at
        const localDateTime = new Date(`${date}T${time}:00`);
        const sendAt = localDateTime.toISOString();
        const subject = 'Assessment Scheduled';
        const body = 'You have an assessment scheduled.';
        for (const member of selectedMembers || []) {
          if (member.email) {
            // Find the group_id for this member (assume first from group_ids array or group_id property)
            let group_id = null;
            if (
              Array.isArray(member.group_ids) &&
              member.group_ids.length > 0
            ) {
              group_id = member.group_ids[0];
            } else if (member.group_id) {
              group_id = member.group_id;
            } else if (Array.isArray(groupIds) && groupIds.length === 1) {
              group_id = groupIds[0];
            }
            await axios.post(
              (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
                '/api/schedule-email',
              {
                recipient_email: member.email,
                subject,
                body,
                send_at: sendAt,
                assessment_id: this.selectedAssessment.id,
                member_id: member.id,
                group_id: group_id,
              },
              { headers: { Authorization: `Bearer ${authToken}` } }
            );
          }
        }

        this.closeScheduleModal();
        this.toast.add({
          severity: 'success',
          summary: 'Scheduled',
          detail: 'Assessment scheduled and emails queued!',
          life: 3500,
        });
      } catch (e) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to schedule assessment or emails.',
          life: 3500,
        });
      }
    },
  },
  mounted: async function () {
    // Fetch assessments and questions from backend
    this.loading = true;
    try {
      const authToken = storage.get('authToken');
      const userId = storage.get('user_id');
      // Fetch assessments
      const res = await axios.get(
        (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
          '/api/assessments',
        {
          headers: { Authorization: `Bearer ${authToken}` },
          params: userId ? { user_id: userId } : {},
        }
      );
      if (Array.isArray(res.data.assessments)) {
        this.assessments = res.data.assessments;
      } else if (Array.isArray(res.data)) {
        this.assessments = res.data;
      } else {
        this.assessments = [];
      }
      // Fetch questions
      const qres = await axios.get(
        (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
          '/api/organization-assessment-questions',
        { headers: { Authorization: `Bearer ${authToken}` } }
      );
      if (Array.isArray(qres.data.questions)) {
        this.questions = qres.data.questions;
      } else if (Array.isArray(qres.data)) {
        this.questions = res.data;
      } else {
        this.questions = [];
      }
      // Fetch all groups
      const groupsRes = await axios.get(
        (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
          '/api/groups',
        { headers: { Authorization: `Bearer ${authToken}` } }
      );
      if (Array.isArray(groupsRes.data.groups)) {
        this.allGroups = groupsRes.data.groups;
      } else if (Array.isArray(groupsRes.data)) {
        this.allGroups = groupsRes.data;
      } else {
        this.allGroups = [];
      }
      // Fetch all members
      const membersRes = await axios.get(
        (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
          '/api/members',
        { headers: { Authorization: `Bearer ${authToken}` } }
      );
      if (Array.isArray(membersRes.data.members)) {
        this.allMembers = membersRes.data.members;
      } else if (Array.isArray(membersRes.data)) {
        this.allMembers = membersRes.data;
      } else {
        this.allMembers = [];
      }
    } catch (e) {
      this.assessments = [];
      this.questions = [];
    } finally {
      this.loading = false;
    }
  },
};
</script>

<style scoped>
.assessments-card {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 64px auto 0 auto;
  padding: 0;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 0;
  position: relative;
}

.assessments-header-row {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 24px 46px 24px 24px;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  min-height: 64px;
  box-sizing: border-box;
  margin: 0; /* Remove any margin */
}

.assessments-header-actions {
  display: flex;
  gap: 16px;
}
.sent-assessments-btn {
  background: #f5f5f5;
  border: none;
  border-radius: 999px;
  padding: 10px 32px;
  font-size: 16px;
  color: #222;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.18s;
}
.sent-assessments-btn:hover {
  background: #e6f0fa;
}

.table-container {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}
.table th,
.table td {
  padding: 16px;
  text-align: left;
  border-bottom: 1px solid #ebebeb;
}
.table th {
  background: #f9f9f9;
  color: #333;
  font-weight: 600;
}
.table td {
  color: #555;
  font-weight: 500;
}

.assessment-name-cell {
  text-align: left;
}
.schedule-btn {
  background: #f5f5f5;
  border: none;
  border-radius: 999px;
  padding: 8px 24px;
  font-size: 15px;
  color: #222;
  font-weight: 500;
  display: flex;
  align-items: center;
  cursor: pointer;
  transition: background 0.18s;
}
.schedule-btn:hover {
  background: #e6f0fa;
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
.modal-close {
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
  font-size: 26px;
  font-weight: 600;
  margin-top: 32px;
  margin-bottom: 32px;
  color: #222;
}
.modal-form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.modal-form-row {
  display: flex;
  gap: 24px;
  width: 100%;
}
.modal-form-group {
  flex: 1 1 0;
  min-width: 0;
  background: #f6f6f6;
  border-radius: 9px;
  padding: 0 16px;
  height: 48px;
  display: flex;
  align-items: center;
  margin-bottom: 0;
  box-sizing: border-box;
}
.modal-form-group.custom-dropdown {
  flex-direction: row;
  align-items: center;
  gap: 0;
  min-height: 48px;
  width: 100%;
  position: relative;
}
.modal-icon {
  margin-right: 10px;
  display: flex;
  align-items: center;
}
.custom-dropdown-input {
  width: 100%;
  background: #f6f6f6;
  border: none;
  border-radius: 9px;
  height: 48px;
  padding: 0;
  font-size: 16px;
  color: #222;
  display: flex;
  align-items: center;
  cursor: pointer;
  position: relative;
  box-shadow: none;
  margin-bottom: 0;
  font-family: inherit;
  font-weight: 500;
}
.custom-dropdown-input .fas {
  color: #222;
  font-size: 18px;
  margin-right: 1px;
  position: static;
  left: unset;
  top: unset;
  transform: none;
}
.custom-dropdown-input .fas.fa-chevron-down {
  margin-left: auto;
  color: #888;
}
.custom-dropdown-input span[style] {
  color: #888 !important;
  font-size: 16px;
  font-weight: 500;
  font-family: inherit;
  margin-left: 2px;
}
.modal-form-actions {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  margin-top: 18px;
}
.modal-save-btn {
  border-radius: 22px;
  background: #0164a5;
  color: #fff;
  font-size: 17px;
  font-weight: 500;
  padding: 10px 32px;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}
.modal-save-btn:hover {
  background: #005fa3;
}
.assessment-link {
  background: none;
  border: none;
  color: #0074c2;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  text-align: left;
  padding: 0;
  transition: color 0.18s;
}
.assessment-link:hover {
  color: #005fa3;
  text-decoration: underline;
}

/* --- UserAssessment style import --- */
.user-assessment-checkbox-label {
  display: flex;
  align-items: center;
  background: #f8f9fb;
  border-radius: 10px;
  padding: 12px 18px;
  font-size: 16px;
  font-weight: 500;
  color: #222;
  cursor: pointer;
  border: 2px solid #f8f9fb;
  transition: border 0.18s, background 0.18s;
  width: 100%;
  user-select: none;
  text-align: left;
}
.user-assessment-checkbox-label.checked {
  background: #e6f0fa;
  border: 2px solid #0074c2;
}
.user-assessment-checkbox-label input[type='checkbox'] {
  display: none;
}
.user-assessment-checkbox-custom {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  border: 2px solid #bbb;
  background: #fff;
  margin-right: 12px;
  display: inline-block;
  position: relative;
}
.user-assessment-checkbox-label.checked .user-assessment-checkbox-custom {
  border: 2px solid #0074c2;
  background: #0074c2;
}
.user-assessment-checkbox-label.checked .user-assessment-checkbox-custom:after {
  content: '';
  display: block;
  position: absolute;
  left: 6px;
  top: 2px;
  width: 6px;
  height: 12px;
  border: solid #fff;
  border-width: 0 3px 3px 0;
  transform: rotate(45deg);
}

/* --- End UserAssessment style import --- */
@media (max-width: 1400px) {
  .assessments-card {
    border-radius: 14px;
    max-width: 100%;
    margin-top: 12px; /* Responsive top margin */
  }
  .assessments-header-row {
    padding: 8px 8px 0 8px;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
  }
}
@media (max-width: 900px) {
  .assessments-card {
    border-radius: 10px;
    margin-top: 4px; /* Responsive top margin */
  }
  .assessments-header-row {
    padding: 8px 4px 0 4px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
  }
}
@media (max-width: 600px) {
  .modal-card {
    min-width: 0;
    max-width: 99vw;
    padding: 8px 2vw 8px 2vw;
    border-radius: 10px;
  }
}

.status-green {
  color: #fff;
  background: #28a745;
  font-weight: 600;
  font-size: 18px;
  padding: 4px 16px;
  border-radius: 20px;
  display: inline-block;
}
.status-yellow {
  color: #fff;
  background: #f7c948;
  font-weight: 600;
  font-size: 18px;
  padding: 4px 16px;
  border-radius: 20px;
  display: inline-block;
}
.status-red {
  color: #fff;
  background: #e74c3c;
  font-weight: 600;
  font-size: 18px;
  padding: 4px 16px;
  border-radius: 20px;
  display: inline-block;
}
</style>
