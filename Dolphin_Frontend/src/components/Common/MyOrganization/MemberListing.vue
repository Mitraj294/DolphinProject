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
                    { label: 'Name', key: 'name' },
                    { label: 'Email', key: 'email' },
                    { label: 'Phone Number', key: 'phone' },
                    { label: 'Role', key: 'role' },
                    { label: 'Actions', key: 'actions' },
                  ]"
                  @sort="sortBy"
                />
                <tbody>
                  <tr
                    v-for="(member, idx) in paginatedMembers"
                    :key="member.id"
                  >
                    <td>{{ member.first_name }} {{ member.last_name }}</td>
                    <td>{{ member.email }}</td>
                    <td>{{ member.phone }}</td>
                    <td>
                      <!-- show all related roles: prefer member.memberRoles (objects) then member.member_role_ids (ids or objects) -->
                      <span
                        v-if="
                          Array.isArray(member.memberRoles) &&
                          member.memberRoles.length
                        "
                      >
                        {{
                          member.memberRoles.map((r) => r.name || r).join(', ')
                        }}
                      </span>
                      <span
                        v-else-if="
                          Array.isArray(member.member_role_ids) &&
                          member.member_role_ids.length
                        "
                      >
                        {{
                          member.member_role_ids
                            .map((r) => (r && (r.name || r)) || r)
                            .join(', ')
                        }}
                      </span>
                      <span v-else>
                        {{ member.member_role || '' }}
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
                  <tr v-if="paginatedMembers.length === 0">
                    <td
                      colspan="5"
                      class="no-data"
                    >
                      No members found.
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
          <div class="profile-card member-profile-card">
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
                  <span
                    v-if="
                      Array.isArray(selectedMemberEdit.memberRoles) &&
                      selectedMemberEdit.memberRoles.length
                    "
                  >
                    {{
                      selectedMemberEdit.memberRoles
                        .map((r) => r.name || r)
                        .join(', ')
                    }}
                  </span>
                  <span
                    v-else-if="
                      Array.isArray(selectedMemberEdit.member_role_ids) &&
                      selectedMemberEdit.member_role_ids.length
                    "
                  >
                    {{
                      selectedMemberEdit.member_role_ids
                        .map((r) => (r && (r.name || r)) || r)
                        .join(', ')
                    }}
                  </span>
                  <span v-else>
                    {{ selectedMemberEdit.member_role || '' }}
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
                @click="deleteMember(selectedMemberEdit)"
              >
                <i class="fas fa-trash"></i>
                Delete Member
              </button>
              <button
                class="btn btn-secondary"
                @click="closeMemberModal"
                style="margin-left: 12px"
              >
                Cancel
              </button>
            </div>
          </div>

          <!-- Edit Modal (Common UI) -->
          <CommonModal
            :visible="showEditModal"
            @close="closeEditModal"
            @submit="onEditSave"
          >
            <template #title>Edit Profile</template>
            <FormRow>
              <FormLabel>First Name</FormLabel>
              <FormInput
                v-model="editMember.first_name"
                type="text"
                required
                placeholder="First Name"
              />
              <FormLabel>Last Name</FormLabel>
              <FormInput
                v-model="editMember.last_name"
                type="text"
                required
                placeholder="Last Name"
              />
            </FormRow>
            <FormRow>
              <FormLabel>Email</FormLabel>
              <FormInput
                v-model="editMember.email"
                type="email"
                required
                placeholder=""
              />
            </FormRow>
            <FormRow>
              <FormLabel>Phone</FormLabel>
              <FormInput
                v-model="editMember.phone"
                type="text"
                placeholder=""
              />
            </FormRow>
            <FormRow>
              <FormLabel>Role</FormLabel>
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
            <template #actions>
              <button
                type="submit"
                class="btn btn-primary"
              >
                <i class="fas fa-save"></i> Save
              </button>
              <button
                type="button"
                class="btn btn-secondary"
                @click="closeEditModal"
                style="margin-left: 12px"
              >
                Cancel
              </button>
            </template>
          </CommonModal>
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
import CommonModal from '@/components/Common/Common_UI/CommonModal.vue';
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
    CommonModal,
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
      showMemberModal: false,
      showEditModal: false,
      selectedMemberEdit: {
        id: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        member_role: '',
        member_role_ids: [],
        country: '',
      },
      editMember: {
        id: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        member_role: '',
        member_role_ids: [],
        country: '',
      },
      // available roles for selection (fallback static list)
      rolesForSelect: [
        { id: 1, name: 'Manager' },
        { id: 2, name: 'CEO' },
        { id: 3, name: 'Owner' },
        { id: 4, name: 'Support' },
      ],
      rolesForSelectMap: {},
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredMembers.length / this.pageSize) || 1;
    },
    paginatedMembers() {
      let sorted = [...this.filteredMembers];
      if (this.sortKey) {
        sorted.sort((a, b) => {
          let aVal = a[this.sortKey];
          let bVal = b[this.sortKey];
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
      } else {
        const pages = [1];
        if (this.currentPage > 4) pages.push('...');
        for (
          let i = Math.max(2, this.currentPage - 1);
          i <= Math.min(total - 1, this.currentPage + 1);
          i++
        ) {
          pages.push(i);
        }
        if (this.currentPage < total - 3) pages.push('...');
        pages.push(total);
        return pages;
      }
    },
  },
  methods: {
    // Normalize a member object to ensure role-related fields are consistent
    normalizeMember(member) {
      const m = { ...member };
      // ensure member_role_ids is an array
      if (!Array.isArray(m.member_role_ids)) {
        // try to read group_ids or group_ids fallback
        m.member_role_ids = Array.isArray(m.member_role_ids)
          ? m.member_role_ids
          : [];
      }
      // normalize ids to objects {id,name} when possible
      m.member_role_ids = m.member_role_ids.map((r) =>
        typeof r === 'object'
          ? r
          : this.rolesForSelectMap[r]
          ? this.rolesForSelectMap[r]
          : { id: r, name: String(r) }
      );

      // ensure memberRoles exists (objects)
      if (!Array.isArray(m.memberRoles) || !m.memberRoles.length) {
        m.memberRoles = m.member_role_ids.slice();
      } else {
        // normalize memberRoles items
        m.memberRoles = m.memberRoles.map((r) =>
          typeof r === 'object'
            ? r
            : this.rolesForSelectMap[r]
            ? this.rolesForSelectMap[r]
            : { id: r, name: String(r) }
        );
      }

      // set a readable fallback string for older UI
      m.member_role =
        (m.memberRoles[0] && (m.memberRoles[0].name || m.memberRoles[0])) ||
        m.member_role ||
        '';
      return m;
    },
    openMemberModal(member) {
      this.selectedMemberEdit = { ...member };
      this.selectedMemberEdit.member_role_ids = member.member_role_ids || [];
      this.selectedMemberEdit.memberRoles = member.memberRoles || [];
      // ensure phone is present (backend now returns it)
      this.selectedMemberEdit.phone = member.phone || member.phone || '';
      this.showMemberModal = true;
    },
    async openEditModal() {
      // Try to fetch the latest member details from API each time Edit is clicked
      const id = this.selectedMemberEdit && this.selectedMemberEdit.id;
      // ensure roles map exists
      this.prepareRolesMap();
      if (!id) {
        this.editMember = { ...this.selectedMemberEdit };
        this.showEditModal = true;
        return;
      }

      const authToken = storage.get('authToken');
      const headers = {};
      if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

      try {
        const res = await axios.get(`${API_BASE_URL}/api/members/${id}`, {
          headers,
        });
        const member = res && res.data ? res.data : this.selectedMemberEdit;
        const normalized = this.normalizeMember(member);
        // set selected and edit models from fresh data
        this.selectedMemberEdit = { ...normalized };
        const ids = normalized.member_role_ids || [];
        this.editMember = { ...normalized };
        this.editMember.member_role_ids = ids.map((r) =>
          typeof r === 'object'
            ? r
            : this.rolesForSelectMap[r]
            ? this.rolesForSelectMap[r]
            : { id: r, name: String(r) }
        );
        this.showEditModal = true;
      } catch (e) {
        // fallback: use already-loaded selectedMemberEdit
        this.editMember = { ...this.selectedMemberEdit };
        const ids = this.selectedMemberEdit.member_role_ids || [];
        this.editMember.member_role_ids = ids.map((id) =>
          this.rolesForSelectMap[id]
            ? this.rolesForSelectMap[id]
            : { id, name: String(id) }
        );
        this.showEditModal = true;
      }
    },
    closeEditModal() {
      this.showEditModal = false;
    },
    closeMemberModal() {
      this.showMemberModal = false;
      this.isEditing = false;
    },
    async onEditSave() {
      const authToken = storage.get('authToken');
      const headers = {};
      if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
      try {
        // build payload and convert selected roles to ids array
        const payload = { ...this.editMember };
        if (Array.isArray(this.editMember.member_role_ids)) {
          payload.member_role = this.editMember.member_role_ids.map(
            (r) => r.id || r
          );
        }
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

        const putRes = await axios.put(
          `${API_BASE_URL}/api/members/${this.editMember.id}`,
          payload,
          {
            headers,
          }
        );

        // Regardless of PUT response shape, refresh the members list so we have
        // canonical objects (including memberRoles) for UI. This avoids stale
        // or partial data and ensures the edit modal shows proper labels.
        try {
          const listRes = await axios.get(`${API_BASE_URL}/api/members`, {
            headers,
          });
          this.members = Array.isArray(listRes.data)
            ? listRes.data.map((m) => this.normalizeMember(m))
            : [];
        } catch (listErr) {
          // If refreshing the list fails, still try to use PUT response if it
          // contains an object, otherwise fall back to the local editMember.
          if (putRes && putRes.data && putRes.data.id) {
            const pm = this.normalizeMember(putRes.data);
            const idx = this.members.findIndex((m) => m.id === pm.id);
            if (idx !== -1) this.members.splice(idx, 1, pm);
            else this.members.push(pm);
          }
        }

        // pick the updated member from refreshed list (or fallback)
        const updatedMember =
          this.members.find((m) => m.id === this.editMember.id) ||
          this.normalizeMember({ ...this.editMember });

        // If server didn't return role details, but we sent role ids in payload,
        // populate member_role_ids/memberRoles from the payload so the UI updates
        if (
          (!updatedMember.memberRoles || !updatedMember.memberRoles.length) &&
          Array.isArray(payload.member_role) &&
          payload.member_role.length
        ) {
          updatedMember.member_role_ids = payload.member_role.map((rid) =>
            this.rolesForSelectMap[rid]
              ? this.rolesForSelectMap[rid]
              : { id: rid, name: String(rid) }
          );
          updatedMember.memberRoles = updatedMember.member_role_ids.slice();
          updatedMember.member_role =
            (updatedMember.memberRoles[0] &&
              (updatedMember.memberRoles[0].name ||
                updatedMember.memberRoles[0])) ||
            updatedMember.member_role ||
            '';
        }

        // normalize returned shape: ensure member_role_ids is array of objects when possible
        if (Array.isArray(updatedMember.member_role_ids)) {
          updatedMember.member_role_ids = updatedMember.member_role_ids.map(
            (r) =>
              typeof r === 'object'
                ? r
                : this.rolesForSelectMap[r]
                ? this.rolesForSelectMap[r]
                : { id: r, name: String(r) }
          );
        } else {
          updatedMember.member_role_ids = [];
        }

        // Ensure memberRoles (objects) exist for UI and set member_role (string fallback)
        updatedMember.memberRoles = Array.isArray(updatedMember.memberRoles)
          ? updatedMember.memberRoles
          : updatedMember.member_role_ids.slice();
        // set a primary string role for older UI usage
        updatedMember.member_role =
          (updatedMember.memberRoles[0] &&
            (updatedMember.memberRoles[0].name ||
              updatedMember.memberRoles[0])) ||
          updatedMember.member_role ||
          '';

        // update members list
        const idx = this.members.findIndex((m) => m.id === updatedMember.id);
        if (idx !== -1) {
          this.members.splice(idx, 1, updatedMember);
        } else {
          // if not present, push and refresh list search
          this.members.push(updatedMember);
        }

        // update selected and edit models used by UI
        this.selectedMemberEdit = { ...updatedMember };
        this.editMember = { ...updatedMember };

        // ensure editMember.member_role_ids is objects for Multiselect
        if (Array.isArray(this.editMember.member_role_ids)) {
          this.editMember.member_role_ids = this.editMember.member_role_ids.map(
            (r) =>
              typeof r === 'object'
                ? r
                : this.rolesForSelectMap[r]
                ? this.rolesForSelectMap[r]
                : { id: r, name: String(r) }
          );
        }

        this.onSearch();
        this.showEditModal = false;
      } catch (e) {
        if (this && this.$toast && typeof this.$toast.add === 'function') {
          this.$toast.add({
            severity: 'error',
            summary: 'Update failed',
            detail: 'Failed to update member.',
            sticky: true,
          });
        }
      }
    },

    // helpers for role select
    prepareRolesMap() {
      // build a lookup of roles used by the app (could be fetched; for now infer from current members or default list)
      this.rolesForSelect = [
        { id: 1, name: 'Manager' },
        { id: 2, name: 'CEO' },
        { id: 3, name: 'Owner' },
        { id: 4, name: 'Support' },
      ];
      this.rolesForSelectMap = {};
      this.rolesForSelect.forEach((r) => (this.rolesForSelectMap[r.id] = r));
    },

    onEditRolesUpdate(selected) {
      // selected items may be objects {id,name} or ids
      this.editMember.member_role_ids = selected.map((s) =>
        typeof s === 'object' ? s : { id: s, name: String(s) }
      );
    },
    async deleteMember(member) {
      this.isEditing = false;
      const memberDisplay =
        member && (member.first_name || member.last_name)
          ? `${member.first_name || ''} ${member.last_name || ''}`.trim()
          : member.email || 'this member';

      this.confirm.require({
        message: `Are you sure you want to delete ${memberDisplay}?`,
        header: 'Confirm Delete',
        icon: 'pi pi-trash',
        acceptLabel: 'Yes',
        rejectLabel: 'No',
        accept: async () => {
          const authToken = storage.get('authToken');
          const headers = {};
          if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
          try {
            await axios.delete(
              (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
                `/api/members/${member.id}`,
              { headers }
            );
            this.members = this.members.filter((m) => m.id !== member.id);
            this.onSearch();
            this.showMemberModal = false;
          } catch (e) {
            if (this && this.$toast && typeof this.$toast.add === 'function') {
              this.$toast.add({
                severity: 'error',
                summary: 'Delete failed',
                detail: 'Failed to delete member.',
                sticky: true,
              });
            }
          }
        },
        reject: () => {
          // no-op
        },
      });
    },
    onSearch() {
      const q = this.searchQuery.trim().toLowerCase();
      if (!q) {
        this.filteredMembers = this.members;
      } else {
        this.filteredMembers = this.members.filter(
          (m) =>
            `${m.first_name} ${m.last_name}`.toLowerCase().includes(q) ||
            m.email.toLowerCase().includes(q) ||
            (m.phone || '')
              .replace(/\s+/g, '')
              .includes(q.replace(/\s+/g, '')) ||
            (m.member_role || '').toLowerCase().includes(q)
        );
      }
      this.currentPage = 1;
    },
    goToPage(page) {
      if (page === '...' || page < 1 || page > this.totalPages) return;
      this.currentPage = page;
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
  },
  async mounted() {
    // Fetch members from backend
    try {
      const authToken = storage.get('authToken');
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      const res = await axios.get(`${API_BASE_URL}/api/members`, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      this.members = Array.isArray(res.data) ? res.data : [];
      this.filteredMembers = this.members;
      // prepare role lookup map
      this.prepareRolesMap();
    } catch (e) {
      this.members = [];
      this.filteredMembers = [];
    }
    // If route includes member_id query, open that member's modal
    const memberIdFromQuery =
      this.$route && this.$route.query && this.$route.query.member_id;
    if (memberIdFromQuery) {
      const id = Number(memberIdFromQuery);
      const found = this.members.find((m) => m.id === id);
      if (found) {
        this.openMemberModal(found);
      } else {
        // try fetch single member and open
        try {
          const authToken = storage.get('authToken');
          const headers = {};
          if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
          const API_BASE_URL =
            process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
          const res = await axios.get(`${API_BASE_URL}/api/members/${id}`, {
            headers,
          });
          const member = res && res.data ? res.data : null;
          if (member) {
            const normalized = this.normalizeMember(member);
            this.selectedMemberEdit = normalized;
            this.showMemberModal = true;
          }
        } catch (e) {
          // ignore
        }
      }
    }
  },
};
</script>

<style>
@import '@/assets/global.css';
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
@media (max-width: 1400px) {
  .org-search {
    font-size: 13px;
    padding: 8px 16px 8px 32px;
    max-width: 320px;
    border-radius: 12px;
  }
}
@media (max-width: 900px) {
  .org-search {
    font-size: 11px;
    padding: 6px 10px 6px 28px;
    max-width: 180px;
    border-radius: 8px;
  }
}
/* Modal Card Styles */
.modal-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 32px rgba(0, 0, 0, 0.1);
  padding: 32px 32px 24px 32px;
  min-width: 340px;
  max-width: 420px;
  margin: 0 auto;
  position: relative;
}
.modal-close {
  position: absolute;
  top: 18px;
  right: 18px;
  background: none;
  border: none;
  font-size: 2rem;
  color: #888;
  cursor: pointer;
  z-index: 1;
}
.modal-title {
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 18px;
  text-align: center;
}
.modal-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.form-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form-label {
  font-size: 1rem;
  font-weight: 500;
  margin-bottom: 2px;
  color: #222;
}
.form-box {
  display: flex;
  align-items: center;
  background: #f6f6f6;
  border-radius: 8px;
  padding: 0 10px;
  flex: 1;
}
.form-input-icon {
  color: #bdbdbd;
  margin-right: 8px;
  font-size: 1.2rem;
}
.form-input.with-icon {
  border: none;
  background: transparent;
  padding: 10px 0;
  width: 100%;
  font-size: 1rem;
  outline: none;
}
.modal-form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 8px;
}
.modal-save-btn {
  background: #0074c2;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 24px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.modal-save-btn:hover {
  background: #005fa3;
}
.modal-delete-btn {
  background: #e74c3c;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 24px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.modal-delete-btn:hover {
  background: #c0392b;
}
.form-row {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 18px;
  margin-bottom: 8px;
}
.form-label {
  width: 120px;
  min-width: 100px;
  font-size: 1rem;
  font-weight: 500;
  color: #222;
  margin-bottom: 0;
  text-align: left;
}
.form-box {
  flex: 1;
  display: flex;
  align-items: center;
  background: #f6f6f6;
  border-radius: 8px;
  padding: 0 10px;
}
.form-box.view-mode {
  background: #f6f6f6;
  min-height: 40px;
}
.form-value {
  font-size: 1rem;
  color: #444;
  padding: 10px 0;
  width: 100%;
}
.form-box.edit-mode {
  background: #f6f6f6;
}
.form-input.with-icon {
  border: none;
  background: transparent;
  padding: 10px 0;
  width: 100%;
  font-size: 1rem;
  outline: none;
}
.form-input-icon {
  color: #bdbdbd;
  margin-right: 8px;
  font-size: 1.2rem;
}
.modal-form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 8px;
}
.modal-save-btn {
  background: #0074c2;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 24px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.modal-save-btn:hover {
  background: #005fa3;
}
.modal-delete-btn {
  background: #e74c3c;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 24px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.modal-delete-btn:hover {
  background: #c0392b;
}
/* Profile card styles from Profile.vue, scoped for member-profile-card */
/* Increased width and reduced side margins for member profile card */
.member-profile-card {
  background: #fff;
  border-radius: 16px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.06);

  margin-bottom: 0;
  display: flex;
  flex-direction: column;
  gap: 0;
  max-width: 650px;
  width: 100%;
  margin: 32px auto 0 auto;
  /* Reduce excessive side margin by using padding on parent if needed */
}
.member-profile-card .profile-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 28px 32px 0 32px;
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
@media (max-width: 900px) {
  .member-profile-card {
    width: 100vw-;
    margin: 12px auto 0 auto;
    /* Remove side padding to avoid extra margin */
  }
  .member-profile-card .profile-header,
  .member-profile-card .profile-info-table,
  .member-profile-card .profile-actions {
    padding: 8px;
  }
}

/* Custom: Increase label width and font size in edit profile modal only */
.common-modal-card .form-row {
  display: grid !important;
  grid-template-columns: 120px 1fr !important;
  align-items: center;
  gap: 18px;
  width: 100%;
  margin-bottom: 18px;
}
.common-modal-card .form-label {
  width: 120px !important;
  min-width: 120px !important;
  max-width: 180px;
  text-align: left;
  white-space: normal;
  margin-bottom: 0 !important;
  font-size: 1.08rem !important;
  font-weight: 500 !important;
  color: #1a1a1a !important;
}
</style>
