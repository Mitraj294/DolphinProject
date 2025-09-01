<template>
  <div class="table-outer">
    <div class="table-card">
      <div class="table-search-bar">
        <input
          class="org-search"
          placeholder="Search Organization Name"
          v-model="search"
        />
      </div>
      <div class="table-container">
        <table class="table">
          <TableHeader
            :columns="[
              { label: 'Organizations Name', key: 'name', sortable: true },
              { label: 'Size', key: 'size' },
              { label: 'Admin Name', key: 'main_contact' },
              { label: 'Contract Start', key: 'contractStart', sortable: true },
              { label: 'Contract End', key: 'contractEnd', sortable: true },
              {
                label: 'Last Contacted',
                key: 'last_contacted',
                sortable: true,
              },

              { label: 'Action', key: 'action' },
            ]"
            :activeSortKey="sortKey"
            :sortAsc="sortAsc"
            @sort="sortBy"
          />
          <tbody>
            <tr
              v-for="org in paginatedOrganizations"
              :key="org.id"
            >
              <td>{{ org.name }}</td>
              <td>{{ org.size }}</td>
              <td>{{ org.main_contact }}</td>
              <td>{{ formatDate(org.contractStart) }}</td>
              <td>{{ formatDate(org.contractEnd) }}</td>
              <td>{{ formatDateTime(org.last_contacted) }}</td>

              <td>
                <button
                  class="btn-view"
                  @click="goToDetail(org)"
                >
                  <img
                    src="@/assets/images/Detail.svg"
                    alt="View"
                    class="btn-view-icon"
                  />
                  View Detail
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <Pagination
      :withPagination="true"
      :pageSize="pageSize"
      :pageSizes="[10, 25, 100]"
      :showPageDropdown="showPageDropdown"
      :currentPage="currentPage"
      :totalPages="totalPages"
      :paginationPages="paginationPages"
      @togglePageDropdown="showPageDropdown = !showPageDropdown"
      @selectPageSize="selectPageSize"
      @goToPage="goToPage"
    />
  </div>
</template>

<script>
import Pagination from '@/components/layout/Pagination.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
// Import the organization service

export default {
  name: 'OrganizationTable',
  components: { Pagination, TableHeader },
  data() {
    return {
      search: '',
      showPageDropdown: false,
      pageSize: 10,
      currentPage: 1,
      sortKey: '',
      sortAsc: true,
      organizations: [], // This is correctly initialized here
    };
  },
  computed: {
    filteredOrganizations() {
      let orgs = this.organizations;
      if (this.search) {
        orgs = orgs.filter((org) =>
          org.name.toLowerCase().includes(this.search.toLowerCase())
        );
      }
      if (this.sortKey) {
        orgs = orgs.slice().sort((a, b) => {
          let aVal = a[this.sortKey];
          let bVal = b[this.sortKey];
          // For contractStart, contractEnd and last_contacted, sort as dates
          if (
            this.sortKey === 'contractStart' ||
            this.sortKey === 'contractEnd' ||
            this.sortKey === 'last_contacted'
          ) {
            // Try to parse as Date, fallback to string compare
            const aDate = new Date(aVal);
            const bDate = new Date(bVal);
            if (!isNaN(aDate) && !isNaN(bDate)) {
              return this.sortAsc ? aDate - bDate : bDate - aDate;
            }
          }
          // Default: string compare
          if (aVal < bVal) return this.sortAsc ? -1 : 1;
          if (aVal > bVal) return this.sortAsc ? 1 : -1;
          return 0;
        });
      }
      return orgs;
    },
    totalPages() {
      return Math.ceil(this.filteredOrganizations.length / this.pageSize) || 1;
    },
    paginatedOrganizations() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredOrganizations.slice(start, start + this.pageSize);
    },
    paginationPages() {
      const pages = [];
      if (10 <= 7) {
        for (let i = 1; i <= 10; i++) pages.push(i);
      } else {
        pages.push(1, 2, 3, '...', 8, 9, 10);
      }
      return pages;
    },
  },
  // Add the mounted lifecycle hook here
  mounted() {
    this.fetchOrganizations();
  },
  methods: {
    // Add the fetchOrganizations method here
    async fetchOrganizations() {
      try {
        const storage = (await import('@/services/storage.js')).default;
        const authToken = storage.get('authToken');
        const headers = authToken
          ? { Authorization: `Bearer ${authToken}` }
          : {};
        const axios = (await import('axios')).default;
        const res = await axios.get('http://127.0.0.1:8000/api/organizations', {
          headers,
        });
        this.organizations = res.data.map((org) => ({
          name: org.org_name,
          size: org.org_size || '',
          main_contact: org.main_contact || '',
          contractStart: org.contract_start || '',
          contractEnd: org.contract_end || '',
          // use snake_case key to match table usage and sorting key
          last_contacted: org.last_contacted || '',
          id: org.id,
        }));
      } catch (e) {
        if (e.response && e.response.status === 401) {
          if (this.$toast && typeof this.$toast.add === 'function') {
            this.$toast.add({
              severity: 'warn',
              summary: 'Session',
              detail: 'Session expired or unauthorized. Please log in again.',
              life: 0,
            });
          } else {
            try {
              if (this.$toast && typeof this.$toast.add === 'function') {
                this.$toast.add({
                  severity: 'warn',
                  summary: 'Session',
                  detail:
                    'Session expired or unauthorized. Please log in again.',
                  life: 0,
                });
              } else {
                console.warn(
                  'Session expired or unauthorized. Please log in again.'
                );
              }
            } catch (e) {
              /* swallow */
            }
          }
          this.$router.push({ name: 'Login' });
        } else {
          console.error('Error fetching organizations:', e);
        }
        this.organizations = [];
      }
    },
    goToDetail(org) {
      this.$router.push({
        name: 'OrganizationDetail',
        params: { id: org.id },
      });
    },
    formatDate(dateVal) {
      if (!dateVal) return null;
      // accept timestamps or 'YYYY-MM-DD' or Date objects
      // If backend sends 'YYYY-MM-DD HH:MM:SS' (UTC), parse as UTC and then show local
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
      // format like: 31 AUG,2025 (kept spacing to match your example)
      return `${day} ${mon},${yr}`;
    },
    formatDateTime(dateVal) {
      if (!dateVal) return null;
      // Parse DB 'YYYY-MM-DD HH:MM:SS' as UTC, otherwise fallback to Date parsing
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
    goToPage(page) {
      if (page === '...' || page < 1 || page > this.totalPages) return;
      this.currentPage = page;
    },
    selectPageSize(size) {
      this.pageSize = size;
      this.currentPage = 1;
      this.showPageDropdown = false;
    },
    sortBy(key) {
      if (this.sortKey === key) {
        this.sortAsc = !this.sortAsc;
      } else {
        this.sortKey = key;
        this.sortAsc = true;
      }
    },
  },
};
</script>

<style scoped>
/* --- Layout and spacing to match reference page --- */

.org-search {
  width: 260px;
  padding: 8px 24px 8px 32px;
  border-radius: 12px;
  border: none;
  background: #f8f8f8;
  font-size: 14px;
  outline: none;
  background-image: url('@/assets/images/Search.svg');
  background-repeat: no-repeat;
  background-position: 8px center;
  background-size: 16px 16px;
  margin-left: 0;
  margin-right: auto;
}
.org-search::placeholder {
  margin-left: 4px;
}

/* --- Pagination and footer spacing --- */
.org-footer-row {
  width: 100%;
  max-width: 1200px;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  margin-top: 18px;
  margin-left: auto;
  margin-right: auto;
  padding-left: 0;
  padding-right: 0;
  box-sizing: border-box;
}

/* Responsive: shrink margin and font on small screens */
@media (max-width: 1400px) {
  .org-search {
    font-size: 13px;
    padding: 8px 16px 8px 32px;
    max-width: 320px;
    border-radius: 12px;
  }
}

@media (max-width: 1200px) {
}

@media (max-width: 900px) {
}

@media (max-width: 600px) {
}

/* --- Sort button and icon --- */
.org-th-sort-btn {
  background: none;
  border: none;

  display: inline-flex;
  align-items: center;
  vertical-align: middle;
  box-shadow: none;
  outline: none;
  cursor: pointer;
  height: 1em;
  line-height: 1;
}
.org-th-sort {
  width: 1em;
  height: 1em;
  min-width: 16px;
  min-height: 16px;
  max-width: 18px;
  max-height: 18px;
  margin-left: 2px;
  margin-right: 0;
  vertical-align: middle;
  display: inline-block;
  border: none;
  background: none;
  box-shadow: none;
  filter: none;
  opacity: 0.7;
  transition: opacity 0.15s;
}
.org-th-sort-btn:hover .org-th-sort,
.org-th-sort-btn:focus .org-th-sort {
  opacity: 1;
}
</style>
