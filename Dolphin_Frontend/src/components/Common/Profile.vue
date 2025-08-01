<template>
  <MainLayout>
    <div class="profile-outer">
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-title">
            <i class="fas fa-user-circle profile-avatar"></i>
            <span>Profile</span>
          </div>
          <div>
            <button
              class="btn btn-primary"
              @click="editAccount"
            >
              <i class="fas fa-pen-to-square"></i>
              Edit
            </button>
          </div>
        </div>
        <div class="profile-info-table">
          <div
            v-if="profileError"
            style="color: red; margin-bottom: 10px"
          >
            {{ profileError }}
          </div>
          <div class="profile-info-row">
            <div class="profile-label">First Name</div>
            <div class="profile-value">
              {{ user.userDetails?.first_name || '' }}
            </div>
          </div>
          <div class="profile-info-row">
            <div class="profile-label">Last Name</div>
            <div class="profile-value">
              {{ user.userDetails?.last_name || '' }}
            </div>
          </div>
          <div class="profile-info-row">
            <div class="profile-label">Email</div>
            <div class="profile-value">
              {{ user.email || user.userDetails?.email || '' }}
            </div>
          </div>
          <div class="profile-info-row">
            <div class="profile-label">Role</div>
            <div class="profile-value">
              <span v-if="user.roles && user.roles.length">
                {{ user.roles.map((r) => r.name).join(', ') }}
              </span>
            </div>
          </div>
          <div class="profile-info-row">
            <div class="profile-label">Country</div>
            <div class="profile-value">
              {{ user.userDetails?.country || '' }}
            </div>
          </div>
          <div class="profile-info-row">
            <div class="profile-label">Phone</div>
            <div class="profile-value">{{ user.userDetails?.phone || '' }}</div>
          </div>
        </div>
        <div class="profile-actions">
          <button
            class="btn btn-danger"
            @click="deleteAccount"
          >
            <i class="fas fa-trash"></i>
            Delete Account
          </button>
        </div>
      </div>
      <!-- Edit Profile Modal -->
      <CommonModal
        :visible="showEditModal"
        @close="showEditModal = false"
        @submit="updateProfile"
      >
        <template #title>Edit Profile</template>
        <FormRow>
          <FormLabel>First Name</FormLabel>
          <FormInput
            v-model="editForm.first_name"
            type="text"
            required
          />
        </FormRow>
        <FormRow>
          <FormLabel>Last Name</FormLabel>
          <FormInput
            v-model="editForm.last_name"
            type="text"
            required
          />
        </FormRow>
        <FormRow>
          <FormLabel>Email</FormLabel>
          <FormInput
            v-model="editForm.email"
            type="email"
            required
          />
        </FormRow>
        <FormRow>
          <FormLabel>Phone</FormLabel>
          <FormInput
            v-model="editForm.phone"
            type="text"
          />
        </FormRow>
        <FormRow>
          <FormLabel>Country</FormLabel>
          <FormInput
            v-model="editForm.country"
            type="text"
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
        <div
          v-if="editMessage"
          class="profile-message"
        >
          {{ editMessage }}
        </div>
      </CommonModal>
      <div class="profile-card">
        <div class="profile-section-title">Change Password</div>
        <form
          class="profile-password-form"
          @submit.prevent="changePassword"
        >
          <div class="profile-form-row">
            <label class="profile-form-label">Current Password*</label>
            <div class="profile-input-wrapper">
              <input
                :type="showCurrentPassword ? 'text' : 'password'"
                v-model="currentPassword"
                required
                placeholder="Enter current password"
                autocomplete="current-password"
              />
              <span
                class="profile-eye-icon"
                @click="showCurrentPassword = !showCurrentPassword"
              >
                <i
                  :class="
                    showCurrentPassword ? 'fas fa-eye-slash' : 'fas fa-eye'
                  "
                ></i>
              </span>
            </div>
          </div>
          <div class="profile-form-row">
            <label class="profile-form-label">New Password*</label>
            <div class="profile-input-wrapper">
              <input
                :type="showNewPassword ? 'text' : 'password'"
                v-model="newPassword"
                required
                placeholder="Enter new password"
                autocomplete="new-password"
              />
              <span
                class="profile-eye-icon"
                @click="showNewPassword = !showNewPassword"
              >
                <i
                  :class="showNewPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"
                ></i>
              </span>
            </div>
          </div>
          <div class="profile-form-row">
            <label class="profile-form-label">Confirm New Password*</label>
            <div class="profile-input-wrapper">
              <input
                :type="showConfirmPassword ? 'text' : 'password'"
                v-model="confirmPassword"
                required
                placeholder="Confirm new password"
                autocomplete="new-password"
              />
              <span
                class="profile-eye-icon"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <i
                  :class="
                    showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'
                  "
                ></i>
              </span>
            </div>
          </div>
          <div class="profile-save-btn-row">
            <button
              type="submit"
              class="btn btn-primary"
            >
              <i class="fas fa-key"></i>
              Change Password
            </button>
          </div>
        </form>
        <div
          v-if="message"
          class="profile-message"
        >
          {{ message }}
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import axios from 'axios';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import CommonModal from '@/components/Common/Common_UI/CommonModal.vue';
import FormRow from '@/components/Common/Common_UI/Form/FormRow.vue';
import FormLabel from '@/components/Common/Common_UI/Form/FormLabel.vue';
import FormInput from '@/components/Common/Common_UI/Form/FormInput.vue';
import storage from '@/services/storage';

export default {
  name: 'Profile',
  components: {
    MainLayout,
    Toast,
    CommonModal,
    FormRow,
    FormLabel,
    FormInput,
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      user: {
        userDetails: {
          first_name: '',
          last_name: '',
          email: '',
          phone: '',
          country: '',
        },
        roles: [],
        email: '',
      },
      currentPassword: '',
      newPassword: '',
      confirmPassword: '',
      message: '',
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      showEditModal: false,
      editForm: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        country: '',
      },
      editMessage: '',
      profileError: '', // <-- error message for profile fetch
      profileRaw: '', // <-- raw API response for debug
    };
  },
  mounted() {
    this.fetchProfile();
  },
  methods: {
    async fetchProfile() {
      try {
        const token = storage.get('authToken');
        if (!token) {
          this.profileError = 'No auth token found. Please login.';
          this.toast.add({
            severity: 'error',
            summary: 'Auth Error',
            detail: 'No auth token found. Please login.',
            life: 3000,
          });
          return;
        }
        const response = await axios.get(
          `${
            process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000'
          }/api/profile`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        // Expect userDetails and roles in response
        this.user = response.data;
        this.profileError = '';
        this.profileRaw = JSON.stringify(response.data, null, 2);
      } catch (error) {
        this.user = { userDetails: {}, roles: [], email: '' };
        if (error.response && error.response.data) {
          this.profileError = 'Error: ' + JSON.stringify(error.response.data);
          this.toast.add({
            severity: 'error',
            summary: 'Profile Error',
            detail: JSON.stringify(error.response.data),
            life: 3500,
          });
        } else {
          this.profileError = 'Failed to fetch profile.';
          this.toast.add({
            severity: 'error',
            summary: 'Profile Error',
            detail: 'Failed to fetch profile.',
            life: 3500,
          });
        }
        this.profileRaw = '';
      }
    },
    async changePassword() {
      this.message = '';
      if (this.newPassword !== this.confirmPassword) {
        this.message = 'New passwords do not match.';
        this.toast.add({
          severity: 'error',
          summary: 'Password Error',
          detail: 'New passwords do not match.',
          life: 3000,
        });
        return;
      }
      try {
        // Use the same token key for all requests
        const token = storage.get('authToken');
        if (!token) {
          this.message = 'Not authenticated.';
          this.toast.add({
            severity: 'error',
            summary: 'Auth Error',
            detail: 'Not authenticated.',
            life: 3000,
          });
          return;
        }
        await axios.post(
          `${
            process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000'
          }/api/change-password`,
          {
            current_password: this.currentPassword,
            new_password: this.newPassword,
            new_password_confirmation: this.confirmPassword,
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
            },
          }
        );
        this.message = 'Password changed successfully!';
        this.toast.add({
          severity: 'success',
          summary: 'Password Changed',
          detail: 'Password changed successfully!',
          life: 3000,
        });
        this.currentPassword = '';
        this.newPassword = '';
        this.confirmPassword = '';
      } catch (error) {
        let msg = 'Failed to change password.';
        if (
          error.response &&
          error.response.data &&
          error.response.data.error
        ) {
          msg = error.response.data.error;
        } else if (error.response && error.response.data) {
          msg = Object.values(error.response.data).join(' ');
        }
        this.message = msg;
        this.toast.add({
          severity: 'error',
          summary: 'Password Error',
          detail: msg,
          life: 3500,
        });
      }
    },
    editAccount() {
      // Prefill modal form with current user data
      this.editForm.first_name = this.user.userDetails.first_name;
      this.editForm.last_name = this.user.userDetails.last_name;
      this.editForm.email = this.user.email;
      this.editForm.phone = this.user.userDetails.phone;
      this.editForm.country = this.user.userDetails.country;
      this.editMessage = '';
      this.showEditModal = true;
    },
    async updateProfile() {
      this.editMessage = '';
      try {
        // Use the same token key for all requests
        const token = storage.get('authToken');
        if (!token) {
          this.editMessage = 'Not authenticated.';
          this.toast.add({
            severity: 'error',
            summary: 'Auth Error',
            detail: 'Not authenticated.',
            life: 3000,
          });
          return;
        }
        const response = await axios.patch(
          `${
            process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000'
          }/api/profile`,
          {
            first_name: this.editForm.first_name,
            last_name: this.editForm.last_name,
            email: this.editForm.email,
            phone: this.editForm.phone,
            country: this.editForm.country,
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
            },
          }
        );
        // Update local user data and encrypted storage
        this.user.userDetails.first_name = response.data.user.first_name;
        this.user.userDetails.last_name = response.data.user.last_name;
        this.user.email = response.data.user.email;
        this.user.userDetails.phone = response.data.user.phone;
        this.user.userDetails.country = response.data.user.country;
        storage.set('first_name', this.user.userDetails.first_name);
        storage.set('last_name', this.user.userDetails.last_name);
        storage.set('email', this.user.email);
        storage.set('phone', this.user.userDetails.phone);
        storage.set('country', this.user.userDetails.country);
        // Also update the full user object for Navbar
        storage.set('user', {
          first_name: this.user.userDetails.first_name,
          last_name: this.user.userDetails.last_name,
          email: this.user.email,
          role: this.user.role,
          country: this.user.userDetails.country,
          phone: this.user.userDetails.phone,
        });
        this.showEditModal = false;
        // Only show toast after modal is closed, do not set editMessage
        setTimeout(() => {
          this.toast.add({
            severity: 'success',
            summary: 'Profile Updated',
            detail: 'Profile updated successfully!',
            life: 3000,
          });
        }, 350);
      } catch (error) {
        let msg = 'Failed to update profile.';
        if (error.response && error.response.data) {
          msg =
            error.response.data.message ||
            Object.values(error.response.data).join(' ');
        }
        this.editMessage = msg;
        this.toast.add({
          severity: 'error',
          summary: 'Profile Error',
          detail: msg,
          life: 3500,
        });
      }
    },
    async deleteAccount() {
      this.editMessage = '';
      if (
        !confirm(
          'Are you sure you want to delete your account? This action cannot be undone.'
        )
      ) {
        return;
      }
      try {
        const token = storage.get('authToken');
        if (!token) {
          this.toast.add({
            severity: 'error',
            summary: 'Auth Error',
            detail: 'Not authenticated.',
            life: 3000,
          });
          return;
        }
        await axios.delete(
          `${
            process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000'
          }/api/profile`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        this.toast.add({
          severity: 'success',
          summary: 'Account Deleted',
          detail: 'Account deleted successfully.',
          life: 3000,
        });
        // Optionally clear encrypted storage and redirect to login
        setTimeout(() => {
          storage.clear();
          window.location.href = '/login';
        }, 1200);
      } catch (error) {
        let msg = 'Failed to delete account.';
        if (
          error.response &&
          error.response.data &&
          error.response.data.message
        ) {
          msg = error.response.data.message;
        }
        this.toast.add({
          severity: 'error',
          summary: 'Delete Error',
          detail: msg,
          life: 3500,
        });
      }
    },
  },
};
</script>

<style scoped>
/* Modal styles for edit profile */
.profile-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.profile-modal {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.12);
  padding: 32px 32px 24px 32px;
  min-width: 320px;
  max-width: 400px;
  width: 100%;
  position: relative;
}
.profile-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 18px;
}
.close-btn {
  background: none;
  border: none;
  font-size: 1.6rem;
  color: #888;
  cursor: pointer;
  margin-left: 12px;
}
.profile-edit-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.profile-outer {
  max-width: 900px;
  margin: 48px auto;
  display: flex;
  flex-direction: column;
  gap: 32px;
}
.profile-card {
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
.profile-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 28px 32px 0 32px;
}
.profile-title {
  display: flex;
  align-items: center;
  gap: 14px;
  font-size: 1.5rem;
  font-weight: 600;
  color: #0074c2;
}
.profile-avatar {
  font-size: 2.2rem;
  color: #0074c2;
}
.profile-info-table {
  padding: 18px 32px 0 32px;
  display: flex;
  flex-direction: column;
  gap: 0;
}
.profile-info-row {
  display: flex;
  border-bottom: 1px solid #f0f0f0;
  padding: 14px 0;
  align-items: center;
}
.profile-label {
  width: 160px;
  color: #888;
  font-weight: 400;
  font-size: 1rem;
}
.profile-value {
  color: #222;
  font-weight: 500;
  font-size: 1rem;
  word-break: break-word;
}
.profile-actions {
  display: flex;
  justify-content: flex-end;
  padding: 18px 32px 0 32px;
}
.profile-section-title {
  font-size: 1.15rem;
  font-weight: 600;
  color: #222;
  margin: 28px 0 18px 32px;
}
.profile-password-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 0 32px;
}
.profile-form-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
}
.profile-form-label {
  width: 170px;
  color: #888;
  font-size: 15px;
  font-weight: 400;
  margin-bottom: 0;
}
.profile-input-wrapper {
  position: relative;
  flex: 1;
  display: flex;
  align-items: center;
}
.profile-input-wrapper input {
  width: 100%;
  background: #fff;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 38px 10px 14px;
  font-size: 15px;
  color: #222;
  outline: none;
  transition: border 0.2s;
}
.profile-eye-icon {
  position: absolute;
  right: 12px;
  cursor: pointer;
  color: #888;
  font-size: 1.1rem;
  z-index: 2;
}
.profile-save-btn-row {
  display: flex;
  justify-content: flex-end;
  margin-top: 4px;
}
.profile-message {
  margin-top: 12px;
  color: #2e7d32;
  font-weight: 500;
  padding-left: 32px;
}
@media (max-width: 900px) {
  .profile-outer {
    max-width: 98vw;
    padding: 0 2vw;
  }
  .profile-header,
  .profile-info-table,
  .profile-actions,
  .profile-section-title,
  .profile-password-form {
    padding-left: 12px;
    padding-right: 12px;
  }
  .profile-section-title {
    margin-left: 12px;
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
