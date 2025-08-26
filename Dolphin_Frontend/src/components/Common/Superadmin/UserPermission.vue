<template>
  <Toast />
  <MainLayout>
    <div class="page">
      <div class="table-outer">
        <div class="table-card">
          <div class="table-header-bar">
            <button
              class="btn btn-primary"
              @click="$router.push('/user-permission/add')"
            >
              <img
                src="@/assets/images/Add.svg"
                alt="Add"
                class="user-permission-add-btn-icon"
              />
              Add New
            </button>
          </div>
          <div class="table-container">
            <table class="table">
              <TableHeader
                :columns="[
                  { label: 'Name', key: 'name' },
                  { label: 'Email', key: 'email' },
                  { label: 'Roles', key: 'role' },
                  { label: 'Actions', key: 'actions' },
                ]"
                @sort="sortBy"
              />
              <tbody>
                <tr
                  v-for="user in paginatedUsers"
                  :key="user.id"
                >
                  <td>
                    <span v-if="user.first_name || user.last_name">
                      {{ user.first_name || ''
                      }}{{ user.last_name ? ' ' + user.last_name : '' }}
                    </span>
                    <span v-else>
                      {{ user.name }}
                    </span>
                  </td>
                  <td>{{ user.email }}</td>
                  <td>
                    {{ formatRoleLabel(user.role) }}
                  </td>
                  <td class="actions actions-scroll">
                    <div class="actions-row">
                      <button
                        class="icon-btn"
                        title="Edit"
                        @click="openEditModal(user)"
                      >
                        <img
                          src="@/assets/images/EditBlack.svg"
                          alt="Edit"
                        />
                      </button>
                      <button
                        class="icon-btn"
                        title="Delete"
                        @click="deleteUser(user)"
                      >
                        <img
                          src="@/assets/images/Delete icon.svg"
                          alt="Delete"
                        />
                      </button>
                      <button
                        v-if="canImpersonate(user)"
                        class="btn-view impersonate-btn"
                        @click="impersonateUser(user)"
                      >
                        <img
                          src="@/assets/images/Impersonate.svg"
                          alt="Impersonate"
                          class="btn-view-icon impersonate-icon"
                        />
                        Impersonate
                      </button>
                      <button
                        v-else
                        class="btn-view"
                        disabled
                      >
                        <img
                          src="@/assets/images/Impersonate.svg"
                          alt="Impersonate"
                          class="btn-view-icon"
                        />
                        Impersonate
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <Pagination
          :pageSize="pageSize"
          :pageSizes="pageSizes"
          :showPageDropdown="showPageDropdown"
          :currentPage="currentPage"
          :totalPages="totalPages"
          @togglePageDropdown="showPageDropdown = !showPageDropdown"
          @selectPageSize="selectPageSize"
          @goToPage="goToPage"
        />
      </div>
    </div>
  </MainLayout>
  <!-- Edit User Modal -->
  <CommonModal
    :visible="showEditModal"
    @close="showEditModal = false"
    @submit="saveEditUser"
  >
    <template #title>Edit User</template>
    <FormRow>
      <FormLabel>First Name</FormLabel>
      <FormInput
        v-model="editUser.first_name"
        type="text"
        required
      />
    </FormRow>
    <FormRow>
      <FormLabel>Last Name</FormLabel>
      <FormInput
        v-model="editUser.last_name"
        type="text"
        required
      />
    </FormRow>
    <FormRow>
      <FormLabel>Email</FormLabel>
      <FormInput
        v-model="editUser.email"
        type="email"
        required
      />
    </FormRow>
    <FormRow>
      <FormLabel>Role</FormLabel>
      <FormDropdown
        v-model="editUser.role"
        :options="roleOptions"
        placeholder="Select role"
        :padding-left="16"
      />
    </FormRow>

    <template #actions>
      <button
        type="submit"
        class="btn btn-primary"
      >
        <i class="fas fa-save"></i> Save
      </button>
    </template>
  </CommonModal>
</template>

<script>
import MainLayout from '../../layout/MainLayout.vue';
import Pagination from '../../layout/Pagination.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
import CommonModal from '@/components/Common/Common_UI/CommonModal.vue';
import FormRow from '@/components/Common/Common_UI/Form/FormRow.vue';
import FormLabel from '@/components/Common/Common_UI/Form/FormLabel.vue';
import FormInput from '@/components/Common/Common_UI/Form/FormInput.vue';
import FormDropdown from '@/components/Common/Common_UI/Form/FormDropdown.vue';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { formatRole } from '@/utils/roles';
export default {
  name: 'UserPermission',
  components: {
    MainLayout,
    Pagination,
    TableHeader,
    CommonModal,
    FormRow,
    FormLabel,
    FormInput,
    FormDropdown,
    Toast,
  },
  data() {
    return {
      users: [],
      loading: false,
      currentPage: 1,
      pageSize: 10,
      pageSizes: [10, 25, 100],
      showPageDropdown: false,
      sortKey: '',
      sortAsc: true,
      showEditModal: false,
      editUser: {
        id: null,
        first_name: '',
        last_name: '',
        email: '',
        role: '',
      },
      // Centralized role options for consistent UI
      roleOptions: [
        { value: 'organizationadmin', text: 'Organization Admin' },
        { value: 'dolphinadmin', text: 'Dolphin Admin' },
        { value: 'salesperson', text: 'Sales Person' },
        { value: 'user', text: 'User' },
      ],
    };
  },
  setup() {
    const toast = useToast();
    return { toast };
  },

  created() {
    this.fetchUsers();
  },
  computed: {
    totalPages() {
      return (
        Math.ceil(
          this.users.filter((u) => u.role !== 'superadmin').length /
            this.pageSize
        ) || 1
      );
    },
    paginatedUsers() {
      // Filter out superadmin users
      let users = this.users.filter((u) => u.role !== 'superadmin');
      if (this.sortKey) {
        users.sort((a, b) => {
          const aVal = a[this.sortKey] || '';
          const bVal = b[this.sortKey] || '';
          if (aVal < bVal) return this.sortAsc ? -1 : 1;
          if (aVal > bVal) return this.sortAsc ? 1 : -1;
          return 0;
        });
      }
      const start = (this.currentPage - 1) * this.pageSize;
      return users.slice(start, start + this.pageSize);
    },
    isSuperAdmin() {
      const storage = require('@/services/storage').default;
      return storage.get('role') === 'superadmin';
    },
    isImpersonating() {
      const storage = require('@/services/storage').default;
      return !!storage.get('superAuthToken');
    },
  },
  methods: {
    // Expose formatRole to the template
    formatRole(role) {
      return formatRole(role);
    },
    // Template wrapper for compatibility with templates
    formatRoleLabel(role) {
      return formatRole(role);
    },
    getAuthHeaders() {
      const storage = require('@/services/storage').default;
      const token = storage.get('authToken');
      return token ? { Authorization: `Bearer ${token}` } : {};
    },

    canImpersonate(user) {
      // Only superadmins can impersonate, not themselves or other superadmins
      const storage = require('@/services/storage').default;
      const myId = parseInt(storage.get('userId') || '0');
      return (
        this.isSuperAdmin && user.role !== 'superadmin' && user.id !== myId
      );
    },

    async impersonateUser(user) {
      // Build a clear display name for confirmation
      const impersonatdisplayName =
        user.first_name || user.last_name
          ? `${user.first_name || ''}${
              user.last_name ? ' ' + user.last_name : ''
            }`.trim()
          : user.name || user.email || 'this user';
      if (
        !confirm(
          `Are you sure you want to impersonate ${impersonatdisplayName}?`
        )
      )
        return;
      try {
        const baseUrl =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const res = await fetch(`${baseUrl}/api/users/${user.id}/impersonate`, {
          method: 'POST',
          headers: this.getAuthHeaders(),
        });
        if (!res.ok) {
          const err = await res.json().catch(() => ({}));
          throw new Error(err.message || 'Failed to impersonate user');
        }
        const data = await res.json();
        const storage = require('@/services/storage').default;
        // Move all current user keys to super* keys
        storage.set('superAuthToken', storage.get('authToken'));
        storage.set('superRole', storage.get('role'));
        storage.set('superUserId', storage.get('userId'));
        storage.set('superFirstName', storage.get('first_name') || '');
        storage.set('superLastName', storage.get('last_name') || '');
        storage.set('superUserName', storage.get('userName') || '');

        // Set impersonated user's info as normal keys
        storage.set('authToken', data.impersonated_token);
        storage.set('role', data.impersonated_role);
        storage.set('userId', data.user_id);
        storage.set('first_name', data.impersonated_first_name || '');
        storage.set('last_name', data.impersonated_last_name || '');
        if (data.impersonated_first_name || data.impersonated_last_name) {
          storage.set(
            'userName',
            `${data.impersonated_first_name || ''}${
              data.impersonated_last_name
                ? ' ' + data.impersonated_last_name
                : ''
            }`.trim()
          );
        } else {
          storage.set('userName', data.impersonated_name);
        }
        // Notify other parts of the app (same-window) that auth info changed
        try {
          window.dispatchEvent(new Event('auth-updated'));
        } catch (e) {
          // ignore
        }
        // Reload to apply new context
        this.$router.go(0);
      } catch (e) {
        alert(e.message || 'Error impersonating user');
      }
    },

    revertImpersonation() {
      if (!this.isImpersonating) return;
      // Restore all super* keys to normal keys
      const storage = require('@/services/storage').default;
      storage.set('authToken', storage.get('superAuthToken'));
      storage.set('role', storage.get('superRole'));
      storage.set('userId', storage.get('superUserId'));
      storage.set('first_name', storage.get('superFirstName') || '');
      storage.set('last_name', storage.get('superLastName') || '');
      storage.set('userName', storage.get('superUserName') || '');
      // Remove all super* keys
      storage.remove('superAuthToken');
      storage.remove('superRole');
      storage.remove('superUserId');
      storage.remove('superFirstName');
      storage.remove('superLastName');
      storage.remove('superUserName');
      this.$router.go(0);
      try {
        window.dispatchEvent(new Event('auth-updated'));
      } catch (e) {}
    },

    async fetchUsers() {
      this.loading = true;
      try {
        const baseUrl =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const res = await fetch(`${baseUrl}/api/users`, {
          headers: this.getAuthHeaders(),
        });
        if (!res.ok) throw new Error('Failed to fetch users');
        const data = await res.json();
        this.users = (data.users || data || []).map((u) => ({
          id: u.id,
          first_name: u.first_name || '',
          last_name: u.last_name || '',
          email: u.email || '',
          role: (u.role || 'user').toString().toLowerCase(),
          name:
            u.first_name || u.last_name
              ? `${u.first_name || ''}${u.last_name ? ' ' + u.last_name : ''}`
              : u.name || u.full_name || '',
        }));
      } catch (e) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: e.message || 'Error fetching users',
          life: 4000,
        });
      } finally {
        this.loading = false;
      }
    },

    async deleteUser(user) {
      if (this.isDeleting) return;
      if (!confirm(`Are you sure you want to delete ${user.name}?`)) return;
      this.isDeleting = true;
      try {
        const baseUrl =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const res = await fetch(`${baseUrl}/api/users/${user.id}/soft-delete`, {
          method: 'PATCH',
          headers: this.getAuthHeaders(),
        });
        if (!res.ok) throw new Error('Failed to delete user');
        this.users = this.users.filter((u) => u.id !== user.id);
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'User deleted successfully!',
          life: 3000,
        });
      } catch (e) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: e.message || 'Error deleting user',
          life: 4000,
        });
      } finally {
        this.isDeleting = false;
      }
    },

    async changeRole(user, newRole) {
      if (user.role === newRole) return;
      try {
        const baseUrl =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const res = await fetch(`${baseUrl}/api/users/${user.id}/role`, {
          method: 'PUT',
          headers: {
            ...this.getAuthHeaders(),
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ role: newRole }),
        });
        if (!res.ok) throw new Error('Failed to change role');
        user.role = newRole;
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Role changed successfully!',
          life: 3000,
        });
      } catch (e) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: e.message || 'Error changing role',
          life: 4000,
        });
      }
    },

    goToPage(page) {
      if (page < 1 || page > this.totalPages) return;
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
    openEditModal(user) {
      this.editUser = { ...user };
      this.showEditModal = true;
    },
    async saveEditUser(event) {
      if (event && event.preventDefault) event.preventDefault();
      if (this.isSaving) return;
      this.isSaving = true;
      const idx = this.users.findIndex((u) => u.id === this.editUser.id);
      if (idx === -1) {
        this.isSaving = false;
        return;
      }
      try {
        const baseUrl =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const res = await fetch(
          `${baseUrl}/api/users/${this.editUser.id}/role`,
          {
            method: 'PATCH',
            headers: {
              ...this.getAuthHeaders(),
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              first_name: this.editUser.first_name,
              last_name: this.editUser.last_name,
              email: this.editUser.email,
              role: this.editUser.role,
            }),
          }
        );
        if (!res.ok) {
          const err = await res.json().catch(() => ({}));
          this.toast.add({
            severity: 'error',
            summary: 'Error',
            detail: err.message || 'Failed to update user',
            life: 4000,
          });
          this.isSaving = false;
          return;
        }
        const updated = await res.json();
        this.users[idx] = { ...this.editUser, ...updated };
        this.showEditModal = false;
        this.toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'User updated successfully!',
          life: 3000,
        });
      } catch (e) {
        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: e.message || 'Failed to update user.',
          life: 4000,
        });
      } finally {
        this.isSaving = false;
      }
    },
  },
};
</script>

<style scoped>
.actions-scroll {
  max-width: 220px;
  overflow-x: auto;
}
.actions-row {
  display: flex;
  flex-direction: row;
  gap: 8px;
  min-width: 220px;
}
.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  padding: 0;
  margin: 0;
  background: #fff;
  border: none; /* Remove border */
  border-radius: 8px;
  cursor: pointer;
  transition: border 0.2s, box-shadow 0.2s;
}
.icon-btn img {
  width: 18px;
  height: 18px;
  display: block;
}
.icon-btn:hover {
  border: 1.5px solid #a1a1a1;
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.08);
}

/* Impersonate button style */
.impersonate-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 0 12px;
  height: 32px;
  font-size: 14px;
  color: #222;
  cursor: pointer;
  font-weight: 500;
  transition: border 0.2s, box-shadow 0.2s;
}
.impersonate-btn img.impersonate-icon {
  width: 18px;
  height: 18px;
  display: block;
}
.impersonate-btn:hover {
  border: 1.5px solid #0074c2;
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.08);
}

.user-permission-add-btn-icon {
  width: 18px;
  height: 18px;
  margin-right: 6px;
  display: inline-block;
  vertical-align: middle;
}
.page {
  padding: 0 32px 32px 32px;
  display: flex;
  background-color: #fff;
  justify-content: center;
  box-sizing: border-box;
}
@media (max-width: 1400px) {
  .page {
    padding: 16px;
  }
}
@media (max-width: 900px) {
  .page {
    padding: 4px;
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
