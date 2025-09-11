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
                    class="org-edit-cancel"
                    @click="closeMemberModal"
                    style="margin-left: 12px"
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

      rolesForSelect: [],
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
        typeof r === 'object' ? r : this.getRoleForSelect(r)
      );

      // ensure memberRoles exists (objects)
      if (!Array.isArray(m.memberRoles) || !m.memberRoles.length) {
        m.memberRoles = m.member_role_ids.slice();
      } else {
        // normalize memberRoles items
        m.memberRoles = m.memberRoles.map((r) =>
          typeof r === 'object' ? r : this.getRoleForSelect(r)
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
      // make sure we use normalized member objects so roles have {id,name}
      const normalized = this.normalizeMember(member);
      this.selectedMemberEdit = { ...normalized };
      this.selectedMemberEdit.member_role_ids = Array.isArray(
        normalized.member_role_ids
      )
        ? normalized.member_role_ids.map((r) =>
            typeof r === 'object' ? r : this.getRoleForSelect(r)
          )
        : [];
      this.selectedMemberEdit.memberRoles = Array.isArray(
        normalized.memberRoles
      )
        ? normalized.memberRoles.map((r) =>
            typeof r === 'object' ? r : this.getRoleForSelect(r)
          )
        : [];
      // ensure phone is present (backend now returns it)
      this.selectedMemberEdit.phone = member.phone || '';
      this.showMemberModal = true;
    },

    // return a comma-separated list of role names for a member
    formatMemberRoles(member) {
      try {
        if (Array.isArray(member.memberRoles) && member.memberRoles.length) {
          return member.memberRoles
            .map((r) => (r && (r.name || r)) || r)
            .join(', ');
        }
        if (
          Array.isArray(member.member_role_ids) &&
          member.member_role_ids.length
        ) {
          return member.member_role_ids
            .map((r) => (r && (r.name || r)) || r)
            .join(', ');
        }
        return member.member_role || '';
      } catch (e) {
        console.warn('formatMemberRoles failed', e);
        return '';
      }
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
        this.editMember.member_role_ids = ids;
        this.showEditModal = true;
      } catch (e) {
        console.error('Failed to fetch member for edit', e);
        this.editMember = { ...this.selectedMemberEdit };
        const ids = this.selectedMemberEdit.member_role_ids || [];
        this.editMember.member_role_ids = ids;
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
          console.error('Failed to refresh members list', listErr);
          if (putRes && putRes.data && putRes.data.id) {
            const pm = this.normalizeMember(putRes.data);
            const memberIdx = this.members.findIndex((m) => m.id === pm.id);
            if (memberIdx !== -1) this.members.splice(memberIdx, 1, pm);
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
            this.getRoleForSelect(rid)
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
            (r) => (typeof r === 'object' ? r : this.getRoleForSelect(r))
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
            (r) => (typeof r === 'object' ? r : this.getRoleForSelect(r))
          );
        }

        this.onSearch();
        this.showEditModal = false;
      } catch (e) {
        console.error('Failed to update member', e);
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
      // Build a lookup of roles used by the app from members fetched from the backend.
      // Prefer explicit memberRoles/member_role_names when available. If none found,
      // fallback to a small static list so UI remains usable.
      const rolesById = {};
      const rolesByName = {};

      // collect roles from this.members
      if (Array.isArray(this.members) && this.members.length) {
        this.members.forEach((m) => {
          // memberRoles array of objects {id,name}
          if (Array.isArray(m.memberRoles)) {
            m.memberRoles.forEach((r) => {
              if (r && (r.id || r.name)) {
                const id = r.id || String(r.name).toLowerCase();
                const name = r.name || String(r);
                rolesById[id] = { id: r.id || id, name };
                rolesByName[String(name).toLowerCase()] = {
                  id: r.id || id,
                  name,
                };
              }
            });
          }

          // member_role_names is sometimes provided as array of strings
          if (Array.isArray(m.member_role_names)) {
            m.member_role_names.forEach((name, idx) => {
              if (name) {
                const key = String(name).toLowerCase();
                if (!rolesByName[key]) {
                  const syntheticId = Object.keys(rolesById).length + 1 + idx;
                  rolesByName[key] = { id: syntheticId, name };
                }
              }
            });
          }

          // single string fallback on member_role
          if (m.member_role && !Array.isArray(m.member_role)) {
            const key = String(m.member_role).toLowerCase();
            if (!rolesByName[key]) {
              const syntheticId = Object.keys(rolesById).length + 1;
              rolesByName[key] = { id: syntheticId, name: m.member_role };
            }
          }
        });
      }

      // Combine collected roles into rolesForSelect
      const combined = [];
      Object.keys(rolesById).forEach((k) => combined.push(rolesById[k]));
      Object.keys(rolesByName).forEach((k) => {
        const r = rolesByName[k];
        if (
          !combined.find(
            (c) => String(c.name).toLowerCase() === String(r.name).toLowerCase()
          )
        ) {
          combined.push(r);
        }
      });

      // If no roles found from members, fallback to a conservative static list
      if (!combined.length) {
        combined.push({ id: 1, name: 'Owner' });
        combined.push({ id: 2, name: 'CEO' });
        combined.push({ id: 3, name: 'Manager' });
        combined.push({ id: 4, name: 'Support' });
      }

      // ensure rolesForSelect remains an array
      this.rolesForSelect = combined;
      this.rolesForSelectMap = {};
      if (Array.isArray(this.rolesForSelect)) {
        this.rolesForSelect.forEach((r) => (this.rolesForSelectMap[r.id] = r));
      }
    },

    // Helper method to get role object from role ID
    getRoleForSelect(roleId) {
      return (
        this.rolesForSelectMap[roleId] || { id: roleId, name: String(roleId) }
      );
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
            console.error('Failed to delete member', e);
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
      this.members = Array.isArray(res.data)
        ? res.data.map((m) => this.normalizeMember(m))
        : [];
      this.filteredMembers = this.members;

      // Try to fetch canonical member roles from the API. If that fails, derive from members.
      try {
        const rolesRes = await axios.get(`${API_BASE_URL}/api/member-roles`, {
          headers: { Authorization: `Bearer ${authToken}` },
        });
        if (Array.isArray(rolesRes.data) && rolesRes.data.length) {
          this.rolesForSelect = rolesRes.data.map((r) => ({
            id: r.id,
            name: r.name,
          }));
          this.rolesForSelectMap = {};
          this.rolesForSelect.forEach(
            (r) => (this.rolesForSelectMap[r.id] = r)
          );
        } else {
          this.prepareRolesMap();
        }
      } catch (roleErr) {
        console.warn(
          'Failed to fetch member roles, falling back to deriving from members',
          roleErr
        );
        // fallback: derive roles from members
        this.prepareRolesMap();
      }
    } catch (e) {
      console.error('Failed to fetch members', e);
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
          console.error('Failed to fetch member from query id', e);
        }
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
