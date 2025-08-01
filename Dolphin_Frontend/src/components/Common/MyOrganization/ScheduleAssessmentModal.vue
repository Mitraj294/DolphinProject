<template>
  <div class="modal-card">
    <button
      class="modal-close"
      @click="$emit('close')"
    >
      &times;
    </button>
    <h2 class="modal-title">Schedule {{ assessmentName }}</h2>
    <form
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
        />
        <MultiSelectDropdown
          :options="members"
          :selectedItems="selectedMembers"
          @update:selectedItems="selectedMembers = $event"
          placeholder="Members"
          icon="fas fa-user"
        />
      </div>
      <div class="modal-form-actions">
        <button
          class="modal-save-btn"
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
    assessmentName: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      date: '',
      time: '',
      selectedGroups: [],
      selectedMembers: [],
      groups: [],
      members: [],
      loadingGroups: false,
      loadingMembers: false,
    };
  },

  mounted: async function () {
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
      if (Array.isArray(memberRes.data)) {
        this.members = memberRes.data.map((m) => ({
          id: m.id,
          name:
            m.first_name && m.last_name
              ? `${m.first_name} ${m.last_name}`
              : m.name || m.email || 'Unknown',
        }));
      } else if (Array.isArray(memberRes.data.members)) {
        this.members = memberRes.data.members.map((m) => ({
          id: m.id,
          name:
            m.first_name && m.last_name
              ? `${m.first_name} ${m.last_name}`
              : m.name || m.email || 'Unknown',
        }));
      } else {
        this.members = [];
      }
      this.loadingMembers = false;
    } catch (e) {
      this.groups = [];
      this.members = [];
      this.loadingGroups = false;
      this.loadingMembers = false;
    }
  },
  watch: {
    // Reset modal fields when opened/closed
    assessmentName() {
      this.date = '';
      this.time = '';
      this.selectedGroups = [];
      this.selectedMembers = [];
    },
  },
  methods: {
    emitSchedule() {
      this.$emit('schedule', {
        date: this.date,
        time: this.time,
        groupIds: this.selectedGroups.map((g) => g.id),
        memberIds: this.selectedMembers.map((m) => m.id),
      });
    },
  },
};
</script>

<style scoped>
.modal-card {
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 4px 32px 0 rgba(33, 150, 243, 0.1);
  padding: 40px 48px 32px 48px;
  min-width: 600px;
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
@media (max-width: 900px) {
  .modal-card {
    min-width: 0;
    max-width: calc(100vw - 32px);
    width: calc(98vw - 32px);
    padding: 20px 16px 20px 16px;
    border-radius: 14px;
    margin: 16px;
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
</style>
