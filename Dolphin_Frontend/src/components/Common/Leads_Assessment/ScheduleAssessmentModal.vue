<template>
  <div class="modal-card">
    <button
      class="modal-close"
      @click="$emit('close')"
    >
      &times;
    </button>
    <h2 class="modal-title center-title">Schedule {{ assessmentName }}</h2>
    <div
      v-if="scheduledLoading"
      class="centered-loading"
    >
      Loading schedule status...
    </div>
    <div
      v-else-if="scheduledStatus"
      class="centered-modal-content"
    >
      <div class="scheduled-status-row centered-status-row">
        <span
          :class="{
            'scheduled-status-green': scheduledStatus === 'sent',
            'scheduled-status-yellow': scheduledStatus === 'scheduled',
            'scheduled-status-red': scheduledStatus === 'failed',
          }"
        >
          {{
            scheduledStatus === 'sent'
              ? 'Mail Sent'
              : scheduledStatus === 'failed'
              ? 'Mail Failed'
              : 'Mail Scheduled'
          }}
        </span>
      </div>
      <div class="scheduled-details-table">
        <table class="justified-table">
          <tbody>
            <tr>
              <td class="field">Assessment</td>
              <td class="value">{{ assessmentName }}</td>
            </tr>
            <tr>
              <td class="field">Date</td>
              <td class="value">
                {{
                  scheduledDetails.send_at
                    ? scheduledDetails.send_at.split('T')[0]
                    : ''
                }}
              </td>
            </tr>
            <tr>
              <td class="field">Time</td>
              <td class="value">
                {{
                  scheduledDetails.send_at
                    ? scheduledDetails.send_at.split('T')[1]
                    : ''
                }}
              </td>
            </tr>
            <tr>
              <td class="field">Emails</td>
              <td class="value">
                {{
                  scheduledStatus === 'sent'
                    ? 'Sent'
                    : scheduledStatus === 'failed'
                    ? 'Failed'
                    : 'Scheduled'
                }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <form
      v-if="!scheduledStatus && !scheduledLoading"
      class="modal-form"
      @submit.prevent="emitSchedule"
    >
      <div class="modal-form-row">
        <FormDateTime
          :date="date"
          :time="time"
          @update:date="date = $event"
          @update:time="time = $event"
        />
      </div>
      <div class="modal-form-row">
        <MultiSelectDropdown
          :options="groups"
          :selectedItems="selectedGroups"
          @update:selectedItems="selectedGroups = $event"
          placeholder="Groups"
          icon="fas fa-users"
          :enableSelectAll="true"
        />
        <MultiSelectDropdown
          :options="members"
          :selectedItems="internalSelectedMembers"
          @update:selectedItems="internalSelectedMembers = $event"
          placeholder="Members"
          icon="fas fa-user"
          :inputValue="internalSelectedMembers.map((m) => m.name).join(', ')"
          :enableSelectAll="true"
        />
      </div>
      <div
        class="modal-form-actions"
        v-if="!scheduledStatus"
      >
        <button
          class="btn btn-primary"
          type="submit"
        >
          Schedule
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import FormDateTime from '../Common_UI/Form/FormDateTime.vue';
import MultiSelectDropdown from '../Common_UI/Form/MultiSelectDropdown.vue';
export default {
  name: 'ScheduleAssessmentModal',
  components: { FormDateTime, MultiSelectDropdown },
  props: {
    selectedMembers: {
      type: Array,
      default: () => [],
    },
    assessmentName: {
      type: String,
      default: '',
    },
    assessment_id: {
      type: [String, Number],
      required: true,
    },
  },
  data() {
    return {
      date: '',
      time: '',
      selectedGroups: [],
      internalSelectedMembers: [],
      groups: [],
      members: [],
      loadingGroups: false,
      loadingMembers: false,
      isSyncingSelection: false,
      scheduledStatus: null,
      scheduledDetails: null,
      scheduledLoading: false,
    };
  },

  mounted: async function () {
    // Check for existing assessment schedule before showing form
    if (
      typeof this.assessment_id === 'undefined' ||
      this.assessment_id === null ||
      this.assessment_id === ''
    ) {
      console.warn(
        '[ScheduleAssessmentModal] assessment_id is undefined/null/empty! Modal will not check schedule.'
      );
      this.scheduledStatus = null;
      this.scheduledDetails = null;
      this.scheduledLoading = false;
      return;
    }
    console.log(
      '[ScheduleAssessmentModal] Checking if schedule exists for assessment_id:',
      this.assessment_id
    );
    this.scheduledLoading = true;
    try {
      const storage = (await import('@/services/storage')).default;
      const axios = (await import('axios')).default;
      const authToken = storage.get('authToken');
      const assessment_id = this.assessment_id;
      let url =
        (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
        '/api/scheduled-email/show?assessment_id=' +
        encodeURIComponent(assessment_id);
      console.log('[ScheduleAssessmentModal] API URL:', url);
      const resp = await axios.get(url, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      console.log('[ScheduleAssessmentModal] API response:', resp.data);
      if (resp.data && resp.data.scheduled && resp.data.data) {
        this.scheduledStatus = 'scheduled';
        this.scheduledDetails = resp.data.data;
        console.log(
          '[ScheduleAssessmentModal] Assessment schedule exists:',
          this.scheduledDetails
        );
      } else {
        this.scheduledStatus = null;
        this.scheduledDetails = null;
        console.log(
          '[ScheduleAssessmentModal] No schedule exists for this assessment.'
        );
      }
    } catch (e) {
      this.scheduledStatus = null;
      this.scheduledDetails = null;
      console.error(
        '[ScheduleAssessmentModal] Error checking schedule status:',
        e
      );
    }
    this.scheduledLoading = false;

    // Fetch groups and members from backend
    this.loadingGroups = true;
    this.loadingMembers = true;
    try {
      const storage = (await import('@/services/storage')).default;
      const axios = (await import('axios')).default;
      const authToken = storage.get('authToken');
      // Fetch groups
      const groupRes = await axios.get(
        (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
          '/api/groups',
        { headers: { Authorization: `Bearer ${authToken}` } }
      );
      console.log('Fetched groups response:', groupRes.data);
      if (Array.isArray(groupRes.data)) {
        this.groups = groupRes.data.map((g) => ({ id: g.id, name: g.name }));
      } else if (Array.isArray(groupRes.data.groups)) {
        this.groups = groupRes.data.groups.map((g) => ({
          id: g.id,
          name: g.name,
        }));
      } else {
        this.groups = [];
      }
      this.loadingGroups = false;
      // Fetch members
      const memberRes = await axios.get(
        (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
          '/api/members',
        { headers: { Authorization: `Bearer ${authToken}` } }
      );
      console.log('Fetched members response:', memberRes.data);
      if (Array.isArray(memberRes.data)) {
        this.members = memberRes.data.map((m) => ({
          id: m.id,
          name:
            m.first_name && m.last_name
              ? `${m.first_name} ${m.last_name}`
              : m.name || m.email || 'Unknown',
          email: m.email,
          group_ids: m.group_ids || (m.group_id ? [m.group_id] : []),
        }));
        console.log('Initialized members:', this.members);
        this.members.forEach((m, idx) => {
          console.log('Member[' + idx + ']:', m);
        });
      } else if (Array.isArray(memberRes.data.members)) {
        this.members = memberRes.data.members.map((m) => ({
          id: m.id,
          name:
            m.first_name && m.last_name
              ? `${m.first_name} ${m.last_name}`
              : m.name || m.email || 'Unknown',
          email: m.email,
          group_ids: m.group_ids || (m.group_id ? [m.group_id] : []),
        }));
        console.log('Initialized members:', this.members);
        this.members.forEach((m, idx) => {
          console.log('Member[' + idx + ']:', m);
        });
      } else {
        this.members = [];
        console.log('No members found');
      }
      this.loadingMembers = false;
    } catch (e) {
      this.groups = [];
      this.members = [];
      this.loadingGroups = false;
      this.loadingMembers = false;
      console.error('Error fetching groups/members:', e);
    }
  },
  watch: {
    // Reset modal fields when opened/closed
    assessmentName() {
      this.date = '';
      this.time = '';
      this.selectedGroups = [];
      this.internalSelectedMembers = [];
    },
    // Watch the prop for changes and update the internal state
    selectedMembers: {
      handler(newVal) {
        this.internalSelectedMembers = [...(newVal || [])];
      },
      immediate: true,
      deep: true,
    },
    // Auto-select members when groups are selected
    selectedGroups: {
      handler(newGroups) {
        console.log('selectedGroups changed:', newGroups);
        if (this.isSyncingSelection) return;
        this.isSyncingSelection = true;
        if (!Array.isArray(newGroups) || newGroups.length === 0) {
          if (this.internalSelectedMembers.length > 0) {
            this.internalSelectedMembers = [];
            console.log('No groups selected, clearing selectedMembers');
          }
          this.isSyncingSelection = false;
          return;
        }
        // Collect all group IDs associated with selected groups
        const selectedGroupIds = newGroups.map((g) => g.id);
        console.log('Selected group IDs:', selectedGroupIds);
        // Find all members associated with any selected group
        const autoSelectedMembers = this.members.filter((m) => {
          if (Array.isArray(m.group_ids)) {
            const match = m.group_ids.some((gid) =>
              selectedGroupIds.includes(gid)
            );
            if (match) console.log('Member matched:', m.name, m.group_ids);
            return match;
          }
          if (m.group_id) {
            const match = selectedGroupIds.includes(m.group_id);
            if (match) console.log('Member matched:', m.name, m.group_id);
            return match;
          }
          return false;
        });
        console.log(
          'Auto-selected members:',
          autoSelectedMembers.map((m) => m.name)
        );
        // Only update selectedMembers if changed
        const autoIds = autoSelectedMembers
          .map((m) => m.id)
          .sort()
          .join(',');
        const currentIds = this.internalSelectedMembers
          .map((m) => m.id)
          .sort()
          .join(',');
        if (autoIds !== currentIds) {
          this.internalSelectedMembers = autoSelectedMembers;
          console.log(
            'selectedMembers set:',
            this.internalSelectedMembers.map((m) => m.name)
          );
        }
        this.isSyncingSelection = false;
      },
      deep: true,
    },
    internalSelectedMembers: {
      handler(newMembers) {
        if (this.isSyncingSelection) return;
        this.isSyncingSelection = true;
        // For each group, check if all its members are selected
        const groupIdToMemberIds = {};
        this.groups.forEach((group) => {
          groupIdToMemberIds[group.id] = this.members
            .filter(
              (m) =>
                Array.isArray(m.group_ids) && m.group_ids.includes(group.id)
            )
            .map((m) => m.id);
        });
        // For each group, if all its members are selected, select the group
        const selectedMemberIds = newMembers.map((m) => m.id);
        const autoSelectedGroups = this.groups.filter((group) => {
          const memberIds = groupIdToMemberIds[group.id];
          return (
            memberIds.length > 0 &&
            memberIds.every((id) => selectedMemberIds.includes(id))
          );
        });
        // Merge auto-selected groups with any manually selected groups
        const manualGroupIds = this.selectedGroups.map((g) => g.id);
        const mergedGroups = [
          ...autoSelectedGroups,
          ...this.groups.filter(
            (g) =>
              manualGroupIds.includes(g.id) &&
              !autoSelectedGroups.some((ag) => ag.id === g.id)
          ),
        ];
        // Only update selectedGroups if changed
        const mergedIds = mergedGroups
          .map((g) => g.id)
          .sort()
          .join(',');
        const currentIds = this.selectedGroups
          .map((g) => g.id)
          .sort()
          .join(',');
        if (mergedIds !== currentIds) {
          this.selectedGroups = mergedGroups;
        }
        this.isSyncingSelection = false;
      },
      deep: true,
    },
  },
  methods: {
    emitSchedule() {
      // Convert local date+time to UTC ISO string for send_at
      const localDateTime = new Date(`${this.date}T${this.time}:00`);
      const sendAtUtc = localDateTime.toISOString();
      this.$emit('schedule', {
        date: this.date,
        time: this.time,
        send_at: sendAtUtc,
        groupIds: this.selectedGroups.map((g) => g.id),
        memberIds: this.internalSelectedMembers.map((m) => m.id),
        selectedMembers: this.internalSelectedMembers,
      });
    },
  },
};
</script>
