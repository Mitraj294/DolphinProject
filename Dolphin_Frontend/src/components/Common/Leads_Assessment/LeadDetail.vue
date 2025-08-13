<template>
  <MainLayout>
    <div class="lead-detail-outer">
      <div class="lead-detail-main-card">
        <div class="lead-detail-main-card-header">
          <button
            class="btn btn-primary"
            @click="goToEditLead"
          >
            Edit Details
          </button>
        </div>
        <div class="lead-detail-main-cols">
          <div
            class="lead-detail-main-cols-group lead-detail-main-cols-group--row"
          >
            <div class="lead-detail-col lead-detail-col-left">
              <h3 class="lead-detail-section-title">Lead Detail</h3>
              <div class="lead-detail-list-card lead-detail-list-card--box">
                <div class="lead-detail-list-row">
                  <span>Main Contact</span><b>{{ leadData.contact }}</b>
                </div>
                <div class="lead-detail-list-row">
                  <span>Admin Email</span><b>{{ leadData.email }}</b>
                </div>
                <div class="lead-detail-list-row">
                  <span>Admin Phone #</span><b>{{ leadData.phone }}</b>
                </div>
                <div class="lead-detail-list-row">
                  <span>Sales Person</span><b>John</b>
                </div>
                <div class="lead-detail-list-row">
                  <span>Source</span><b>{{ leadData.source }}</b>
                </div>
                <div class="lead-detail-list-row">
                  <span>Status</span><b>{{ leadData.status }}</b>
                </div>
              </div>
            </div>
            <div class="lead-detail-col lead-detail-col-right">
              <h3 class="lead-detail-section-title">Organization Detail</h3>
              <div class="lead-detail-list-card lead-detail-list-card--box">
                <div class="lead-detail-list-row">
                  <span>Organization Name</span
                  ><b>{{ leadData.organization }}</b>
                </div>
                <div class="lead-detail-list-row">
                  <span>Organization Size</span>
                  <b>{{ leadData.size }}</b>
                </div>
                <div class="lead-detail-list-row">
                  <span>Contract Start</span><b>Jun 18, 2024</b>
                </div>
                <div class="lead-detail-list-row">
                  <span>Contract End</span><b>Jun 18, 2025</b>
                </div>
                <div class="lead-detail-list-row">
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
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import axios from 'axios';
export default {
  name: 'LeadDetail',
  components: { MainLayout },
  props: {
    lead: {
      type: Object,
      default: () => ({
        contact: '',
        email: '',
        phone: '',
        source: '',
        status: '',
        organization: '',
        size: '',
        address: '',
        city: '',
        state: '',
        zip: '',
        country: '',
        country_id: null,
        state_id: null,
        city_id: null,
      }),
    },
  },
  data() {
    return {
      localLead: { ...this.lead },
      countryName: '',
      stateName: '',
      cityName: '',
    };
  },
  computed: {
    leadData() {
      return this.localLead;
    },
    addressDisplay() {
      // Compose address with looked-up names
      const arr = [];
      if (this.leadData.address) arr.push(this.leadData.address);
      if (this.cityName) arr.push(this.cityName);
      if (this.stateName) arr.push(this.stateName);
      if (this.leadData.zip) arr.push(this.leadData.zip);
      if (this.countryName) arr.push(this.countryName);
      return arr.filter((f) => f && String(f).trim().length > 0);
    },
  },
  methods: {
    goToEditLead() {
      this.$router.push({
        path: '/leads/edit-lead',
        query: {
          ...this.leadData,
          id: this.$route.query.id || this.leadData.id || '',
        },
      });
    },
    async lookupLocationNames() {
      const API_BASE_URL = 'http://127.0.0.1:8000';
      if (this.leadData.country_id) {
        try {
          const res = await axios.get(
            `${API_BASE_URL}/api/countries/${this.leadData.country_id}`
          );
          this.countryName = res.data?.name || '';
        } catch (e) {
          this.countryName = '';
        }
      } else {
        this.countryName = this.leadData.country || '';
      }
      if (this.leadData.state_id) {
        try {
          const res = await axios.get(
            `${API_BASE_URL}/api/states/${this.leadData.state_id}`
          );
          this.stateName = res.data?.name || '';
        } catch (e) {
          this.stateName = '';
        }
      } else {
        this.stateName = this.leadData.state || '';
      }
      if (this.leadData.city_id) {
        try {
          const res = await axios.get(
            `${API_BASE_URL}/api/cities/${this.leadData.city_id}`
          );
          this.cityName = res.data?.name || '';
        } catch (e) {
          this.cityName = '';
        }
      } else {
        this.cityName = this.leadData.city || '';
      }
    },
  },
  async created() {
    // If id is present, fetch lead details from backend
    const id = this.$route.query.id || this.lead.id;
    if (id) {
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');
        const res = await axios.get(`${API_BASE_URL}/api/leads`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        const found = Array.isArray(res.data)
          ? res.data.find((l) => l.id == id)
          : null;
        if (found) {
          this.localLead = {
            contact:
              (found.first_name || '') +
              (found.last_name ? ' ' + found.last_name : ''),
            email: found.email || '',
            phone: found.phone || '',
            source: found.find_us || '',
            status: found.status || '',
            organization: found.org_name || '',
            size: found.org_size || '',
            address: found.address !== undefined ? found.address : '',
            city: found.city !== undefined ? found.city : '',
            state: found.state !== undefined ? found.state : '',
            zip: found.zip !== undefined ? found.zip : '',
            country: found.country !== undefined ? found.country : '',
            country_id: found.country_id || null,
            state_id: found.state_id || null,
            city_id: found.city_id || null,
          };
          ['address', 'city', 'state', 'zip', 'country'].forEach((f) => {
            if (this.localLead[f] === undefined) this.localLead[f] = '';
          });
          await this.lookupLocationNames();
          return;
        }
      } catch (e) {
        // fallback to query params
      }
    }
    // fallback to query params if no id or fetch failed
    if (
      this.$route &&
      this.$route.query &&
      Object.keys(this.$route.query).length
    ) {
      this.localLead = {
        contact: this.$route.query.contact || '',
        email: this.$route.query.email || '',
        phone: this.$route.query.phone || '',
        source: this.$route.query.source || '',
        status: this.$route.query.status || '',
        organization: this.$route.query.organization || '',
        size: this.$route.query.size || '',
        address:
          this.$route.query.address !== undefined
            ? this.$route.query.address
            : '',
        city:
          this.$route.query.city !== undefined ? this.$route.query.city : '',
        state:
          this.$route.query.state !== undefined ? this.$route.query.state : '',
        zip: this.$route.query.zip !== undefined ? this.$route.query.zip : '',
        country:
          this.$route.query.country !== undefined
            ? this.$route.query.country
            : '',
        country_id: this.$route.query.country_id || null,
        state_id: this.$route.query.state_id || null,
        city_id: this.$route.query.city_id || null,
      };
      ['address', 'city', 'state', 'zip', 'country'].forEach((f) => {
        if (this.localLead[f] === undefined) this.localLead[f] = '';
      });
      await this.lookupLocationNames();
    }
  },
  watch: {
    '$route.query': {
      handler(newQuery) {
        if (newQuery && Object.keys(newQuery).length) {
          this.localLead = {
            contact: newQuery.contact || '',
            email: newQuery.email || '',
            phone: newQuery.phone || '',
            source: newQuery.source || '',
            status: newQuery.status || '',
            organization: newQuery.organization || '',
            size: newQuery.size || '',
            address: newQuery.address !== undefined ? newQuery.address : '',
            city: newQuery.city !== undefined ? newQuery.city : '',
            state: newQuery.state !== undefined ? newQuery.state : '',
            zip: newQuery.zip !== undefined ? newQuery.zip : '',
            country: newQuery.country !== undefined ? newQuery.country : '',
            country_id: newQuery.country_id || null,
            state_id: newQuery.state_id || null,
            city_id: newQuery.city_id || null,
          };
          ['address', 'city', 'state', 'zip', 'country'].forEach((f) => {
            if (this.localLead[f] === undefined) this.localLead[f] = '';
          });
          this.lookupLocationNames();
        }
      },
      deep: true,
    },
    lead: {
      handler(newLead) {
        this.localLead = { ...newLead };
        this.lookupLocationNames();
      },
      deep: true,
    },
  },
};
</script>

<style scoped>
.lead-detail-outer {
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

.lead-detail-main-card {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-sizing: border-box;
  overflow: visible;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 0 auto;
  padding: 32px 32px 24px 32px;
  display: flex;
  flex-direction: column;
  gap: 32px;
  position: relative;
}

.lead-detail-main-card-header {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 8px;
  min-height: 0;
}

.lead-detail-main-cols {
  display: flex;
  flex-direction: column;
  gap: 32px;
  width: 100%;
  justify-content: center;
  align-items: stretch;
  margin-bottom: 0;
}
.lead-detail-main-cols-group {
  display: flex;
  flex-direction: row;
  gap: 64px;
  width: 100%;
}
.lead-detail-main-cols-group--row {
  flex-direction: row;
  gap: 32px;
  margin-top: 0;
  margin-bottom: 0;
}

.lead-detail-col {
  flex: 1 1 0;
  min-width: 0;
  max-width: 100%;
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
  margin: 0;
}

.lead-detail-section-title {
  font-family: 'Helvetica Neue LT Std', Helvetica, Arial, sans-serif;
  font-weight: 600;
  font-size: 20px;
  color: #222;
  margin-bottom: 18px;
  margin-top: 0;
  text-align: left;
  width: 100%;
}

.lead-detail-list-card--box {
  border-radius: 20px;
  background: #f8f8f8;
  padding: 24px 32px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  font-size: 18px;
  margin: 10px;
  box-sizing: border-box;
  width: 100%;
  min-width: 0;
  max-width: 100%;
  min-height: 270px;
  justify-content: flex-start;
}

.lead-detail-list-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0;
  flex-wrap: wrap;
  word-break: break-word;
  padding: 2px 0;
}

.lead-detail-list-row span {
  color: #555;
  font-weight: 400;
  min-width: 160px;
  text-align: left;
  font-size: 19px;
  font-family: 'Inter', Arial, sans-serif;
  line-height: 1.7;
  letter-spacing: 0.01em;
  flex: 1 1 50%;
}

.lead-detail-list-row b {
  color: #222;
  font-weight: 600;
  text-align: left;
  word-break: break-word;
  font-size: 17px;
  font-family: 'Inter', Arial, sans-serif;
  line-height: 1.7;
  letter-spacing: 0.01em;
  flex: 1 1 50%;
  justify-content: flex-start;
  display: flex;
}

@media (max-width: 1400px) {
  .lead-detail-outer {
    margin: 12px;
    max-width: 100%;
  }
  .lead-detail-main-card {
    max-width: 100%;
    border-radius: 14px;
    padding: 18px 8px 12px 8px;
  }
  .lead-detail-main-cols {
    gap: 32px;
  }
  .lead-detail-main-cols-group {
    gap: 32px;
  }
  .lead-detail-main-cols,
  .lead-detail_row--split {
    gap: 0;
  }
  .lead-detail-col {
    min-width: 0;
    max-width: 100%;
    margin: 0 0 18px 0;
  }
  .lead-detail-list-card--box {
    padding: 18px 8px;
    font-size: 15px;
    min-height: 0;
    min-width: 0;
    max-width: 100%;
    width: 100%;
  }
}

@media (max-width: 900px) {
  .lead-detail-outer {
    margin: 4px;
    max-width: 100%;
  }
  .lead-detail-main-card {
    padding: 8px 2vw 8px 2vw;
    border-radius: 10px;
  }
  .lead-detail-main-cols {
    flex-direction: column;
    gap: 0;
  }
  .lead-detail-main-cols-group {
    flex-direction: column;
    gap: 0;
    width: 100%;
    margin-bottom: 18px;
  }
  .lead-detail-main-cols-group--row {
    flex-direction: column;
    gap: 0;
    width: 100%;
    margin-bottom: 18px;
  }
  .lead-detail-col {
    min-width: 0;
    max-width: 100%;
    width: 100%;
    margin: 0 0 18px 0;
  }
  .lead-detail-list-card--box {
    padding: 8px 4px;
    font-size: 12px;
    gap: 6px;
    min-height: 0;
    min-width: 0;
    max-width: 100%;
    width: 100%;
  }
}
</style>
