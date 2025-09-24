<template>
  <MainLayout>
    <ConfirmDialog />
    <Toast />

    <div class="page">
      <div class="profile-outer">
        <div class="profile-card">
          <div class="profile-header">
            <div class="profile-title">
              <i class="fas fa-user-circle profile-avatar"></i>
              <span>Profile</span>
            </div>
            <button
              class="btn btn-primary"
              @click="openEditModal"
            >
              <i class="fas fa-pen-to-square"></i>
              Edit
            </button>
          </div>

          <div
            v-if="isLoading"
            class="profile-info-table"
          >
            Loading profile...
          </div>
          <div
            v-else-if="error"
            class="profile-info-table"
            style="color: red"
          >
            {{ error }}
          </div>
          <div
            v-else-if="profile"
            class="profile-info-table"
          >
            <div class="profile-info-row">
              <div class="profile-label">First Name</div>
              <div class="profile-value">{{ profile.first_name }}</div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Last Name</div>
              <div class="profile-value">{{ profile.last_name }}</div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Email</div>
              <div class="profile-value">{{ profile.email }}</div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Role</div>
              <div class="profile-value">{{ formattedRole }}</div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Country</div>
              <div class="profile-value">{{ profile.country }}</div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Phone</div>
              <div class="profile-value">{{ profile.phone }}</div>
            </div>
          </div>

          <div class="profile-actions">
            <button
              class="btn btn-danger"
              @click="confirmDeleteAccount"
            >
              <i class="fas fa-trash"></i>
              Delete Account
            </button>
          </div>
        </div>

        <div
          v-if="isEditModalVisible"
          class="modal-overlay"
          @click.self="isEditModalVisible = false"
        >
          <div
            class="modal-card"
            style="max-width: 550px"
          >
            <button
              class="modal-close-btn"
              @click="isEditModalVisible = false"
            >
              &times;
            </button>
            <div class="modal-title">Edit Profile</div>
            <div
              class="modal-desc"
              style="font-size: 1.5rem !important"
            >
              Update your profile information.
            </div>
            <form
              class="modal-form"
              @submit.prevent="handleUpdateProfile"
            >
              <FormRow style="margin-bottom: 0 !important">
                <FormLabel
                  style="font-size: 1rem !important; margin: 0 !important"
                  >First Name</FormLabel
                >
                <FormInput
                  v-model="editForm.first_name"
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
                  v-model="editForm.last_name"
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
                  v-model="editForm.email"
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
                  v-model="editForm.phone"
                  icon="fas fa-phone"
                  type="text"
                  placeholder="Enter phone number"
                />
              </FormRow>
              <FormRow style="margin-bottom: 0 !important">
                <FormLabel
                  style="font-size: 1rem !important; margin: 0 !important"
                  >Country</FormLabel
                >
                <FormDropdown
                  v-model="editForm.country_id"
                  icon="fas fa-globe"
                  :options="countries"
                  placeholder="Select country"
                />
              </FormRow>

              <div class="modal-form-actions">
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="isUpdating"
                >
                  <i class="fas fa-save"></i>
                  {{ isUpdating ? 'Saving...' : 'Save' }}
                </button>
                <button
                  type="button"
                  class="org-edit-cancel"
                  @click="isEditModalVisible = false"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="profile-card">
          <div class="profile-section-title">Change Password</div>
          <form
            class="profile-password-form"
            @submit.prevent="handleChangePassword"
          >
            <div class="profile-info-row">
              <div class="profile-label">Current Password*</div>
              <div class="profile-value">
                <div style="position: relative; width: 100%">
                  <FormInput
                    v-model="passwordForm.current_password"
                    icon="fas fa-lock"
                    :type="showPassword ? 'text' : 'password'"
                    required
                  />
                  <span
                    class="icon right"
                    style="
                      position: absolute;
                      right: 12px;
                      top: 50%;
                      transform: translateY(-50%);
                      cursor: pointer;
                    "
                    @click="togglePassword"
                  >
                    <i
                      :class="showPassword ? 'fas fa-eye' : 'fas fa-eye-slash'"
                    ></i>
                  </span>
                </div>
              </div>
            </div>

            <div class="profile-info-row">
              <div class="profile-label">New Password*</div>
              <div class="profile-value">
                <div style="position: relative; width: 100%">
                  <FormInput
                    v-model="passwordForm.new_password"
                    icon="fas fa-lock"
                    :type="showPassword ? 'text' : 'password'"
                    required
                  />
                  <span
                    class="icon right"
                    style="
                      position: absolute;
                      right: 12px;
                      top: 50%;
                      transform: translateY(-50%);
                      cursor: pointer;
                    "
                    @click="togglePassword"
                  >
                    <i
                      :class="showPassword ? 'fas fa-eye' : 'fas fa-eye-slash'"
                    ></i>
                  </span>
                </div>
              </div>
            </div>
            <div class="profile-info-row">
              <div class="profile-label">Confirm New Password*</div>
              <div class="profile-value">
                <div style="position: relative; width: 100%">
                  <FormInput
                    v-model="passwordForm.new_password_confirmation"
                    icon="fas fa-lock"
                    class="form-input"
                    :type="showPassword ? 'text' : 'password'"
                    required
                  />
                  <span
                    class="icon right"
                    style="
                      position: absolute;
                      right: 12px;
                      top: 50%;
                      transform: translateY(-50%);
                      cursor: pointer;
                    "
                    @click="togglePassword"
                  >
                    <i
                      :class="showPassword ? 'fas fa-eye' : 'fas fa-eye-slash'"
                    ></i>
                  </span>
                </div>
              </div>
            </div>

            <div class="profile-save-btn-row">
              <button
                type="submit"
                class="btn btn-primary"
                :disabled="isPasswordChanging"
              >
                <i class="fas fa-key"></i>
                {{ isPasswordChanging ? 'Changing...' : 'Change Password' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

// Component Imports
import MainLayout from '@/components/layout/MainLayout.vue';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import FormRow from '@/components/Common/Common_UI/Form/FormRow.vue';
import FormLabel from '@/components/Common/Common_UI/Form/FormLabel.vue';
import FormInput from '@/components/Common/Common_UI/Form/FormInput.vue';

import FormDropdown from '@/components/Common/Common_UI/Form/FormDropdown.vue';

// Services & Utils
import storage from '@/services/storage';
import { formatRole } from '@/utils/roles';

// Composables
const toast = useToast();
const confirm = useConfirm();

// --- STATE MANAGEMENT ---

const profile = ref(null);
const countries = ref([]);
const isLoading = ref(true);
const error = ref(null);
const isUpdating = ref(false);
const isPasswordChanging = ref(false);
const isEditModalVisible = ref(false);
// Password visibility toggle
const showPassword = ref(false);

const editForm = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  country_id: '',
});

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
});
/** Toggle new password visibility */
function togglePassword() {
  showPassword.value = !showPassword.value;
}

// --- COMPUTED PROPERTIES ---

const formattedRole = computed(() => {
  return profile.value?.role ? formatRole(profile.value.role) : '';
});

// --- API & BUSINESS LOGIC ---

const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
const authToken = storage.get('authToken');

const fetchProfile = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await axios.get(`${API_BASE_URL}/api/profile`, {
      headers: { Authorization: `Bearer ${authToken}` },
    });
    profile.value = response.data;
  } catch (err) {
    console.log(err);
    error.value = 'Failed to fetch profile information.';
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.value,
      life: 3000,
    });
  } finally {
    isLoading.value = false;
  }
};

const fetchCountries = async () => {
  try {
    const response = await axios.get(`${API_BASE_URL}/api/countries`, {
      headers: { Authorization: `Bearer ${authToken}` },
    });
    countries.value = response.data.map((c) => ({ value: c.id, text: c.name }));
  } catch (err) {
    console.error('Failed to fetch countries:', err);
  }
};

const openEditModal = () => {
  if (!profile.value) return;
  // Pre-fill the form with the current profile data
  editForm.first_name = profile.value.first_name;
  editForm.last_name = profile.value.last_name;
  editForm.email = profile.value.email;
  editForm.phone = profile.value.phone;
  editForm.country_id = profile.value.country_id;
  isEditModalVisible.value = true;
};

const handleUpdateProfile = async () => {
  isUpdating.value = true;
  try {
    const payload = {
      user: { email: editForm.email },
      user_details: {
        first_name: editForm.first_name,
        last_name: editForm.last_name,
        phone: editForm.phone,
        country: editForm.country_id,
      },
    };
    const response = await axios.patch(`${API_BASE_URL}/api/profile`, payload, {
      headers: { Authorization: `Bearer ${authToken}` },
    });
    profile.value = response.data.user; // Update local state
    isEditModalVisible.value = false;
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Profile updated successfully!',
      life: 3000,
    });
  } catch (err) {
    const errorMessage =
      err.response?.data?.message || 'Failed to update profile.';
    toast.add({
      severity: 'error',
      summary: 'Update Error',
      detail: errorMessage,
      life: 3000,
    });
  } finally {
    isUpdating.value = false;
  }
};

const handleChangePassword = async () => {
  if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
    toast.add({
      severity: 'error',
      summary: 'Password Error',
      detail: 'New passwords do not match.',
      life: 3000,
    });
    return;
  }
  isPasswordChanging.value = true;
  try {
    await axios.post(`${API_BASE_URL}/api/change-password`, passwordForm, {
      headers: { Authorization: `Bearer ${authToken}` },
    });
    toast.add({
      severity: 'success',
      summary: 'Success',
      detail: 'Password changed successfully!',
      life: 3000,
    });
    // Reset form
    Object.keys(passwordForm).forEach((key) => (passwordForm[key] = ''));
  } catch (err) {
    const errorMessage =
      err.response?.data?.error || 'Failed to change password.';
    toast.add({
      severity: 'error',
      summary: 'Password Error',
      detail: errorMessage,
      life: 3000,
    });
  } finally {
    isPasswordChanging.value = false;
  }
};

const confirmDeleteAccount = () => {
  confirm.require({
    message:
      'Are you sure you want to delete your account? This action is permanent.',
    header: 'Confirm Account Deletion',
    icon: 'pi pi-exclamation-triangle',
    accept: async () => {
      try {
        await axios.delete(`${API_BASE_URL}/api/profile`, {
          headers: { Authorization: `Bearer ${authToken}` },
        });
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Account deleted.',
          life: 3000,
        });
        storage.clear();
        setTimeout(() => (window.location.href = '/login'), 1500);
      } catch (err) {
        console.log(err);
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to delete account.',
          life: 3000,
        });
      }
    },
  });
};

// --- LIFECYCLE HOOK ---
onMounted(() => {
  fetchProfile();
  fetchCountries();
});
</script>

<style scoped>
/* Scoped styles from the original file */
@import '@/assets/modelcssnotificationandassesment.css';

.profile-outer {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 40px;
}

.profile-card {
  width: 100%;
  max-width: 800px; /* Constrain width for better readability */
  background: #fff;
  border-radius: 16px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.06);
  padding: 24px 32px;
}
@media (max-width: 600px) {
  .profile-card {
    padding: 16px 20px;
  }
}

.profile-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}
@media (max-width: 600px) {
  .profile-header {
    display: grid;
    grid-template-columns: 1fr;
    align-items: flex-start;
    gap: 16px;
  }
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
}

.profile-info-table {
  display: flex;
  flex-direction: column;
}

.profile-info-row {
  display: flex;
  border-bottom: 1px solid #f0f0f0;
  padding: 16px 0;
  gap: 20px;
}
@media (max-width: 600px) {
  .profile-info-row {
    flex-direction: column;
    padding: 8px 0;
    gap: 8px;
  }
}
.profile-info-row:last-child {
  border-bottom: none;
}

.profile-label {
  min-width: 180px;
  color: #888;
  font-weight: 500;
  align-content: center;
}
@media (max-width: 600px) {
  .profile-label {
    min-width: 80px;
    text-align: justify;
  }
}

.profile-value {
  flex-grow: 1;
  color: #222;
  font-weight: 500;
  word-break: break-word;
  text-align: left;
}
@media (max-width: 600px) {
  .profile-value {
    min-width: 100px;
    text-align: left;
  }
}

.profile-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 24px;
}

.profile-section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #222;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.profile-password-form {
  display: flex;
  flex-direction: column;
}

.profile-password-form .profile-value {
  flex-grow: 1;
  max-width: none;
}

.profile-form-row {
  display: grid;
  grid-template-columns: 180px 1fr;
  align-items: center;
  gap: 16px;
}

.profile-form-label {
  text-align: left;
  color: #555;
  font-weight: 500;
}

.profile-save-btn-row {
  display: flex;
  justify-content: flex-end;
  margin-top: 16px;
}
.form-input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 16px;
  color: #222;
  width: 100%;
  height: 44px;
  padding: 0 36px 0 32px; /* left for lock, right for eye */
  font-family: inherit;
  box-sizing: border-box;
}
.form-input:disabled {
  background: #f0f0f0;
  color: #aaa;
}
.form-input-icon {
  position: absolute;
  left: 12px;
  color: #888;
  font-size: 18px;
  display: flex;
  align-items: center;
  height: 100%;
  z-index: 2;
  pointer-events: none;
}
.input-eye {
  position: absolute;
  right: 12px;
  color: #888;
  font-size: 18px;
  cursor: pointer;
  z-index: 3;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 0;
}
.form-box {
  min-width: 220px;
  position: relative;
  display: flex;
  align-items: center;
  background: #f6f6f6;
  border-radius: 10px;
  border: 1.5px solid #e0e0e0;
  padding: 0;
  min-height: 48px;
  margin-bottom: 0;
  box-sizing: border-box;
  transition: border 0.18s;
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
.org-edit-cancel {
  padding: 10px 20px;
  border-radius: 8px;
  background: #f0f0f0;
  color: #333;
  font-weight: 500;
  transition: background 0.2s;
  border: none;
}
.org-edit-cancel:hover {
  background: #e0e0e0;
}
</style>
