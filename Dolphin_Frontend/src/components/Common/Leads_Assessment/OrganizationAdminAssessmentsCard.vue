<template>
  <div>
    <div class="assessments-card">
      <div class="assessments-header-row">
        <div class="assessments-header-actions">
          <button
            class="btn btn-primary"
            @click="showCreateModal = true"
          >
            <img
              src="@/assets/images/Add.svg"
              alt="Add"
              class="btn-icon"
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
                <div
                  v-if="item.schedule"
                  class="scheduled-details"
                ></div>
                <button
                  class="schedule-btn"
                  :id="'schedule-btn-' + item.id"
                  @click="onScheduleButtonClick(item)"
                >
                  <img
                    src="@/assets/images/Schedule.svg"
                    :alt="item.schedule ? 'Details' : 'Schedule'"
                    class="btn-icon"
                  />
                  {{ item.schedule ? 'Details' : 'Schedule' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modals -->
      <CreateAssessmentModal
        v-if="showCreateModal"
        :questions="questions"
        @close="closeCreateModal"
        @assessment-created="handleAssessmentCreated"
        @validation-error="handleValidationError"
        @error="handleError"
      />

      <div
        v-if="showScheduleModal"
        class="modal-overlay"
      >
        <ScheduleAssessmentModal
          :assessmentName="selectedAssessment?.name"
          :assessment_id="selectedAssessment?.id"
          @close="closeScheduleModal"
          @schedule="handleScheduleAssessment"
        />
      </div>

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
  </div>
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
      questions: [],
      allGroups: [],
      allMembers: [],
      selectedAssessment: null,
      scheduleDetails: null,
      showCreateModal: false,
      showScheduleModal: false,
      showScheduleDetailsModal: false,
      loading: false,
      toast: null,
      // Pagination
      pageSize: 10,
      currentPage: 1,
      showPageDropdown: false,
    };
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
      return (this.allGroups || []).reduce((map, g) => {
        map[g.id] = g.name;
        return map;
      }, {});
    },
    memberNameMap() {
      return (this.allMembers || []).reduce((map, m) => {
        const first = (m.first_name || m.name || '').toString().trim();
        const last = (m.last_name || '').toString().trim();
        const role = (m.member_role || m.role || '').toString().trim();
        let full = first;
        if (last) full = full ? `${full} ${last}` : last;
        if (!full) full = m.email || m.id || 'Unknown';
        if (role) full = `${full} — ${role}`;
        map[m.id] = full;
        return map;
      }, {});
    },
    memberDetailMap() {
      return (this.allMembers || []).reduce((map, m) => {
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
        return map;
      }, {});
    },
  },
  created() {
    this.toast = useToast();
  },
  mounted() {
    this.initializeComponent();
  },
  methods: {
    // --- Data Fetching & Initialization ---
    async initializeComponent() {
      this.loading = true;
      try {
        const authToken = storage.get('authToken');
        const params = await this.getRequestParams(authToken);

        if (!params.organization_id && !params.user_id) {
          console.warn('[AssessmentsCard] No orgId or userId found.');
          return;
        }
        await this.fetchData(authToken, params);
      } catch (e) {
        console.error(
          '[AssessmentsCard] Failed to fetch initial data',
          e?.message
        );
        this.assessments = [];
        this.questions = [];
        this._showToast('error', 'Error', 'Could not load assessment data.');
      } finally {
        this.loading = false;
      }
    },
    async getRequestParams(authToken) {
      const userId = storage.get('user_id');
      let orgId =
        storage.get('organization_id') ||
        storage.get('org_id') ||
        storage.get('organizationId') ||
        storage.get('orgId') ||
        (storage.get('user') && storage.get('user').organization_id) ||
        null;

      if (!orgId && authToken) {
        try {
          const base = process.env.VUE_APP_API_BASE_URL;
          const profileRes = await axios.get(`${base}/api/profile`, {
            headers: { Authorization: `Bearer ${authToken}` },
          });
          const prof = profileRes.data || {};
          orgId =
            prof.organization_id ||
            (prof.user && prof.user.organization_id) ||
            null;
        } catch (e) {
          console.warn('[AssessmentsCard] Failed to fetch profile', e?.message);
        }
      }

      const params = {};
      if (orgId) params.organization_id = orgId;
      else if (userId) params.user_id = userId;
      else console.warn('[AssessmentsCard] No orgId or userId found.');

      return params;
    },
    async fetchData(authToken, params) {
      const base = process.env.VUE_APP_API_BASE_URL;
      const headers = { Authorization: `Bearer ${authToken}` };

      const [assessmentsRes, questionsRes, groupsRes, membersRes] =
        await Promise.all([
          axios.get(`${base}/api/assessments`, { headers, params }),
          axios.get(`${base}/api/organization-assessment-questions`, {
            headers,
          }),
          axios.get(`${base}/api/groups`, { headers }),
          axios.get(`${base}/api/members`, { headers }),
        ]);

      this.assessments = this.parseApiResponse(assessmentsRes, 'assessments');
      this.questions = this.parseApiResponse(questionsRes, 'questions');
      this.allGroups = this.parseApiResponse(groupsRes, 'groups');
      this.allMembers = this.parseApiResponse(membersRes, 'members');

      await this.fetchScheduleStatuses(authToken);
    },
    parseApiResponse(response, key) {
      const data = response?.data;
      if (!data) return [];
      if (Array.isArray(data[key])) return data[key];
      if (Array.isArray(data)) return data;
      return [data];
    },
    async fetchScheduleStatuses(authToken) {
      try {
        const scheduleChecks = this.assessments.map((assessment) =>
          this.fetchScheduleForAssessment(assessment, authToken)
        );
        const updatedItems = await Promise.all(scheduleChecks);
        this.assessments = updatedItems;
      } catch (err) {
        console.warn(
          '[AssessmentsCard] Failed to pre-fetch schedule statuses.',
          err
        );
      }
    },
    async fetchScheduleForAssessment(assessment, authToken) {
      try {
        const base = process.env.VUE_APP_API_BASE_URL;
        const res = await axios.get(`${base}/api/scheduled-email/show`, {
          params: { assessment_id: assessment.id },
          headers: { Authorization: `Bearer ${authToken}` },
        });
        return res.data?.scheduled
          ? { ...assessment, schedule: res.data }
          : { ...assessment, schedule: null };
      } catch (e) {
        console.warn(
          `[AssessmentsCard] Failed to fetch schedule for assessment ${assessment.id}`,
          e?.message
        );
        return { ...assessment, schedule: null };
      }
    },

    // --- Modal Control ---
    openScheduleModal(item) {
      this.selectedAssessment = item;
      this.showScheduleModal = true;
    },
    closeScheduleModal() {
      this.showScheduleModal = false;
      this.selectedAssessment = null;
    },
    openScheduleDetails(item) {
      this.selectedAssessment = item;
      this.scheduleDetails = item.schedule;
      this.showScheduleDetailsModal = true;
    },
    closeScheduleDetailsModal() {
      this.showScheduleDetailsModal = false;
      this.scheduleDetails = null;
    },
    closeCreateModal() {
      this.showCreateModal = false;
    },

    // --- Event Handlers ---
    onScheduleButtonClick(item) {
      if (item?.schedule) {
        this.openScheduleDetails(item);
      } else {
        this.openScheduleModal(item);
      }
    },
    handleAssessmentCreated(newAssessment) {
      this.assessments.unshift(newAssessment);
      this.closeCreateModal();
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
      this._showToast(errorData.type, errorData.title, errorData.message);
    },
    async handleScheduleAssessment(payload) {
      // payload expected: { assessment_id, date, time, group_ids, member_ids, selectedMembers }
      const date = payload?.date;
      const time = payload?.time;
      const groupIds = payload?.group_ids || payload?.groupIds || [];
      const memberIds = payload?.member_ids || payload?.memberIds || [];
      const selectedMembers = payload?.selectedMembers || [];

      if (!this.selectedAssessment || !date || !time) {
        return this._showToast(
          'warn',
          'Missing Data',
          'Please select assessment, date, and time.'
        );
      }

      this.loading = true;
      try {
        const authToken = storage.get('authToken');
        const base = process.env.VUE_APP_API_BASE_URL;

        // Compute sendAt (interpret date/time as frontend local time) and convert to UTC ISO
        const localDateTime = new Date(`${date}T${time}:00`);
        const sendAt = localDateTime.toISOString();

        // 1) Create the assessment schedule record and include send_at so backend can schedule correctly in UTC
        await axios.post(
          `${base}/api/assessment-schedules`,
          {
            assessment_id: this.selectedAssessment.id,
            date,
            time,
            send_at: sendAt,
            group_ids: groupIds,
            member_ids: memberIds,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
          },
          { headers: { Authorization: `Bearer ${authToken}` } }
        );

        // 2) Queue individual scheduled emails. Use allSettled so one failing
        // recipient doesn't cancel the whole batch (we'll surface failures below).
        const emailPromises = (selectedMembers || []).map((member) => {
          if (member && member.email) {
            const group_id =
              member.group_id ||
              (Array.isArray(member.group_ids) && member.group_ids[0]) ||
              (Array.isArray(groupIds) && groupIds.length === 1
                ? groupIds[0]
                : null);
            return axios
              .post(
                `${base}/api/schedule-email`,
                {
                  recipient_email: member.email,
                  subject: 'Assessment Scheduled',
                  body: `You have an assessment scheduled: ${this.selectedAssessment.name}`,
                  send_at: sendAt,
                  assessment_id: this.selectedAssessment.id,
                  member_id: member.id,
                  group_id: group_id,
                },
                { headers: { Authorization: `Bearer ${authToken}` } }
              )
              .catch((err) => ({ error: err, member }));
          }
          return Promise.resolve({ skipped: true });
        });

        const results = await Promise.allSettled(emailPromises);
        const failed = results
          .filter((r) => r.status === 'fulfilled' && r.value && r.value.error)
          .map((r) => r.value && r.value.member?.email)
          .filter(Boolean);

        const msg = failed.length
          ? 'Assessment scheduled - ' + failed.length + ' email(s) failed'
          : 'Assessment scheduled';
        this._showToast('success', 'Scheduled', msg);

        // Close modal then refresh the single assessment's schedule to avoid
        // a full re-initialize (less disruptive and faster).
        this.closeScheduleModal();
        try {
          const updated = await this.fetchScheduleForAssessment(
            this.selectedAssessment,
            authToken
          );
          // Replace the matching assessment in the list (preserve ordering)
          this.assessments = this.assessments.map((a) =>
            a.id === updated.id ? updated : a
          );
        } catch (refreshErr) {
          // If single refresh fails, fall back to full refresh (best-effort)
          console.warn(
            '[AssessmentsCard] Failed to refresh single schedule, falling back to full refresh.',
            refreshErr
          );
          await this.initializeComponent();
        }
      } catch (e) {
        console.error('Failed to schedule assessment', e);
      } finally {
        this.loading = false;
      }
    },
    goToSummary(item) {
      this.$router.push({
        name: 'AssessmentSummary',
        params: { assessmentId: item.id },
      });
    },

    // --- Pagination ---
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

    // --- Formatters & Utilities ---
    formatLocalDateTime(dateStr, timeStr) {
      try {
        const date = this._parseDateTime(dateStr, timeStr);
        if (!date) return `${dateStr || ''} ${timeStr || ''}`.trim();

        const options = {
          day: '2-digit',
          month: 'short',
          year: 'numeric',
          hour: 'numeric',
          minute: '2-digit',
          hour12: true,
        };
        const formatter = new Intl.DateTimeFormat('en-US', options);
        return formatter.format(date).replace(',', '');
      } catch (e) {
        console.error('Failed to format date/time', e);
        return `${dateStr || ''} ${timeStr || ''}`.trim();
      }
    },
    _parseDateTime(dateStr, timeStr) {
      if (!dateStr) return null;
      const dateTimeString = `${String(dateStr).trim()} ${String(
        timeStr || '00:00:00'
      ).trim()}`;
      const date = new Date(dateTimeString);
      return isNaN(date.getTime()) ? null : date;
    },
    _showToast(severity, summary, detail, life = 3500) {
      this.toast.add({ severity, summary, detail, life });
    },
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
  display: flex;
  flex-direction: column;
  position: relative;
}

.assessments-header-row {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 24px;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
}

.table-container {
  width: 100%;
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 12px 24px;
  text-align: left;
  border-bottom: 1px solid #ebebeb;
}

.table th {
  background: #f9f9f9;
  font-weight: 600;
}

.assessment-link {
  background: none;
  border: none;
  color: #0074c2;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  text-align: left;
}
.assessment-link:hover {
  text-decoration: underline;
}

.schedule-btn {
  background: #f5f5f5;
  border-radius: 999px;
  padding: 8px 16px;
  min-width: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  border: none;
  cursor: pointer;
}
.schedule-btn:hover {
  background: #e6f0fa;
}

.btn-icon {
  width: 18px;
  height: 18px;
  vertical-align: middle;
}

.assessments-header-actions .btn-icon {
  margin-right: 8px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;

  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

@media (max-width: 768px) {
  .assessments-header-row {
    padding: 16px;
  }
  .table th,
  .table td {
    padding: 8px 12px;
  }
}
</style>
