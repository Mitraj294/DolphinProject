<template>
  <MainLayout>
    <div class="page">
      <div class="lead-capture-outer">
        <div class="lead-capture-card">
          <h2 class="lead-capture-card-title">Edit Organization</h2>
          <form
            class="lead-capture-form"
            @submit.prevent="updateDetails"
          >
            <FormRow>
              <div>
                <FormLabel>Organization Name</FormLabel>
                <FormInput
                  v-model="form.orgName"
                  icon="fas fa-cog"
                  placeholder="Flexi-Finders"
                />
              </div>
              <div>
                <FormLabel>Organization Size</FormLabel>
                <FormDropdown
                  v-model="form.orgSize"
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
              </div>

              <div>
                <FormLabel>PIN</FormLabel>
                <FormInput
                  v-model="form.zip"
                  icon="fas fa-map-pin"
                  placeholder="Enter PIN code"
                />
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
              </div>
              <div>
                <FormLabel>Last Name</FormLabel>
                <FormInput
                  v-model="form.lastName"
                  icon="fas fa-user"
                  placeholder="Enter last name"
                />
              </div>
              <div>
                <FormLabel>Admin Email</FormLabel>
                <FormInput
                  v-model="form.adminEmail"
                  icon="fas fa-envelope"
                  type="email"
                  placeholder="Admin email"
                />
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
              </div>
              <div>
                <FormLabel>Sales Person</FormLabel>
                <FormInput
                  v-model="form.salesPerson"
                  icon="fas fa-user-tie"
                  placeholder="Enter sales person name"
                />
              </div>
              <div>
                <FormLabel>Certified Staff</FormLabel>
                <FormInput
                  v-model="form.certifiedStaff"
                  icon="fas fa-users"
                  type="number"
                  placeholder="Enter number of certified staff"
                />
              </div>
            </FormRow>
            <div class="org-edit-actions">
              <button
                type="button"
                class="org-edit-cancel"
                @click="cancelEdit"
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

export default {
  name: 'OrganizationEdit',
  components: { MainLayout, FormRow, FormLabel, FormInput, FormDropdown },
  data() {
    return {
      form: {
        orgName: '',
        orgSize: '',
        source: '',
        address: '',
        zip: '',
        // Location IDs
        country_id: null,
        state_id: null,
        city_id: null,
        // Other fields
        contractStart: '',
        contractEnd: '',
        firstName: '',
        lastName: '',
        adminEmail: '',
        adminPhone: '',
        salesPerson: '',
        lastContacted: '',
        certifiedStaff: '',
      },
      orgId: null,
      countries: [],
      states: [],
      cities: [],
    };
  },
  mounted() {
    this.fetchCountries();
    this.fetchOrganization();
    // inform navbar of page title when loaded
    this.$nextTick(() => {
      const name = this.form.orgName || this.$route.params.orgName || '';
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
        const authToken = storage.get('authToken');
        const headers = authToken
          ? { Authorization: `Bearer ${authToken}` }
          : {};
        const orgId = this.$route.params.id;
        if (!orgId) return;

        const res = await axios.get(
          `http://127.0.0.1:8000/api/organizations/${orgId}`,
          { headers }
        );

        const found = res.data;
        if (found) {
          // Prefer user object if it exists, otherwise fallback to org fields
          const user = found.user || {};
          const userDetails = user.user_details || {};

          const fName = user.first_name || found.first_name || '';
          const lName = user.last_name || found.last_name || '';

          // Normalize org size to dropdown values ('Large','Medium','Small')
          const rawOrgSize = found.org_size || userDetails.org_size || '';
          let normalizedOrgSize = '';
          if (/large/i.test(rawOrgSize)) {
            normalizedOrgSize = 'Large';
          } else if (/medium/i.test(rawOrgSize)) {
            normalizedOrgSize = 'Medium';
          } else if (
            /small/i.test(rawOrgSize) ||
            /1-99|250\+/.test(rawOrgSize)
          ) {
            normalizedOrgSize = 'Small';
          }

          // Prefer explicit source field, fallback to find_us on org or user_details
          const sourceVal =
            found.source || found.find_us || userDetails.find_us || '';

          // Prefer org-level location ids, otherwise use user_details values
          const countryId = found.country_id || userDetails.country_id || null;
          const stateId = found.state_id || userDetails.state_id || null;
          const cityId = found.city_id || userDetails.city_id || null;

          this.orgId = found.id; // Save org id for update
          this.form = {
            orgName: found.org_name || '',
            orgSize: normalizedOrgSize || '',
            source: sourceVal || '',
            address: found.address || userDetails.address || '',
            zip: found.zip || userDetails.zip || '',
            country_id: countryId,
            state_id: stateId,
            city_id: cityId,
            contractStart: found.contract_start
              ? this.formatContractDate(found.contract_start)
              : '',
            contractEnd: found.contract_end
              ? this.formatContractDate(found.contract_end)
              : '',
            firstName: fName || (found.main_contact || '').split(' ')[0] || '',
            lastName:
              lName ||
              (found.main_contact || '').split(' ').slice(1).join(' ') ||
              '',
            adminEmail: user.email || found.admin_email || '',
            adminPhone: userDetails.phone || found.admin_phone || '',
            salesPerson: found.sales_person || '',
            lastContacted: found.last_contacted
              ? this.formatLastContacted(found.last_contacted)
              : '',
            certifiedStaff:
              found.certified_staff || userDetails.certified_staff || '',
          };
          if (this.form.country_id) await this.fetchStates();
          if (this.form.state_id) await this.fetchCities();
        }
      } catch (e) {
        // fallback: leave form as is
        console.error('Failed to fetch organization details', e);
      }
    },
    formatContractDate(input) {
      // expected input: ISO timestamp string
      try {
        // Parse DB datetimes as UTC when in SQL format, else fallback
        const m = String(input).match(
          /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/
        );
        let d;
        if (m) {
          const Y = parseInt(m[1], 10);
          const Mo = parseInt(m[2], 10) - 1;
          const D = parseInt(m[3], 10);
          const hh = m[4] ? parseInt(m[4], 10) : 0;
          const mm = m[5] ? parseInt(m[5], 10) : 0;
          const ss = m[6] ? parseInt(m[6], 10) : 0;
          d = new Date(Date.UTC(Y, Mo, D, hh, mm, ss));
        } else {
          d = new Date(input);
        }
        const day = String(d.getDate()).padStart(2, '0');
        const monthShort = d
          .toLocaleString('en-GB', { month: 'short' })
          .toUpperCase();
        const year = d.getFullYear();
        return `${day} ${monthShort},${year}`;
      } catch (e) {
        return input;
      }
    },
    formatLastContacted(input) {
      try {
        // Parse DB datetimes as UTC when in SQL format
        const m = String(input).match(
          /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/
        );
        let d;
        if (m) {
          const Y = parseInt(m[1], 10);
          const Mo = parseInt(m[2], 10) - 1;
          const D = parseInt(m[3], 10);
          const hh = m[4] ? parseInt(m[4], 10) : 0;
          const mm = m[5] ? parseInt(m[5], 10) : 0;
          const ss = m[6] ? parseInt(m[6], 10) : 0;
          d = new Date(Date.UTC(Y, Mo, D, hh, mm, ss));
        } else {
          d = new Date(input);
        }
        const day = String(d.getDate()).padStart(2, '0');
        const monthShort = d
          .toLocaleString('en-GB', { month: 'short' })
          .toUpperCase();
        const year = d.getFullYear();
        let hours = d.getHours();
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 -> 12
        return `${day} ${monthShort},${year} ${hours}:${minutes} ${ampm}`;
      } catch (e) {
        return input;
      }
    },
    // Parse human-friendly contract date (e.g. "05 AUG,2025") to ISO string
    parseContractInput(input) {
      if (!input) return null;
      try {
        const cleaned = String(input).replace(/,/g, '').replace(/\s+/g, ' ');
        const parts = cleaned.split(' '); // [day, MON, YYYY]
        let d = null;
        if (parts.length >= 3) {
          const dateStr = `${parts[0]} ${parts[1]} ${parts[2]}`;
          d = new Date(dateStr);
        }
        if (!d || isNaN(d.getTime())) {
          d = new Date(input);
        }
        if (isNaN(d.getTime())) return null;
        // Return MySQL DATETIME in UTC: 'YYYY-MM-DD HH:MM:SS'
        const Y = d.getUTCFullYear();
        const M = String(d.getUTCMonth() + 1).padStart(2, '0');
        const D = String(d.getUTCDate()).padStart(2, '0');
        const h = String(d.getUTCHours()).padStart(2, '0');
        const m = String(d.getUTCMinutes()).padStart(2, '0');
        const s = String(d.getUTCSeconds()).padStart(2, '0');
        return `${Y}-${M}-${D} ${h}:${m}:${s}`;
      } catch (e) {
        return null;
      }
    },
    // Parse lastContacted like "22 AUG,2025 9:42 AM" to ISO
    parseLastContactedInput(input) {
      if (!input) return null;
      try {
        const s = String(input).replace(/,/g, ' ');
        const d = new Date(s);
        if (isNaN(d.getTime())) return null;
        const Y = d.getUTCFullYear();
        const M = String(d.getUTCMonth() + 1).padStart(2, '0');
        const D = String(d.getUTCDate()).padStart(2, '0');
        const h = String(d.getUTCHours()).padStart(2, '0');
        const m = String(d.getUTCMinutes()).padStart(2, '0');
        const s2 = String(d.getUTCSeconds()).padStart(2, '0');
        return `${Y}-${M}-${D} ${h}:${m}:${s2}`;
      } catch (e) {
        return null;
      }
    },
    onDisabledClick() {
      const msg = 'This field is not editable.';
      if (this.$toast && typeof this.$toast.add === 'function') {
        this.$toast.add({
          severity: 'info',
          summary: 'Not editable',
          detail: msg,
          life: 3000,
        });
      } else {
        if (this.$root && typeof this.$root.$emit === 'function') {
          this.$root.$emit('show-toast', {
            severity: 'info',
            summary: 'Not editable',
            detail: msg,
            life: 3000,
          });
        } else {
          console.info(msg);
        }
      }
    },
    async updateDetails() {
      try {
        const authToken = storage.get('authToken');
        const headers = authToken
          ? { Authorization: `Bearer ${authToken}` }
          : {};
        // Find org id
        const orgId = this.orgId;
        if (!orgId) {
          if (this.$toast && typeof this.$toast.add === 'function') {
            this.$toast.add({
              severity: 'error',
              summary: 'Update Failed',
              detail: 'Organization not found.',
              life: 3500,
            });
          }
          return;
        }
        // Normalize org_size back to the display string the backend expects
        let orgSizePayload = this.form.orgSize;
        if (this.form.orgSize === '1-99 Employees (Small)') {
          orgSizePayload = '1-99 Employees (Small)';
        } else if (this.form.orgSize === '100-249 Employees (Medium)') {
          orgSizePayload = '100-249 Employees (Medium)';
        } else if (this.form.orgSize === '250+ Employees (Large)') {
          orgSizePayload = '250+ Employees (Large)';
        } else {
          orgSizePayload = '';
        }
        // Convert user-facing formatted dates back to ISO timestamp strings where possible
        const contractStartISO = this.parseContractInput(
          this.form.contractStart
        );
        const contractEndISO = this.parseContractInput(this.form.contractEnd);
        const lastContactedISO = this.parseLastContactedInput(
          this.form.lastContacted
        );

        // Prepare payload (convert camelCase to snake_case for backend)
        const payload = {
          org_name: this.form.orgName,
          // send backend-friendly org_size display string
          org_size: orgSizePayload,
          source: this.form.source || null,
          address: this.form.address || null,
          city_id: this.form.city_id || null,
          state_id: this.form.state_id || null,
          zip: this.form.zip || null,
          country_id: this.form.country_id || null,
          sales_person: this.form.salesPerson || null,
          certified_staff: this.form.certifiedStaff || null,
          first_name: this.form.firstName || null,
          last_name: this.form.lastName || null,
          admin_email: this.form.adminEmail || null,
          admin_phone: this.form.adminPhone || null,
          main_contact: `${this.form.firstName} ${this.form.lastName}`.trim(),
          // include contract and last_contacted fields when available (or null to clear)
          contract_start: contractStartISO,
          contract_end: contractEndISO,
          last_contacted: lastContactedISO,
        };
        await axios.put(
          `http://127.0.0.1:8000/api/organizations/${orgId}`,
          payload,
          { headers }
        );
        if (this.$toast && typeof this.$toast.add === 'function') {
          this.$toast.add({
            severity: 'success',
            summary: 'Updated',
            detail: 'Organization updated successfully.',
            life: 3500,
          });
        }
        const id = this.orgId || this.$route.params.id;
        // clear override and navigate
        if (this.$root && this.$root.$emit)
          this.$root.$emit('page-title-override', null);
        this.$router.push(`/organizations/${id}`);
      } catch (e) {
        console.error('Failed to update organization', e);
        if (this.$toast && typeof this.$toast.add === 'function') {
          this.$toast.add({
            severity: 'error',
            summary: 'Update Failed',
            detail: 'Failed to update organization.',
            life: 4500,
          });
        }
      }
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
  .org-edit-divider {
    margin: 16px 0 16px 0;
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
  .org-edit-divider {
    margin: 12px 0 12px 0;
  }
}
</style>
