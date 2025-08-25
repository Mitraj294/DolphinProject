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
            <div class="table-scroll">
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
                    <td data-label="Contact">
                      <span
                        class="lead-contact-link"
                        @click="goToLeadDetail(lead)"
                        >{{ lead.contact }}</span
                      >
                    </td>
                    <td data-label="Email">{{ lead.email }}</td>
                    <td data-label="Phone Number">{{ lead.phone }}</td>
                    <td data-label="Organization">{{ lead.organization }}</td>
                    <td data-label="Size">{{ lead.size }}</td>
                    <td data-label="Source">{{ lead.source }}</td>
                    <td data-label="Status">{{ lead.status }}</td>
                    <td data-label="Notes">
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
                      data-label="Actions"
                      style="position: relative"
                    >
                      <div class="actions-row">
                        <button
                          class="leads-menu-btn"
                          @click.stop="toggleMenu(idx)"
                          aria-haspopup="true"
                          :aria-expanded="menuOpen === idx"
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
                          :style="menuStyle"
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
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
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
      menuStyle: {},
      customMenuOptions: [
        // 'Schedule Follow Up',
        'Schedule Demo',
        'Schedule Class/Training',
        'Send Assessment',
        //'Send Agreement/Payment Link',
        //'Convert to Client',
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
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');
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
        // Transform backend data to frontend format and keep notes from backend
        this.leads = response.data.map((lead) => ({
          contact: `${lead.first_name} ${lead.last_name}`,
          email: lead.email,
          phone: lead.phone,
          organization: lead.org_name,
          size: lead.org_size,
          source: lead.find_us,
          status: lead.status || 'Lead Stage',
          // If backend provides notes field, show "View" else "Add"
          notesAction:
            lead.notes && String(lead.notes).trim().length ? 'View' : 'Add',
          notes: lead.notes || '',
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
      // Close any open menu and restore scrolling
      this.menuOpen = null;
      try {
        document.body.style.overflow = '';
      } catch (e) {}
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
    async submitNotes() {
      // Save new notes (add)
      const lead = this.leads[this.currentLeadIdx];
      if (!lead || !lead.id) {
        // update locally and close
        if (lead) {
          lead.notes = this.notesInput;
          lead.notesAction = 'View';
        }
        this.closeNotesModal();
        return;
      }
      try {
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        await axios.patch(
          `${API_BASE_URL}/api/leads/${lead.id}`,
          { notes: this.notesInput },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        // update local copy
        lead.notes = this.notesInput;
        lead.notesAction = 'View';
      } catch (e) {
        console.error('Failed to submit notes', e);
        this.errorMessage = 'Failed to save notes.';
      } finally {
        this.closeNotesModal();
      }
    },
    async saveNotes() {
      // Save edited notes
      const lead = this.leads[this.currentLeadIdx];
      if (!lead || !lead.id) {
        if (lead) lead.notes = this.notesInput;
        this.closeNotesModal();
        return;
      }
      try {
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        await axios.patch(
          `${API_BASE_URL}/api/leads/${lead.id}`,
          { notes: this.notesInput },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        lead.notes = this.notesInput;
      } catch (e) {
        console.error('Failed to save notes', e);
        this.errorMessage = 'Failed to save notes.';
      } finally {
        this.closeNotesModal();
      }
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
      const opening = this.menuOpen !== idx;
      this.menuOpen = opening ? idx : null;
      // Lock body scrolling when menu open
      try {
        document.body.style.overflow = opening ? 'hidden' : '';
      } catch (e) {}
      if (opening) {
        // set initial position and attach listeners
        this.updateMenuPosition(idx);
        window.addEventListener('scroll', this._menuScrollHandler, {
          passive: true,
        });
        window.addEventListener('resize', this._menuResizeHandler);
      } else {
        // closing
        this.menuStyle = {};
        window.removeEventListener('scroll', this._menuScrollHandler);
        window.removeEventListener('resize', this._menuResizeHandler);
      }
    },
    getMenuPosition(idx) {
      // Fallback (unused when menuStyle is set) - compute fixed coordinates
      const buttons = document.querySelectorAll('.leads-menu-btn');
      const btn = buttons && buttons[idx] ? buttons[idx] : null;
      if (!btn) return {};
      const rect = btn.getBoundingClientRect();
      const menuWidth = 220; // menu width
      const padding = 8;
      const viewportWidth = window.innerWidth;
      let left = rect.left + rect.width / 2 - menuWidth / 2;
      left = Math.max(
        padding,
        Math.min(left, viewportWidth - menuWidth - padding)
      );
      const top = rect.bottom + window.scrollY + 8;
      return {
        left: `${Math.round(left)}px`,
        top: `${Math.round(top)}px`,
        minWidth: menuWidth + 'px',
      };
    },

    updateMenuPosition(idx) {
      const buttons = document.querySelectorAll('.leads-menu-btn');
      const btn = buttons && buttons[idx] ? buttons[idx] : null;
      if (!btn) return;
      const rect = btn.getBoundingClientRect();
      const menuWidth = 220;
      const padding = 8;
      const viewportWidth = window.innerWidth;
      let left = rect.left + rect.width / 2 - menuWidth / 2;
      left = Math.max(
        padding,
        Math.min(left, viewportWidth - menuWidth - padding)
      );
      const top = rect.bottom + window.scrollY + 8;
      this.menuStyle = {
        left: `${Math.round(left)}px`,
        top: `${Math.round(top)}px`,
        minWidth: menuWidth + 'px',
      };
    },
  },
  mounted() {
    document.addEventListener('click', this.handleGlobalClick);
    // handlers for keeping menu aligned
    this._menuScrollHandler = () => {
      if (this.menuOpen !== null) this.updateMenuPosition(this.menuOpen);
    };
    this._menuResizeHandler = () => {
      if (this.menuOpen !== null) this.updateMenuPosition(this.menuOpen);
    };
    this.fetchLeads();
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleGlobalClick);
    window.removeEventListener('scroll', this._menuScrollHandler);
    window.removeEventListener('resize', this._menuResizeHandler);
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
  /* Use fixed positioning so the menu is never clipped by scrolling containers */
  position: fixed !important;
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.08);
  min-width: 180px;
  z-index: 10050;
  display: flex;
  flex-direction: column;
  padding: 4px 0;
  left: auto;
  right: auto;
  top: auto;
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

.table-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
</style>
