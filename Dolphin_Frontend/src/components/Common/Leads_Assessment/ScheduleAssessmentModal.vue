<template>
  <div
    class="modal-overlay"
    @click.self="$emit('close')"
  >
    <div
      class="modal-card"
      style="max-width: 900px"
    >
      <button
        class="modal-close-btn"
        @click="$emit('close')"
      >
        &times;
      </button>
      <div class="modal-title">Schedule an Assessment</div>
      <div
        class="modal-desc"
        style="font-size: 1.2rem !important; margin-bottom: 32px !important"
      >
        Schedule this assessment to be sent to members of your organization.
      </div>

      <!-- Loading State -->
      <div
        v-if="scheduledLoading || loadingGroups || loadingMembers"
        class="loading-container"
      >
        Loading...
      </div>

      <!-- Existing Schedule Display -->
      <div
        v-else-if="scheduledStatus === 'scheduled' && scheduledDetails"
        class="scheduled-info"
      >
        <h3>Assessment Already Scheduled</h3>
        <p>This assessment is scheduled to be sent on:</p>
        <p>
          <strong>Date:</strong>
          {{
            scheduledDetails?.schedule
              ? new Date(
                  `${scheduledDetails.schedule.date}T${scheduledDetails.schedule.time}`
                ).toLocaleDateString()
              : ''
          }}
        </p>
        <p>
          <strong>Time:</strong>
          {{
            scheduledDetails?.schedule
              ? new Date(
                  `${scheduledDetails.schedule.date}T${scheduledDetails.schedule.time}`
                ).toLocaleTimeString()
              : ''
          }}
        </p>
        <p>
          <strong>To:</strong>
          {{
            (scheduledDetails.emails &&
              scheduledDetails.emails[0] &&
              (scheduledDetails.emails[0].recipient_email ||
                scheduledDetails.emails[0].email)) ||
            'Selected members/groups'
          }}
        </p>
        <div class="modal-form-actions">
          <button
            type="button"
            class="org-edit-cancel"
            @click="$emit('close')"
          >
            Close
          </button>
        </div>
      </div>

      <!-- Scheduling Form -->
      <form
        v-else
        class="modal-form"
        @submit.prevent="schedule"
      >
        <FormRow
          class="modal-form-row"
          style="
            margin-bottom: 0 !important;
            display: flex;
            gap: 18px;
            align-items: flex-start;
            flex-direction: row;
          "
        >
          <div
            class="modal-form-row-div"
            style="flex: 1; min-width: 0"
          >
            <FormLabel
              style="font-size: 1rem !important; margin: 0 0 6px 0 !important"
              >Select Date</FormLabel
            >
            <FormInput
              v-model="scheduleDate"
              type="date"
              required
            />
          </div>
          <div
            class="modal-form-row-div"
            style="flex: 1; min-width: 0"
          >
            <FormLabel
              style="font-size: 1rem !important; margin: 0 0 6px 0 !important"
              >Select Time</FormLabel
            >
            <FormInput
              v-model="scheduleTime"
              type="time"
              required
            />
          </div>
        </FormRow>
        <FormRow
          class="modal-form-row"
          style="
            margin-bottom: 0 !important;
            display: flex;
            gap: 18px;
            align-items: flex-start;
            flex-direction: row;
          "
        >
          <div
            class="modal-form-row-div"
            style="flex: 1; min-width: 0"
          >
            <FormLabel
              style="font-size: 1rem !important; margin: 0 0 6px 0 !important"
              >Select Group</FormLabel
            >
            <MultiSelectDropdown
              :options="groups"
              :selectedItems="
                Array.isArray(selectedGroupIds) ? selectedGroupIds : []
              "
              @update:selectedItems="onGroupSelection"
              placeholder="Select one or more groups"
              :enableSelectAll="true"
            />
          </div>
          <div
            class="modal-form-row-div"
            style="flex: 1; min-width: 0"
          >
            <FormLabel
              style="font-size: 1rem !important; margin: 0 0 6px 0 !important"
              >Select Member</FormLabel
            >
            <MultiSelectDropdown
              :options="filteredMembers"
              :selectedItems="
                Array.isArray(selectedMemberIds) ? selectedMemberIds : []
              "
              @update:selectedItems="onMemberSelection"
              placeholder="Select one or more members"
              :enableSelectAll="true"
            />
          </div>
        </FormRow>

        <div class="modal-form-actions">
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="isSubmitting"
          >
            <i class="fas fa-calendar-check"></i>
            {{ isSubmitting ? 'Scheduling...' : 'Schedule' }}
          </button>
          <button
            type="button"
            class="org-edit-cancel"
            @click="$emit('close')"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import FormInput from '@/components/Common/Common_UI/Form/FormInput.vue';
import FormLabel from '@/components/Common/Common_UI/Form/FormLabel.vue';
import MultiSelectDropdown from '@/components/Common/Common_UI/Form/MultiSelectDropdown.vue';
import FormRow from '@/components/Common/Common_UI/Form/FormRow.vue';
import axios from 'axios';
import storage from '@/services/storage';
import { useToast } from 'primevue/usetoast';

export default {
  name: 'ScheduleAssessmentModal',
  components: {
    FormInput,
    FormLabel,
    MultiSelectDropdown,
    FormRow,
  },
  props: {
    assessment_id: {
      type: [Number, String],
      required: true,
    },
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      scheduledLoading: true,
      scheduledStatus: null,
      scheduledDetails: null,
      loadingGroups: true,
      loadingMembers: true,
      isSubmitting: false,
      groups: [],
      members: [],
      selectedGroupIds: [],
      selectedMemberIds: [],
      scheduleDate: '',
      scheduleTime: '',
    };
  },
  computed: {
    // Always show the full members list in the dropdown. When groups are
    // selected we still display all members, but group-members will be
    // auto-selected via onGroupSelection(). This keeps the UI consistent
    // with the user's request to "show all members" while pre-selecting
    // members that belong to the selected groups.
    filteredMembers() {
      return this.members;
    },
  },
  methods: {
    // --- Helpers ---------------------------------------------------------
    // Return an array of member objects who belong to the supplied group id
    groupMembersFor(groupId) {
      return this.members.filter(
        (member) =>
          Array.isArray(member.group_ids) && member.group_ids.includes(groupId)
      );
    },

    // Merge two arrays of member objects (existing + toAdd) deduped by id.
    // The resulting array preserves the ordering of `this.members`.
    mergeMembersById(existing = [], toAdd = []) {
      const mergedById = {};
      existing.forEach((m) => {
        if (m && m.id !== undefined) mergedById[m.id] = m;
      });
      toAdd.forEach((m) => {
        if (m && m.id !== undefined) mergedById[m.id] = m;
      });
      return this.members.filter((m) => mergedById[m.id]);
    },

    // Return true if all members of a group are present in the selectedIdSet
    allGroupMembersSelected(group, selectedIdSet) {
      const groupMembers = this.groupMembersFor(group.id);
      if (!groupMembers.length) return false;
      return groupMembers.every((gm) => selectedIdSet.has(Number(gm.id)));
    },

    // --- Event handlers -------------------------------------------------
    // Called when the groups multi-select changes.
    // Behavior: preserve manual member selections and add members that belong
    // to the selected groups (no removals).
    onGroupSelection(selectedGroups) {
      this.selectedGroupIds = selectedGroups;

      // If no groups selected, keep manual member selections unchanged.
      if (!Array.isArray(selectedGroups) || selectedGroups.length === 0) {
        return;
      }

      const selectedGroupIds = selectedGroups.map((g) => g.id);

      // Collect members who belong to any selected group
      const groupMembers = this.members.filter(
        (member) =>
          Array.isArray(member.group_ids) &&
          member.group_ids.some((gid) => selectedGroupIds.includes(gid))
      );

      // Merge manual selections with group members (deduped)
      const existing = Array.isArray(this.selectedMemberIds)
        ? this.selectedMemberIds
        : [];
      this.selectedMemberIds = this.mergeMembersById(existing, groupMembers);
    },

    onMemberSelection(selectedMembers) {
      // selectedMembers is an array of member objects from MultiSelectDropdown
      this.selectedMemberIds = Array.isArray(selectedMembers)
        ? selectedMembers
        : [];

      // Build a set of selected member ids for quick lookup
      const selectedIds = new Set(
        this.selectedMemberIds.map((m) => Number(m.id))
      );

      // Determine which groups should be auto-selected: those where ALL members
      // of the group are present in selectedMemberIds
      const autoSelectedGroups = [];
      for (const group of this.groups) {
        // find members belonging to this group
        const groupMembers = this.members.filter(
          (member) =>
            Array.isArray(member.group_ids) &&
            member.group_ids.includes(group.id)
        );

        if (groupMembers.length === 0) {
          // no members in this group, skip
        } else {
          const allSelected = groupMembers.every((gm) =>
            selectedIds.has(Number(gm.id))
          );

          if (allSelected) {
            autoSelectedGroups.push(group);
          }
        }
      }

      this.selectedGroupIds = autoSelectedGroups;
    },

    async schedule() {
      this.isSubmitting = true;
      try {
        // Emit schedule payload to parent so parent can perform both
        // assessment schedule creation and scheduling individual emails.
        const payload = {
          assessment_id: this.assessment_id,
          date: this.scheduleDate,
          time: this.scheduleTime,
          group_ids: this.selectedGroupIds.map((g) => g.id),
          member_ids: this.selectedMemberIds.map((m) => m.id),
          // include selectedMembers with email and ids so parent can call /api/schedule-email
          selectedMembers: (this.selectedMemberIds || []).map((m) => ({
            id: m.id,
            email: m.email,
            name: m.name,
          })),
        };

        this.$emit('schedule', payload);
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Assessment scheduled (sending)...',
          life: 3000,
        });
        this.$emit('close');
      } catch (error) {
        console.error('Failed to schedule assessment:', error);
        const errorDetail =
          error.response?.data?.message || 'Failed to schedule assessment.';
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: errorDetail,
          life: 4000,
        });
      } finally {
        this.isSubmitting = false;
      }
    },

    async checkExistingSchedule() {
      if (!this.assessment_id) {
        console.warn('[ScheduleAssessmentModal] assessment_id is missing.');
        this.scheduledLoading = false;
        return;
      }

      this.scheduledLoading = true;
      try {
        const authToken = storage.get('authToken');
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const url = `${API_BASE_URL}/api/scheduled-email/show?assessment_id=${encodeURIComponent(
          this.assessment_id
        )}`;

        const response = await axios.get(url, {
          headers: { Authorization: `Bearer ${authToken}` },
        });

        // The backend returns { scheduled: bool, schedule: ..., emails: ..., ... }
        if (response.data?.scheduled) {
          this.scheduledStatus = 'scheduled';
          // store the whole response so callers can access schedule/emails/groups_with_members etc.
          this.scheduledDetails = response.data;
        } else {
          this.scheduledStatus = null;
          this.scheduledDetails = null;
        }
      } catch (error) {
        this.scheduledStatus = null;
        console.error('Error checking schedule status:', error);
      } finally {
        this.scheduledLoading = false;
      }
    },

    async fetchGroups() {
      const authToken = storage.get('authToken');
      const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
      const response = await axios.get(`${API_BASE_URL}/api/groups`, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      const groupsData = response.data?.data || response.data || [];
      return groupsData.map((g) => ({ id: g.id, name: g.name }));
    },

    async fetchMembers() {
      const authToken = storage.get('authToken');
      const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
      const response = await axios.get(`${API_BASE_URL}/api/members`, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      const membersData = response.data?.data || response.data || [];
      return membersData.map((m) => ({
        id: m.id,
        name: `${m.first_name} ${m.last_name}`.trim(),
        email: m.email,
        group_ids: Array.isArray(m.group_ids) ? m.group_ids : [],
      }));
    },

    async fetchModalData() {
      this.loadingGroups = true;
      this.loadingMembers = true;
      try {
        const [groups, members] = await Promise.all([
          this.fetchGroups(),
          this.fetchMembers(),
        ]);
        this.groups = groups;
        this.members = members;
      } catch (error) {
        console.error('Error fetching modal data:', error);
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Could not load groups or members.',
          life: 4000,
        });
      } finally {
        this.loadingGroups = false;
        this.loadingMembers = false;
      }
    },
  },
  async mounted() {
    this.checkExistingSchedule();
    this.fetchModalData();
  },
};
</script>

<style scoped>
.loading-container {
  text-align: center;
  padding: 40px;
  font-size: 1.2rem;
}
.scheduled-info {
  padding: 20px;
  text-align: center;
}
.scheduled-info h3 {
  margin-bottom: 1rem;
}
</style>
