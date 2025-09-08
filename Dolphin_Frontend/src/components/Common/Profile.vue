<template>
  <ConfirmDialog />
  <MainLayout>
    <div class="page">
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
                {{ user.first_name || '' }}
              </div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Last Name</div>
              <div class="profile-value">
                {{ user.last_name || '' }}
              </div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Email</div>
              <div class="profile-value">
                {{ user.email || '' }}
              </div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Role</div>
              <div class="profile-value">
                <span v-if="user.roles && user.roles.length">
                  {{
                    user.roles
                      .map((r) => formatRoleLabel(r.name || r))
                      .join(', ')
                  }}
                </span>
              </div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Country</div>
              <div class="profile-value">
                {{ countryName }}
              </div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Phone</div>
              <div class="profile-value">
                {{ user.userDetails?.phone || '' }}
              </div>
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
          modal-padding="38px"
        >
          <div class="profile-modal-header">
            <h1>Edit Profile</h1>
          </div>
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
            <FormDropdown
              v-model="editForm.country"
              :options="countries"
              placeholder="Select country"
              :padding-left="16"
            />
          </FormRow>
          <template #actions>
            <button
              type="submit"
              class="btn btn-primary"
              :disabled="isUpdatingProfile"
            >
              <i class="fas fa-save"></i>
              {{ isUpdatingProfile ? ' Saving...' : ' Save' }}
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
            <!-- Hidden username input for password managers and accessibility -->
            <input
              v-if="true"
              class="visually-hidden"
              type="text"
              autocomplete="username"
              aria-hidden="true"
              tabindex="-1"
            />
            <div class="profile-form-row">
              <label class="profile-form-label">Current Password*</label>
              <div class="profile-input-wrapper">
                <input
                  :type="showCurrentPassword ? 'text' : 'password'"
                  v-model="currentPassword"
                  required
                  placeholder="Enter current password"
                  autocomplete="off"
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
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import axios from 'axios';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import CommonModal from '@/components/Common/Common_UI/CommonModal.vue';
import FormRow from '@/components/Common/Common_UI/Form/FormRow.vue';
import FormLabel from '@/components/Common/Common_UI/Form/FormLabel.vue';
import FormInput from '@/components/Common/Common_UI/Form/FormInput.vue';
import FormDropdown from '@/components/Common/Common_UI/Form/FormDropdown.vue';
import storage from '@/services/storage';
import { formatRole } from '@/utils/roles';

export default {
  name: 'Profile',
  components: {
    MainLayout,
    ConfirmDialog,
    Toast,
    CommonModal,
    FormRow,
    FormLabel,
    FormInput,
    FormDropdown,
  },
  setup() {
    const toast = useToast();
    const confirm = useConfirm();
    return { toast, confirm };
  },
  data() {
    return {
      user: {
        // Top-level name fields come from `users` table. Other profile metadata
        // (phone, country, etc.) remain in userDetails for compatibility.
        first_name: '',
        last_name: '',
        email: '',
        userDetails: {
          phone: '',
          country: '',
        },
        roles: [],
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
        country: '', // will store country id
      },
      countries: [],
      editMessage: '',
      isUpdatingProfile: false,
      profileError: '', // <-- error message for profile fetch
      profileRaw: '', // <-- raw API response for debug
      usernameForPassword: '',
    };
  },
  computed: {
    countryName() {
      // prefer countries lookup
      const raw = this.user.userDetails?.country || this.user.country || '';
      // if raw is an object with name
      if (raw && typeof raw === 'object') {
        return raw.name || raw.text || '';
      }
      // if raw looks like a JSON string, try parse
      if (typeof raw === 'string') {
        try {
          const parsed = JSON.parse(raw);
          if (parsed && typeof parsed === 'object') {
            return parsed.name || parsed.text || '';
          }
        } catch (e) {
          // not JSON
        }
      }
      // lookup in countries list by id or string value
      if (this.countries && this.countries.length) {
        const found = this.countries.find(
          (c) =>
            String(c.value) === String(raw) || String(c.text) === String(raw)
        );
        if (found) return found.text || '';
      }
      // fallback to raw string/number
      return raw || '';
    },
  },
  mounted() {
    this.fetchProfile();
    this.fetchCountries();
  },
  methods: {
    // Expose formatRole to the template
    formatRole(role) {
      return formatRole(role);
    },
    // Template wrapper used in templates throughout the app
    formatRoleLabel(role) {
      return formatRole(role);
    },
    async fetchCountries() {
      try {
        const apiUrl =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const token = storage.get('authToken');
        const res = await axios.get(apiUrl + '/api/countries', {
          headers: { Authorization: `Bearer ${token}` },
        });
        // Expect array of { id, name } or { value, text }
        const raw = Array.isArray(res.data)
          ? res.data
          : res.data.countries || [];
        this.countries = raw.map((c) => {
          if (c.value !== undefined && c.text !== undefined)
            return { value: String(c.value), text: c.text };
          const val = c.id || c.value || c.code || c.name;
          return {
            value: val !== undefined && val !== null ? String(val) : '',
            text: c.name || c.text || String(val),
          };
        });
      } catch (e) {
        console.error('Failed to fetch countries', e);
        this.countries = [];
      }
    },
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
        // Accept multiple response shapes and normalize to { email, roles, userDetails }
        const data = response.data;
        this.profileRaw = JSON.stringify(data, null, 2);

        // Extract candidate first/last name from multiple possible sources.
        const userFirstFromUsers =
          (data.user && data.user.first_name) || data.first_name || null;
        const userLastFromUsers =
          (data.user && data.user.last_name) || data.last_name || null;

        // userDetails payload from response (may be under several keys)
        const detailsPayload =
          data.user_details ||
          data.userDetails ||
          (data.user && data.user.user_details) ||
          {};

        // prefer names from users table (top-level user object) and keep other
        // metadata under userDetails for compatibility
        const firstName = userFirstFromUsers || detailsPayload.first_name || '';
        const lastName = userLastFromUsers || detailsPayload.last_name || '';

        // Build normalized user object for the component
        this.user = {
          first_name: firstName,
          last_name: lastName,
          email:
            (data.user && data.user.email) ||
            data.email ||
            this.user.email ||
            '',
          roles: data.roles || (data.user && data.user.roles) || [],
          userDetails: Object.assign({}, detailsPayload || {}, {
            email: (data.user && data.user.email) || data.email || '',
            phone: detailsPayload.phone || '',
            country: detailsPayload.country || data.country || '',
          }),
        };
        this.profileError = '';
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
      if (this.newPassword !== this.confirmPassword) {
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

        this.toast.add({
          severity: 'error',
          summary: 'Password Error',
          detail: msg,
          life: 3500,
        });
      }
    },
    async editAccount() {
      // Prefill modal form with current user data (use top-level names)
      this.editForm.first_name = this.user.first_name || '';
      this.editForm.last_name = this.user.last_name || '';
      this.editForm.email = this.user.email;
      this.editForm.phone = this.user.userDetails?.phone || '';

      // prefill country as id if available; user.userDetails.country may be id or name
      const rawCountry =
        this.user.userDetails.country || this.user.country || '';
      const countryId =
        rawCountry !== undefined && rawCountry !== null
          ? String(rawCountry)
          : '';

      // If countries are not yet loaded, try to load them so the dropdown can display the label
      if (!this.countries || !this.countries.length) {
        try {
          await this.fetchCountries();
        } catch (e) {
          // fetchCountries handles errors internally; ignore here
        }
      }

      // Try to find a matching option after ensuring countries are loaded
      let found = null;
      if (this.countries && this.countries.length) {
        found = this.countries.find(
          (c) =>
            String(c.value) === String(countryId) ||
            String(c.text) === String(countryId)
        );
      }

      if (found) {
        this.editForm.country = String(found.value);
      } else {
        // Provide a temporary option so FormDropdown can show a label even when the
        // canonical country list doesn't include the current value yet.
        const label = this.countryName || countryId || 'Select country';
        if (countryId) {
          const exists = (this.countries || []).some(
            (c) => String(c.value) === String(countryId)
          );
          if (!exists) {
            // append a non-destructive temporary option
            this.countries = (this.countries || []).concat([
              { value: countryId, text: label },
            ]);
          }
        }
        this.editForm.country = countryId;
      }

      this.editMessage = '';
      this.showEditModal = true;
    },
    async updateProfile() {
      this.editMessage = '';
      if (this.isUpdatingProfile) return;
      this.isUpdatingProfile = true;
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
        // ensure country is a primitive (id or code) before sending
        let countryToSend = this.editForm.country;
        if (countryToSend && typeof countryToSend === 'object') {
          countryToSend =
            countryToSend.value ||
            countryToSend.id ||
            countryToSend.code ||
            countryToSend.name ||
            '';
        }
        // backend expects a string for country field; send id as string
        if (countryToSend !== undefined && countryToSend !== null) {
          countryToSend = String(countryToSend);
        } else {
          countryToSend = '';
        }

        // send structured payload so backend can update `users` and `user_details` tables and organizations.admin_email
        const payload = {
          user: {
            email: this.editForm.email,
          },
          user_details: {
            first_name: this.editForm.first_name,
            last_name: this.editForm.last_name,
            phone: this.editForm.phone,
            country: countryToSend,
          },
          // also update admin_email in organizations table (backend should handle this field)
          admin_email: this.editForm.email,
        };

        // helpful debug output when validation fails
        console.log('Updating profile with payload:', payload);

        const response = await axios.patch(
          `${
            process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000'
          }/api/profile`,
          payload,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
            },
          }
        );
        // Update local user data and encrypted storage from response (handle multiple shapes)
        const res = response.data || {};
        // response may contain { user, user_details } or merged user object
        const resUser = res.user || res;
        const resDetails =
          res.user_details ||
          res.userDetails ||
          res.user_details ||
          res.user_details ||
          (res.user && res.user.user_details) ||
          {};

        // Update top-level name fields when backend returns them, otherwise
        // fall back to the editForm values (backend should update users table)
        this.user.email =
          resUser.email || this.editForm.email || this.user.email;
        this.user.first_name =
          resUser.first_name ||
          (resDetails && resDetails.first_name) ||
          this.editForm.first_name ||
          this.user.first_name;
        this.user.last_name =
          resUser.last_name ||
          (resDetails && resDetails.last_name) ||
          this.editForm.last_name ||
          this.user.last_name;
        this.user.userDetails = Object.assign(
          {},
          this.user.userDetails || {},
          resDetails || {},
          {
            phone: (resDetails && resDetails.phone) || this.editForm.phone,
            country: (resDetails && resDetails.country) || countryToSend,
          }
        );
        // Persist top-level names to storage for Navbar and other components
        storage.set('first_name', this.user.first_name);
        storage.set('last_name', this.user.last_name);
        storage.set('email', this.user.email);
        storage.set('phone', this.user.userDetails.phone);
        storage.set('country', this.user.userDetails.country);
        // Also update the full user object for Navbar
        storage.set('user', {
          first_name: this.user.first_name,
          last_name: this.user.last_name,
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
        this.isUpdatingProfile = false;
      } catch (error) {
        let msg = 'Failed to update profile.';
        if (error.response && error.response.data) {
          const data = error.response.data;
          // Laravel validation errors are under data.errors
          if (data.errors && typeof data.errors === 'object') {
            const msgs = [];
            Object.values(data.errors).forEach((v) => {
              if (Array.isArray(v)) msgs.push(...v);
              else msgs.push(String(v));
            });
            msg = msgs.join(' ');
          } else if (data.message) {
            msg = data.message;
          } else {
            try {
              msg = JSON.stringify(data);
            } catch (e) {
              msg = String(data);
            }
          }
          console.error('Profile update error response:', data);
        }
        this.editMessage = msg;
        this.toast.add({
          severity: 'error',
          summary: 'Profile Error',
          detail: msg,
          life: 3500,
        });
        this.isUpdatingProfile = false;
      }
    },
    async deleteAccount() {
      this.editMessage = '';
      const message =
        'Are you sure you want to delete your account? This action cannot be undone.';

      this.confirm.require({
        message,
        header: 'Confirm Delete Account',
        icon: 'pi pi-trash',
        acceptLabel: 'Yes',
        rejectLabel: 'No',
        accept: async () => {
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
        reject: () => {
          // no-op
        },
      });
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
  text-align: left;
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
  text-align: left;
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

/* Visually hidden but accessible field for username (password manager helpers) */
.visually-hidden {
  position: absolute !important;
  height: 1px;
  width: 1px;
  overflow: hidden;
  clip: rect(1px, 1px, 1px, 1px);
  white-space: nowrap; /* added line */
  border: 0;
  padding: 0;
  margin: -1px;
}

@media (max-width: 900px) {
  .profile-outer {
    display: flex;
    flex-direction: column;
    gap: 32px;
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
