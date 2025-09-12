<template>
  <MainLayout
    :navbarTitle="orgDetail.organization_name || 'Organization Details'"
    sidebarActive="organization"
  >
    <div class="page">
      <div class="org-detail-outer">
        <div class="org-detail-main-card">
          <div class="org-detail-header-row">
            <div
              class="org-detail-main-card-header-title"
              style="
                font-family: 'Helvetica Neue LT Std', Helvetica, Arial,
                  sans-serif;
                font-weight: 600;
                font-size: 24px;
                color: #222;
              "
            >
              {{ orgDetail.organization_name }}
            </div>
            <div class="org-detail-main-card-header">
              <button
                class="btn btn-primary"
                @click="$router.push(`/organizations/${orgDetail.id}/edit`)"
              >
                <img
                  src="@/assets/images/EditWhite.svg"
                  alt="Edit"
                  class="org-edit-icon"
                />
                Edit Details
              </button>
            </div>
          </div>

          <div class="org-detail-main-cols">
            <!-- Main details: Organization and Admin, side by side -->
            <div
              class="org-detail-main-cols-group org-detail-main-cols-group--row"
            >
              <div class="org-detail-col org-detail-col-left">
                <h3 class="org-detail-section-title">Organization Detail</h3>
                <div class="org-detail-list-card org-detail-list-card--box">
                  <div class="org-detail-list-row">
                    <span>Organization Name</span
                    ><b>{{ orgDetail.organization_name }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Organization Size</span>
                    <b>{{ orgDetail.organization_size }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Contract Start</span
                    ><b>{{ formatDate(orgDetail.contract_start) }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Contract End</span
                    ><b>{{ formatDate(orgDetail.contract_end) }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Source</span>
                    <b>{{ orgDetail.find_us }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Address</span>
                    <b>
                      <template v-if="addressDisplay.length">
                        {{ addressDisplay.join(', ') }}
                      </template>
                      <template v-else>N/A</template>
                    </b>
                  </div>
                </div>
              </div>
              <div class="org-detail-col org-detail-col-right">
                <h3 class="org-detail-section-title">Admin Detail</h3>
                <div class="org-detail-list-card org-detail-list-card--box">
                  <div class="org-detail-list-row">
                    <span>Main Contact</span><b>{{ orgDetail.main_contact }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Admin Email</span><b>{{ orgDetail.admin_email }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Admin Phone</span>
                    <b>{{
                      orgDetail.admin_phone || orgDetail.user?.phone || ''
                    }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Sales Person</span
                    ><b>
                      <template v-if="salesPersonUser">
                        {{ salesPersonUser.name || salesPersonUser.email }}
                      </template>
                      <template v-else>
                        {{
                          orgDetail.sales_person ||
                          (orgDetail.sales_person_id
                            ? 'ID: ' + orgDetail.sales_person_id
                            : 'N/A')
                        }}
                      </template>
                    </b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Last Contacted</span
                    ><b>{{ formatDateTime(orgDetail.last_contacted) }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Certified Staff</span
                    ><b>{{ orgDetail.certified_staff }}</b>
                  </div>
                </div>
              </div>
            </div>
            <!-- Lower boxes: Org Chart Type and Billing Status, side by side -->
            <div
              class="org-detail-main-cols-group org-detail-main-cols-group--row"
            >
              <div
                class="org-detail-box org-detail-box--half org-detail-box-flex"
              >
                <div class="org-detail-box-info">
                  <div class="org-detail-box-label">Org Chart Type</div>
                  <div class="org-detail-box-value">
                    Functional/Role - Based
                  </div>
                </div>
                <div class="org-detail-box-action">
                  <button class="org-view-btn custom-view-btn">
                    <img
                      src="@/assets/images/Chart.svg"
                      alt="View Chart"
                      class="org-view-btn-icon"
                    />
                    View Chart
                  </button>
                </div>
              </div>
              <div
                class="org-detail-box org-detail-box--half org-detail-box-flex"
              >
                <div class="org-detail-box-info">
                  <div class="org-detail-box-label">Billing Status</div>
                  <div class="org-detail-box-value">
                    <template v-if="currentPlan">
                      <div
                        style="
                          text-align: left;
                          display: flex;
                          justify-content: flex-start;
                        "
                      >
                        {{
                          currentPlan.plan_name || currentPlan.plan || 'Plan'
                        }}
                      </div>
                      <div style="font-weight: 500; font-size: 16px">
                        ${{ formatAmount(currentPlan.amount) }}/{{
                          currentPlan.period ||
                          (currentPlan.amount >= 1000 ? 'Year' : 'Month')
                        }}
                      </div>
                    </template>
                    <template v-else>None</template>
                  </div>
                </div>
                <div class="org-detail-box-action">
                  <button
                    class="org-view-btn custom-view-btn"
                    @click="
                      $router.push({
                        name: 'BillingDetails',
                        query: { orgId: orgDetail.id },
                      })
                    "
                    v-if="orgDetail && orgDetail.id"
                  >
                    <img
                      src="@/assets/images/Billing Status view details.svg"
                      alt="View Details"
                      class="org-view-btn-icon"
                    />
                    View Details
                  </button>
                </div>
              </div>
            </div>
            <!-- Current Algorithm row, left aligned, right empty for symmetry -->
            <div
              class="org-detail-main-cols-group org-detail-main-cols-group--row"
            >
              <div
                class="org-detail-box org-detail-box--half org-detail-box-algo org-detail-box-flex"
              >
                <div class="org-detail-box-info">
                  <div class="org-detail-box-label">Current Algorithm</div>
                </div>
                <div class="org-detail-box-action org-detail-box-action-algo">
                  <select class="org-algo-select">
                    <option>Dolphin 1.0</option>
                  </select>
                  <button class="org-algo-btn custom-view-btn">Assigned</button>
                </div>
              </div>
              <div
                class="org-detail-box org-detail-box--half org-detail-box-empty"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import axios from 'axios';
import storage from '@/services/storage.js';

export default {
  name: 'OrganizationDetail',
  components: { MainLayout },

  // Component State
  data() {
    return {
      orgDetail: {}, // raw organization object (from API)
      countryName: '', // resolved name (from ID lookups)
      stateName: '',
      cityName: '',
      currentPlan: null, // billing plan info
      salesPersonUser: null, // resolved sales person user
    };
  },

  // Derived Values
  computed: {
    /**
     * Build a formatted address array.
     * Uses userDetails if available, falls back to orgDetail.
     */
    addressDisplay() {
      const parts = [];
      const details = this.orgDetail.user?.userDetails || this.orgDetail;

      // Address line
      const addr = details.address || this.orgDetail.address || '';
      if (addr) parts.push(addr);

      // City / State / Zip / Country
      const city =
        this.orgDetail.city_name ||
        this.cityName ||
        details.city ||
        this.orgDetail.city ||
        '';
      const state =
        this.orgDetail.state_name ||
        this.stateName ||
        details.state ||
        this.orgDetail.state ||
        '';
      const zip = details.zip || this.orgDetail.zip || '';
      const country =
        this.orgDetail.country ||
        this.countryName ||
        details.country ||
        this.orgDetail.country ||
        '';

      if (city) parts.push(city);
      if (state) parts.push(state);
      if (zip) parts.push(zip);
      if (country) parts.push(country);

      return parts.filter((p) => String(p).trim().length > 0);
    },
  },

  // Lifecycle
  async mounted() {
    await this.fetchOrganization();
    await this.lookupLocationNames();
    await this.fetchOrgBillingPreview();
  },

  // Methods
  methods: {
    // API: Fetch organization details
    async fetchOrganization() {
      const authToken = storage.get('authToken');
      const headers = authToken ? { Authorization: `Bearer ${authToken}` } : {};
      const orgId = this.$route.params.id || null;

      try {
        // Try /organizations/:id endpoint first
        if (orgId) {
          const res = await axios.get(
            `http://127.0.0.1:8000/api/organizations/${orgId}`,
            { headers }
          );
          if (res?.data) {
            this.orgDetail = res.data;
            this.resolveSalesPerson();
            return;
          }
        }

        // Fallback: get list and find by name
        const res = await axios.get(`http://127.0.0.1:8000/api/organizations`, {
          headers,
        });
        const orgName = this.$route.params.orgName;
        const found = orgName
          ? res.data.find((o) => o.organization_name === orgName)
          : res.data[0];

        this.orgDetail = found || {};
        if (this.orgDetail.sales_person_id) {
          this.fetchSalesPersonUser(this.orgDetail.sales_person_id);
        }
      } catch (e) {
        console.error('Error fetching organization details:', e.message || e);
        this.orgDetail = {};
      }
    },

    // API: Fetch billing preview
    async fetchOrgBillingPreview() {
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const authToken = storage.get('authToken');
        const headers = authToken
          ? { Authorization: `Bearer ${authToken}` }
          : {};
        const orgId = this.orgDetail?.id || this.$route.params.id;

        if (!orgId) return;

        const res = await axios.get(
          `${API_BASE_URL}/api/billing/current?org_id=${orgId}`,
          {
            headers,
          }
        );
        this.currentPlan = res?.data || null;
      } catch (e) {
        console.error('Failed to fetch organization billing preview:', e);
        this.currentPlan = null;
      }
    },

    // API: Resolve salesperson user
    resolveSalesPerson() {
      if (this.orgDetail?.sales_person) {
        // API returned string directly (no need to fetch user)
        this.salesPersonUser = { name: this.orgDetail.sales_person };
      } else if (this.orgDetail?.sales_person_id) {
        // Need to fetch user list
        this.fetchSalesPersonUser(this.orgDetail.sales_person_id);
      }
    },

    async fetchSalesPersonUser(userId) {
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const authToken = storage.get('authToken');
        const headers = authToken
          ? { Authorization: `Bearer ${authToken}` }
          : {};

        const res = await axios.get(`${API_BASE_URL}/api/users`, { headers });
        const users = res.data?.users || [];
        this.salesPersonUser = users.find((u) => u.id === userId) || null;
      } catch (e) {
        console.warn('Failed to fetch sales person user', e);
        this.salesPersonUser = null;
      }
    },

    // API: Lookup city/state/country names by IDs
    async lookupLocationNames() {
      const API_BASE_URL = 'http://127.0.0.1:8000';
      const details = this.orgDetail.user?.userDetails || this.orgDetail;

      const countryId = details.country_id || this.orgDetail.country_id;
      const stateId = details.state_id || this.orgDetail.state_id;
      const cityId = details.city_id || this.orgDetail.city_id;

      try {
        if (countryId) {
          const res = await axios.get(
            `${API_BASE_URL}/api/countries/${countryId}`
          );
          this.countryName = res.data?.name || '';
        }
        if (stateId) {
          const res = await axios.get(`${API_BASE_URL}/api/states/${stateId}`);
          this.stateName = res.data?.name || '';
        }
        if (cityId) {
          const res = await axios.get(`${API_BASE_URL}/api/cities/${cityId}`);
          this.cityName = res.data?.name || '';
        }
      } catch (e) {
        console.error('Failed to fetch location names:', e);
      }

      // fallback if nothing was resolved
      if (!this.cityName)
        this.cityName = details.city || this.orgDetail.city || '';
      if (!this.stateName)
        this.stateName = details.state || this.orgDetail.state || '';
      if (!this.countryName)
        this.countryName = details.country || this.orgDetail.country || '';
    },

    // Helpers: Formatters
    planLabel(planObj) {
      if (!planObj) return 'None';
      const name = planObj.plan_name || planObj.plan || 'Plan';
      const amount = planObj.amount || planObj.plan_amount;
      const period = planObj.period || '';
      return amount
        ? `${name} ($${this.formatAmount(amount)}/${
            period || (parseFloat(amount) >= 1000 ? 'Year' : 'Month')
          })`
        : name;
    },

    formatAmount(amount) {
      const num = typeof amount === 'number' ? amount : parseFloat(amount);
      return isNaN(num) ? amount : num.toFixed(2);
    },

    formatDate(dateVal) {
      if (!dateVal) return null;
      const d = new Date(dateVal);
      if (isNaN(d.getTime())) return null;

      const day = String(d.getUTCDate()).padStart(2, '0');
      const mon = [
        'JAN',
        'FEB',
        'MAR',
        'APR',
        'MAY',
        'JUN',
        'JUL',
        'AUG',
        'SEP',
        'OCT',
        'NOV',
        'DEC',
      ][d.getUTCMonth()];
      const yr = d.getUTCFullYear();
      return `${day} ${mon},${yr}`;
    },

    formatDateTime(dateVal) {
      if (!dateVal) return null;
      const d = new Date(dateVal);
      if (isNaN(d.getTime())) return null;

      const day = String(d.getUTCDate()).padStart(2, '0');
      const mon = [
        'JAN',
        'FEB',
        'MAR',
        'APR',
        'MAY',
        'JUN',
        'JUL',
        'AUG',
        'SEP',
        'OCT',
        'NOV',
        'DEC',
      ][d.getUTCMonth()];
      const yr = d.getUTCFullYear();

      let hr = d.getHours();
      const min = String(d.getMinutes()).padStart(2, '0');
      const ampm = hr >= 12 ? 'PM' : 'AM';
      hr = hr % 12 || 12;

      return `${day} ${mon},${yr} ${hr}:${min} ${ampm}`;
    },
  },
};
</script>

<style scoped>
.org-detail-outer {
  width: 100%;

  min-width: 0;

  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  background: none !important;
}

.org-detail-main-card {
  width: 100%;

  min-width: 0;
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

.org-detail-header-row {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.org-detail-main-card-header {
  flex: 0 0 40%; /* button area */
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin: 0;
}
.org-detail-main-card-header-title {
  flex: 1 1 60%;
  display: flex;
  justify-content: flex-start;
  align-items: center;
  margin: 0;
}

.org-detail-main-cols {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
  justify-content: center;
  align-items: stretch;
  margin-bottom: 0;
  padding-left: 16px;
  padding-right: 16px;
  box-sizing: border-box;
}
.org-detail-main-cols-group {
  display: flex;
  flex-direction: row;
  gap: 20px;
  width: 100%;
  align-items: stretch;
}
.org-detail-main-cols-group--row {
  margin-top: 0;
  margin-bottom: 0;
}

.org-detail-col {
  flex: 1 1 0;
  min-width: 0;
  max-width: 100%;
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
  margin: 0;
  height: 100%;
}

.org-detail-section-title {
  font-family: 'Helvetica Neue LT Std', Helvetica, Arial, sans-serif;
  font-weight: 600;
  font-size: 20px;
  color: #222;
  margin-bottom: 18px;
  margin-top: 0;
  text-align: left;
  width: 100%;
}

.org-detail-list-card--box {
  border-radius: 16px;
  background: #f8f8f8;
  padding: 18px 24px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 17px;
  margin: 0;
  box-sizing: border-box;
  width: 100%;
  min-width: 0;
  max-width: 100%;
  min-height: 180px;
  justify-content: flex-start;
  height: 100%;
}

.org-detail-list-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0;
  flex-wrap: wrap;
  word-break: break-word;
  padding: 2px 0; /* Less vertical padding for compactness */
}

.org-detail-list-row span {
  color: #555; /* Darker shade */
  font-weight: 400;
  min-width: 160px;
  text-align: left;
  font-size: 19px; /* Increased font size */
  font-family: 'Inter', Arial, sans-serif;
  line-height: 1.7;
  letter-spacing: 0.01em;
  flex: 1 1 50%;
}

.org-detail-list-row b {
  color: #222;
  font-weight: 600;
  text-align: left; /* Add this */
  word-break: break-word;
  font-size: 17px; /* Increased font size */
  font-family: 'Inter', Arial, sans-serif;
  line-height: 1.7;
  letter-spacing: 0.01em;
  flex: 1 1 50%;
  justify-content: flex-start; /* Change from flex-end to flex-start */
  display: flex;
}

.org-edit-btn {
  border-radius: 29.01px;
  background: #0164a5;
  color: #fff;
  border: none;
  padding: 8px 24px 8px 16px;
  font-size: 15px;
  font-family: 'Helvetica Neue LT Std', Helvetica, Arial, sans-serif;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.2s, border 0.2s;
  box-shadow: none;
}
.org-edit-btn:hover {
  background: #005fa3;
}

.org-edit-icon {
  width: 18px;
  height: 18px;
  margin-right: 6px;
  display: inline-block;
  vertical-align: middle;
}

.org-detail-row {
  display: flex;
  gap: 0;
  margin-bottom: 0;
  justify-content: flex-start;
  width: 100%;
  flex-wrap: wrap;
}

.org-detail-row--split {
  display: flex;
  gap: 32px; /* Add gap for visible space between columns */
  width: 100%;
  margin: 16px 0 0 0;
  justify-content: center;
  align-items: stretch;
}

.org-detail-box {
  border-radius: 20px;
  background: #fafafa;
  padding: 24px 44px;
  min-width: 0;
  width: 100%;
  box-sizing: border-box;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  position: relative;
}

.org-detail-box--half,
.org-detail-box-algo {
  flex: 1 1 0;
  min-width: 0;
  max-width: 100%;
  width: 100%;
  background: #fafafa;
  border-radius: 20px;
  padding: 24px 32px;
  box-sizing: border-box;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
}

.org-detail-box--half.org-detail-box-flex,
.org-detail-box--half.org-detail-box-algo.org-detail-box-flex {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.org-detail-box-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
}

.org-detail-box-action {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  min-width: 140px;
}
.org-detail-box-action-algo {
  gap: 16px;
}

.org-detail-box-empty {
  background: transparent;
  border: none;
  box-shadow: none;
  pointer-events: none;
}

.org-detail-box-label {
  color: #888;
  font-size: 16px;
  font-weight: 400;
  margin-bottom: 2px;
  margin-top: 0;
}

.org-detail-box-value {
  color: #222;
  font-weight: 700;
  font-size: 20px;
  margin-bottom: 16px;
  margin-top: 2px;
  letter-spacing: 0.01em;
  font-family: 'Inter', Arial, sans-serif;
}

.org-detail-algo-row {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-top: 8px;
  flex-wrap: wrap;
  width: 100%;
  justify-content: flex-start;
}

/* Button and select tweaks for visual match */
.org-view-btn,
.custom-view-btn,
.org-algo-btn {
  border-radius: 999px;
  background: #fff;
  border: 1px solid #e0e0e0;
  color: #0164a5;
  font-weight: 500;
  font-size: 15px;
  padding: 7px 22px 7px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: 0;
  margin-top: 0;
  box-shadow: none;
  transition: background 0.18s, border 0.18s;
}

.org-view-btn:hover,
.custom-view-btn:hover,
.org-algo-btn:hover {
  background: #f5faff;
  border: 1px solid #bcbcbc;
}

.org-view-btn-icon {
  width: 18px;
  height: 18px;
  margin-right: 6px;
  display: inline-block;
  vertical-align: middle;
}

.org-algo-select {
  border-radius: 8px;
  border: 1.5px solid #e0e0e0;
  font-size: 15px;
  padding: 7px 32px 7px 12px;
  color: #222;
  outline: none;
  background: #fff
    url('data:image/svg+xml;utf8,<svg fill="%23888" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7.293 7.293a1 1 0 011.414 0L10 8.586l1.293-1.293a1 1 0 111.414 1.414l-2 2a1 1 0 01-1.414 0l-2-2a1 1 0 010-1.414z"/></svg>')
    no-repeat right 10px center/18px 18px;
  appearance: none;
  min-width: 120px;
}

/* Responsive styles to match other pages */
@media (max-width: 1400px) {
  .org-detail-main-cols {
    gap: 12px;
    padding-left: 6px;
    padding-right: 6px;
  }
  .org-detail-list-card--box {
    padding: 12px 10px;
  }
  .org-detail-main-cols-group {
    flex-direction: column;
    gap: 12px;
  }
}

@media (max-width: 900px) {
  .org-detail-main-cols {
    gap: 6px;
    padding-left: 2px;
    padding-right: 2px;
  }
  .org-detail-main-cols-group {
    flex-direction: column;
    gap: 6px;
  }
  .org-detail-col {
    padding-left: 12px;
    padding-right: 12px;
    box-sizing: border-box;
  }
  .org-detail-list-card--box {
    padding: 8px 8px;
    border-radius: 8px;
    font-size: 15px;
    min-height: 120px;
  }
}
</style>
