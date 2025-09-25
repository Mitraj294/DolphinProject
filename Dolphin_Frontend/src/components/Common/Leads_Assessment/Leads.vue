<template>
  <MainLayout>
    <div class="page">
      <Toast />
      <div class="table-outer">
        <div class="table-card">
          <div class="table-search-header-row">
            <div class="table-search-bar-filters">
              <input
                class="org-search"
                placeholder="Search Leads ...."
                v-model="search"
              />
              <div class="table-search-bar-filters">
                <FormDropdown
                  v-model="form.organization_size"
                  icon="fas fa-users"
                  :options="[
                    { value: null, text: 'Select', disabled: true },
                    ...orgSizeOptions.map((o) => ({ value: o, text: o })),
                  ]"
                  required
                />
                <FormDropdown
                  v-model="form.find_us"
                  icon="fas fa-search"
                  :options="[
                    { value: null, text: 'Select', disabled: true },
                    ...findUsOptions.map((o) => ({ value: o, text: o })),
                  ]"
                  required
                />
              </div>
            </div>

            <button
              class="btn btn-primary add-new-btn"
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
                  :columns="tableColumns"
                  @sort="sortBy"
                />
                <tbody>
                  <tr
                    v-for="(lead, idx) in paginatedLeads"
                    :key="lead.id || lead.email"
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
                        @click="openNotesModal(lead)"
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
                          @click.stop="toggleMenu(lead, $event)"
                          aria-haspopup="true"
                          :aria-expanded="menuOpen === lead"
                        >
                          <img
                            src="@/assets/images/Actions.svg"
                            alt="Actions"
                            width="20"
                            height="20"
                            class="leads-menu-icon"
                          />
                        </button>
                        <!-- menu is rendered globally via teleport (see below) -->
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
                class="btn btn-primary"
                @click="updateLeadNotes"
              >
                {{ notesModalMode === 'add' ? 'Submit' : 'Update' }}
              </button>
              <button
                class="btn btn-secondary"
                @click="closeNotesModal"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
        <!-- Teleported menu rendered at body to avoid table clipping -->
        <teleport to="body">
          <div
            v-if="menuOpen"
            class="leads-menu custom-leads-menu teleported-leads-menu"
            :style="{
              top: `${menuPosition.top}px`,
              right: '33px',
            }"
            role="menu"
            @click.stop
          >
            <div
              class="leads-menu-item"
              v-for="option in customMenuOptions"
              :key="option"
              @click="selectCustomAction(menuOpen, option)"
              role="menuitem"
              tabindex="0"
            >
              {{ option }}
            </div>
          </div>
        </teleport>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import FormDropdown from '@/components/Common/Common_UI/Form/FormDropdown.vue';
// Component Imports
import MainLayout from '@/components/layout/MainLayout.vue';
import Pagination from '@/components/layout/Pagination.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
import Toast from 'primevue/toast';
import { findUsOptions, orgSizeOptions } from '@/utils/formUtils';
// Services
import storage from '@/services/storage.js';

export default {
  name: 'Leads',
  components: {
    MainLayout,
    Pagination,
    TableHeader,
    Toast,
    FormDropdown,
  },
  setup() {
    const router = useRouter();
    const toast = useToast();

    const leads = ref([]);
    const menuOpen = ref(null);
    const menuPosition = ref({ top: 0, left: 0 });
    const pageSize = ref(10);
    const currentPage = ref(1);
    const showPageDropdown = ref(false);
    const showNotesModal = ref(false);
    const notesModalMode = ref('add');
    const notesInput = ref('');
    const currentLead = ref(null);
    const sortKey = ref('');
    const sortAsc = ref(true);
    const isLoading = ref(false);
    const search = ref('');

    const customMenuOptions = [
      'Schedule Follow up',
      'Schedule Demo',
      'Schedule Class/Training',
      'Send Assessment',
      'Send Agreement/Payment Link',
      'Convert to Client',
      'Delete Lead',
    ];

    const tableColumns = [
      { label: 'Contact', key: 'contact', sortable: true, width: '170px' },
      { label: 'Email', key: 'email', width: '250px' },
      { label: 'Phone Number', key: 'phone', width: '150px' },
      {
        label: 'Organization',
        key: 'organization',
        sortable: true,
        width: '175px',
      },
      { label: 'Size', width: '250px' },
      { label: 'Source', width: '120px' },
      { label: 'Status', key: 'status', sortable: true, width: '150px' },
      { label: 'Notes', key: 'notesAction', width: '100px' },
      { label: 'Actions', key: 'actions', width: '80px' },
    ];

    const form = ref({
      organization_size: null,
      find_us: null,
    });

    const filteredLeads = computed(() => {
      let filtered = leads.value;

      if (search.value) {
        const searchTerm = search.value.toLowerCase();
        filtered = filtered.filter(
          (lead) =>
            lead.contact.toLowerCase().includes(searchTerm) ||
            lead.email.toLowerCase().includes(searchTerm) ||
            lead.organization.toLowerCase().includes(searchTerm)
        );
      }

      if (form.value.organization_size) {
        filtered = filtered.filter(
          (lead) => lead.size === form.value.organization_size
        );
      }

      if (form.value.find_us) {
        filtered = filtered.filter(
          (lead) => lead.source === form.value.find_us
        );
      }

      return filtered;
    });

    const statusOrder = {
      'Lead Stage': 1,
      'Assessment Sent': 2,
      Registered: 3,
    };

    const sortedLeads = computed(() => {
      if (!sortKey.value) return filteredLeads.value;

      return [...filteredLeads.value].sort((a, b) => {
        let comparison = 0;
        if (sortKey.value === 'status') {
          const orderA = statusOrder[a.status || 'Lead Stage'] || 0;
          const orderB = statusOrder[b.status || 'Lead Stage'] || 0;
          comparison = orderA - orderB;
        } else {
          const valA = a[sortKey.value];
          const valB = b[sortKey.value];
          if (valA < valB) comparison = -1;
          if (valA > valB) comparison = 1;
        }
        return sortAsc.value ? comparison : -comparison;
      });
    });

    const totalPages = computed(() =>
      Math.ceil(sortedLeads.value.length / pageSize.value)
    );

    const paginatedLeads = computed(() => {
      const start = (currentPage.value - 1) * pageSize.value;
      const end = start + pageSize.value;
      return sortedLeads.value.slice(start, end);
    });

    const paginationPages = computed(() => {
      const pages = [];
      for (let i = 1; i <= totalPages.value; i++) {
        pages.push(i);
      }
      return pages;
    });

    const fetchLeads = async () => {
      isLoading.value = true;
      try {
        const token = storage.get('authToken');
        if (!token) {
          router.push('/login');
          return;
        }
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const response = await axios.get(`${API_BASE_URL}/api/leads`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        leads.value = response.data.map((lead) => ({
          id: lead.id,
          contact: `${lead.first_name} ${lead.last_name}`,
          email: lead.email,
          phone: lead.phone,
          organization: lead.organization_name,
          size: lead.organization_size,
          source: lead.find_us,
          status: lead.status,
          notes: lead.notes,
          notesAction: lead.notes ? 'View' : 'Add',
        }));
      } catch (error) {
        console.error('Failed to fetch leads:', error);
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to fetch leads.',
          life: 3000,
        });
      } finally {
        isLoading.value = false;
      }
    };

    const sortBy = (key) => {
      if (sortKey.value === key) {
        sortAsc.value = !sortAsc.value;
      } else {
        sortKey.value = key;
        sortAsc.value = true;
      }
    };

    const goToPage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
      }
    };

    const selectPageSize = (size) => {
      pageSize.value = size;
      currentPage.value = 1;
      showPageDropdown.value = false;
    };

    const toggleMenu = (lead, event) => {
      // if closing the same menu
      if (menuOpen.value && menuOpen.value === lead) {
        menuOpen.value = null;
        return;
      }
      // compute a safe position for the teleported menu
      try {
        const btn = event.currentTarget || event.target;
        const rect = btn.getBoundingClientRect();
        const menuWidth = 220; // approximate
        const padding = 8;
        // prefer aligning right edge with button right edge
        let left = rect.right + window.scrollX - menuWidth;
        if (left < padding) left = rect.left + window.scrollX;
        if (left + menuWidth > window.innerWidth - padding) {
          left = Math.max(padding, window.innerWidth - menuWidth - padding);
        }
        const top = rect.bottom + window.scrollY + 6;
        menuPosition.value = { top, left };
      } catch (e) {
        console.warn(
          'Failed to position teleported menu, defaulting to 0,0',
          e
        );
        menuPosition.value = { top: 0, left: 0 };
      }
      menuOpen.value = lead;
    };

    const handleClickOutside = (event) => {
      if (!menuOpen.value) return;
      const clickedInActions = event.target.closest('.actions-row');
      const clickedInMenu = event.target.closest('.teleported-leads-menu');
      if (!clickedInActions && !clickedInMenu) {
        menuOpen.value = null;
      }
    };

    const onKeyDown = (event) => {
      if (event.key === 'Escape' && menuOpen.value) menuOpen.value = null;
    };

    const openNotesModal = (lead) => {
      currentLead.value = lead;
      notesInput.value = lead.notes || '';
      notesModalMode.value = lead.notes ? 'view' : 'add';
      showNotesModal.value = true;
    };

    const closeNotesModal = () => {
      showNotesModal.value = false;
      currentLead.value = null;
      notesInput.value = '';
    };

    const updateLeadNotes = async () => {
      if (!currentLead.value) return;
      try {
        const token = storage.get('authToken');
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        await axios.patch(
          `${API_BASE_URL}/api/leads/${currentLead.value.id}`,
          { notes: notesInput.value },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        toast.add({
          severity: 'success',
          summary: 'Success',
          detail: 'Notes updated successfully.',
          life: 3000,
        });
        const lead = leads.value.find((l) => l.id === currentLead.value.id);
        if (lead) {
          lead.notes = notesInput.value;
          lead.notesAction = notesInput.value ? 'View' : 'Add';
        }
        closeNotesModal();
      } catch (error) {
        console.error('Failed to update notes:', error);
        toast.add({
          severity: 'error',
          summary: 'Error',
          detail: 'Failed to update notes.',
          life: 3000,
        });
      }
    };

    const goToLeadDetail = (lead) => {
      router.push({ name: 'LeadDetail', params: { id: lead.id } });
    };

    const selectCustomAction = (lead, option) => {
      console.log(`Action "${option}" selected for lead:`, lead.contact);
      menuOpen.value = null;
      if (option === 'Schedule Demo') {
        router.push({ name: 'ScheduleDemo', query: { lead_id: lead.id } });
      } else if (option === 'Schedule Class/Training') {
        router.push({
          name: 'ScheduleClassTraining',
          query: { lead_id: lead.id },
        });
      } else if (option === 'Send Assessment') {
        router.push({ name: 'SendAssessment', params: { id: lead.id } });
      } else {
        toast.add({
          severity: 'info',
          summary: 'Info',
          detail: `Action "${option}" is not implemented yet.`,
          life: 3000,
        });
      }
    };

    onMounted(() => {
      fetchLeads();
      document.addEventListener('click', handleClickOutside);
      document.addEventListener('keydown', onKeyDown);
    });

    onBeforeUnmount(() => {
      document.removeEventListener('click', handleClickOutside);
      document.removeEventListener('keydown', onKeyDown);
    });

    return {
      leads,
      menuOpen,
      menuPosition,
      pageSize,
      currentPage,
      showPageDropdown,
      showNotesModal,
      notesModalMode,
      notesInput,
      currentLead,
      sortKey,
      sortAsc,
      isLoading,
      customMenuOptions,
      tableColumns,
      paginatedLeads,
      totalPages,
      paginationPages,
      sortBy,
      goToPage,
      selectPageSize,
      toggleMenu,
      openNotesModal,
      closeNotesModal,
      updateLeadNotes,
      goToLeadDetail,
      selectCustomAction,
      findUsOptions,
      orgSizeOptions,
      form,
      search,
    };
  },
};
</script>

<style scoped>
.form-box {
  min-height: 32px !important;
  max-height: 32px !important;
  max-width: 210px !important;
  border: 1.5px solid #e0e0e0;
  border-radius: 999px;
}
@media (max-width: 950px) {
  .table-search-header-row {
    display: flex;
    flex-direction: column;
    padding: 18px 24px 18px 24px;
    background: #fff;
    border-top-left-radius: 24px;
    border-top-right-radius: 24px;
    margin-bottom: 0;
    flex-wrap: wrap;
    align-content: stretch;
    align-items: flex-end;
  }
  .table-search-bar-filters {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-right: auto;
    width: 100%;
    margin-bottom: 12px;
  }
}

/* Flex row for search and add button */
.table-search-header-row {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px 18px 24px;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  margin-bottom: 0;
}
.table-search-bar-filters {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  margin-right: auto;
}
.table-search-bar-filters-right {
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
  align-items: right;
  gap: 12px;
  flex-wrap: wrap;
  margin-right: auto;
}

/* All original styles are preserved below */
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
  position: absolute;
  top: 100%;
  right: 0;
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.08);
  min-width: 180px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  padding: 4px 0;
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
  min-width: 280px;
  max-width: 600px;
  width: 90%;
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
  color: #0074c2;
}

.btn-view {
  min-width: 80px;
  justify-content: center;
  background: none;
  border: 1px solid #ccc;
  border-radius: 20px;
  padding: 5px 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
}

.btn-view-icon {
  width: 16px;
  height: 16px;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 135px;
  height: 30px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  text-align: center;
  white-space: nowrap;
  padding: 0 12px;
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

/* --- Table Search Bar Styles --- */
.table-search-bar {
  padding: 18px 46px 18px 24px;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: flex-start;
}
.org-search {
  min-width: 210px;
  width: 432px;
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
}
.org-search::placeholder {
  margin-left: 4px;
}

.form-input-with-icon {
  max-height: 32px !important;
  border: 1.5px solid #e0e0e0;
  border-radius: 999px;
}
</style>
