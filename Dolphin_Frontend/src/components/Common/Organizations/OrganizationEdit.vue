<template>
  <MainLayout>
    <div class="page">
      <div class="lead-capture-outer">
        <div class="lead-capture-card">
          <div
            class="org-detail-main-card-header-title"
            style="
              font-family: 'Helvetica Neue LT Std', Helvetica, Arial, sans-serif;
              font-weight: 600;
              font-size: 24px;
              color: #222;
            "
          >
            Edit Organization : {{ form.organization_name }}
          </div>

          <form
            class="lead-capture-form"
            @submit.prevent="updateDetails"
          >
            <FormRow>
              <div>
                <FormLabel>Organization Name</FormLabel>
                <FormInput
                  v-model="form.organization_name"
                  icon="fas fa-cog"
                  placeholder="Organization Name"
                />
                <FormLabel
                  v-if="errors.organization_name"
                  class="error-message"
                >
                  {{ errors.organization_name[0] }}
                </FormLabel>
              </div>
              <div>
                <FormLabel>Organization Size</FormLabel>
                <FormDropdown
                  v-model="form.organization_size"
                  icon="fas fa-users"
                  :options="[
                    {
                      value: '',
                      text: 'Select ',
                      disabled: true,
                    },
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
                />
                <FormLabel
                  v-if="errors.organization_size"
                  class="error-message"
                >
                  {{ errors.organization_size[0] }}
                </FormLabel>
              </div>
              <div>
                <FormLabel>Source</FormLabel>
                <FormDropdown
                  v-model="form.source"
                  icon="fas fa-bullhorn"
                  :options="[
                    { value: '', text: 'Select', disabled: true },
                    { value: 'Google', text: 'Google' },
                    { value: 'Friend', text: 'Friend' },
                    { value: 'Colleague', text: 'Colleague' },
                    { value: 'Other', text: 'Other' },
                  ]"
                />
                <FormLabel
                  v-if="errors.source"
                  class="error-message"
                >
                  {{ errors.source[0] }}
                </FormLabel>
              </div>
            </FormRow>
            <FormRow>
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
                />
                <FormLabel
                  v-if="errors.country_id"
                  class="error-message"
                >
                  {{ errors.country_id[0] }}
                </FormLabel>
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
                />
                <FormLabel
                  v-if="errors.state_id"
                  class="error-message"
                >
                  {{ errors.state_id[0] }}
                </FormLabel>
              </div>
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
                />
                <FormLabel
                  v-if="errors.city_id"
                  class="error-message"
                >
                  {{ errors.city_id[0] }}
                </FormLabel>
              </div>
            </FormRow>
            <FormRow>
              <div>
                <FormLabel>Address</FormLabel>
                <FormInput
                  v-model="form.address"
                  icon="fas fa-map-marker-alt"
                  placeholder="Enter address"
                />
                <FormLabel
                  v-if="errors.address"
                  class="error-message"
                >
                  {{ errors.address[0] }}
                </FormLabel>
              </div>

              <div>
                <FormLabel>PIN</FormLabel>
                <FormInput
                  v-model="form.zip"
                  icon="fas fa-map-pin"
                  placeholder="Enter PIN code"
                />
                <FormLabel
                  v-if="errors.zip"
                  class="error-message"
                >
                  {{ errors.zip[0] }}
                </FormLabel>
              </div>
              <div><FormLabel>&nbsp;</FormLabel></div>
            </FormRow>

            <!-- Contract dates row: use 3 columns, last is empty -->
            <FormRow>
              <div>
                <FormLabel>Contract Start</FormLabel>
                <div
                  class="disabled-clickable"
                  @click="onDisabledClick"
                >
                  <FormInput
                    v-model="form.contractStart"
                    icon="fas fa-calendar-alt"
                    disabled
                  />
                </div>
              </div>
              <div>
                <FormLabel>Contract End</FormLabel>
                <div
                  class="disabled-clickable"
                  @click="onDisabledClick"
                >
                  <FormInput
                    v-model="form.contractEnd"
                    icon="fas fa-calendar-alt"
                    disabled
                  />
                </div>
              </div>
              <div>
                <FormLabel>Last Contacted</FormLabel>
                <div
                  class="disabled-clickable"
                  @click="onDisabledClick"
                >
                  <FormInput
                    v-model="form.lastContacted"
                    icon="fas fa-history"
                    disabled
                    placeholder="Last contacted date"
                  />
                </div>
              </div>
            </FormRow>
            <!-- Divider line -->
            <div class="org-edit-divider"></div>
            <!-- Admin/contact section: 2 rows of 3 fields -->
            <FormRow>
              <div>
                <FormLabel>First Name</FormLabel>
                <FormInput
                  v-model="form.firstName"
                  icon="fas fa-user"
                  placeholder="Enter first name"
                />
                <FormLabel
                  v-if="errors.first_name"
                  class="error-message"
                >
                  {{ errors.first_name[0] }}
                </FormLabel>
              </div>
              <div>
                <FormLabel>Last Name</FormLabel>
                <FormInput
                  v-model="form.lastName"
                  icon="fas fa-user"
                  placeholder="Enter last name"
                />
                <FormLabel
                  v-if="errors.last_name"
                  class="error-message"
                >
                  {{ errors.last_name[0] }}
                </FormLabel>
              </div>
              <div>
                <FormLabel>Admin Email</FormLabel>
                <FormInput
                  v-model="form.adminEmail"
                  icon="fas fa-envelope"
                  type="email"
                  placeholder="Admin email"
                />
                <FormLabel
                  v-if="errors.admin_email"
                  class="error-message"
                >
                  {{ errors.admin_email[0] }}
                </FormLabel>
              </div>
            </FormRow>
            <FormRow>
              <div>
                <FormLabel>Admin Phone</FormLabel>
                <FormInput
                  v-model="form.adminPhone"
                  icon="fas fa-phone"
                  placeholder="Admin phone"
                />
                <FormLabel
                  v-if="errors.admin_phone"
                  class="error-message"
                >
                  {{ errors.admin_phone[0] }}
                </FormLabel>
              </div>
              <div>
                <FormLabel>Sales Person</FormLabel>
                <FormDropdown
                  v-model="form.sales_person_id"
                  icon="fas fa-user-tie"
                  :options="[
                    { value: null, text: 'Select', disabled: true },
                    ...salesPersons.map((u) => ({
                      value: u.id,
                      text: u.name || u.email,
                    })),
                  ]"
                />
                <FormLabel
                  v-if="errors.sales_person_id"
                  class="error-message"
                >
                  {{ errors.sales_person_id[0] }}
                </FormLabel>
              </div>
              <div>
                <FormLabel>Certified Staff</FormLabel>

                <FormInput
                  :value="form.certifiedStaff"
                  icon="fas fa-users"
                  type="number"
                  disabled
                />
                <FormLabel
                  v-if="errors.certified_staff"
                  class="error-message"
                >
                  {{ errors.certified_staff[0] }}
                </FormLabel>
              </div>
            </FormRow>
            <div class="org-edit-actions">
              <button
                type="button"
                class="org-edit-cancel"
                @click="$router.back()"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="org-edit-update"
              >
                Update Details
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
} from '@/components/Common/Common_UI/Form';
import axios from 'axios';
import storage from '@/services/storage.js';

const API_BASE_URL =
  process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';

//  Utility: API Helpers

function getAuthHeaders() {
  const authToken = storage.get('authToken');
  return authToken ? { Authorization: `Bearer ${authToken}` } : {};
}

/**
 *  Utility: Date Helpers
 */
function formatContractDate(input) {
  try {
    const d = new Date(input);
    if (isNaN(d.getTime())) return input;

    const day = String(d.getDate()).padStart(2, '0');
    const monthShort = d
      .toLocaleString('en-GB', { month: 'short' })
      .toUpperCase();
    const year = d.getFullYear();
    return `${day} ${monthShort},${year}`;
  } catch {
    return input;
  }
}

function formatLastContacted(input) {
  try {
    const d = new Date(input);
    if (isNaN(d.getTime())) return input;

    const day = String(d.getDate()).padStart(2, '0');
    const monthShort = d
      .toLocaleString('en-GB', { month: 'short' })
      .toUpperCase();
    const year = d.getFullYear();
    let hours = d.getHours();
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;
    return `${day} ${monthShort},${year} ${hours}:${minutes} ${ampm}`;
  } catch {
    return input;
  }
}

function parseContractInput(input) {
  if (!input) return null;
  try {
    const d = new Date(input);
    if (isNaN(d.getTime())) return null;
    return d.toISOString().slice(0, 19).replace('T', ' ');
  } catch {
    return null;
  }
}

function parseLastContactedInput(input) {
  if (!input) return null;
  try {
    // Use UTC conversion to ensure difference from parseContractInput
    const d = new Date(input);
    if (isNaN(d.getTime())) return null;
    // Build YYYY-MM-DD HH:MM:SS in UTC
    const y = d.getUTCFullYear();
    const m = String(d.getUTCMonth() + 1).padStart(2, '0');
    const day = String(d.getUTCDate()).padStart(2, '0');
    const hh = String(d.getUTCHours()).padStart(2, '0');
    const mm = String(d.getUTCMinutes()).padStart(2, '0');
    const ss = String(d.getUTCSeconds()).padStart(2, '0');
    return `${y}-${m}-${day} ${hh}:${mm}:${ss}`;
  } catch {
    return null;
  }
}

/**
 *  Utility: Mapping Backend Data → Form
 */
function mapOrganizationToForm(found) {
  const user = found.user || {};
  const userDetails = user.user_details || {};

  return {
    organization_name: found.organization_name || '',
    organization_size: found.organization_size || '',
    source: found.source || found.find_us || userDetails.find_us || '',
    address: found.address || userDetails.address || '',
    zip: found.zip || userDetails.zip || '',
    country_id: found.country_id || userDetails.country_id || null,
    state_id: found.state_id || userDetails.state_id || null,
    city_id: found.city_id || userDetails.city_id || null,
    contractStart: found.contract_start
      ? formatContractDate(found.contract_start)
      : '',
    contractEnd: found.contract_end
      ? formatContractDate(found.contract_end)
      : '',
    firstName:
      user.first_name ||
      found.first_name ||
      (found.main_contact || '').split(' ')[0] ||
      '',
    lastName:
      user.last_name ||
      found.last_name ||
      (found.main_contact || '').split(' ').slice(1).join(' ') ||
      '',
    adminEmail: user.email || found.admin_email || '',
    adminPhone: userDetails.phone || found.admin_phone || '',
    sales_person_id: found.sales_person_id || null,
    lastContacted: found.last_contacted
      ? formatLastContacted(found.last_contacted)
      : '',
    certifiedStaff: found.certified_staff || userDetails.certified_staff || '0',
  };
}

function mapFormToPayload(form) {
  return {
    organization_name: form.organization_name,
    organization_size: form.organization_size || null,
    source: form.source || null,
    address: form.address || null,
    city_id: form.city_id || null,
    state_id: form.state_id || null,
    zip: form.zip || null,
    country_id: form.country_id || null,
    sales_person_id: form.sales_person_id || null,
    certified_staff: form.certifiedStaff || null,
    first_name: form.firstName || null,
    last_name: form.lastName || null,
    admin_email: form.adminEmail || null,
    admin_phone: form.adminPhone || null,
    main_contact: `${form.firstName} ${form.lastName}`.trim(),
    contract_start: parseContractInput(form.contractStart),
    contract_end: parseContractInput(form.contractEnd),
    last_contacted: parseLastContactedInput(form.lastContacted),
  };
}

export default {
  name: 'OrganizationEdit',
  components: { MainLayout, FormRow, FormLabel, FormInput, FormDropdown },

  data() {
    return {
      form: {
        organization_name: '',
        organization_size: '',
        source: '',
        address: '',
        zip: '',
        country_id: null,
        state_id: null,
        city_id: null,
        contractStart: '',
        contractEnd: '',
        firstName: '',
        lastName: '',
        adminEmail: '',
        adminPhone: '',
        sales_person_id: null,
        lastContacted: '',
        certifiedStaff: '',
      },
      errors: {},
      orgId: null,
      countries: [],
      states: [],
      cities: [],
      salesPersons: [],
    };
  },

  async mounted() {
    await this.fetchCountries();
    await this.fetchOrganization();

    // page title
    this.$nextTick(() => {
      const name =
        this.form.organization_name ||
        this.$route.params.organization_name ||
        '';
      if (this.$root && this.$root.$emit) {
        this.$root.$emit(
          'page-title-override',
          name ? `Edit organization : ${name}` : 'Edit organization'
        );
      }
    });
  },

  methods: {
    async fetchOrganization() {
      try {
        const orgId = this.$route.params.id;
        if (!orgId) return;

        const res = await axios.get(
          `${API_BASE_URL}/api/organizations/${orgId}`,
          {
            headers: getAuthHeaders(),
          }
        );

        if (res.data) {
          this.orgId = res.data.id;
          this.form = mapOrganizationToForm(res.data);

          if (this.form.country_id) await this.fetchStates();
          if (this.form.state_id) await this.fetchCities();

          if (res.data.sales_person) {
            this.salesPersons = [
              {
                id: res.data.sales_person_id || null,
                name: res.data.sales_person,
              },
            ];
          } else {
            await this.fetchSalesPersons();
          }
        }
      } catch (e) {
        console.error('Failed to fetch organization details', e);
      }
    },

    async updateDetails() {
      try {
        // clear previous errors
        this.errors = {};
        if (!this.orgId) {
          this.$toast?.add({
            severity: 'error',
            summary: 'Update Failed',
            detail: 'Organization not found.',
            life: 3500,
          });
          return;
        }

        const payload = mapFormToPayload(this.form);

        await axios.put(
          `${API_BASE_URL}/api/organizations/${this.orgId}`,
          payload,
          {
            headers: getAuthHeaders(),
          }
        );

        this.$toast?.add({
          severity: 'success',
          summary: 'Updated',
          detail: 'Organization updated successfully.',
          life: 3500,
        });

        this.$root?.$emit('page-title-override', null);
        this.$router.push(`/organizations/${this.orgId}`);
      } catch (e) {
        console.error('Failed to update organization', e);
        // If validation errors from backend (Laravel returns 422 with errors object)
        if (e.response && e.response.status === 422 && e.response.data) {
          this.errors = e.response.data.errors || {};
          // show first validation message in toast as summary
          const firstField = Object.keys(this.errors)[0];
          const firstMsg = this.errors[firstField]
            ? this.errors[firstField][0]
            : 'Validation failed';
          this.$toast?.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: firstMsg,
            life: 6000,
          });
          return;
        }

        this.$toast?.add({
          severity: 'error',
          summary: 'Update Failed',
          detail: 'Failed to update organization.',
          life: 4500,
        });
      }
    },

    // helper to read error text for a given field name (accepts camelCase or snake_case)
    errorText(field) {
      if (!this.errors) return null;
      // try snake_case
      const snake = field.replace(/[A-Z]/g, (m) => `_${m.toLowerCase()}`);
      if (this.errors[snake] && this.errors[snake].length)
        return this.errors[snake][0];
      if (this.errors[field] && this.errors[field].length)
        return this.errors[field][0];
      return null;
    },

    async fetchCountries() {
      const res = await axios.get(`${API_BASE_URL}/api/countries`);
      this.countries = res.data;
    },

    async fetchStates() {
      if (!this.form.country_id) {
        this.states = [];
        return;
      }
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
      const res = await axios.get(
        `${API_BASE_URL}/api/cities?state_id=${this.form.state_id}`
      );
      this.cities = res.data;
    },

    async fetchSalesPersons() {
      try {
        const res = await axios.get(`${API_BASE_URL}/api/users`, {
          headers: getAuthHeaders(),
        });
        const users = (res.data && res.data.users) || [];
        this.salesPersons = users.filter((u) =>
          (u.roles || []).some((r) => r.name === 'salesperson')
        );
      } catch (e) {
        console.warn('Failed to fetch sales persons', e);
        this.salesPersons = [];
      }
    },

    onCountryChange() {
      this.form.state_id = null;
      this.form.city_id = null;
      this.states = [];
      this.cities = [];
      this.fetchStates();
    },

    onStateChange() {
      this.form.city_id = null;
      this.cities = [];
      this.fetchCities();
    },
  },
};
</script>

<style scoped>
/* Remove specific cancel button margin only on this page */
.lead-capture-actions > .btn.btn-secondary {
  margin-right: 0 !important;
}
</style>
<style scoped>
/* Using lead-capture classes for consistency */
.lead-capture-outer {
  width: 100%;

  min-width: 0;

  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  background: none !important;
  padding: 0;
}

.lead-capture-card {
  width: 100%;

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

.org-edit-divider {
  width: 100%;
  border-bottom: 1.5px solid #e0e0e0;
  margin: 24px 0 24px 0;
}

.lead-capture-actions {
  display: flex;
  justify-content: flex-end;
  gap: 18px;
}

/* Ensure form-box inside child form components has no left/right padding for this page */
:deep(.form-box) {
  padding-left: 0 !important;
  padding-right: 0 !important;
  position: relative !important;
}

/* Slightly nudge the input icon so it sits inside the input area */
:deep(.form-input-icon) {
  left: 12px !important;
}

/* Slightly darker disabled input text for better contrast on this page */
:deep(.form-input:disabled),
:deep(.form-input[disabled]) {
  color: #6b6b6b !important; /* slightly light from black */
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
/* Responsive styles to match other pages */

.org-detail-main-card-header-title {
  flex: 1 1 60%;
  display: flex;
  justify-content: flex-start;
  align-items: center;
  margin: 0;
}

.error-message {
  color: #d32f2f; /* red */
  font-size: 0.8em;
  margin-top: 6px;
  display: block;
}
</style>
