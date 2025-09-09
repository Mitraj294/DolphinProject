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
      @click.self="showAddMemberModal = false"
    >
      <div
        class="modal-card"
        style="max-width: 900px"
      >
        <button
          class="modal-close-btn"
          @click="showAddMemberModal = false"
        >
          &times;
        </button>
        <div class="modal-title">Add New Member</div>
        <div
          class="modal-desc"
          style="font-size: 1.2rem !important; margin-bottom: 32px !important"
        >
          Add a new member to your organization.
        </div>
        <form
          class="modal-form"
          @submit.prevent="saveMember"
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
                >First Name</FormLabel
              >
              <FormInput
                v-model="newMember.firstName"
                icon="fas fa-user"
                type="text"
                placeholder="Enter first name"
                required
              />
            </div>

            <div
              class="modal-form-row-div"
              style="flex: 1; min-width: 0"
            >
              <FormLabel
                style="font-size: 1rem !important; margin: 0 0 6px 0 !important"
                >Last Name</FormLabel
              >
              <FormInput
                v-model="newMember.lastName"
                icon="fas fa-user"
                type="text"
                placeholder="Enter last name"
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
                >Email</FormLabel
              >
              <FormInput
                v-model="newMember.email"
                icon="fas fa-envelope"
                type="email"
                placeholder="Enter email address"
                required
              />
            </div>
            <div
              class="modal-form-row-div"
              style="flex: 1; min-width: 0"
            >
              <FormLabel
                style="font-size: 1rem !important; margin: 0 0 6px 0 !important"
                >Phone</FormLabel
              >
              <FormInput
                v-model="newMember.phone"
                icon="fas fa-phone"
                type="text"
                placeholder="Enter phone number"
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
                >Role</FormLabel
              >
              <MultiSelectDropdown
                :options="roles"
                :selectedItems="
                  Array.isArray(newMember.roles) ? newMember.roles : []
                "
                @update:selectedItems="newMember.roles = $event"
                placeholder="Select role"
                :enableSelectAll="true"
              />
            </div>
            <div
              class="modal-form-row-div"
              style="flex: 1; min-width: 0"
            >
              <FormLabel
                style="font-size: 1rem !important; margin: 0 0 6px 0 !important"
                >Groups</FormLabel
              >
              <MultiSelectDropdown
                :options="groups"
                :selectedItems="
                  Array.isArray(newMember.groups) ? newMember.groups : []
                "
                @update:selectedItems="newMember.groups = $event"
                placeholder="Select groups"
                :enableSelectAll="true"
              />
            </div>
          </FormRow>
          <div class="modal-form-actions">
            <button
              type="submit"
              class="btn btn-primary"
            >
              <i class="fas fa-save"></i>
              Save
            </button>
            <button
              type="button"
              class="org-edit-cancel"
              @click="showAddMemberModal = false"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
    <!-- Add New Group Modal -->
    <div
      v-if="showAddGroupModal"
      class="modal-overlay"
      @click.self="showAddGroupModal = false"
    >
      <div
        class="modal-card"
        style="max-width: 900px"
      >
        <button
          class="modal-close-btn"
          @click="showAddGroupModal = false"
        >
          &times;
        </button>
        <div class="modal-title">Add New Group</div>
        <div
          class="modal-desc"
          style="font-size: 1.2rem !important ; margin-bottom: 32px !important"
        >
          Create a new group for your organization.
        </div>
        <form
          class="modal-form"
          @submit.prevent="saveGroup"
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
                >Group Name</FormLabel
              >
              <FormInput
                v-model="newGroup.name"
                icon="fas fa-users"
                type="text"
                placeholder="Enter group name"
                required
              />
            </div>

            <div
              class="modal-form-row-div"
              style="flex: 1; min-width: 0"
            >
              <FormLabel
                style="font-size: 1rem !important; margin: 0 0 6px 0 !important"
                >Members</FormLabel
              >
              <MultiSelectDropdown
                :options="availableMembers"
                :selectedItems="
                  Array.isArray(newGroup.members) ? newGroup.members : []
                "
                @update:selectedItems="newGroup.members = $event"
                placeholder="Select members"
                :enableSelectAll="true"
              />
            </div>
          </FormRow>
          <div class="modal-form-actions">
            <button
              type="submit"
              class="btn btn-primary"
            >
              <i class="fas fa-save"></i>
              Save
            </button>
            <button
              type="button"
              class="org-edit-cancel"
              @click="showAddGroupModal = false"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
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
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
export default {
  name: 'OrgActionButtons',
  components: {
    FormInput,
    FormLabel,
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
        { id: 1, name: 'Owner' },
        { id: 2, name: 'CEO' },
        { id: 3, name: 'Manager' },
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
        console.error('Failed to fetch groups:', e);
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
        } else {
          console.error(e);
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
        } else {
          console.error('Failed to add group:', e);
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
@import '@/assets/modelcssnotificationandassesment.css';

/* Modal form customization */
.modal-form .form-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 100%;
  margin-bottom: 18px;
}

@media (max-width: 600px) {
  .modal-form-row {
    flex-direction: column !important;
    gap: 12px;
  }
  .modal-form-row-div {
    flex: 1 !important;
    width: 100% !important;
    min-width: 0;
  }
}

.modal-form .form-label {
  font-size: 14px;
  font-weight: 500;
  color: var(--text);
  text-align: left;
  margin-bottom: 6px;
}

/* Ensure form components work well in modal */
.modal-form .form-box {
  position: relative;
}

.modal-form .form-input.with-icon {
  padding-left: 40px;
}

.modal-form .form-input-icon {
  color: var(--muted);
  font-size: 16px;
}

.modal-form .form-dropdown-chevron {
  color: var(--muted);
}

.my-org-action-buttons {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  flex-wrap: wrap;
}

/* Small screens: column */
@media (max-width: 600px) {
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

.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 1rem;
  font-weight: 500;
  border-radius: 8px;
  border: none;
  padding: 10px 24px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary {
  background: #0074c2;
  color: #fff;
}

.btn-primary:hover {
  background: #005fa3;
}

.org-edit-cancel {
  background: #f0f0f0;
  color: #222;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}

.org-edit-cancel:hover {
  background: #e0e0e0;
}

.form-box {
  padding: 0 !important;
}
</style>
