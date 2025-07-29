<template>
  <MainLayout>
    <div class="page">
      <div class="table-outer">
        <div class="table-card">
          <div class="table-header-bar">
            <button
              class="btn btn-primary"
              @click="$router.push('/leads/lead-capture')"
            >
              <img
                src="@/assets/images/Add.svg"
                alt="Add"
                class="leads-add-btn-icon"
              />
              Add New
            </button>
          </div>
          <div class="table-container">
            <table class="table">
              <TableHeader
                :columns="[
                  { label: 'Contact', key: 'contact', sortable: true },
                  { label: 'Email', key: 'email' },
                  { label: 'Phone Number', key: 'phone' },
                  {
                    label: 'Organization',
                    key: 'organization',
                    sortable: true,
                  },
                  { label: 'Size', key: 'size' },
                  { label: 'Source', key: 'source' },
                  { label: 'Status', key: 'status' },
                  { label: 'Notes', key: 'notes' },
                  { label: '', key: 'actions' },
                ]"
                @sort="sortBy"
              />
              <tbody>
                <tr
                  v-for="(lead, idx) in paginatedLeads"
                  :key="lead.email"
                >
                  <td>
                    <span
                      class="lead-contact-link"
                      @click="goToLeadDetail(lead)"
                      >{{ lead.contact }}</span
                    >
                  </td>
                  <td>{{ lead.email }}</td>
                  <td>{{ lead.phone }}</td>
                  <td>{{ lead.organization }}</td>
                  <td>{{ lead.size }}</td>
                  <td>{{ lead.source }}</td>
                  <td>{{ lead.status }}</td>
                  <td>
                    <button
                      class="btn-view"
                      @click="openNotesModal(lead, idx)"
                    >
                      <template v-if="lead.notesAction === 'View'">
                        <img
                          src="@/assets/images/Detail.svg"
                          alt="View"
                          class="btn-view-icon"
                        />
                        View
                      </template>
                      <template v-else>
                        <img
                          src="@/assets/images/AddBlack.svg"
                          alt="Add"
                          class="btn-view-icon"
                        />
                        Add
                      </template>
                    </button>
                  </td>
                  <td
                    style="position: relative"
                    @click.stop
                  >
                    <button
                      class="leads-menu-btn"
                      @click.stop="toggleMenu(idx)"
                    >
                      <img
                        src="@/assets/images/Actions.svg"
                        alt="Actions"
                        width="20"
                        height="20"
                        class="leads-menu-icon"
                      />
                    </button>
                    <div
                      v-if="menuOpen === idx"
                      class="leads-menu custom-leads-menu"
                      :style="getMenuPosition($event, idx)"
                      ref="menuDropdown"
                      @click.stop
                    >
                      <div
                        class="leads-menu-item"
                        v-for="option in customMenuOptions"
                        :key="option"
                        @click="selectCustomAction(idx, option)"
                      >
                        {{ option }}
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <Pagination
          :pageSize="pageSize"
          :pageSizes="[10, 25, 100]"
          :showPageDropdown="showPageDropdown"
          :currentPage="currentPage"
          :totalPages="totalPages"
          :paginationPages="paginationPages"
          @goToPage="goToPage"
          @selectPageSize="selectPageSize"
          @togglePageDropdown="showPageDropdown = !showPageDropdown"
        />
        <!-- Notes Modal -->
        <div
          v-if="showNotesModal"
          class="notes-modal-overlay"
          @click.self="closeNotesModal"
        >
          <div class="notes-modal">
            <h3>{{ notesModalMode === 'add' ? 'Add Notes' : 'Notes' }}</h3>
            <textarea
              v-model="notesInput"
              rows="5"
              placeholder="Enter notes here..."
              class="notes-textarea"
            ></textarea>
            <div class="notes-modal-actions">
              <button
                class="btn btn-secondary"
                @click="notesModalMode === 'add' ? submitNotes() : saveNotes()"
              >
                {{ notesModalMode === 'add' ? 'Submit' : 'Save' }}
              </button>
              <button
                class="btn btn-primary"
                @click="closeNotesModal"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import Pagination from '@/components/layout/Pagination.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
import axios from 'axios';
export default {
  name: 'Leads',
  components: { MainLayout, Pagination, TableHeader },
  data() {
    return {
      menuOpen: null,
      customMenuOptions: [
        'Schedule Follow Up',
        'Schedule Demo',
        'Schedule Class/Training',
        'Send Assessment',
        'Send Agreement/Payment Link',
        'Convert to Client',
      ],
      leads: [],
      pageSize: 10,
      currentPage: 1,
      showPageDropdown: false,
      showNotesModal: false,
      notesModalMode: 'add', // 'add' or 'view'
      notesInput: '',
      currentLead: null,
      currentLeadIdx: null,
      sortKey: '',
      sortAsc: true,
      loading: false,
      errorMessage: '',
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.leads.length / this.pageSize) || 1;
    },
    paginatedLeads() {
      let leads = [...this.leads];
      if (this.sortKey) {
        leads.sort((a, b) => {
          const aVal = a[this.sortKey] || '';
          const bVal = b[this.sortKey] || '';
          if (aVal < bVal) return this.sortAsc ? -1 : 1;
          if (aVal > bVal) return this.sortAsc ? 1 : -1;
          return 0;
        });
      }
      const start = (this.currentPage - 1) * this.pageSize;
      return leads.slice(start, start + this.pageSize);
    },
    paginationPages() {
      // Show 1, 2, 3, ..., 8, 9, 10 (with ellipsis in the middle)
      const total = this.totalPages;
      if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
      } else {
        const pages = [1];
        if (this.currentPage > 4) pages.push('...');
        for (
          let i = Math.max(2, this.currentPage - 1);
          i <= Math.min(total - 1, this.currentPage + 1);
          i++
        ) {
          pages.push(i);
        }
        if (this.currentPage < total - 3) pages.push('...');
        pages.push(total);
        return pages;
      }
    },
  },
  methods: {
    async fetchLeads() {
      this.loading = true;
      this.errorMessage = '';
      try {
        const token = localStorage.getItem('authToken');
        if (!token) {
          this.errorMessage = 'Authentication token not found. Please log in.';
          this.loading = false;
          return;
        }
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const response = await axios.get(`${API_BASE_URL}/api/leads`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        // Transform backend data to frontend format
        this.leads = response.data.map((lead) => ({
          contact: `${lead.first_name} ${lead.last_name}`,
          email: lead.email,
          phone: lead.phone,
          organization: lead.org_name,
          size: lead.org_size,
          source: lead.find_us,
          status: 'Lead Stage', // You can adjust this if you have a status field
          notesAction: 'Add', // Default, can be changed if you have notes
          notes: '', // Default, can be changed if you have notes
          ...lead,
        }));
      } catch (error) {
        console.error('Error fetching leads:', error);
        this.errorMessage = 'Failed to load leads.';
      } finally {
        this.loading = false;
      }
    },
    selectCustomAction(idx, option) {
      this.menuOpen = null;
      const lead = this.leads[idx];
      if (option === 'Send Assessment') {
        // Pass all lead details as query params
        const query = {
          contact: lead.contact,
          email: lead.email,
          phone: lead.phone,
          organization: lead.organization,
          size: lead.size,
          source: lead.source,
          status: lead.status,
        };
        if (this.$route.path.startsWith('/assessments')) {
          this.$router.push({ path: '/assessments/send-assessment', query });
        } else {
          this.$router.push({ path: '/leads/send-assessment', query });
        }
        return;
      }
      if (option === 'Schedule Demo') {
        this.$router.push('/leads/schedule-demo');
        return;
      }
      if (option === 'Schedule Class/Training') {
        this.$router.push('/leads/schedule-class-training');
        return;
      }
      this.$set(this.leads[idx], 'selectedCustomAction', option);
    },
    handleGlobalClick(e) {
      if (this.menuOpen !== null) {
        let menu = document.querySelector('.leads-menu.custom-leads-menu');
        let btns = document.querySelectorAll('.leads-menu-btn');
        if (menu && menu.contains(e.target)) return;
        for (let btn of btns) {
          if (btn.contains(e.target)) return;
        }
        this.menuOpen = null;
      }
    },
    goToLeadDetail(lead) {
      this.$router.push({
        name: 'LeadDetail',
        params: { email: lead.email },
        query: { ...lead },
      });
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
    openNotesModal(lead, idx) {
      this.currentLead = lead;
      this.currentLeadIdx = idx + (this.currentPage - 1) * this.pageSize;
      if (lead.notesAction === 'View') {
        this.notesModalMode = 'view';
      } else {
        this.notesModalMode = 'add';
      }
      this.notesInput = lead.notes || '';
      this.showNotesModal = true;
    },
    submitNotes() {
      // Logic to save notes for add
      const lead = this.leads[this.currentLeadIdx];
      lead.notes = this.notesInput;
      lead.notesAction = 'View';
      this.closeNotesModal();
    },
    saveNotes() {
      // Logic to save notes for view/edit
      const lead = this.leads[this.currentLeadIdx];
      lead.notes = this.notesInput;
      // notesAction remains 'View'
      this.closeNotesModal();
    },
    closeNotesModal() {
      this.showNotesModal = false;
      this.notesInput = '';
      this.currentLead = null;
      this.currentLeadIdx = null;
    },
    sortBy(key) {
      if (this.sortKey === key) {
        this.sortAsc = !this.sortAsc;
      } else {
        this.sortKey = key;
        this.sortAsc = true;
      }
    },
    toggleMenu(idx) {
      this.menuOpen = this.menuOpen === idx ? null : idx;
    },
    getMenuPosition(event, idx) {
      // Find the button and menu DOM nodes
      const btn = event?.target?.closest('.leads-menu-btn');
      if (!btn) return {};
      const rect = btn.getBoundingClientRect();
      const menuWidth = 200; // Approximate width of menu
      const padding = 8;
      const viewportWidth = window.innerWidth;
      let left = rect.left;
      let right = 'auto';
      // If menu would overflow right, align right
      if (rect.left + menuWidth + padding > viewportWidth) {
        left = 'auto';
        right = 0;
      }
      return {
        left: left !== 'auto' ? `${left}px` : 'auto',
        right: right !== 'auto' ? `${right}px` : 'auto',
        top: `${rect.bottom + window.scrollY}px`,
        minWidth: menuWidth + 'px',
        zIndex: 2000,
      };
    },
  },
  mounted() {
    document.addEventListener('click', this.handleGlobalClick);
    this.fetchLeads();
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleGlobalClick);
  },
};
</script>

<style scoped>
/* --- Other styles (unchanged, but moved for clarity) --- */
.leads-notes-btn {
  background: #fff;
  border: 1.5px solid #e0e0e0;
  border-radius: 24px;
  padding: 4px 12px;
  font-size: 14px;
  color: #222;
  cursor: pointer;
  transition: border 0.2s;
  display: flex;
  align-items: center;
  font-weight: 500;
  gap: 4px;
}
.leads-notes-btn:hover {
  border: 1.5px solid #0074c2;
}
.leads-menu-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 2px;
  border-radius: 50%;
  transition: background 0.2s;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.leads-menu-btn:hover {
  background: #f0f0f0;
  border-radius: 50%;
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.08);
}
.leads-menu-icon {
  width: 20px;
  height: 20px;
  display: block;
}
.leads-menu.custom-leads-menu {
  position: absolute;
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.08);
  min-width: 180px;
  z-index: 4000;
  display: flex;
  flex-direction: column;
  padding: 4px 0;
  right: 0;
  left: auto;
  top: 44px;
  max-width: 90vw;
  overflow-x: visible;
}
.leads-menu-item {
  padding: 8px 12px;
  font-size: 14px;
  color: #222;
  cursor: pointer;
  transition: background 0.2s;
}
.leads-menu-item:hover {
  background: #f0f8ff;
}

.leads-add-btn-icon {
  width: 18px;
  height: 18px;
  margin-right: 6px;
  display: inline-block;
  vertical-align: middle;
}
.notes-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.25);
  z-index: 3000;
  display: flex;
  align-items: center;
  justify-content: center;
}
.notes-modal {
  background: #fff;
  border-radius: 12px;
  padding: 32px 32px 24px 32px;
  min-width: 480px;
  max-width: 600px;
  box-shadow: 0 4px 32px rgba(0, 0, 0, 0.12);
  display: flex;
  flex-direction: column;
  align-items: stretch;
}
.notes-modal h3 {
  margin-top: 0;
  margin-bottom: 18px;
  text-align: center;
  font-size: 20px;
  font-weight: 600;
}
.notes-textarea {
  width: 100%;
  box-sizing: border-box;
  min-height: 100px;
  border-radius: 8px;
  border: 1.5px solid #e0e0e0;
  padding: 14px 16px;
  font-size: 15px;
  margin-bottom: 18px;
  resize: vertical;
  display: block;
}
.notes-modal-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 8px;
}

.lead-contact-link {
  cursor: pointer;
  font-weight: 500;
}

.btn-view {
  min-width: 80px;
  justify-content: center;
}
</style>
