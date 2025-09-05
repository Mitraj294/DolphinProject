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
              <!-- show schedule metadata when present -->
              <div
                v-if="item.schedule"
                class="scheduled-details"
              ></div>

              <!-- unified button: label and click chosen at runtime to avoid template mismatch -->
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
      @click.self="closeScheduleDetailsModal"
    >
      <div
        class="modal-card"
        v-if="scheduleDetails"
      >
        <button
          class="modal-close-btn"
          @click="closeScheduleDetailsModal"
        >
          &times;
        </button>
        <div class="modal-title2">Scheduled Assessment Details</div>
        <div class="modal-desc">
          Details for the selected scheduled assessment.
        </div>
        <br />
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
                {{ scheduleDetails.assessment.name }}
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
                    scheduleDetails.schedule.date,
                    scheduleDetails.schedule.time
                  )
                }}
              </div>
            </div>
          </div>

          <div class="schedule-header-right">
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

        <div v-if="scheduleDetails && scheduleDetails.schedule">
          <div class="detail-row">
            <div class="detail-table">
              <div class="recipient-table-wrap">
                <table
                  v-if="filteredEmails && filteredEmails.length"
                  class="recipient-table compact"
                >
                  <thead>
                    <tr>
                      <th style="width: 20%">Group</th>
                      <th style="width: 30%">Name</th>
                      <th style="width: 30%">Email</th>
                      <th style="width: 20%">Role</th>
                    </tr>
                  </thead>
                  <tbody>
                    <template
                      v-for="(g, gi) in groupedEmails"
                      :key="'group-' + gi"
                    >
                      <tr
                        v-for="(e, ei) in g.items"
                        :key="'email-' + gi + '-' + ei"
                      >
                        <td
                          v-if="ei === 0"
                          :rowspan="g.items.length"
                          class="group-cell"
                        >
                          {{ g.name || 'Ungrouped' }}
                        </td>
                        <td>
                          {{
                            e.member_id && memberDetailMap[e.member_id]
                              ? memberDetailMap[e.member_id].name
                              : e.recipient_email ||
                                e.email ||
                                e.to ||
                                'Unknown'
                          }}
                        </td>
                        <td>
                          {{
                            e.member_id && memberDetailMap[e.member_id]
                              ? memberDetailMap[e.member_id].email
                              : e.recipient_email || e.email || e.to || ''
                          }}
                        </td>
                        <td>
                          {{
                            e.member_id && memberDetailMap[e.member_id]
                              ? memberDetailMap[e.member_id].role || 'Member'
                              : ''
                          }}
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </div>
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
import ScheduleAssessmentModal from '@/components/Common/Leads_Assessment/ScheduleAssessmentModal.vue';
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
    filteredEmails() {
      if (!this.scheduleDetails || !this.scheduleDetails.emails) return [];
      const schedule = this.scheduleDetails.schedule;
      if (!schedule) return [];
      // Only include emails for this assessment; guard against falsy entries
      return (this.scheduleDetails.emails || []).filter(
        (e) => e && e.assessment_id == this.scheduleDetails.assessment.id
      );
    },
    groupedEmails() {
      // Prefer schedule.group_ids so we display all scheduled groups even if
      // there are no explicit email rows for some members in that group.
      const list = this.filteredEmails || [];
      const schedule =
        (this.scheduleDetails && this.scheduleDetails.schedule) || null;
      const map = new Map();

      const parseArrayField = (v) => {
        if (!v) return [];
        if (Array.isArray(v)) return v.map((x) => Number(x));
        try {
          const p = JSON.parse(v);
          if (Array.isArray(p)) return p.map((x) => Number(x));
        } catch (e) {
          return v
            .toString()
            .replace(/\[|\]|\s+/g, '')
            .split(',')
            .filter(Boolean)
            .map((x) => Number(x));
        }
        return [];
      };

      const scheduleGroupIds = schedule
        ? parseArrayField(schedule.group_ids)
        : [];
      const scheduleMemberIds = schedule
        ? parseArrayField(schedule.member_ids)
        : [];

      if (scheduleGroupIds && scheduleGroupIds.length) {
        scheduleGroupIds.forEach((gid) => {
          const gobj = (this.allGroups || []).find(
            (gg) => Number(gg.id) === Number(gid)
          );
          const gname = (gobj && (gobj.name || gobj.group)) || `Group ${gid}`;
          const items = [];

          // include any explicit email rows that reference this group
          list.forEach((e) => {
            const egids = new Set();
            if (e.group_id) egids.add(Number(e.group_id));
            if (e.group_ids) {
              try {
                const parsed = Array.isArray(e.group_ids)
                  ? e.group_ids
                  : JSON.parse(e.group_ids);
                if (Array.isArray(parsed))
                  parsed.forEach((x) => egids.add(Number(x)));
              } catch (err) {
                // ignore parse errors
              }
            }
            if (egids.has(Number(gid))) items.push(e);
          });

          // Determine members that actually belong to this group using the
          // group's pivot data (gobj.members) or by scanning allMembers for
          // members whose group_ids/group_id include this group.
          let groupMemberIds = [];
          if (gobj && Array.isArray(gobj.members) && gobj.members.length) {
            groupMemberIds = gobj.members
              .map((m) => (m && m.id ? Number(m.id) : Number(m)))
              .filter(Boolean);
          }
          // fallback: inspect allMembers for association fields
          if (!groupMemberIds.length) {
            (this.allMembers || []).forEach((m) => {
              const mid = Number(m.id);
              if (!mid) return;
              // direct group_id
              if (m.group_id && Number(m.group_id) === Number(gid)) {
                groupMemberIds.push(mid);
                return;
              }
              // group_ids array/string
              const mgids = parseArrayField(
                m.group_ids || m.group_ids_raw || m.groups || m.group_ids_json
              );
              if (mgids && mgids.includes(Number(gid)))
                groupMemberIds.push(mid);
            });
          }

          // Add members belonging to this group (avoid duplicates)
          (groupMemberIds || []).forEach((mid) => {
            const already = items.find(
              (it) => Number(it.member_id) === Number(mid)
            );
            if (!already) items.push({ member_id: mid });
          });

          map.set(gid, { id: gid, name: gname, items });
        });
        return Array.from(map.values());
      }

      // fallback: group by explicit group on email rows
      list.forEach((e) => {
        const gid = e.group_id || e.group || 'ungrouped';
        const gname =
          e.group_name || e.group || (gid === 'ungrouped' ? 'Ungrouped' : null);
        if (!map.has(gid))
          map.set(gid, { id: gid, name: gname || 'Group', items: [] });
        map.get(gid).items.push(e);
      });
      return Array.from(map.values());
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
        // ignore in non-DOM environments
      }

      if (toast && typeof toast.add === 'function') {
        toast.add({ severity, summary, detail, life });
      } else {
        if (this && this.$toast && typeof this.$toast.add === 'function') {
          this.$toast.add({ severity, summary, detail, life });
        } else if (
          typeof window !== 'undefined' &&
          window.$toast &&
          typeof window.$toast.add === 'function'
        ) {
          window.$toast.add({ severity, summary, detail, life });
        } else {
          // fallback to console if no toast instance is available
          console[
            severity === 'error'
              ? 'error'
              : severity === 'warn'
              ? 'warn'
              : 'log'
          ](`${summary}: ${detail}`);
        }
      }

      if (highZ && typeof window !== 'undefined') {
        // Remove the body class shortly after the toast lifetime to avoid
        // leaving global state set.
        setTimeout(() => {
          try {
            document.body.classList.remove('toast-above-modal');
          } catch (e) {
            /* ignore */
          }
        }, life + 300);
      }
    },
    async openScheduleDetails(item) {
      // Fetch schedule details from backend
      try {
        const res = await axios.get(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/scheduled-email/show',
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
        hr = hr ? hr : 12;
        return `${dayNum} ${mon},${yr} ${hr}:${min} ${ampm}`;
      } catch (e) {
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
    async goToSummary(item) {
      // Before navigating, verify the assessment has a schedule entry
      try {
        const res = await axios.get(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/scheduled-email/show',
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
      this.newAssessment = { name: '', selectedQuestionIds: [] };
    },
    submitAssessment: async function () {
      // Use real questions from backend and store in DB
      const selectedQuestions = this.questions.filter((q) =>
        this.newAssessment.selectedQuestionIds.includes(q.id)
      );
      if (!this.newAssessment.name || selectedQuestions.length === 0) {
        // Use highZ=true so this validation toast appears above any open modal.
        this._showToast(
          'warn',
          'Missing Data',
          'Please enter a name and select at least one question.',
          4000,
          true
        );
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
        this._showToast('error', 'Error', 'Failed to create assessment.', 3500);
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
      }

      // Base API URL
      const base = process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

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

      // Fetch assessment schedules and map them into the assessments so
      // the template can show "Details" when a schedule exists for a row.
      try {
        // The bulk GET /api/assessment-schedules endpoint is not available (405 Method Not Allowed).
        // Instead, we will fetch the schedule status for each assessment individually
        // for the currently visible page to avoid making too many requests on load.
        const pageItems = this.paginatedAssessments || [];
        const scheduleChecks = pageItems.map(async (assessment) => {
          try {
            const res = await axios.get(base + '/api/scheduled-email/show', {
              params: { assessment_id: assessment.id },
              headers: { Authorization: `Bearer ${authToken}` },
            });
            if (
              res.data &&
              (res.data.scheduled === true || res.data.scheduled === 1)
            ) {
              return { ...assessment, schedule: res.data };
            }
            return { ...assessment, schedule: null };
          } catch (e) {
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
        (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
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

  table-layout: auto; /* let columns size naturally */
}

.table th,
.table td {
  padding: 16px;
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

/* First column: fixed width for Assessment Name */

/* Second column: fixed width for Actions/details */

/* Responsive fallback: on narrow screens let columns wrap/size naturally */

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

  user-select: none;
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
  left: 5px;
  top: 2px;
  width: 6px;
  height: 9px;
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
@media (max-width: 00px) {
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

.notifications-add-btn-icon {
  width: 18px;
  height: 18px;
  margin-right: 6px;
  display: inline-block;
  vertical-align: middle;
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
.modal-title2 {
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
.detail-label {
  text-align: left;
}
.detail-row > .detail-label {
  margin-top: 8px;

  text-align: left;
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
  margin-top: 12px;
  max-width: 600px;
}
.detail-table .detail-label {
  font-weight: 600;
  color: #333;
  flex: 0 0 36% !important;
  max-width: 40% !important;
  text-align: left !important;
}

.detail-table .detail-value {
  color: #222;
  word-break: break-word;
  flex: 1 1 64% !important;
  text-align: left !important;
  padding-left: 8px !important;
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
  padding: 8px 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  max-width: 600px;
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 8px;
}
.detail-table .detail-row:last-child {
  border-bottom: none;
}

/* Schedule header responsive layout */
.schedule-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  gap: 20px;
  padding-left: 8px;
  padding-right: 28px;
  box-sizing: border-box;
}
.schedule-header-left {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  min-width: 0;
}
.schedule-assessment-name {
  font-weight: 600;
  color: #222;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 520px;
}

.schedule-header-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  min-width: 0;
}

@media (max-width: 900px) {
  .schedule-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    gap: 20px;
    padding-left: 8px;
    padding-right: 38px;
    box-sizing: border-box;
  }
  .schedule-header-right {
    align-self: flex-start;
  }
  .schedule-assessment-name {
    max-width: 100%;
    white-space: normal;
  }
}
@media (max-width: 500px) {
  .schedule-header {
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 8px;
  }
  .schedule-header-left,
  .schedule-header-right {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
  }
  .schedule-header-right {
    align-self: center;
  }
  .schedule-assessment-name {
    max-width: 100%;
    white-space: normal;
    margin: 0 auto;
  }
}
.recipient-list {
  max-height: 140px;
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
    max-width: 40%;
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
    max-width: 40%;
  }
}

/* Status badge */
.recipient-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 999px;
  font-weight: 600;
  font-size: 13px;
  color: #fff;
}
.recipient-badge.read {
  background: #28a745; /* green */
}
.recipient-badge.unread {
  background: #e74c3c; /* red */
}

/* Ensure recipient table stays inside modal and truncates long text */
.modal-card {
  overflow: auto;
  max-height: calc(100vh - 120px);
}
.recipient-table.compact {
  table-layout: fixed;
  width: 100%;
}
.recipient-table.compact td,
.recipient-table.compact th {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.recipient-table.compact th:nth-child(1),
.recipient-table.compact td:nth-child(1) {
  width: 25%; /* Group */
}
.recipient-table.compact th:nth-child(2),
.recipient-table.compact td:nth-child(2) {
  width: 30%; /* Name */
}
.recipient-table.compact th:nth-child(3),
.recipient-table.compact td:nth-child(3) {
  width: 35%; /* Email */
}
.recipient-table.compact th:nth-child(4),
.recipient-table.compact td:nth-child(4) {
  width: 10%; /* Role */
}
.recipient-table-wrap {
  max-width: 600px !important;
  width: 100% !important;
  margin: 0 auto !important;
  overflow-x: auto !important;
  -webkit-overflow-scrolling: touch;
}

.recipient-table-wrap .recipient-table {
  max-width: 600px !important;
  table-layout: fixed !important;
  width: 100% !important;
  min-width: 0 !important;
}
.recipient-table-wrap .recipient-table thead,
.recipient-table-wrap .recipient-table thead th {
  min-width: 0 !important;
}
.recipient-table-wrap .recipient-table th,
.recipient-table-wrap .recipient-table td {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* If something else sets an inline min-width on the thead cells,
   make the table allow shrinking by forcing max-content to not apply. */
.recipient-table-wrap .recipient-table thead th {
  max-width: 600px !important;
}

/* Make the table more compact so it fits better in the modal header
   — reduce font-size and cell padding. If content still exceeds the
   available width the wrapper will allow horizontal scrolling. */
.recipient-table.compact {
  font-size: 13px !important;
}
.recipient-table.compact th,
.recipient-table.compact td {
  padding: 8px 10px !important;
}
.recipient-table.compact thead th {
  font-size: 12px !important;
  font-weight: 600;
  color: #666;
}

/* If space allows, shrink long email addresses on very small screens */
@media (max-width: 480px) {
  .recipient-table.compact {
    font-size: 12px !important;
  }
  .recipient-table.compact th,
  .recipient-table.compact td {
    padding: 6px 8px !important;
  }
}

@media (max-width: 700px) {
  .recipient-table.compact thead {
    display: none;
  }
  .recipient-table.compact td {
    white-space: normal;
  }
  .modal-card {
    max-height: calc(100vh - 80px);
  }
}

/* cap recipient table width and center inside modal */
.detail-table .recipient-table {
  max-width: 600px;
  margin: 0 auto;
}
.detail-table .recipient-table.compact th,
.detail-table .recipient-table.compact td {
  overflow: hidden;
  text-overflow: ellipsis;
}

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
</style>
