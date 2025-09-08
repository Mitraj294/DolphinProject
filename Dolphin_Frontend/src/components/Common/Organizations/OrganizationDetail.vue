<template>
  <MainLayout
    :navbarTitle="orgDetail.org_name || 'Organization Details'"
    sidebarActive="organization"
  >
    <div class="page">
      <div class="org-detail-outer">
        <div class="org-detail-main-card">
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
                    ><b>{{ orgDetail.org_name }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Organization Size</span>
                    <b>{{ orgDetail.org_size }}</b>
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
                    <span>Sales Person</span><b>{{ orgDetail.sales_person }}</b>
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
  data() {
    return {
      orgDetail: {},
      countryName: '',
      stateName: '',
      cityName: '',
      currentPlan: null,
    };
  },
  computed: {
    addressDisplay() {
      const arr = [];
      // prefer userDetails address fields when present
      const details =
        this.orgDetail.user && this.orgDetail.user.userDetails
          ? this.orgDetail.user.userDetails
          : this.orgDetail;
      // Address: prefer details.address then orgDetail.address
      const addr = details.address || this.orgDetail.address || '';
      if (addr) arr.push(addr);
      // Prefer explicit name fields returned from API (city_name/state_name/country)
      // then local lookup names, then details/orgDetail string fields
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
      if (city) arr.push(city);
      if (state) arr.push(state);
      if (zip) arr.push(zip);
      if (country) arr.push(country);
      return arr.filter((f) => f && String(f).trim().length > 0);
    },
  },

  async mounted() {
    await this.fetchOrganization();
    await this.lookupLocationNames();
    await this.fetchOrgBillingPreview();
  },
  methods: {
    async fetchOrganization() {
      try {
        const authToken = storage.get('authToken');
        const headers = authToken
          ? { Authorization: `Bearer ${authToken}` }
          : {};
        // Prefer numeric id param when available (route: /organizations/:id)
        const orgId = this.$route.params.id || null;
        if (orgId) {
          try {
            const res = await axios.get(
              `http://127.0.0.1:8000/api/organizations/${orgId}`,
              { headers }
            );
            if (res && res.data) {
              this.orgDetail = res.data;
              return;
            }
          } catch (e) {
            // fall through to index-based lookup
          }
        }

        // Fallback: if no id param, or show returned nothing, try index and match by org_name param
        const orgName = this.$route.params.orgName;
        let res = null;
        try {
          res = await axios.get(`http://127.0.0.1:8000/api/organizations`, {
            headers,
          });
          if (res.data && Array.isArray(res.data)) {
            const found = orgName
              ? res.data.find((o) => o.org_name === orgName)
              : res.data[0];
            if (found) {
              this.orgDetail = found;
              return;
            }
          }
        } catch (e) {
          // fallback
        }
        this.orgDetail = res && res.data && res.data.length ? res.data[0] : {};
      } catch (e) {
        this.orgDetail = {};
      }
    },
    async fetchOrgBillingPreview() {
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const authToken = storage.get('authToken');
        const headers = authToken
          ? { Authorization: `Bearer ${authToken}` }
          : {};
        const orgId =
          this.orgDetail && this.orgDetail.id
            ? this.orgDetail.id
            : this.$route.params.id || null;
        if (!orgId) return;
        const res = await axios.get(
          `${API_BASE_URL}/api/billing/current?org_id=${orgId}`,
          { headers }
        );
        this.currentPlan = res && res.data ? res.data : null;
      } catch (e) {
        this.currentPlan = null;
      }
    },
    async lookupLocationNames() {
      // Only lookup if IDs are present
      const API_BASE_URL = 'http://127.0.0.1:8000';
      // Prefer IDs from userDetails when available
      const details =
        this.orgDetail.user && this.orgDetail.user.userDetails
          ? this.orgDetail.user.userDetails
          : this.orgDetail;
      // Prefer IDs from details but fall back to orgDetail top-level ids
      const countryId = details.country_id || this.orgDetail.country_id || null;
      const stateId = details.state_id || this.orgDetail.state_id || null;
      const cityId = details.city_id || this.orgDetail.city_id || null;
      if (countryId) {
        try {
          const res = await axios.get(
            `${API_BASE_URL}/api/countries/${countryId}`
          );
          this.countryName = res.data?.name || '';
        } catch (e) {
          this.countryName = '';
        }
      }
      if (stateId) {
        try {
          const res = await axios.get(`${API_BASE_URL}/api/states/${stateId}`);
          this.stateName = res.data?.name || '';
        } catch (e) {
          this.stateName = '';
        }
      }
      if (cityId) {
        try {
          const res = await axios.get(`${API_BASE_URL}/api/cities/${cityId}`);
          this.cityName = res.data?.name || '';
        } catch (e) {
          this.cityName = '';
        }
      }
      // fallback to old fields if not found
      if (!this.cityName)
        this.cityName = details.city || this.orgDetail.city || '';
      if (!this.stateName)
        this.stateName = details.state || this.orgDetail.state || '';
      if (!this.countryName)
        this.countryName = details.country || this.orgDetail.country || '';
    },
    planLabel(planObj) {
      // prefer backend-provided friendly fields when present
      if (!planObj) return 'None';
      const name = planObj.plan_name || planObj.plan || 'Plan';
      const amount = planObj.amount || planObj.plan_amount || null;
      const period = planObj.period || '';
      if (!amount) return `${name}`;
      return `${name} ($${this.formatAmount(amount)}/${
        period || (parseFloat(amount) >= 1000 ? 'Year' : 'Month')
      })`;
    },
    formatAmount(amount) {
      if (amount === null || amount === undefined || amount === '') return '';
      const num = typeof amount === 'number' ? amount : parseFloat(amount);
      if (isNaN(num)) return amount;
      // show without thousands sep to match existing UI
      return num.toFixed(2);
    },
    formatDate(dateVal) {
      if (!dateVal) return null;
      // Parse DB date as UTC when in 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS' form
      const m = String(dateVal).match(
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
        d = new Date(dateVal);
      }
      if (isNaN(d.getTime())) return null;
      const day = String(d.getDate()).padStart(2, '0');
      const months = [
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
      ];
      const mon = months[d.getMonth()];
      const yr = d.getFullYear();
      // format like: 31 AUG ,2025 (kept spacing to match your example)
      return `${day} ${mon},${yr}`;
    },
    formatDateTime(dateVal) {
      if (!dateVal) return null;
      // Parse DB datetimes as UTC when in common SQL format
      const m = String(dateVal).match(
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
        d = new Date(dateVal);
      }
      if (isNaN(d.getTime())) return null;

      const day = String(d.getDate()).padStart(2, '0');
      const months = [
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
      ];
      const mon = months[d.getMonth()];
      const yr = d.getFullYear();

      let hr = d.getHours();
      const min = String(d.getMinutes()).padStart(2, '0');
      const ampm = hr >= 12 ? 'PM' : 'AM';
      hr = hr % 12;
      hr = hr ? hr : 12; // the hour '0' should be '12'
      const strTime = `${hr}:${min} ${ampm}`;

      return `${day} ${mon},${yr} ${strTime}`;
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

.org-detail-main-card-header {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 8px;
  min-height: 0;
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
