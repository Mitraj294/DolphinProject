<template>
  <div class="table-header-bar">
    <div class="my-org-action-buttons">
      <button
        class="my-org-secondary"
        @click="$router.push({ name: 'MemberListing' })"
      >
        Members Listing
      </button>

      <button
        class="my-org-primary"
        @click="openAddGroupModal"
      >
        <img
          src="@/assets/images/Add.svg"
          alt="Add"
          style="
            width: 18px;
            height: 18px;
            margin-right: 6px;
            vertical-align: middle;
          "
        />
        Add New Group
      </button>
      <button
        class="my-org-primary"
        @click="openAddMemberModal"
      >
        <img
          src="@/assets/images/Add.svg"
          alt="Add"
          style="
            width: 18px;
            height: 18px;
            margin-right: 6px;
            vertical-align: middle;
          "
        />
        Add New Member
      </button>
    </div>
    <!-- Add New Member Modal -->
    <div
      v-if="showAddMemberModal"
      class="modal-overlay"
    >
      <div class="modal-card">
        <button
          class="modal-close"
          @click="showAddMemberModal = false"
        >
          &times;
        </button>
        <h2 class="modal-title">Add New Member</h2>
        <form
          class="modal-form"
          @submit.prevent="saveMember"
        >
          <FormRow>
            <FormInput
              v-model="newMember.firstName"
              placeholder="First Name"
              icon="fas fa-user"
              required
            />
            <FormInput
              v-model="newMember.lastName"
              placeholder="Last Name"
              icon="fas fa-user"
              required
            />
          </FormRow>
          <FormRow>
            <FormInput
              v-model="newMember.email"
              placeholder="Email"
              icon="fas fa-envelope"
              type="email"
              required
            />
            <FormInput
              v-model="newMember.phone"
              placeholder="Phone Number"
              icon="fas fa-phone"
              required
            />
          </FormRow>
          <FormRow>
            <MultiSelectDropdown
              :options="roles"
              :selectedItems="
                Array.isArray(newMember.roles) ? newMember.roles : []
              "
              @update:selectedItems="newMember.roles = $event"
              placeholder="Role"
              icon="fas fa-user-tag"
              :enableSelectAll="true"
            />
            <MultiSelectDropdown
              :options="groups"
              :selectedItems="
                Array.isArray(newMember.groups) ? newMember.groups : []
              "
              @update:selectedItems="newMember.groups = $event"
              placeholder="Groups associated with"
              :enableSelectAll="true"
            />
          </FormRow>
          <div class="modal-form-actions">
            <button
              type="submit"
              class="modal-save-btn"
            >
              Save Member
            </button>
          </div>
        </form>
      </div>
    </div>
    <!-- Add New Group Modal -->
    <div
      v-if="showAddGroupModal"
      class="modal-overlay"
    >
      <div class="modal-card">
        <button
          class="modal-close"
          @click="showAddGroupModal = false"
        >
          &times;
        </button>
        <h2 class="modal-title">Add New Group</h2>
        <form
          class="modal-form"
          @submit.prevent="saveGroup"
        >
          <FormRow>
            <FormInput
              icon="fas fa-users"
              v-model="newGroup.name"
              placeholder=" Group Name"
              required
            />
            <MultiSelectDropdown
              :options="availableMembers"
              :selectedItems="
                Array.isArray(newGroup.members) ? newGroup.members : []
              "
              @update:selectedItems="newGroup.members = $event"
              placeholder="Members"
              icon="fas fa-user"
              :enableSelectAll="true"
            />
          </FormRow>
          <div class="modal-form-actions">
            <button
              type="submit"
              class="modal-save-btn"
            >
              Save Group
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import FormInput from '@/components/Common/Common_UI/Form/FormInput.vue';
import MultiSelectDropdown from '@/components/Common/Common_UI/Form/MultiSelectDropdown.vue';
import FormRow from '@/components/Common/Common_UI/Form/FormRow.vue';
import axios from 'axios';
import storage from '@/services/storage';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
export default {
  name: 'OrgActionButtons',
  components: {
    FormInput,
    MultiSelectDropdown,
    FormRow,
    Toast,
  },
  data() {
    return {
      showAddMemberModal: false,
      showAddGroupModal: false,
      newMember: {
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        roles: [],
        groups: [],
      },
      newGroup: {
        name: '',
        members: [],
      },
      roles: [
        { id: 1, name: 'Manager' },
        { id: 2, name: 'CEO' },
        { id: 3, name: 'Owner' },
        { id: 4, name: 'Support' },
      ],
      groups: [],
      availableMembers: [],
      toast: null,
    };
  },
  mounted() {
    this.toast = useToast();
  },
  methods: {
    async openAddMemberModal() {
      // Fetch groups for this organization
      try {
        const authToken = storage.get('authToken');
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const res = await axios.get(`${API_BASE_URL}/api/groups`, {
          headers: { Authorization: `Bearer ${authToken}` },
        });
        this.groups = Array.isArray(res.data) ? res.data : [];
      } catch (e) {
        this.groups = [];
      }
      this.showAddMemberModal = true;
    },

    openAddGroupModal() {
      // Fetch members for this organization
      this.availableMembers = [];
      const authToken = storage.get('authToken');
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      axios
        .get(`${API_BASE_URL}/api/members`, {
          headers: { Authorization: `Bearer ${authToken}` },
        })
        .then((res) => {
          if (Array.isArray(res.data)) {
            this.availableMembers = res.data.map((m) => ({
              ...m,
              id: m.id || m.value || m,
              name: `${m.first_name || m.firstName || ''} ${
                m.last_name || m.lastName || ''
              }`.trim(),
            }));
          } else {
            this.availableMembers = [];
          }
        })
        .catch(() => {
          this.availableMembers = [];
        })
        .finally(() => {
          this.showAddGroupModal = true;
        });
    },

    async saveMember() {
      try {
        // Require at least one role
        if (
          !Array.isArray(this.newMember.roles) ||
          this.newMember.roles.length === 0
        ) {
          this.toast.add({
            severity: 'warn',
            summary: 'Warning',
            detail: 'Please select a role for the member.',
            life: 4000,
          });
          return;
        }
        const payload = {
          first_name: this.newMember.firstName,
          last_name: this.newMember.lastName,
          email: this.newMember.email,
          phone: this.newMember.phone,
          // send role ids to match pivot-backed roles on the backend
          member_role: Array.isArray(this.newMember.roles)
            ? this.newMember.roles.map((r) => r.id || r.value || r)
            : [],
          group_ids: Array.isArray(this.newMember.groups)
            ? this.newMember.groups.map((g) => g.id || g.value || g)
            : [],
        };
        const authToken = storage.get('authToken');
        const headers = {};
        if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
        await axios.post(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/members',
          payload,
          { headers }
        );
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Member added successfully!',
          life: 3000,
        });
        this.$emit('member-added');
      } catch (e) {
        let msg = 'Failed to add member.';
        if (e.response && e.response.data && e.response.data.message) {
          msg = e.response.data.message;
        } else if (
          e.response &&
          e.response.data &&
          typeof e.response.data === 'string'
        ) {
          msg = e.response.data;
        }
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: msg,
          life: 4000,
        });
      }
      this.showAddMemberModal = false;
      this.groups = [];
      this.newMember = {
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        roles: [],
        groups: [],
      };
    },

    async saveGroup() {
      try {
        const payload = {
          name: this.newGroup.name,
          member_ids: Array.isArray(this.newGroup.members)
            ? this.newGroup.members.map((m) => m.id || m.value || m)
            : [],
        };
        const authToken = storage.get('authToken');
        const headers = {};
        if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
        await axios.post(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/groups',
          payload,
          { headers }
        );
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Group added successfully!',
          life: 3000,
        });
        this.$emit('group-added');
      } catch (e) {
        let msg = 'Failed to add group.';
        if (e.response && e.response.data && e.response.data.message) {
          msg = e.response.data.message;
        } else if (
          e.response &&
          e.response.data &&
          typeof e.response.data === 'string'
        ) {
          msg = e.response.data;
        }
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: msg,
          life: 4000,
        });
      }
      this.showAddGroupModal = false;
      this.availableMembers = [];
      this.newGroup = { name: '', members: [] };
    },
  },
};
</script>

<style scoped>
.my-org-action-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  flex-wrap: wrap;
}

/* Small screens: column */
@media (max-width: 650px) {
  .my-org-action-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: stretch;
  }
}

.my-org-primary,
.my-org-secondary {
  border-radius: 29.01px;
  font-family: 'Helvetica Neue LT Std', Helvetica, Arial, sans-serif;
  font-weight: 500;
  font-size: 15px;

  padding: 8px 24px 8px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-right: 0;
  margin-top: 0;
  box-shadow: none;
  border: none;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  white-space: nowrap;
  min-width: 0;
  max-width: none;
  overflow: visible;
  border: 1px solid #e6e6e6;
}
.my-org-primary {
  background: #0164a5;
  color: #fff;
}
.my-org-primary:hover {
  background: #005fa3;
  color: #fff;
}
.my-org-secondary {
  background: #f5f5f5;
  color: #000000;
}

.my-org-action-buttons .my-org-secondary:nth-child(2) {
  background: #0164a5 !important;
  color: #fff !important;
}
.my-org-action-buttons .my-org-secondary:nth-child(2):hover {
  background: #005fa3 !important;
  color: #fff !important;
}

/* Move all modal styles here */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.13);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}
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
.form-row {
  display: flex;
  gap: 24px;
  width: 100%;
  margin-bottom: 18px;
}
.form-box {
  flex: 1 1 0;
  min-width: 0;
  background: #f6f6f6;
  border-radius: 9px;
  padding: 6px 12px;
  display: flex;
  align-items: flex-start; /* allow vertical growth when chips wrap */
  box-sizing: border-box;
  position: relative;
  min-height: 44px; /* baseline for single-line */
  max-height: 240px;
  overflow: hidden;
}

.form-box .selected-container {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
  max-width: calc(100% - 56px);
  overflow-x: auto;
  padding: 6px 0;
}

.selected-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #e6f0fa;
  color: #0164a5;
  border-radius: 20px;
  padding: 6px 10px;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
  margin-bottom: 6px;
}

.selected-placeholder {
  color: #888;
  font-size: 15px;
  padding: 6px 8px;
  display: inline-block;
}

.chip-label {
  display: inline-block;
  max-width: 160px;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chip-remove {
  background: transparent;
  border: none;
  color: #555;
  font-size: 16px;
  line-height: 1;
  cursor: pointer;
  padding: 0 4px;
}

.form-dropdown-chevron {
  margin-left: 8px;
  color: #888;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
  align-self: flex-start; /* align with top when chips wrap */
  margin-top: 6px;
}

.form-input-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  color: #888;
  margin-right: 8px;
  align-self: flex-start;
  margin-top: 6px;
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

/* Responsive styles for modals and form elements */
@media (max-width: 700px) {
  .modal-card {
    min-width: 0;
    max-width: calc(100vw - 32px);
    width: calc(98vw - 32px);
    padding: 20px 16px 20px 16px;
    border-radius: 14px;
    margin: 26px;
  }
}
@media (max-width: 600px) {
  .modal-card {
    min-width: 0;
    max-width: calc(100vw - 32px);
    width: calc(98vw - 24px);
    padding: 18px 12px 18px 12px;
    border-radius: 12px;
    margin: 22px;
  }
  .modal-title {
    font-size: 20px;
    margin-bottom: 18px;
  }
  .modal-form {
    gap: 10px;
    padding: 0;
  }
  .form-row,
  .modal-form-row {
    flex-direction: column;
    gap: 10px;
    width: 100%;
    margin-bottom: 0;
  }
  /* Make form-box height dynamic on small screens so it grows with wrapped chips.
     Keep a sensible max-height and allow internal scrolling if there are too many chips. */
  .form-box {
    height: auto;
    min-height: 44px;
    max-height: 240px; /* prevents pushing modal too far */
    padding: 8px 10px;
    border-radius: 7px;
    font-size: 15px;
    display: flex;
    align-items: flex-start; /* allow chips to wrap and grow vertically */
    box-sizing: border-box;
    overflow: visible;
  }

  /* Selected items wrap onto multiple lines when needed */
  .form-box .selected-container {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    flex-wrap: wrap; /* allow wrapping */
    overflow-x: hidden;
    overflow-y: auto; /* scroll inside the container if too tall */
    -webkit-overflow-scrolling: touch;
    max-width: calc(100% - 52px);
    padding: 4px 0;
    white-space: normal;
    box-sizing: border-box;
  }

  .selected-chip {
    font-size: 13px;
    padding: 6px 8px;
    flex: 0 0 auto;
  }

  .selected-placeholder {
    display: inline-block;
    vertical-align: middle;
    line-height: 1.2;
  }

  /* align icons/chevron to the top so they sit beside wrapped chips */
  .form-dropdown-chevron,
  .form-input-icon {
    align-self: flex-start;
    margin-top: 6px;
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
