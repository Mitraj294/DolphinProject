<template>
  <MainLayout>
    <div class="page">
      <div class="lead-capture-outer">
        <div class="lead-capture-card">
          <h3 class="lead-capture-card-title">Edit Lead</h3>
          <form
            class="lead-capture-form"
            @submit.prevent="handleUpdateLead"
          >
            <FormRow>
              <div>
                <FormLabel>First Name</FormLabel>
                <FormInput
                  v-model="form.first_name"
                  icon="fas fa-user"
                  placeholder="Type here"
                  required
                />
                <FormLabel
                  v-if="errors.firstName"
                  class="error-message"
                  >{{ errors.firstName[0] }}</FormLabel
                >
              </div>
              <div>
                <FormLabel>Last Name</FormLabel>
                <FormInput
                  v-model="form.last_name"
                  icon="fas fa-user"
                  placeholder="Type here"
                  required
                />
                <FormLabel
                  v-if="errors.lastName"
                  class="error-message"
                  >{{ errors.lastName[0] }}</FormLabel
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
                /><FormLabel
                  v-if="errors.email"
                  class="error-message"
                  >{{ errors.email[0] }}</FormLabel
                >
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
                /><FormLabel
                  v-if="errors.phone"
                  class="error-message"
                  >{{ errors.phone[0] }}</FormLabel
                >
              </div>

              <div>
                <FormLabel>How did you find us?</FormLabel>
                <FormDropdown
                  v-model="form.find_us"
                  icon="fas fa-search"
                  :options="[
                    { value: null, text: 'Select', disabled: true },
                    { value: 'Google', text: 'Google' },
                    { value: 'Friend', text: 'Friend' },
                    { value: 'Colleague', text: 'Colleague' },
                    { value: 'Other', text: 'Other' },
                  ]"
                  required
                /><FormLabel
                  v-if="errors.find_us"
                  class="error-message"
                  >{{ errors.find_us[0] }}</FormLabel
                >
              </div>
              <div></div>
            </FormRow>
            <FormRow>
              <div>
                <FormLabel>Organization Name</FormLabel>
                <FormInput
                  v-model="form.org_name"
                  icon="fas fa-cog"
                  placeholder="Flexi-Finders"
                  required
                /><FormLabel
                  v-if="errors.org_name"
                  class="error-message"
                  >{{ errors.org_name[0] }}</FormLabel
                >
              </div>
              <div>
                <FormLabel>Organization Size</FormLabel>
                <FormDropdown
                  v-model="form.org_size"
                  icon="fas fa-users"
                  :options="[
                    { value: null, text: 'Select', disabled: true },
                    {
                      value: 'Large',
                      text: '250+ Employees (Large)',
                    },
                    {
                      value: 'Medium',
                      text: '100-249 Employees (Medium)',
                    },
                    {
                      value: 'Small',
                      text: '1-99 Employees (Small)',
                    },
                  ]"
                  required
                /><FormLabel
                  v-if="errors.org_size"
                  class="error-message"
                  >{{ errors.org_size[0] }}</FormLabel
                >
              </div>
              <div></div>
            </FormRow>
            <FormRow>
              <div>
                <FormLabel>Address </FormLabel>
                <FormInput
                  v-model="form.address"
                  icon="fas fa-map-marker-alt"
                  placeholder="Enter address"
                  required
                /><FormLabel
                  v-if="errors.address"
                  class="error-message"
                  >{{ errors.address[0] }}</FormLabel
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
                  class="error-message"
                  >{{ errors.country_id[0] }}</FormLabel
                >
              </div>
              <div>
                <FormLabel>State</FormLabel>
                <FormDropdown
                  v-model="form.state_id"
                  icon="fas fa-map-marker-alt"
                  @change="onStateChange"
                  :options="[
                    { value: null, text: 'Select', disabled: true },
                    ...states.map((s) => ({ value: s.id, text: s.name })),
                  ]"
                  required
                /><FormLabel
                  v-if="errors.state_id"
                  class="error-message"
                  >{{ errors.state_id[0] }}</FormLabel
                >
              </div>
            </FormRow>
            <FormRow>
              <div>
                <FormLabel>City</FormLabel>
                <FormDropdown
                  v-model="form.city_id"
                  icon="fas fa-map-marker-alt"
                  :options="[
                    { value: null, text: 'Select', disabled: true },
                    ...cities.map((city) => ({
                      value: city.id,
                      text: city.name,
                    })),
                  ]"
                  required
                /><FormLabel
                  v-if="errors.city_id"
                  class="error-message"
                  >{{ errors.city_id[0] }}</FormLabel
                >
              </div>
              <div>
                <FormLabel>Zip Code</FormLabel>
                <FormInput
                  v-model="form.zip"
                  icon="fas fa-map-marker-alt"
                  placeholder="Enter PIN code"
                  required
                /><FormLabel
                  v-if="errors.zip"
                  class="error-message"
                  >{{ errors.zip[0] }}</FormLabel
                >
              </div>
              <div></div>
            </FormRow>
            <div class="org-edit-actions">
              <button
                type="button"
                class="org-edit-cancel"
                @click="$router.push('/leads')"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="org-edit-update"
              >
                Update Lead
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
import axios from 'axios';

export default {
  name: 'EditLead',
  components: {
    MainLayout,
    FormRow,
    FormLabel,
    FormInput,
    FormDropdown,
    FormBox,
  },
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        find_us: '',
        org_name: '',
        org_size: '',
        address: '',
        country_id: null,
        state_id: null,
        city_id: null,
        zip: '',
      },
      countries: [],
      states: [],
      cities: [],
      loading: false,
      successMessage: '',
      errorMessage: '',
      errors: {},
    };
  },
  async created() {
    // Fetch countries on mount
    await this.fetchCountries();
    // Prefill form from backend if id is present in params or query
    const q = this.$route.query;
    const leadId = this.$route.params.id || q.id;
    if (leadId) {
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');
        const res = await axios.get(`${API_BASE_URL}/api/leads/${leadId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        const payload = res.data || null;
        const leadObj = payload && payload.lead ? payload.lead : payload;
        if (leadObj) {
          this.form.first_name = leadObj.first_name || '';
          this.form.last_name = leadObj.last_name || '';
          this.form.email = leadObj.email || '';
          this.form.phone = leadObj.phone || '';
          this.form.find_us = leadObj.find_us || '';
          this.form.org_name = leadObj.org_name || '';
          this.form.org_size = leadObj.org_size || '';
          this.form.address = leadObj.address || '';
          this.form.country_id = leadObj.country_id || null;
          this.form.state_id = leadObj.state_id || null;
          this.form.city_id = leadObj.city_id || null;
          this.form.zip = leadObj.zip || '';
          // Fetch states and cities for selected country/state
          if (this.form.country_id) await this.fetchStates();
          if (this.form.state_id) await this.fetchCities();
          // set navbar override
          this.$nextTick(() => {
            const name = `${this.form.first_name || ''} ${
              this.form.last_name || ''
            }`.trim();
            if (this.$root && this.$root.$emit) {
              this.$root.$emit(
                'page-title-override',
                name ? `Edit Lead : ${name}` : 'Edit Lead'
              );
            }
          });
          return;
        }
      } catch (e) {
        // fallback to query params
      }
    }
    // fallback to query params if no id or fetch failed
    const lead = q.lead
      ? typeof q.lead === 'string'
        ? JSON.parse(q.lead)
        : q.lead
      : {};
    this.form.first_name =
      q.first_name || q.contact?.split(' ')[0] || lead.first_name || '';
    this.form.last_name =
      q.last_name || q.contact?.split(' ')[1] || lead.last_name || '';
    this.form.email = q.email || lead.email || '';
    this.form.phone = q.phone || lead.phone || '';
    this.form.find_us = q.source || q.find_us || lead.find_us || '';
    this.form.org_name = q.organization || q.org_name || lead.org_name || '';
    this.form.org_size = q.size || q.org_size || lead.org_size || '';
    this.form.address =
      q.address || q.address_line || lead.address || lead.address_line || '';
    this.form.country_id = q.country_id || lead.country_id || null;
    this.form.state_id = q.state_id || lead.state_id || null;
    this.form.city_id = q.city_id || lead.city_id || null;
    this.form.zip = q.zip || lead.zip || '';
    if (this.form.country_id) await this.fetchStates();
    if (this.form.state_id) await this.fetchCities();
  },
  methods: {
    async fetchCountries() {
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      const res = await axios.get(`${API_BASE_URL}/api/countries`);
      this.countries = res.data;
    },
    async fetchStates() {
      if (!this.form.country_id) {
        this.states = [];
        return;
      }
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      const res = await axios.get(
        `${API_BASE_URL}/api/states?country_id=${this.form.country_id}`
      );
      this.states = res.data;
    },
    async fetchCities() {
      if (!this.form.state_id) {
        this.cities = [];
        return;
      }
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      const res = await axios.get(
        `${API_BASE_URL}/api/cities?state_id=${this.form.state_id}`
      );
      this.cities = res.data;
    },
    onCountryChange() {
      let val = this.form.country_id;
      if (val !== null && val !== '' && typeof val !== 'number') {
        const num = Number(val);
        if (!isNaN(num)) {
          this.form.country_id = num;
          val = num;
        }
      }
      if (val && typeof val === 'number') {
        this.form.state_id = null;
        this.form.city_id = null;
        this.states = [];
        this.cities = [];
        this.fetchStates();
      } else {
        this.form.state_id = null;
        this.form.city_id = null;
        this.states = [];
        this.cities = [];
      }
    },
    onStateChange() {
      let val = this.form.state_id;
      if (val !== null && val !== '' && typeof val !== 'number') {
        const num = Number(val);
        if (!isNaN(num)) {
          this.form.state_id = num;
          val = num;
        }
      }
      if (val && typeof val === 'number') {
        this.form.city_id = null;
        this.cities = [];
        this.fetchCities();
      } else {
        this.form.city_id = null;
        this.cities = [];
      }
    },
    async handleUpdateLead() {
      this.loading = true;
      this.successMessage = '';
      this.errorMessage = '';
      try {
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');
        if (!token) {
          this.errorMessage = 'Authentication token not found. Please log in.';
          this.loading = false;
          // Show error toast
          this.$toast.add({
            severity: 'error',
            summary: 'Authentication Error',
            detail: this.errorMessage,
            life: 5000,
          });
          return;
        }
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

        const leadId = this.$route.params.id || this.$route.query.id;
        if (!leadId) {
          this.errorMessage = 'Lead ID not found in route query.';
          this.loading = false;
          // Show error toast
          this.$toast.add({
            severity: 'error',
            summary: 'Error',
            detail: this.errorMessage,
            life: 5000,
          });
          return;
        }

        const payload = { ...this.form };

        const response = await axios.patch(
          `${API_BASE_URL}/api/leads/${leadId}`,
          payload,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );

        this.successMessage =
          response.data.message || 'Lead updated successfully!';
        // Use PrimeVue Toast for success notification
        this.$toast.add({
          severity: 'success',
          summary: 'Success',
          detail: this.successMessage,
          life: 3000,
        });
        this.$router.push('/leads'); // Redirect back to leads list
      } catch (error) {
        console.error('Error updating lead:', error);
        if (error.response) {
          this.errors = error.response.data.errors;
          this.$toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Please correct the highlighted errors.',
            life: 5000,
          });
          if (error.response && error.response.data) {
            this.errorMessage =
              error.response.data.message || 'Failed to update lead.';
            if (error.response.data.errors) {
              // Concatenate specific validation errors
              for (const key in error.response.data.errors) {
                this.errorMessage;
              }
            }
          }
        } else {
          this.errorMessage = 'An unexpected error occurred.';
        }
        // Use PrimeVue Toast for error notification
        this.$toast.add({
          severity: 'error',
          summary: 'Error',
          detail: this.errorMessage,
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
  max-width: 1400px;
  min-width: 0;
  margin: 64px auto 64px auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  background: none !important;
  padding: 0;
}

.lead-capture-card {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
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

.lead-capture-card-title {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 24px;
  text-align: left;
  width: 100%;
}

.lead-capture-form {
  width: 100%;
}

.lead-capture-actions {
  display: flex;
  justify-content: flex-end;
  gap: 18px;
}

/* Responsive styles to match other pages */
@media (max-width: 1400px) {
  .lead-capture-outer {
    margin: 12px;
    max-width: 100%;
  }
  .lead-capture-card {
    max-width: 100%;
    border-radius: 14px;
    padding: 18px 8px 12px 8px;
  }
}

@media (max-width: 900px) {
  .lead-capture-outer {
    margin: 4px;
    max-width: 100%;
  }
  .lead-capture-card {
    padding: 8px 2vw 8px 2vw;
    border-radius: 10px;
  }
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
.error-message {
  color: red;
  font-size: 0.8em;
  margin-left: 8px;
}
</style>
