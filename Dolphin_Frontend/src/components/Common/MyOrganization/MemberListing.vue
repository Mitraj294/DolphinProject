<template>
  <ConfirmDialog />
  <Toast />
  <MainLayout>
    <div class="page">
      <div class="table-outer">
        <template v-if="!showMemberModal">
          <div class="table-card">
            <div class="table-search-bar">
              <input
                class="org-search"
                placeholder="Search Member..."
                v-model="searchQuery"
                @input="onSearch"
              />
            </div>
            <div class="table-container">
              <table class="table">
                <TableHeader
                  :columns="[
                    { label: 'Name', key: 'name', minWidth: '200px' },
                    {
                      label: 'Email',
                      key: 'email',
                      minWidth: '200px',
                    },
                    {
                      label: 'Phone Number',
                      key: 'phone',
                      minWidth: '150px',
                    },
                    { label: 'Role', key: 'role', minWidth: '150px' },
                    {
                      label: 'Actions',
                      key: 'actions',
                      minWidth: '100px',
                    },
                  ]"
                  @sort="sortBy"
                />
                <tbody>
                  <tr v-if="loading">
                    <td
                      colspan="5"
                      class="no-data"
                    >
                      Loading members...
                    </td>
                  </tr>
                  <tr v-else-if="paginatedMembers.length === 0">
                    <td
                      colspan="5"
                      class="no-data"
                    >
                      No members found.
                    </td>
                  </tr>
                  <tr
                    v-else
                    v-for="member in paginatedMembers"
                    :key="member.id"
                  >
                    <td>{{ member.first_name }} {{ member.last_name }}</td>
                    <td>{{ member.email }}</td>
                    <td>{{ member.phone }}</td>
                    <td>
                      <span>
                        {{ formatMemberRoles(member) }}
                      </span>
                    </td>
                    <td>
                      <button
                        class="btn-view"
                        @click="openMemberModal(member)"
                      >
                        <img
                          src="@/assets/images/Notes.svg"
                          alt="View"
                          class="btn-view-icon"
                        />
                        View
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <Pagination
            :pageSize="pageSize"
            :pageSizes="[10, 25, 100]"
            :showPageDropdown="showPageDropdown"
            :currentPage="currentPage"
            :totalPages="totalPages"
            :paginationPages="paginationPages"
            @goToPage="goToPage"
            @selectPageSize="selectPageSize"
            @togglePageDropdown="showPageDropdown = !showPageDropdown"
          />
        </template>
        <template v-else>
          <div class="profile-outer">
            <div class="profile-card">
              <div class="member-profile-card">
                <div class="profile-header">
                  <div class="profile-title">
                    <i class="fas fa-user-circle profile-avatar"></i>
                    <span>Member</span>
                  </div>
                  <div>
                    <button
                      class="btn btn-primary"
                      @click="openEditModal"
                    >
                      <i class="fas fa-pen-to-square"></i>
                      Edit
                    </button>
                  </div>
                </div>
                <div class="profile-info-table">
                  <div class="profile-info-row">
                    <div class="profile-label">Name</div>
                    <div class="profile-value">
                      {{ selectedMemberEdit.first_name }}
                      {{ selectedMemberEdit.last_name }}
                    </div>
                  </div>
                  <div class="profile-info-row">
                    <div class="profile-label">Email</div>
                    <div class="profile-value">
                      {{ selectedMemberEdit.email }}
                    </div>
                  </div>
                  <div class="profile-info-row">
                    <div class="profile-label">Role</div>
                    <div class="profile-value">
                      <span>
                        {{ formatMemberRoles(selectedMemberEdit) }}
                      </span>
                    </div>
                  </div>
                  <div class="profile-info-row">
                    <div class="profile-label">Phone</div>
                    <div class="profile-value">
                      {{ selectedMemberEdit.phone }}
                    </div>
                  </div>
                </div>
                <div class="profile-actions">
                  <button
                    class="btn btn-danger"
                    style="margin: 0 12px !important"
                    @click="deleteMember(selectedMemberEdit)"
                  >
                    <i class="fas fa-trash"></i>
                    Delete Member
                  </button>
                  <button
                    class="org-edit-cancel"
                    @click="closeMemberModal"
                    style="margin: 0 12px"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            </div>
          </div>
          <!-- Edit Profile Modal -->
          <div
            v-if="showEditModal"
            class="modal-overlay"
            @click.self="showEditModal = false"
          >
            <div
              class="modal-card"
              style="max-width: 550px"
            >
              <button
                class="modal-close-btn"
                @click="showEditModal = false"
              >
                &times;
              </button>
              <div class="modal-title">Edit Member Profile</div>
              <div
                class="modal-desc"
                style="font-size: 1.5rem !important"
              >
                Update member information.
              </div>
              <form
                class="modal-form"
                @submit.prevent="onEditSave"
              >
                <FormRow style="margin-bottom: 0 !important">
                  <FormLabel
                    style="font-size: 1rem !important; margin: 0 !important"
                    >First Name</FormLabel
                  >
                  <FormInput
                    v-model="editMember.first_name"
                    icon="fas fa-user"
                    type="text"
                    placeholder="Enter first name"
                    required
                  />
                </FormRow>
                <FormRow style="margin-bottom: 0 !important">
                  <FormLabel
                    style="font-size: 1rem !important; margin: 0 !important"
                    >Last Name</FormLabel
                  >
                  <FormInput
                    v-model="editMember.last_name"
                    icon="fas fa-user"
                    type="text"
                    placeholder="Enter last name"
                    required
                  />
                </FormRow>
                <FormRow style="margin-bottom: 0 !important">
                  <FormLabel
                    style="font-size: 1rem !important; margin: 0 !important"
                    >Email</FormLabel
                  >
                  <FormInput
                    v-model="editMember.email"
                    icon="fas fa-envelope"
                    type="email"
                    placeholder="Enter email address"
                    required
                  />
                </FormRow>
                <FormRow style="margin-bottom: 0 !important">
                  <FormLabel
                    style="font-size: 1rem !important; margin: 0 !important"
                    >Phone</FormLabel
                  >
                  <FormInput
                    v-model="editMember.phone"
                    icon="fas fa-phone"
                    type="text"
                    placeholder="Enter phone number"
                  />
                </FormRow>
                <FormRow style="margin-bottom: 0 !important">
                  <FormLabel
                    style="font-size: 1rem !important; margin: 0 !important"
                    >Role</FormLabel
                  >
                  <MultiSelectDropdown
                    :options="rolesForSelect"
                    :selectedItems="
                      Array.isArray(editMember.member_role_ids)
                        ? editMember.member_role_ids
                        : []
                    "
                    @update:selectedItems="onEditRolesUpdate"
                    placeholder="Select role"
                    :enableSelectAll="true"
                  />
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
                    @click="showEditModal = false"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>
        </template>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import Pagination from '@/components/layout/Pagination.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
import axios from 'axios';
import storage from '@/services/storage';
import FormRow from '@/components/Common/Common_UI/Form/FormRow.vue';
import FormLabel from '@/components/Common/Common_UI/Form/FormLabel.vue';
import FormInput from '@/components/Common/Common_UI/Form/FormInput.vue';
import MultiSelectDropdown from '@/components/Common/Common_UI/Form/MultiSelectDropdown.vue';
import Toast from 'primevue/toast';
import { useConfirm } from 'primevue/useconfirm';

export default {
  name: 'MemberListing',
  components: {
    MainLayout,
    Pagination,
    TableHeader,
    FormRow,
    FormLabel,
    FormInput,
    MultiSelectDropdown,
    Toast,
  },
  setup() {
    const confirm = useConfirm();
    return { confirm };
  },
  data() {
    return {
      currentPage: 1,
      pageSize: 10,
      searchQuery: '',
      showPageDropdown: false,
      sortKey: '',
      sortAsc: true,
      members: [],
      filteredMembers: [],
      loading: true,
      showMemberModal: false,
      showEditModal: false,
      selectedMemberEdit: {},
      editMember: {},
      rolesForSelect: [],
      rolesForSelectMap: {},
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredMembers.length / this.pageSize) || 1;
    },
    paginatedMembers() {
      const sorted = [...this.filteredMembers];
      if (this.sortKey) {
        sorted.sort((a, b) => {
          const aVal = a[this.sortKey] || '';
          const bVal = b[this.sortKey] || '';
          if (aVal < bVal) return this.sortAsc ? -1 : 1;
          if (aVal > bVal) return this.sortAsc ? 1 : -1;
          return 0;
        });
      }
      const start = (this.currentPage - 1) * this.pageSize;
      return sorted.slice(start, start + this.pageSize);
    },
    paginationPages() {
      const total = this.totalPages;
      if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
      }
      const pages = [1];
      if (this.currentPage > 4) pages.push('...');
      const start = Math.max(2, this.currentPage - 1);
      const end = Math.min(total - 1, this.currentPage + 1);
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      if (this.currentPage < total - 3) pages.push('...');
      pages.push(total);
      return pages;
    },
  },
  methods: {
    normalizeMember(member) {
      if (!member) return {};
      const normalized = { ...member };
      normalized.memberRoles = Array.isArray(normalized.memberRoles)
        ? normalized.memberRoles
        : [];
      normalized.member_role_ids = Array.isArray(normalized.member_role_ids)
        ? normalized.member_role_ids
        : [];
      return normalized;
    },

    openMemberModal(member) {
      this.selectedMemberEdit = this.normalizeMember(member);
      this.showMemberModal = true;
    },

    formatMemberRoles(member) {
      if (
        member &&
        Array.isArray(member.memberRoles) &&
        member.memberRoles.length > 0
      ) {
        return member.memberRoles.map((r) => r.name).join(', ');
      }
      return member.member_role || 'No Role';
    },

    async openEditModal() {
      const memberId = this.selectedMemberEdit?.id;
      if (!memberId) {
        this.$toast.add({
          severity: 'warn',
          summary: 'Warning',
          detail: 'No member selected.',
          life: 3000,
        });
        return;
      }

      try {
        const authToken = storage.get('authToken');
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const response = await axios.get(
          `${API_BASE_URL}/api/members/${memberId}`,
          {
            headers: { Authorization: `Bearer ${authToken}` },
          }
        );

        const memberData = response.data.data;
        this.editMember = this.normalizeMember(memberData);

        this.showEditModal = true;
      } catch (error) {
        console.error('Failed to fetch member details for editing:', error);
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Could not load member details.',
          life: 3000,
        });
        // Fallback to existing data if fetch fails
        this.editMember = { ...this.selectedMemberEdit };
        this.showEditModal = true;
      }
    },

    closeMemberModal() {
      this.showMemberModal = false;
    },

    async onEditSave() {
      try {
        const authToken = storage.get('authToken');
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

        const payload = { ...this.editMember };
        payload.member_role = this.editMember.member_role_ids.map((r) => r.id);
        delete payload.memberRoles;
        delete payload.member_role_ids;

        const response = await axios.put(
          `${API_BASE_URL}/api/members/${this.editMember.id}`,
          payload,
          {
            headers: { Authorization: `Bearer ${authToken}` },
          }
        );

        const updatedMember = this.normalizeMember(
          response.data.data || response.data
        );

        const index = this.members.findIndex((m) => m.id === updatedMember.id);
        if (index !== -1) {
          this.members.splice(index, 1, updatedMember);
        }

        this.selectedMemberEdit = { ...updatedMember };
        this.onSearch();
        this.showEditModal = false;

        this.$toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Member updated successfully.',
          life: 3000,
        });
      } catch (error) {
        console.error('Failed to update member:', error);
        const errorDetail =
          error.response?.data?.message || 'An unexpected error occurred.';
        this.$toast.add({
          severity: 'error',
          summary: 'Update Failed',
          detail: errorDetail,
          sticky: true,
        });
      }
    },

    getRoleForSelect(roleId) {
      return (
        this.rolesForSelectMap[roleId] || { id: roleId, name: `Role ${roleId}` }
      );
    },

    onEditRolesUpdate(selectedItems) {
      this.editMember.member_role_ids = selectedItems;
    },

    async deleteMember(member) {
      const memberDisplay =
        `${member.first_name} ${member.last_name}`.trim() || member.email;
      this.confirm.require({
        message: `Are you sure you want to delete ${memberDisplay}?`,
        header: 'Confirm Delete',
        icon: 'pi pi-trash',
        accept: async () => {
          try {
            const authToken = storage.get('authToken');
            const API_BASE_URL =
              process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
            await axios.delete(`${API_BASE_URL}/api/members/${member.id}`, {
              headers: { Authorization: `Bearer ${authToken}` },
            });

            this.members = this.members.filter((m) => m.id !== member.id);
            this.onSearch();
            this.showMemberModal = false;
            this.$toast.add({
              severity: 'info',
              summary: 'Deleted',
              detail: 'Member has been deleted.',
              life: 3000,
            });
          } catch (e) {
            console.error('Failed to delete member', e);
            this.$toast.add({
              severity: 'error',
              summary: 'Delete Failed',
              detail: 'Failed to delete member.',
              sticky: true,
            });
          }
        },
      });
    },

    onSearch() {
      const query = this.searchQuery.trim().toLowerCase();
      if (!query) {
        this.filteredMembers = [...this.members];
      } else {
        this.filteredMembers = this.members.filter((m) =>
          Object.values(m).some((val) =>
            String(val).toLowerCase().includes(query)
          )
        );
      }
      this.currentPage = 1;
    },

    goToPage(page) {
      if (typeof page === 'number' && page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },

    selectPageSize(size) {
      this.pageSize = size;
      this.currentPage = 1;
      this.showPageDropdown = false;
    },

    sortBy(key) {
      if (this.sortKey === key) {
        this.sortAsc = !this.sortAsc;
      } else {
        this.sortKey = key;
        this.sortAsc = true;
      }
    },

    async fetchInitialData() {
      this.loading = true;
      try {
        const authToken = storage.get('authToken');
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

        const [membersRes, rolesRes] = await Promise.all([
          axios.get(`${API_BASE_URL}/api/members`, {
            headers: { Authorization: `Bearer ${authToken}` },
          }),
          axios.get(`${API_BASE_URL}/api/member-roles`, {
            headers: { Authorization: `Bearer ${authToken}` },
          }),
        ]);

        if (Array.isArray(rolesRes.data) && rolesRes.data.length) {
          this.rolesForSelect = rolesRes.data.map((r) => ({
            id: r.id,
            name: r.name,
          }));
          this.rolesForSelectMap = this.rolesForSelect.reduce((map, role) => {
            map[role.id] = role;
            return map;
          }, {});
        }

        const membersData = membersRes.data?.data || [];
        this.members = membersData.map((m) => this.normalizeMember(m));
        this.filteredMembers = [...this.members];
      } catch (error) {
        console.error('Failed to fetch initial data:', error);
        this.$toast.add({
          severity: 'error',
          summary: 'Failed to load data',
          detail: 'Could not fetch members and roles from the server.',
          life: 5000,
        });
      } finally {
        this.loading = false;
      }
    },
  },
  async mounted() {
    await this.fetchInitialData();
    const memberIdFromQuery = this.$route.query.member_id;
    if (memberIdFromQuery) {
      const member = this.members.find((m) => m.id === memberIdFromQuery);
      if (member) {
        this.openMemberModal(member);
      } else {
        console.warn(
          `Member with ID ${memberIdFromQuery} not found in the initial list.`
        );
      }
    }
  },
};
</script>

<style>
@import '@/assets/global.css';
@import '@/assets/modelcssnotificationandassesment.css';

/* Modal form customization for member edit */
.modal-form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 8px !important;
}

.modal-form .form-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
  width: 100%;
  margin-bottom: 18px;
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
.org-search {
  width: 260px;
  padding: 8px 24px 8px 32px;
  border-radius: 12px;
  border: none;
  background: #f8f8f8;
  font-size: 14px;
  outline: none;
  background-image: url('@/assets/images/Search.svg');
  background-repeat: no-repeat;
  background-position: 8px center;
  background-size: 16px 16px;
  margin-left: 0;
  margin-right: auto;
}
.org-search::placeholder {
  margin-left: 4px;
}

.member-profile-card .profile-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 28px 32px 0 32px;
}
@media (max-width: 600px) {
  .member-profile-card .profile-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 28px 32px 0 32px;
    flex-direction: column;
  }
}
.member-profile-card .profile-title {
  display: flex;
  align-items: center;
  gap: 14px;
  font-size: 1.5rem;
  font-weight: 600;
  color: #0074c2;
}
.member-profile-card .profile-avatar {
  font-size: 2.2rem;
  color: #0074c2;
}
.member-profile-card .profile-info-table {
  padding: 18px 32px 0 32px;
  display: flex;
  flex-direction: column;
  gap: 0;
}
.member-profile-card .profile-info-row {
  display: flex;
  border-bottom: 1px solid #f0f0f0;
  padding: 14px 0;
  align-items: center;
}
.member-profile-card .profile-label {
  width: 160px;
  color: #888;
  font-weight: 400;
  font-size: 1rem;
}
.member-profile-card .profile-value {
  color: #222;
  font-weight: 500;
  font-size: 1rem;
  word-break: break-word;
}
.member-profile-card .profile-edit-input {
  background: #fff;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 15px;
  color: #222;
  outline: none;
  transition: border 0.2s;
  margin-bottom: 0;
  margin-right: 0;
}
.member-profile-card .profile-actions {
  display: flex;
  justify-content: flex-end;
  padding: 18px 32px 32px 32px;
}
@media (max-width: 600px) {
  .member-profile-card .profile-actions {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
}
.member-profile-card .btn {
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
.member-profile-card .btn-primary {
  background: #0074c2;
  color: #fff;
}
.member-profile-card .btn-primary:hover {
  background: #005fa3;
}
.member-profile-card .btn-danger {
  background: #e74c3c;
  color: #fff;
}
.member-profile-card .btn-danger:hover {
  background: #c0392b;
}
.member-profile-card .btn-secondary {
  background: #f0f0f0;
  color: #222;
}
.member-profile-card .btn-secondary:hover {
  background: #e0e0e0;
}

.profile-outer {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  gap: 40px;
}

.profile-card {
  width: 100%;
  background: #fff;
  border-radius: 16px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.06);
  padding: 0 0 24px 0;
  margin-bottom: 0;
  display: flex;
  flex-direction: column;
  gap: 0;
}
.form-box {
  padding: 0 !important;
}
</style>
