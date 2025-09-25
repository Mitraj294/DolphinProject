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
      <div class="table-scroll">
        <table class="table">
          <colgroup>
            <col style="width: 25%" />
            <col style="width: 75%" />
          </colgroup>
          <TableHeader
            :columns="[
              { label: 'Assessment Name', key: 'name', style: 'width: 30%' },
              { label: 'Actions', key: 'actions', style: 'width: 70%' },
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
                <div
                  v-if="item.schedule"
                  class="scheduled-details"
                ></div>

                <button
                  class="schedule-btn"
                  :id="'schedule-btn-' + item.id"
                  :data-assessment-id="item.id"
                  :ref="'scheduleBtn-' + item.id"
                  @click="onScheduleButtonClick(item)"
                  style="min-width: 135px; max-width: 135px"
                >
                  <img
                    src="@/assets/images/Schedule.svg"
                    :alt="item.schedule ? 'Details' : 'Schedule'"
                    style="
                      margin-right: 6px;
                      width: 18px;
                      height: 18px;
                      vertical-align: middle;
                      display: inline-block;
                    "
                  />
                  {{ item.schedule ? 'Details' : 'Schedule' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Create Assessment Modal -->
    <CreateAssessmentModal
      v-if="showCreateModal"
      :questions="questions"
      @close="closeCreateModal"
      @assessment-created="handleAssessmentCreated"
      @validation-error="handleValidationError"
      @error="handleError"
    />
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
    <ScheduleDetailsModal
      v-if="showScheduleDetailsModal"
      :scheduleDetails="scheduleDetails"
      :allGroups="allGroups"
      :allMembers="allMembers"
      @close="closeScheduleDetailsModal"
    />
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
import ScheduleAssessmentModal from '@/components/Common/Leads_Assessment/ScheduleAssessmentModal.vue';
import ScheduleDetailsModal from '@/components/Common/Leads_Assessment/ScheduleDetailsModal.vue';
import CreateAssessmentModal from '@/components/Common/Leads_Assessment/CreateAssessmentModal.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
import axios from 'axios';
import storage from '@/services/storage';
import { useToast } from 'primevue/usetoast';
export default {
  name: 'OrganizationAdminAssessmentsCard',
  components: {
    Pagination,
    ScheduleAssessmentModal,
    ScheduleDetailsModal,
    CreateAssessmentModal,
    TableHeader,
  },
  data() {
    return {
      assessments: [],
      showScheduleModal: false,
      selectedAssessment: null,
      showCreateModal: false,
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
        // Prefer explicit first/last name fields if present, otherwise fall back to m.name
        const first = (m.first_name || m.name || '').toString().trim();
        const last = (m.last_name || '').toString().trim();
        const role = (m.member_role || m.role || '').toString().trim();
        let full = first;
        if (last) full = full ? `${full} ${last}` : last;
        if (!full) full = m.email || m.id || 'Unknown';
        if (role) full = `${full} — ${role}`;
        map[m.id] = full;
      });
      return map;
    },
    memberDetailMap() {
      const map = {};
      (this.allMembers || []).forEach((m) => {
        const first = (m.first_name || m.name || '').toString().trim();
        const last = (m.last_name || '').toString().trim();
        let name = first;
        if (last) name = name ? `${name} ${last}` : last;
        if (!name) name = m.email || `Member ${m.id}`;
        map[m.id] = {
          name,
          email: m.email || '',
          role: (m.member_role || m.role || '').toString().trim() || '',
        };
      });
      return map;
    },
  },
  methods: {
    // show toast; pass `highZ = true` as 5th arg to temporarily raise
    // toast z-index above modals (adds a body class and removes it after life)
    _showToast(severity, summary, detail, life = 3500, highZ = false) {
      const toast = this.toast || this.$toast;
      try {
        if (highZ && typeof document !== 'undefined') {
          document.body.classList.add('toast-above-modal');
        }
      } catch (e) {
        console.warn('Failed to set toast-above-modal class', e);
      }

      if (toast && typeof toast.add === 'function') {
        toast.add({ severity, summary, detail, life });
      } else if (this && this.$toast && typeof this.$toast.add === 'function') {
        this.$toast.add({ severity, summary, detail, life });
      } else if (
        typeof window !== 'undefined' &&
        window.$toast &&
        typeof window.$toast.add === 'function'
      ) {
        window.$toast.add({ severity, summary, detail, life });
      } else {
        console[severity === 'warn' ? 'warn' : 'log'](`${summary}: ${detail}`);
      }

      if (highZ && typeof window !== 'undefined') {
        // Remove the body class shortly after the toast lifetime to avoid
        // leaving global state set.
        setTimeout(() => {
          try {
            document.body.classList.remove('toast-above-modal');
          } catch (e) {
            console.warn('Failed to remove toast-above-modal class', e);
          }
        }, life + 300);
      }
    },
    async openScheduleDetails(item) {
      // Fetch schedule details from backend
      try {
        const res = await axios.get(
          process.env.VUE_APP_API_BASE_URL + '/api/scheduled-email/show',
          { params: { assessment_id: item.id } }
        );
        this.scheduleDetails = res.data;
        console.log(
          '[OrganizationAdminAssessmentsCard] schedule details response for',
          item.id,
          res.data
        );
      } catch (e) {
        this.scheduleDetails = null;
        console.error(
          '[OrganizationAdminAssessmentsCard] failed to fetch schedule details for',
          item.id,
          e && e.message
        );
      }
      this.selectedAssessment = item;
      this.showScheduleDetailsModal = true;
    },

    onScheduleButtonClick(item) {
      // Log the value we have in the row and then delegate
      try {
        console.log(
          '[OrganizationAdminAssessmentsCard] schedule button clicked:',
          {
            id: item && item.id,
            hasSchedule: !!(item && item.schedule),
            item,
          }
        );
      } catch (err) {
        console.log(
          '[OrganizationAdminAssessmentsCard] schedule button click - failed to stringify item',
          err
        );
      }
      if (item && item.schedule) {
        this.openScheduleDetails(item);
      } else {
        this.openScheduleModal(item);
      }
    },
    formatLocalDateTime(dateStr, timeStr) {
      if (!dateStr) return '';
      // combine date and time parts and parse as UTC components
      try {
        const dpart = (dateStr || '').toString().trim();
        const tpart = (timeStr || '').toString().trim();
        // Expect date as YYYY-MM-DD and time as HH:MM or HH:MM:SS
        const dmatch = dpart.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        const tmatch = tpart.match(/^(\d{2}):(\d{2})(?::(\d{2}))?$/);
        let year, month, day, hour, minute, second;
        if (dmatch) {
          year = Number(dmatch[1]);
          month = Number(dmatch[2]) - 1;
          day = Number(dmatch[3]);
        } else {
          const alt = new Date(dpart);
          if (isNaN(alt.getTime()))
            return dateStr + (timeStr ? ' ' + timeStr : '');
          year = alt.getFullYear();
          month = alt.getMonth();
          day = alt.getDate();
        }
        if (tmatch) {
          hour = Number(tmatch[1]);
          minute = Number(tmatch[2]);
          second = tmatch[3] ? Number(tmatch[3]) : 0;
        } else {
          hour = 0;
          minute = 0;
          second = 0;
        }
        // Construct a local Date using the provided components so we display the
        // time exactly as entered (local), avoiding unintended timezone shifts.
        const dt = new Date(year, month, day, hour, minute, second);
        if (isNaN(dt.getTime()))
          return dateStr + (timeStr ? ' ' + timeStr : '');
        const dayNum = String(dt.getDate()).padStart(2, '0');
        const months = [
          'Jan',
          'Feb',
          'Mar',
          'Apr',
          'May',
          'Jun',
          'Jul',
          'Aug',
          'Sep',
          'Oct',
          'Nov',
          'Dec',
        ];
        const mon = months[dt.getMonth()];
        const yr = dt.getFullYear();
        let hr = dt.getHours();
        const min = String(dt.getMinutes()).padStart(2, '0');
        const ampm = hr >= 12 ? 'PM' : 'AM';
        hr = hr % 12;
        hr = hr || 12;
        return `${dayNum} ${mon},${yr} ${hr}:${min} ${ampm}`;
      } catch (e) {
        console.error('Failed to format date/time', e);
        return dateStr + (timeStr ? ' ' + timeStr : '');
      }
    },
    formatSendAt(sendAt) {
      if (!sendAt) return '';
      try {
        const s = (sendAt || '').toString().trim();
        // ISO format with T
        if (s.includes('T')) {
          const d = new Date(s);
          if (isNaN(d.getTime())) return s;
          const yyyy = d.getFullYear();
          const mm = String(d.getMonth() + 1).padStart(2, '0');
          const dd = String(d.getDate()).padStart(2, '0');
          const hh = String(d.getHours()).padStart(2, '0');
          const min = String(d.getMinutes()).padStart(2, '0');
          return this.formatLocalDateTime(
            `${yyyy}-${mm}-${dd}`,
            `${hh}:${min}`
          );
        }
        // DB format 'YYYY-MM-DD HH:MM:SS' or similar
        if (s.includes(' ')) {
          const [date, time] = s.split(' ');
          const timeShort = (time || '').split(':').slice(0, 2).join(':');
          return this.formatLocalDateTime(date, timeShort);
        }
        return s;
      } catch (err) {
        console.error('Failed to format sendAt', err);
        return sendAt;
      }
    },
    closeScheduleDetailsModal() {
      this.showScheduleDetailsModal = false;
      this.scheduleDetails = null;
    },
    async openScheduleModal(item) {
      // Always check backend for schedule status
      try {
        const res = await axios.get(
          process.env.VUE_APP_API_BASE_URL + '/api/scheduled-email/show',
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
          console.warn(
            'Unexpected schedule status from backend, defaulting to details view:',
            res.data
          );
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
    async goToSummary(item) {
      // Before navigating, verify the assessment has a schedule entry
      try {
        const res = await axios.get(
          process.env.VUE_APP_API_BASE_URL + '/api/scheduled-email/show',
          { params: { assessment_id: item.id } }
        );
        // If backend returns scheduled=true (or 1), allow navigation
        if (
          res &&
          res.data &&
          (res.data.scheduled === true || res.data.scheduled === 1)
        ) {
          this.$router.push({
            name: 'AssessmentSummary',
            params: { assessmentId: item.id, assessment: item },
          });
          return;
        }
        // If explicitly not scheduled, prevent navigation and show toast
        if (
          res &&
          res.data &&
          (res.data.scheduled === false || res.data.scheduled === 0)
        ) {
          const msg =
            'Assessment is not yet scheduled. Please schedule it first.';
          this._showToast('info', 'Not Scheduled', msg, 4000);
          return;
        }
        // Defensive default: if response is malformed, allow navigation
        this.$router.push({
          name: 'AssessmentSummary',
          params: { assessmentId: item.id, assessment: item },
        });
      } catch (e) {
        console.error('Schedule check error:', e);
        // On error, show toast and prevent navigation
        const msg = 'Could not verify schedule status. Try again later.';
        this._showToast('error', 'Error', msg);
        return;
      }
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
    },
    handleAssessmentCreated(newAssessment) {
      this.assessments.unshift(newAssessment);
    },
    handleValidationError(errorData) {
      this._showToast(
        errorData.type,
        errorData.title,
        errorData.message,
        4000,
        true
      );
    },
    handleError(errorData) {
      this._showToast(errorData.type, errorData.title, errorData.message, 3500);
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
          process.env.VUE_APP_API_BASE_URL + '/api/assessment-schedules',
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
            } else {
              console.warn('No group_id found for member', member);
            }
            await axios.post(
              process.env.VUE_APP_API_BASE_URL + '/api/schedule-email',
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
        console.error('Failed to schedule assessment or emails', e);
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
      // Try several storage keys that may hold the organization id
      const orgId =
        storage.get('organization_id') ||
        storage.get('org_id') ||
        storage.get('organizationId') ||
        storage.get('orgId') ||
        (storage.get('user') && storage.get('user').organization_id) ||
        null;
      // Build request params: prefer organization_id for org-admin view, fall back to user_id
      const params = {};
      if (orgId) {
        params.organization_id = orgId;
      } else if (userId) {
        params.user_id = userId;
      } else {
        console.warn(
          '[OrganizationAdminAssessmentsCard] No orgId or userId found in storage'
        );
      }

      // Base API URL
      const base = process.env.VUE_APP_API_BASE_URL;

      // If we don't have params, try to get organization_id from authenticated profile
      if (Object.keys(params).length === 0 && authToken) {
        try {
          const profileRes = await axios.get(base + '/api/profile', {
            headers: { Authorization: `Bearer ${authToken}` },
          });
          const prof = profileRes.data || {};
          const profOrgId =
            prof.organization_id ||
            (prof.user && prof.user.organization_id) ||
            null;
          if (profOrgId) {
            params.organization_id = profOrgId;
          }
        } catch (e) {
          console.warn(
            '[OrganizationAdminAssessmentsCard] failed to fetch profile',
            e && e.message
          );
        }
      }

      // Fetch assessments
      const res = await axios.get(base + '/api/assessments', {
        headers: { Authorization: `Bearer ${authToken}` },
        params,
      });
      if (Array.isArray(res.data.assessments)) {
        this.assessments = res.data.assessments;
      } else if (Array.isArray(res.data)) {
        this.assessments = res.data;
      } else {
        this.assessments = [];
      }

      try {
        const pageItems = this.paginatedAssessments || [];
        const scheduleChecks = pageItems.map(async (assessment) => {
          try {
            const sres = await axios.get(base + '/api/scheduled-email/show', {
              params: { assessment_id: assessment.id },
              headers: { Authorization: `Bearer ${authToken}` },
            });
            if (
              sres.data &&
              (sres.data.scheduled === true || sres.data.scheduled === 1)
            ) {
              return { ...assessment, schedule: sres.data };
            }
            return { ...assessment, schedule: null };
          } catch (e) {
            console.warn(
              `[OrganizationAdminAssessmentsCard] Failed to fetch schedule for assessment ${assessment.id}`,
              e && e.message
            );
            return { ...assessment, schedule: null };
          }
        });

        const updatedPageItems = await Promise.all(scheduleChecks);
        const updatedItemsMap = new Map(
          updatedPageItems.map((item) => [item.id, item])
        );
        this.assessments = this.assessments.map((assessment) => {
          return updatedItemsMap.get(assessment.id) || assessment;
        });
      } catch (err) {
        console.warn(
          '[OrganizationAdminAssessmentsCard] Failed to pre-fetch schedule statuses.',
          err
        );
      }
      // Fetch questions
      const qres = await axios.get(
        process.env.VUE_APP_API_BASE_URL +
          '/api/organization-assessment-questions',
        { headers: { Authorization: `Bearer ${authToken}` } }
      );
      if (Array.isArray(qres.data.questions)) {
        this.questions = qres.data.questions;
      } else if (Array.isArray(qres.data)) {
        this.questions = qres.data;
      } else {
        this.questions = [];
      }
      // Fetch all groups
      const groupsRes = await axios.get(
        process.env.VUE_APP_API_BASE_URL + '/api/groups',
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
        process.env.VUE_APP_API_BASE_URL + '/api/members',
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
      console.error(
        '[OrganizationAdminAssessmentsCard] Failed to fetch assessments or questions',
        e && e.message
      );
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

  min-width: 0;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);

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

.table-container {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.table {
  width: 100%;

  table-layout: auto; /* let columns size naturally */
}

.table th,
.table td {
  padding: 10px;
  text-align: left;
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

/* --- End UserAssessment style import --- */
@media (max-width: 1400px) {
  .assessments-header-row {
    padding: 8px 8px 0 8px;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
  }
}
@media (max-width: 00px) {
  .assessments-header-row {
    padding: 8px 4px 0 4px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
  }
}
</style>
