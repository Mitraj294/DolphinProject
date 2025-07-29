<template>
  <div class="table-header-bar">
    <div class="my-org-action-buttons">
      <button
        class="my-org-secondary"
        @click="$router.push({ name: 'MemberListing' })"
      >
        Members Listing
      </button>
      <button class="my-org-secondary">
        <img
          src="@/assets/images/Templates.svg"
          alt="Template"
          style="
            width: 18px;
            height: 18px;
            margin-right: 6px;
            vertical-align: middle;
          "
        />
        Template
      </button>
      <button
        class="my-org-primary"
        @click="showAddGroupModal = true"
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
        @click="showAddMemberModal = true"
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
            />
            <MultiSelectDropdown
              :options="groups"
              :selectedItems="
                Array.isArray(newMember.groups) ? newMember.groups : []
              "
              @update:selectedItems="newMember.groups = $event"
              placeholder="Groups associated with"
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
export default {
  name: 'OrgActionButtons',
  components: {
    FormInput,
    MultiSelectDropdown,
    FormRow,
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
      groups: [
        { id: 1, name: 'Flexi-Finders' },
        { id: 2, name: 'Interim Solutions' },
        { id: 3, name: 'Talent on Demand' },
        { id: 4, name: 'QuickStaff' },
      ],
      availableMembers: [
        { id: 1, name: 'John Doe' },
        { id: 2, name: 'Jane Smith' },
        { id: 3, name: 'Alice Johnson' },
        { id: 4, name: 'Bob Brown' },
        { id: 5, name: 'Charlie White' },
        { id: 6, name: 'Diana Green' },
        { id: 7, name: 'Ethan Blue' },
      ],
    };
  },
  methods: {
    saveMember() {
      this.showAddMemberModal = false;
      this.newMember = {
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        roles: [],
        groups: [],
      };
    },
    saveGroup() {
      this.showAddGroupModal = false;
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

/* Medium screens: 2x2 grid */
@media (max-width: 900px) {
  .my-org-action-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 12px;
    justify-items: stretch;
    align-items: stretch;
  }
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
  border: none;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  white-space: nowrap;
  min-width: 0;
  max-width: none;
  overflow: visible;
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
  color: #888;
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
  padding: 0 16px;
  height: 48px;
  display: flex;
  align-items: center;
  box-sizing: border-box;
  position: relative;
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
  .form-row,
  .modal-form-row {
    flex-direction: column;
    gap: 10px;
    width: 100%;
    margin-bottom: 0;
  }
  .form-box {
    height: 40px;
    padding: 0 8px;
    border-radius: 7px;
    font-size: 15px;
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
