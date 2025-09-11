<template>
  <MainLayout>
    <div class="page">
      <Toast />
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
                    {
                      label: 'Contact',
                      key: 'contact',
                      sortable: true,
                      width: '170px',
                    },
                    { label: 'Email', key: 'email', width: '250px' },
                    {
                      label: 'Phone Number',
                      key: 'phone',
                      width: '150px',
                      position: 'relative',
                    },
                    {
                      label: 'Organization',
                      key: 'organization',
                      sortable: true,
                      width: '150px',
                      position: 'relative',
                    },
                    {
                      label: 'Size',
                      key: 'size',
                      sortable: true,
                      width: '220px',
                      class: 'text-center',
                      position: 'relative',
                    },
                    { label: 'Source', key: 'source', width: '90px' },
                    {
                      label: 'Status',
                      key: 'status',
                      sortable: true,
                      width: '150px',
                      position: 'relative',
                    },
                    {
                      label: 'Notes',
                      key: 'notes',
                      width: '150px',
                      position: 'relative',
                    },
                    {
                      label: '',
                      key: 'actions',
                      width: '75px',
                      class: 'text-center',
                      position: 'initial',
                    },
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
                    <td data-label="Status">
                      <span
                        class="status-badge"
                        :class="
                          lead.status &&
                          lead.status.toLowerCase().replace(/\s+/g, '-')
                        "
                      >
                        {{ lead.status || 'Lead Stage' }}
                      </span>
                    </td>
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
                class="org-edit-cancel"
                @click="notesModalMode === 'add' ? submitNotes() : saveNotes()"
              >
                {{ notesModalMode === 'add' ? 'Submit' : 'Update' }}
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
import Toast from 'primevue/toast';
export default {
  name: 'Leads',
  components: { MainLayout, Pagination, TableHeader, Toast },
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
      currentLeadId: null,
      currentLeadIdx: null,
      sortKey: '',
      sortAsc: true,
      loading: false,
      errorMessage: '',
      _scrollableParents: null, // Store scrollable parent elements
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
          organization: lead.organization_name,
          size: lead.organization_size,
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

      // Handle different action types
      if (option === 'Send Assessment') {
        this.handleSendAssessment(lead);
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

      // Default action
      this.$set(this.leads[idx], 'selectedCustomAction', option);
    },

    handleSendAssessment(lead) {
      // Handle persisted leads with ID
      if (lead.id) {
        this.navigateToAssessmentWithId(lead);
        return;
      }

      // Handle unsaved leads with query params
      this.navigateToAssessmentWithQuery(lead);
    },

    navigateToAssessmentWithId(lead) {
      try {
        this.$router.push({
          name: 'SendAssessment',
          params: { id: lead.id },
        });
      } catch (e) {
        console.error(
          'Failed to navigate with named route, falling back to path-based navigation:',
          e
        );

        const basePath = this.getAssessmentBasePath();
        this.$router.push({
          path: `${basePath}/send-assessment`,
          query: { id: lead.id }, // Use query instead of params for path-based navigation
        });
      }
    },

    navigateToAssessmentWithQuery(lead) {
      const query = {
        contact: lead.contact,
        email: lead.email,
        phone: lead.phone,
        organization: lead.organization,
        size: lead.size,
        source: lead.source,
        status: lead.status,
      };

      const basePath = this.getAssessmentBasePath();
      this.$router.push({
        path: `${basePath}/send-assessment`,
        query,
      });
    },

    getAssessmentBasePath() {
      return this.$route.path.startsWith('/assessments')
        ? '/assessments'
        : '/leads';
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
      // Navigate to lead detail by id (preferred). Keep some basic query info
      // for backward compatibility, but route param will be the canonical id.
      const id = lead.id || lead.email || '';
      this.$router.push({
        name: 'LeadDetail',
        params: { id },
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
      } catch (e) {
        console.error('Failed to toggle body overflow', e);
      }

      this.currentLeadId = lead.id || null;

      if (this.currentLeadId) {
        this.currentLeadIdx = this.leads.findIndex(
          (l) => l.id === this.currentLeadId
        );
      } else {
        // best-effort matching for leads without id
        this.currentLeadIdx = this.leads.findIndex(
          (l) =>
            (l.email && l.email === lead.email) ||
            (l.phone && l.phone === lead.phone) ||
            (l.contact && l.contact === lead.contact)
        );
        // if still not found, fallback to positional mapping (old behavior)
        if (this.currentLeadIdx === -1) {
          this.currentLeadIdx = idx + (this.currentPage - 1) * this.pageSize;
        }
      }
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
      // Resolve the lead to update from stored id or index
      let lead = null;
      if (this.currentLeadId) {
        lead = this.leads.find((l) => l.id === this.currentLeadId);
      }
      if (!lead && this.currentLeadIdx != null && this.currentLeadIdx >= 0) {
        lead = this.leads[this.currentLeadIdx];
      }
      if (!lead || !lead.id) {
        // update locally and close for leads that aren't persisted yet
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
        // show success toast
        if (this.$toast && typeof this.$toast.add === 'function') {
          this.$toast.add({
            severity: 'success',
            summary: 'Notes Saved',
            detail: 'Notes added successfully.',
            life: 3000,
          });
        }
      } catch (e) {
        console.error('Failed to submit notes', e);
        this.errorMessage = 'Failed to save notes.';
        if (this.$toast && typeof this.$toast.add === 'function') {
          this.$toast.add({
            severity: 'error',
            summary: 'Save Failed',
            detail: 'Could not save notes. Try again.',
            life: 4000,
          });
        }
      } finally {
        this.closeNotesModal();
      }
    },
    async saveNotes() {
      // Save edited notes. Resolve lead the same way as submitNotes.
      let lead = null;
      if (this.currentLeadId) {
        lead = this.leads.find((l) => l.id === this.currentLeadId);
      }
      if (!lead && this.currentLeadIdx != null && this.currentLeadIdx >= 0) {
        lead = this.leads[this.currentLeadIdx];
      }
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
        if (this.$toast && typeof this.$toast.add === 'function') {
          this.$toast.add({
            severity: 'success',
            summary: 'Notes Updated',
            detail: 'Notes updated successfully.',
            life: 3000,
          });
        }
      } catch (e) {
        console.error('Failed to save notes', e);
        this.errorMessage = 'Failed to save notes.';
        if (this.$toast && typeof this.$toast.add === 'function') {
          this.$toast.add({
            severity: 'error',
            summary: 'Update Failed',
            detail: 'Could not update notes. Try again.',
            life: 4000,
          });
        }
      } finally {
        this.closeNotesModal();
      }
    },
    closeNotesModal() {
      this.showNotesModal = false;
      this.notesInput = '';
      this.currentLead = null;
      this.currentLeadIdx = null;
      this.currentLeadId = null;
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
      } catch (e) {
        console.error('Failed to toggle body overflow', e);
      }
      if (opening) {
        // set initial position and attach listeners
        this.updateMenuPosition(idx);
        this.attachScrollListeners();
        window.addEventListener('resize', this._menuResizeHandler);
      } else {
        // closing
        this.menuStyle = {};
        this.detachScrollListeners();
        window.removeEventListener('resize', this._menuResizeHandler);
      }
    },

    // Find all scrollable parent elements
    findScrollableParents(element) {
      const scrollableParents = [];
      let parent = element.parentElement;

      while (parent && parent !== document.body) {
        const style = window.getComputedStyle(parent);
        const overflow = style.overflow + style.overflowX + style.overflowY;

        if (overflow.includes('scroll') || overflow.includes('auto')) {
          scrollableParents.push(parent);
        }
        parent = parent.parentElement;
      }

      // Always include window for document-level scrolling
      scrollableParents.push(window);
      return scrollableParents;
    },

    // Attach scroll listeners to all scrollable parents
    attachScrollListeners() {
      if (this.menuOpen === null) return;

      const buttons = document.querySelectorAll('.leads-menu-btn');
      const btn = buttons[this.menuOpen];
      if (!btn) return;

      // Find all scrollable parents
      this._scrollableParents = this.findScrollableParents(btn);

      // Attach scroll listeners to each scrollable parent
      this._scrollableParents.forEach((scrollableElement) => {
        scrollableElement.addEventListener('scroll', this._menuScrollHandler, {
          passive: true,
        });
      });
    },

    // Remove scroll listeners from all scrollable parents
    detachScrollListeners() {
      if (this._scrollableParents) {
        this._scrollableParents.forEach((scrollableElement) => {
          scrollableElement.removeEventListener(
            'scroll',
            this._menuScrollHandler
          );
        });
        this._scrollableParents = null;
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
      // For fixed positioning, use rect.bottom directly (no scrollY needed)
      const top = rect.bottom + 8;
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

      // Check if button is still visible in viewport
      if (
        rect.bottom < 0 ||
        rect.top > window.innerHeight ||
        rect.right < 0 ||
        rect.left > window.innerWidth
      ) {
        // Button is not visible, hide menu
        this.menuOpen = null;
        return;
      }

      const menuWidth = 220;
      const padding = 8;
      const viewportWidth = window.innerWidth;
      const viewportHeight = window.innerHeight;

      // Calculate horizontal position
      let left = rect.left + rect.width / 2 - menuWidth / 2;
      left = Math.max(
        padding,
        Math.min(left, viewportWidth - menuWidth - padding)
      );

      // Calculate vertical position with fallback for bottom overflow
      let top = rect.bottom + 8;
      const menuHeight = 120; // Approximate menu height

      // If menu would go below viewport, position it above the button
      if (top + menuHeight > viewportHeight) {
        top = rect.top - menuHeight - 8;
        // If still not enough space above, position at viewport edge
        if (top < padding) {
          top = Math.max(padding, viewportHeight - menuHeight - padding);
        }
      }

      this.menuStyle = {
        left: `${Math.round(left)}px`,
        top: `${Math.round(top)}px`,
        minWidth: menuWidth + 'px',
      };
    },
  },
  mounted() {
    document.addEventListener('click', this.handleGlobalClick);
    // handlers for keeping menu aligned with throttling for better performance
    this._menuScrollHandler = () => {
      if (this.menuOpen !== null) {
        // Use requestAnimationFrame for smooth updates
        if (!this._scrollRAF) {
          this._scrollRAF = requestAnimationFrame(() => {
            this.updateMenuPosition(this.menuOpen);
            this._scrollRAF = null;
          });
        }
      }
    };
    this._menuResizeHandler = () => {
      if (this.menuOpen !== null) {
        this.updateMenuPosition(this.menuOpen);
      }
    };
    this.fetchLeads();
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleGlobalClick);
    this.detachScrollListeners();
    window.removeEventListener('resize', this._menuResizeHandler);
    // Clean up any pending animation frame
    if (this._scrollRAF) {
      cancelAnimationFrame(this._scrollRAF);
      this._scrollRAF = null;
    }
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
  height: 30px;
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

.status-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 125px; /* fixed equal width for all badges */
  height: 30px; /* consistent height */
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  text-align: center;
  white-space: nowrap;
  padding: 0 8px; /* horizontal padding retained if text needs it */
  box-sizing: border-box;
}
.status-badge.lead-stage {
  background: #6c757d;
}
.status-badge.assessment-sent {
  background: #007bff;
}
.status-badge.registered {
  background: #28a745;
}
</style>
