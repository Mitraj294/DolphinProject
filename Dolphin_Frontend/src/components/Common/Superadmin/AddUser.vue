<template>
  <Toast />
  <MainLayout>
    <div class="page">
      <div class="lead-capture-outer">
        <div class="lead-capture-card">
          <h3 class="lead-capture-card-title">Add User</h3>
          <form
            class="lead-capture-form"
            @submit.prevent="handleAddUser"
          >
            <FormRow>
              <div>
                <FormLabel>First Name</FormLabel>
                <FormInput
                  v-model="form.firstName"
                  icon="fas fa-user"
                  placeholder="Type here"
                  required
                />
                <FormLabel
                  v-if="errors.first_name"
                  class="error-message1"
                  >{{ errors.first_name[0] }}</FormLabel
                >
              </div>
              <div>
                <FormLabel>Last Name</FormLabel>
                <FormInput
                  v-model="form.lastName"
                  icon="fas fa-user"
                  placeholder="Type here"
                  required
                />
                <FormLabel
                  v-if="errors.last_name"
                  class="error-message1"
                  >{{ errors.last_name[0] }}</FormLabel
                >
              </div>
              <div>
                <FormLabel>Email</FormLabel>
                <FormInput
                  v-model="form.email"
                  icon="fas fa-envelope"
                  type="email"
                  placeholder="abc@gmail.com"
                  required
                />
                <div>
                  <FormLabel
                    v-if="errors.email"
                    class="error-message1"
                  >
                    {{
                      Array.isArray(errors.email)
                        ? errors.email[0]
                        : errors.email
                    }}
                  </FormLabel>
                </div>
              </div>
            </FormRow>
            <FormRow>
              <div>
                <FormLabel>Phone</FormLabel>
                <FormInput
                  v-model="form.phone"
                  icon="fas fa-phone"
                  placeholder="Type here"
                  required
                />
                <FormLabel
                  v-if="errors.phone"
                  class="error-message1"
                  >{{ errors.phone[0] }}</FormLabel
                >
              </div>
              <div>
                <FormLabel>Country</FormLabel>
                <FormDropdown
                  v-model="form.country_id"
                  icon="fas fa-globe"
                  @change="onCountryChange"
                  :options="[
                    { value: null, text: 'Select', disabled: true },
                    ...countries.map((c) => ({ value: c.id, text: c.name })),
                  ]"
                  required
                />
                <FormLabel
                  v-if="errors.country_id"
                  class="error-message1"
                  >{{ errors.country_id[0] }}</FormLabel
                >
              </div>
              <div>
                <FormLabel>Select User Role</FormLabel>
                <FormDropdown
                  v-model="form.role"
                  icon="fas fa-user-tag"
                  :options="[
                    { value: null, text: 'Select Role', disabled: true },
                    ...roleOptions,
                  ]"
                  required
                /><FormLabel
                  v-if="errors.role"
                  class="error-message1"
                  >{{ errors.role[0] }}</FormLabel
                >
              </div>
            </FormRow>
            <div v-if="form.role === 'organizationadmin'">
              <FormRow>
                <div>
                  <FormLabel>Organization Name</FormLabel>
                  <FormInput
                    v-model="form.organization_name"
                    icon="fas fa-cog"
                    placeholder="Organization Name"
                    required
                  />
                  <FormLabel
                    v-if="errors.organization_name"
                    class="error-message1"
                  >
                    {{ errors.organization_name[0] }}
                  </FormLabel>
                </div>
                <div>
                  <FormLabel>Organization Size</FormLabel>
                  <FormDropdown
                    v-model="organization_size"
                    icon="fas fa-users"
                    ref="orgSizeSelect"
                    :options="[
                      {
                        value: '',
                        text: 'Select Organization Size',
                        disabled: true,
                      },
                      ...orgSizeOptions.map((o) => ({ value: o, text: o })),
                    ]"
                    required
                  />
                  <FormLabel
                    v-if="errors.organization_size"
                    class="error-message1"
                  >
                    {{ errors.organization_size[0] }}
                  </FormLabel>
                </div>
                <div></div>
              </FormRow>
            </div>

            <div class="lead-capture-actions">
              <button
                type="button"
                class="org-edit-cancel"
                @click="$router.push('/user-permission')"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="org-edit-update"
                :disabled="loading"
              >
                {{ loading ? 'Adding...' : 'Add User' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import {
  FormRow,
  FormLabel,
  FormInput,
  FormDropdown,
  FormBox,
} from '@/components/Common/Common_UI/Form';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import { orgSizeOptions } from '@/utils/formUtils';
export default {
  name: 'AddUser',
  components: {
    MainLayout,
    FormRow,
    FormLabel,
    FormInput,
    FormDropdown,
    FormBox,

    Toast,
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      loading: false,
      form: {
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        country_id: null,
        role: null,
        organization_name: '',
        organization_size: '',
      },
      roleOptions: [
        { value: 'organizationadmin', text: 'Organization Admin' },
        { value: 'dolphinadmin', text: 'Dolphin Admin' },
        { value: 'salesperson', text: 'Sales Person' },
        { value: 'user', text: 'User' },
      ],
      countries: [],
      organization_size: '',
      orgSizeOptions: orgSizeOptions,

      successMessage: '',
      errorMessage: '',
      errors: {},
    };
  },
  mounted() {
    this.fetchCountries();
  },
  methods: {
    async fetchCountries() {
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const res = await axios.get(`${API_BASE_URL}/api/countries`);
        this.countries = res.data || [];
      } catch (e) {
        console.warn('Failed to fetch countries', e);
      }
    },
    onCountryChange(value) {
      // placeholder for handling country change if needed
    },
    async handleAddUser() {
      if (this.loading) return;
      this.loading = true;
      // clear previous field errors
      this.errors = {};

      try {
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

        const payload = {
          first_name: this.form.firstName,
          last_name: this.form.lastName,
          email: this.form.email,
          phone: this.form.phone,
          country_id: this.form.country_id,
          role: this.form.role,
          // send organization fields (backend uses required_if for validation)
          organization_name: this.form.organization_name,
          organization_size: this.form.organization_size,
        };

        const response = await axios.post(
          `${API_BASE_URL}/api/users`,
          payload,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'application/json',
            },
          }
        );

        // Show success message
        this.toast.add({
          severity: 'success',
          summary: 'User Added',
          detail: `New user has been created successfully. Password: ${response.data.password}`,
          life: 8000,
        });

        // Reset form
        this.form = {
          firstName: '',
          lastName: '',
          email: '',
          phone: '',
          role: null,
          organization_name: '',
          organization_size: '',
        };

        // Navigate back to user list
        this.$router.push('/user-permission');
      } catch (error) {
        console.error('Error adding user:', error);

        let errorMessage = 'Failed to add user.';
        if (error.response && error.response.data) {
          if (error.response.data.message) {
            errorMessage = error.response.data.message;
          }
          if (error.response.data.errors) {
            // If backend returned validation errors, attach them to `this.errors` so the template can show field labels
            this.errors = error.response.data.errors;
          } else {
            this.errors = {};
          }
        } else if (error.code === 'ERR_NETWORK') {
          errorMessage =
            'Network error - check if backend server is running and accessible';
        } else {
          errorMessage = error.message;
        }

        this.toast.add({
          severity: 'error',
          summary: 'Error',
          detail: errorMessage,
          life: 5000,
        });
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.lead-capture-outer {
  width: 100%;

  min-width: 260px;

  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  background: none !important;
  padding: 0;
}

.lead-capture-card {
  width: 100%;

  min-width: 240px;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 0 auto;
  box-sizing: border-box;
  padding: 32px 32px 24px 32px;
  display: flex;
  flex-direction: column;
  gap: 32px;
  position: relative;
}
@media (max-width: 600px) {
  .lead-capture-card {
    padding: 8px;
  }
}

.lead-capture-card-title {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 24px;
  text-align: left;
  width: 100%;
}

.lead-capture-form {
  width: 100%;
  min-width: 200px;
}

.lead-capture-actions {
  display: flex;
  justify-content: flex-end;
  gap: 18px;
  min-width: 240px;
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

.org-edit-actions {
  display: flex;
  justify-content: flex-end;
  gap: 18px;
  min-width: 240px;
}

.org-edit-cancel {
  background: #f5f5f5;
  color: #888;
  border: none;
  border-radius: 24px;
  padding: 10px 32px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.org-edit-cancel:hover {
  background: #e0e0e0;
}
.org-edit-update {
  background: #0074c2;
  color: #fff;
  border: none;
  border-radius: 24px;
  padding: 10px 32px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.org-edit-update:hover {
  background: #005fa3;
}

.error-message1 {
  color: red;
  font-size: 0.8em;
  margin-top: 8px;
}
</style>
