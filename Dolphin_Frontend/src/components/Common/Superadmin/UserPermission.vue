<template>
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
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>
                    {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
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
      <FormLabel>Name</FormLabel>
      <FormInput
        v-model="editUser.name"
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
      <select
        v-model="editUser.role"
        required
        class="form-input"
        style="
          width: 100%;
          height: 44px;
          padding: 0 12px;
          border-radius: 8px;
          border: 1.5px solid #e0e0e0;
        "
      >
        <option value="organizationadmin">Organization Admin</option>
        <option value="Dolphinadmin">Dolphin Admin</option>
        <option value="salesperson">Sales Person</option>
        <option value="user">User</option>
      </select>
    </FormRow>

    <template #actions>
      <button
        type="submit"
        class="btn btn-primary"
      >
        <i class="fas fa-save"></i> Save
      </button>
    </template>
    <div
      v-if="editMessage"
      style="margin-top: 12px; color: #2e7d32; font-weight: 500"
    >
      {{ editMessage }}
    </div>
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
  },
  data() {
    return {
      users: [],
      loading: false,
      error: '',
      currentPage: 1,
      pageSize: 10,
      pageSizes: [10, 25, 100],
      showPageDropdown: false,
      sortKey: '',
      sortAsc: true,
      showEditModal: false,
      editUser: {
        id: null,
        name: '',
        email: '',
        role: '',
      },
      editMessage: '',
    };
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
      return localStorage.getItem('role') === 'superadmin';
    },
    isImpersonating() {
      return !!localStorage.getItem('superAuthToken');
    },
  },
  methods: {
    getAuthHeaders() {
      const token = localStorage.getItem('authToken');
      return token ? { Authorization: `Bearer ${token}` } : {};
    },

    canImpersonate(user) {
      // Only superadmins can impersonate, not themselves or other superadmins
      const myId = parseInt(localStorage.getItem('userId') || '0');
      return (
        this.isSuperAdmin && user.role !== 'superadmin' && user.id !== myId
      );
    },

    async impersonateUser(user) {
      if (!confirm(`Are you sure you want to impersonate ${user.name}?`))
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
        // Store superadmin's original token and info
        localStorage.setItem(
          'superAuthToken',
          localStorage.getItem('authToken')
        );
        localStorage.setItem('superRole', localStorage.getItem('role'));
        localStorage.setItem('superUserName', localStorage.getItem('userName'));
        localStorage.setItem('superUserId', localStorage.getItem('userId'));
        // Set impersonated user's info
        localStorage.setItem('authToken', data.impersonated_token);
        localStorage.setItem('role', data.impersonated_role);
        localStorage.setItem('userName', data.impersonated_name);
        localStorage.setItem('userId', data.user_id);
        // Reload to apply new context
        this.$router.go(0);
      } catch (e) {
        alert(e.message || 'Error impersonating user');
      }
    },

    revertImpersonation() {
      if (!this.isImpersonating) return;
      // Restore superadmin's info
      localStorage.setItem('authToken', localStorage.getItem('superAuthToken'));
      localStorage.setItem('role', localStorage.getItem('superRole'));
      localStorage.setItem('userName', localStorage.getItem('superUserName'));
      localStorage.setItem('userId', localStorage.getItem('superUserId'));
      // Remove impersonation keys
      localStorage.removeItem('superAuthToken');
      localStorage.removeItem('superRole');
      localStorage.removeItem('superUserName');
      localStorage.removeItem('superUserId');
      this.$router.go(0);
    },

    async fetchUsers() {
      this.loading = true;
      this.error = '';
      try {
        const baseUrl =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const res = await fetch(`${baseUrl}/api/users`, {
          headers: this.getAuthHeaders(),
        });
        if (!res.ok) throw new Error('Failed to fetch users');
        const data = await res.json();
        // Map backend fields to frontend fields
        this.users = (data.users || data || []).map((u) => ({
          id: u.id,
          name: u.name || u.full_name || '',
          email: u.email || '',
          role: u.role || 'user',
        }));
      } catch (e) {
        this.error = e.message || 'Error fetching users';
      } finally {
        this.loading = false;
      }
    },

    async deleteUser(user) {
      if (!confirm(`Are you sure you want to delete ${user.name}?`)) return;
      try {
        const baseUrl =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        // Use PATCH for soft delete
        const res = await fetch(`${baseUrl}/api/users/${user.id}/soft-delete`, {
          method: 'PATCH',
          headers: this.getAuthHeaders(),
        });
        if (!res.ok) throw new Error('Failed to delete user');
        this.users = this.users.filter((u) => u.id !== user.id);
      } catch (e) {
        alert(e.message || 'Error deleting user');
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
      } catch (e) {
        alert(e.message || 'Error changing role');
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
      this.editMessage = '';
      this.showEditModal = true;
    },
    async saveEditUser() {
      this.editMessage = '';
      const idx = this.users.findIndex((u) => u.id === this.editUser.id);
      if (idx === -1) return;
      try {
        const baseUrl =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        // Update role in backend
        const res = await fetch(
          `${baseUrl}/api/users/${this.editUser.id}/role`,
          {
            method: 'PATCH',
            headers: {
              ...this.getAuthHeaders(),
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ role: this.editUser.role }),
          }
        );
        if (!res.ok) {
          const err = await res.json().catch(() => ({}));
          throw new Error(err.message || 'Failed to update role');
        }
        // Update local user
        this.users[idx] = { ...this.editUser };
        this.editMessage = 'User updated successfully!';
        setTimeout(() => {
          this.showEditModal = false;
        }, 1000);
      } catch (e) {
        this.editMessage = e.message || 'Failed to update user.';
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
</style>
