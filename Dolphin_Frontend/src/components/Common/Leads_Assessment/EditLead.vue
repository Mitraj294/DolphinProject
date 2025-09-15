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
                  v-model="form.organization_name"
                  icon="fas fa-cog"
                  placeholder="Organization Name"
                  required
                /><FormLabel
                  v-if="errors.organization_name"
                  class="error-message"
                  >{{ errors.organization_name[0] }}</FormLabel
                >
              </div>
              <div>
                <FormLabel>Organization Size</FormLabel>
                <FormDropdown
                  v-model="form.organization_size"
                  icon="fas fa-users"
                  :options="[
                    { value: null, text: 'Select', disabled: true },
                    {
                      value: '250+ Employees (Large)',
                      text: '250+ Employees (Large)',
                    },
                    {
                      value: '100-249 Employees (Medium)',
                      text: '100-249 Employees (Medium)',
                    },
                    {
                      value: '1-99 Employees (Small)',
                      text: '1-99 Employees (Small)',
                    },
                  ]"
                  required
                />
                <FormLabel
                  v-if="errors.organization_size"
                  class="error-message"
                  >{{ errors.organization_size[0] }}</FormLabel
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
        organization_name: '',
        organization_size: '',
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
    await this.fetchCountries();

    const leadId = this.getLeadId();
    if (leadId) {
      const success = await this.loadLeadFromApi(leadId);
      if (success) return;
    }

    this.loadLeadFromQuery();
  },

  methods: {
    //  Helper Methods
    getLeadId() {
      const q = this.$route.query;
      return this.$route.params.id || q.id || null;
    },

    async loadLeadFromApi(leadId) {
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');

        const res = await axios.get(`${API_BASE_URL}/api/leads/${leadId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        const payload = res.data || {};
        const leadObj = payload.lead || payload;

        if (!leadObj) return false;

        this.fillForm(leadObj);

        if (this.form.country_id) await this.fetchStates();
        if (this.form.state_id) await this.fetchCities();

        this.updatePageTitle(leadObj);
        return true;
      } catch (e) {
        console.error('Error fetching lead data:', e);
        return false;
      }
    },

    loadLeadFromQuery() {
      const q = this.$route.query;
      let lead = {};

      if (q.lead) {
        try {
          lead = typeof q.lead === 'string' ? JSON.parse(q.lead) : q.lead;
        } catch (e) {
          console.error('Failed to parse lead query param:', e);
        }
      }

      this.fillForm({
        first_name: q.first_name || q.contact?.split(' ')[0],
        last_name: q.last_name || q.contact?.split(' ')[1],
        email: q.email,
        phone: q.phone,
        find_us: q.source || q.find_us,
        organization_name: q.organization || q.organization_name,
        organization_size: q.size || q.organization_size,
        address: q.address || q.address_line,
        country_id: q.country_id,
        state_id: q.state_id,
        city_id: q.city_id,
        zip: q.zip,
        ...lead,
      });

      if (this.form.country_id) this.fetchStates();
      if (this.form.state_id) this.fetchCities();
    },

    fillForm(leadObj) {
      this.form = {
        first_name: leadObj.first_name || '',
        last_name: leadObj.last_name || '',
        email: leadObj.email || '',
        phone: leadObj.phone || '',
        find_us: leadObj.find_us || '',
        organization_name: leadObj.organization_name || '',
        organization_size: leadObj.organization_size || '',
        address: leadObj.address || leadObj.address_line || '',
        country_id: leadObj.country_id || null,
        state_id: leadObj.state_id || null,
        city_id: leadObj.city_id || null,
        zip: leadObj.zip || '',
      };
    },

    updatePageTitle(leadObj) {
      this.$nextTick(() => {
        const name = `${leadObj.first_name || ''} ${
          leadObj.last_name || ''
        }`.trim();
        this.$root?.$emit(
          'page-title-override',
          name ? `Edit Lead : ${name}` : 'Edit Lead'
        );
      });
    },
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
      this.resetMessages();
      this.loading = true;

      try {
        const token = this.getAuthToken();
        if (!token) return this.handleAuthError();

        const leadId = this.getLeadId();
        if (!leadId) return this.handleMissingId();

        const response = await this.updateLeadApi(leadId, token, this.form);
        this.handleSuccess(response.data);
      } catch (error) {
        this.handleError(error);
      } finally {
        this.loading = false;
      }
    },
    resetMessages() {
      this.successMessage = '';
      this.errorMessage = '';
      this.errors = {};
    },
    getAuthToken() {
      const storage = require('@/services/storage').default;
      return storage.get('authToken');
    },

    handleAuthError() {
      this.errorMessage = 'Authentication token not found. Please log in.';
      this.showToast('error', 'Authentication Error', this.errorMessage);
      this.loading = false;
    },

    handleMissingId() {
      this.errorMessage = 'Lead ID not found in route query.';
      this.showToast('error', 'Error', this.errorMessage);
      this.loading = false;
    },
    async updateLeadApi(leadId, token, payload) {
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      return axios.patch(`${API_BASE_URL}/api/leads/${leadId}`, payload, {
        headers: { Authorization: `Bearer ${token}` },
      });
    },
    handleSuccess(data) {
      this.successMessage = data.message || 'Lead updated successfully!';
      this.showToast('success', 'Success', this.successMessage);
      this.$router.push('/leads');
    },
    handleError(error) {
      console.error('Error updating lead:', error);

      if (error.response?.data) {
        const { message, errors } = error.response.data;

        this.errorMessage = message || 'Failed to update lead.';
        this.errors = errors || {};
      } else {
        this.errorMessage = 'An unexpected error occurred.';
      }

      this.showToast('error', 'Validation Error', this.errorMessage);
    },

    showToast(severity, summary, detail, life = 5000) {
      this.$toast.add({ severity, summary, detail, life });
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
  border-radius: 20px;
  border: 1px solid #ebebeb;
  box-sizing: border-box;
  overflow: visible;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 0 auto;
  padding: 24px 20px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 24px;
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
.error-message {
  color: red;
  font-size: 0.8em;
  margin-top: 10px;
}
</style>
