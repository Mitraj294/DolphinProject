<template>
  <MainLayout>
    <div class="page">
      <div class="table-outer">
        <div class="assessment-table-card">
          <div class="assessment-summary-cards">
            <div class="assessment-summary-card">
              <div class="summary-label">Total Sent Assessment</div>
              <div class="summary-value">7</div>
            </div>
            <div class="assessment-summary-card">
              <div class="summary-label">Submitted Assessment</div>
              <div class="summary-value">5</div>
            </div>
            <div class="assessment-summary-card">
              <div class="summary-label">Pending</div>
              <div class="summary-value">2</div>
            </div>
          </div>
          <div class="assessment-table-header-spacer"></div>
          <div class="assessment-table-container">
            <table class="assessment-table">
              <TableHeader
                :columns="tableColumns"
                @sort="sortBy"
              />
              <tbody>
                <tr
                  v-for="row in paginatedRows"
                  :key="row.name"
                >
                  <td class="member-name-td">{{ row.name }}</td>
                  <td>
                    <span
                      v-if="row.result === 'Submitted'"
                      class="status submitted"
                    >
                      <svg
                        width="20"
                        height="20"
                        viewBox="0 0 20 20"
                        fill="none"
                      >
                        <circle
                          cx="10"
                          cy="10"
                          r="10"
                          fill="#48B02C"
                        />
                        <path
                          d="M6 10.5L9 13.5L14 8.5"
                          stroke="white"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                      Submitted
                    </span>
                    <span
                      v-else
                      class="status pending"
                    >
                      Pending
                    </span>
                  </td>
                  <td>
                    <button
                      class="btn-view"
                      @click="openModal(row)"
                    >
                      <img
                        src="@/assets/images/Notes.svg"
                        alt="View"
                        class="btn-view-icon"
                        width="18"
                        height="18"
                      />
                      View
                    </button>
                  </td>
                </tr>
                <tr v-if="paginatedRows.length === 0">
                  <td
                    colspan="3"
                    class="no-data"
                  >
                    No assessments found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <Pagination
          :pageSize="pageSize"
          :pageSizes="pageSizes"
          :showPageDropdown="showPageDropdown"
          :currentPage="currentPage"
          :totalPages="totalPages"
          @goToPage="goToPage"
          @selectPageSize="selectPageSize"
          @togglePageDropdown="togglePageDropdown"
        />
        <!-- Modal for assessment details -->
        <div
          v-if="showModal"
          class="assessment-modal-overlay"
          @click.self="closeModal"
        >
          <div class="assessment-modal-content">
            <div class="assessment-modal-header-row sticky-modal-header">
              <h2>{{ selectedMember.name }}’s Assessments</h2>
              <button
                class="btn modal-close-btn"
                @click="closeModal"
              >
                &times;
              </button>
            </div>
            <div class="assessment-modal-scrollable">
              <div
                v-for="(q, idx) in selectedMember.assessment || []"
                :key="idx"
                class="assessment-question-block"
              >
                <div class="assessment-question">
                  Q.{{ idx + 1 }} {{ q.question }}
                </div>
                <div class="assessment-answer">{{ q.answer }}</div>
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
import Pagination from '@/components/layout/Pagination.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';

export default {
  name: 'AssessmentSummaryPage',
  components: { MainLayout, Pagination, TableHeader },
  setup() {
    const route = useRoute();
    const assessmentId = route.params.assessmentId;

    // Example assessment data for each member
    const rows = [
      {
        name: 'Emily Carter',
        result: 'Submitted',
        assessment: [
          {
            question: 'What is the primary goal of a for-profit business?',
            answer:
              'The primary goal is to maximize profits while providing value to customers.',
          },
          {
            question: 'What does SWOT stand for in business analysis?',
            answer:
              'SWOT stands for Strengths, Weaknesses, Opportunities, and Threats.',
          },
          {
            question:
              'What is the difference between leadership and management?',
            answer:
              'Leadership focuses on inspiring and guiding people toward a vision, while management is about organizing, planning, and ensuring tasks are completed efficiently.',
          },
          {
            question: 'What is a mission statement?',
            answer:
              "A mission statement is a brief statement that defines an organization's purpose, core values, and overall goals.",
          },
          {
            question: 'What is the 80/20 rule (Pareto Principle) in business?',
            answer:
              'The 80/20 rule states that 80% of results come from 20% of efforts or customers. It helps businesses focus on the most impactful activities.',
          },
          {
            question: 'What is the difference between revenue and profit?',
            answer:
              'Revenue is the total income a business generates from sales, while profit is what remains after deducting expenses from revenue.',
          },
        ],
      },
      // ...other members, add assessment data as needed...
      { name: 'James Parker', result: 'Submitted', assessment: [] },
      { name: 'Sophia Mitchell', result: 'Submitted', assessment: [] },
      { name: 'Mason Walker', result: 'Submitted', assessment: [] },
      { name: 'Olivia Bennett', result: 'Submitted', assessment: [] },
      { name: 'Benjamin Hayes', result: 'Submitted', assessment: [] },
      { name: 'Ava Richardson', result: 'Submitted', assessment: [] },
    ];

    // Table columns for TableHeader
    const tableColumns = [
      { label: 'Member Name', key: 'name' },
      { label: 'Result', key: 'result' },
      { label: 'Actions', key: 'actions' },
    ];

    // Pagination state
    const pageSizes = [10, 25, 100];
    const pageSize = ref(10);
    const currentPage = ref(1);
    const showPageDropdown = ref(false);

    const totalPages = computed(() =>
      Math.max(1, Math.ceil(rows.length / pageSize.value))
    );

    const paginatedRows = computed(() => {
      const start = (currentPage.value - 1) * pageSize.value;
      return rows.slice(start, start + pageSize.value);
    });

    // Modal state
    const showModal = ref(false);
    const selectedMember = ref({});

    function openModal(row) {
      selectedMember.value = row;
      showModal.value = true;
    }
    function closeModal() {
      showModal.value = false;
    }

    function goToPage(page) {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
      }
    }
    function selectPageSize(size) {
      pageSize.value = size;
      currentPage.value = 1;
      showPageDropdown.value = false;
    }
    function togglePageDropdown() {
      showPageDropdown.value = !showPageDropdown.value;
    }

    // Sorting logic (optional, for TableHeader compatibility)
    function sortBy() {
      // No-op: implement if you want sorting
    }

    return {
      assessmentId,
      rows,
      paginatedRows,
      pageSizes,
      pageSize,
      currentPage,
      totalPages,
      showPageDropdown,
      goToPage,
      selectPageSize,
      togglePageDropdown,
      showModal,
      selectedMember,
      openModal,
      closeModal,
      tableColumns,
      sortBy,
    };
  },
};
</script>

<style scoped>
.assessment-table-outer {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
  margin: 64px auto 64px auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
}
.assessment-table-card {
  width: 100%;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  overflow: visible;
  margin: 0 auto;
  box-sizing: border-box;
  min-width: 0;
  max-width: 1400px;
  display: flex;
  flex-direction: column;
  gap: 0;
  position: relative;
}
.assessment-summary-cards {
  display: flex;
  gap: 16px;
  margin-bottom: 0;
  flex-wrap: wrap;
  justify-content: flex-start;
  padding: 24px 46px 0 24px;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  min-height: 64px;
  box-sizing: border-box;
}
.assessment-summary-card {
  background: #f8f8f8;
  border-radius: 16px;
  padding: 18px 16px;
  min-width: 90px;
  flex: 1 1 90px;
  text-align: center;
  margin: 0 0 12px 0;
  box-sizing: border-box;
}
.summary-label {
  color: #888;
  font-size: 16px;
  margin-bottom: 8px;
}
.summary-value {
  font-size: 32px;
  font-weight: 700;
  color: #222;
}
.assessment-table-header-spacer {
  height: 18px;
  width: 100%;
  background: transparent;
  display: block;
}
.assessment-table-container {
  width: 100%;
  overflow-x: auto;
  box-sizing: border-box;
  padding: 0 24px 24px 24px;
  background: #fff;
  border-bottom-left-radius: 24px;
  border-bottom-right-radius: 24px;
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.assessment-table-container::-webkit-scrollbar {
  display: none;
}
.assessment-table {
  min-width: 800px;
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin-bottom: 8px;
  background: transparent;
  margin-left: 0;
  margin-right: 0;
  table-layout: auto;
  border: none;
  margin-top: 0;
}
.assessment-table th,
.assessment-table td {
  padding: 12px 8px;
  text-align: left;
  font-size: 14px;
  border-bottom: 1px solid #f0f0f0;
  background: #fff;
}
.assessment-table th:first-child {
  padding-left: 20px !important;
}
.assessment-table th {
  background: #f8f8f8;
  font-weight: 600;
  color: #888;
  position: relative;
  vertical-align: middle;
  min-width: 100px;
  border-bottom: 1.5px solid #ebebeb;
}
.rounded-th-left {
  border-top-left-radius: 24px;
  border-bottom-left-radius: 24px;
  overflow: hidden;
  background: #f8f8f8;
  padding-left: 20px !important;
}
.rounded-th-right {
  border-top-right-radius: 24px;
  border-bottom-right-radius: 24px;
  overflow: hidden;
  background: #f8f8f8;
}
.assessment-table td {
  color: #222;
  background: #fff;
}
.status {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}
.status.submitted {
  color: #48b02c;
}
.status.pending {
  color: #bdbdbd;
}
.no-data {
  text-align: center;
  color: #888;
  font-size: 16px;
  padding: 32px 0;
}
/* Modal styles - match base modal style */
.assessment-modal-overlay {
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
.assessment-modal-content {
  background: #fff;
  border-radius: 12px;
  padding: 0;
  min-width: 480px;
  max-width: 600px;
  box-shadow: 0 4px 32px rgba(0, 0, 0, 0.12);
  display: flex;
  flex-direction: column;
  align-items: stretch;
  position: relative;
  max-height: 65vh;
  overflow: hidden;
}
.assessment-modal-header-row {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 0;
  padding: 16px 32px 10px 32px;
  background: #fff;
  z-index: 2;
}
.sticky-modal-header {
  position: sticky;
  top: 0;
  left: 0;
  right: 0;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}
.assessment-modal-header-row h2 {
  font-size: 1.45rem;
  font-weight: 700;
  letter-spacing: 0.01em;
  color: #222;
  margin: 0;
  padding: 0;
  flex: 1 1 0;
  text-align: left;
}
.assessment-modal-header-row .modal-close-btn {
  margin-left: 16px;
  margin-top: 0;
  font-size: 2rem;
  line-height: 1;
  padding: 0 8px;
  height: 32px;
  width: 32px;
  min-width: 32px;
  min-height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.assessment-modal-scrollable {
  overflow-y: auto;
  padding: 24px 32px 24px 32px;
  flex: 1 1 auto;
  max-height: calc(65vh - 80px);
}
.assessment-question-block {
  margin-bottom: 24px;
  text-align: left;
}
.assessment-question {
  font-weight: 600;
  font-size: 1.08rem;
  margin-bottom: 10px;
  color: #222;
  letter-spacing: 0.01em;
  text-align: left;
}
.assessment-answer {
  background: #f8f8f8;
  border-radius: 10px;
  padding: 13px 18px;
  font-size: 1rem;
  color: #222;
  margin-bottom: 2px;
  font-weight: 500;
  line-height: 1.5;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
  text-align: left;
}
/* Responsive styles to match base pages */
@media (max-width: 1400px) {
  .assessment-table-outer {
    margin: 0 12px 12px 12px;
    max-width: 100%;
  }
  .assessment-table-card {
    border-radius: 14px;
    max-width: 100%;
  }
  .assessment-summary-cards {
    padding: 8px 8px 0 8px;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
  }
  .assessment-table-container {
    padding: 0 8px 8px 8px;
    border-bottom-left-radius: 14px;
    border-bottom-right-radius: 14px;
  }
  .assessment-table th,
  .assessment-table td {
    font-size: 12px;
    padding: 8px 4px;
  }
  .rounded-th-left {
    border-top-left-radius: 14px;
    border-bottom-left-radius: 14px;
  }
  .rounded-th-right {
    border-top-right-radius: 14px;
    border-bottom-right-radius: 14px;
  }
}
@media (max-width: 900px) {
  .assessment-table-outer {
    margin: 0 4px 4px 4px;
    max-width: 100%;
  }
  .assessment-table-card {
    border-radius: 10px;
  }
  .assessment-summary-cards {
    padding: 8px 4px 0 4px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
  }
  .assessment-table-container {
    padding: 0 4px 4px 4px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
  }
  .assessment-table th,
  .assessment-table td {
    font-size: 11px;
    padding: 6px 2px;
  }
  .rounded-th-left {
    border-top-left-radius: 10px;
    border-bottom-left-radius: 10px;
  }
  .rounded-th-right {
    border-top-right-radius: 10px;
    border-bottom-right-radius: 10px;
  }
}
@media (max-width: 600px) {
  .assessment-modal-content {
    padding: 0;
    font-size: 0.92rem;
    min-width: 0;
    max-width: 98vw;
    border-radius: 12px;
  }
  .assessment-modal-header-row {
    padding: 8px;
  }
  .assessment-modal-header-row h2 {
    font-size: 1rem;
  }
  .assessment-modal-scrollable {
    padding: 12px 8px 12px 8px;
    max-height: calc(65vh - 48px);
  }
  .assessment-summary-cards {
    flex-direction: column;
    gap: 8px;
    align-items: stretch;
  }
  .assessment-summary-card {
    min-width: 0;
    width: 100%;
    padding: 12px 6px;
    font-size: 0.95rem;
  }
}
.member-name-td {
  padding-left: 20px !important;
}
</style>
