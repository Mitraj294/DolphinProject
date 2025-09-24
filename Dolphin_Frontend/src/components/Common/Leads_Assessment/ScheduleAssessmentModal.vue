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
          {{ new Date(scheduledDetails.send_at).toLocaleDateString() }}
        </p>
        <p>
          <strong>Time:</strong>
          {{ new Date(scheduledDetails.send_at).toLocaleTimeString() }}
        </p>
        <p><strong>To:</strong> {{ scheduledDetails.recipient_email }}</p>
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
              @update:selectedItems="selectedMemberIds = $event"
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
    filteredMembers() {
      if (this.selectedGroupIds.length === 0) {
        return this.members;
      }
      const selectedIds = this.selectedGroupIds.map((g) => g.id);
      return this.members.filter((member) =>
        member.group_ids.some((groupId) => selectedIds.includes(groupId))
      );
    },
  },
  methods: {
    onGroupSelection(selectedGroups) {
      this.selectedGroupIds = selectedGroups;
      this.selectedMemberIds = []; // Reset member selection when groups change
    },

    async schedule() {
      this.isSubmitting = true;
      try {
        const authToken = storage.get('authToken');
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;

        const payload = {
          assessment_id: this.assessment_id,
          date: this.scheduleDate,
          time: this.scheduleTime,
          group_ids: this.selectedGroupIds.map((g) => g.id),
          member_ids: this.selectedMemberIds.map((m) => m.id),
        };

        await axios.post(`${API_BASE_URL}/api/assessment-schedules`, payload, {
          headers: { Authorization: `Bearer ${authToken}` },
        });

        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Assessment scheduled successfully!',
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

        if (response.data?.scheduled && response.data?.data) {
          this.scheduledStatus = 'scheduled';
          this.scheduledDetails = response.data.data;
        } else {
          this.scheduledStatus = null;
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
