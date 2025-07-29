<template>
  <div class="assessments-card">
    <div class="assessments-header-row">
      <div></div>
      <div class="assessments-header-actions">
        <button
          class="sent-assessments-btn"
          @click="goToSendAssessment"
        >
          Sent Assessments
        </button>
        <button class="btn btn-primary">
          <img
            src="@/assets/images/Add.svg"
            alt="Add"
            style="
              width: 18px;
              height: 18px;
              vertical-align: middle;
              margin-right: 8px;
            "
          />
          Create Assessments
        </button>
      </div>
    </div>
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Assessments</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in paginatedAssessments"
            :key="item.id"
          >
            <td class="assessment-name-cell">
              <button
                class="assessment-link"
                @click="goToSummary(item)"
              >
                {{ item.name }}
              </button>
            </td>
            <td>
              <button
                class="schedule-btn"
                @click="openScheduleModal(item)"
              >
                <img
                  src="@/assets/images/Schedule.svg"
                  alt="Schedule"
                  style="
                    margin-right: 6px;
                    width: 18px;
                    height: 18px;
                    vertical-align: middle;
                    display: inline-block;
                  "
                />
                Schedule
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- Schedule Modal -->
    <div
      v-if="showScheduleModal"
      class="modal-overlay"
    >
      <ScheduleAssessmentModal
        :assessmentName="selectedAssessment && selectedAssessment.name"
        @close="closeScheduleModal"
        @schedule="closeScheduleModal"
      />
    </div>
  </div>
  <Pagination
    :pageSize="pageSize"
    :pageSizes="[10, 25, 100]"
    :showPageDropdown="showPageDropdown"
    :currentPage="currentPage"
    :totalPages="totalPages"
    @togglePageDropdown="showPageDropdown = !showPageDropdown"
    @selectPageSize="selectPageSize"
    @goToPage="goToPage"
  />
</template>

<script>
import Pagination from '@/components/layout/Pagination.vue';
import ScheduleAssessmentModal from '@/components/Common/MyOrganization/ScheduleAssessmentModal.vue';
export default {
  name: 'OrganizationAdminAssessmentsCard',
  components: { Pagination, ScheduleAssessmentModal },
  data() {
    return {
      assessments: Array.from({ length: 10 }, (_, i) => ({
        id: i + 1,
        name: `Assessment ${i + 1}`,
      })),
      showScheduleModal: false,
      selectedAssessment: null,
      // Pagination state
      pageSize: 10,
      currentPage: 1,
      showPageDropdown: false,
      // Dropdown state for modal (future use)
      showGroupsDropdown: false,
      showMembersDropdown: false,
    };
  },
  computed: {
    paginatedAssessments() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.assessments.slice(start, start + this.pageSize);
    },
    totalPages() {
      return Math.ceil(this.assessments.length / this.pageSize) || 1;
    },
  },
  methods: {
    openScheduleModal(item) {
      this.selectedAssessment = item;
      this.showScheduleModal = true;
    },
    closeScheduleModal() {
      this.showScheduleModal = false;
      this.selectedAssessment = null;
      this.showGroupsDropdown = false;
      this.showMembersDropdown = false;
    },
    goToSummary(item) {
      this.$router.push({
        name: 'AssessmentSummary',
        params: { assessmentId: item.id },
      });
    },
    goToSendAssessment() {
      this.$router.push({ name: 'SendAssessment' });
    },
    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    selectPageSize(size) {
      this.pageSize = size;
      this.currentPage = 1;
      this.showPageDropdown = false;
    },
  },
};
</script>

<style scoped>
.assessments-card {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 64px auto 0 auto;
  padding: 0;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 0;
  position: relative;
}

.assessments-header-row {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 24px 46px 24px 24px;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  min-height: 64px;
  box-sizing: border-box;
  margin: 0; /* Remove any margin */
}

.assessments-header-actions {
  display: flex;
  gap: 16px;
}
.sent-assessments-btn {
  background: #f5f5f5;
  border: none;
  border-radius: 999px;
  padding: 10px 32px;
  font-size: 16px;
  color: #222;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.18s;
}
.sent-assessments-btn:hover {
  background: #e6f0fa;
}

.assessment-name-cell {
  text-align: left;
}
.schedule-btn {
  background: #f5f5f5;
  border: none;
  border-radius: 999px;
  padding: 8px 24px;
  font-size: 15px;
  color: #222;
  font-weight: 500;
  display: flex;
  align-items: center;
  cursor: pointer;
  transition: background 0.18s;
}
.schedule-btn:hover {
  background: #e6f0fa;
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.13);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}
.modal-card {
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 4px 32px 0 rgba(33, 150, 243, 0.1);
  padding: 40px 48px 32px 48px;
  min-width: 400px;
  max-width: 700px;
  width: 100%;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
.modal-close {
  position: absolute;
  top: 24px;
  right: 32px;
  background: none;
  border: none;
  font-size: 32px;
  color: #888;
  cursor: pointer;
  z-index: 10;
}
.modal-title {
  font-size: 26px;
  font-weight: 600;
  margin-bottom: 32px;
  color: #222;
}
.modal-form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.modal-form-row {
  display: flex;
  gap: 24px;
  width: 100%;
}
.modal-form-group {
  flex: 1 1 0;
  min-width: 0;
  background: #f6f6f6;
  border-radius: 9px;
  padding: 0 16px;
  height: 48px;
  display: flex;
  align-items: center;
  margin-bottom: 0;
  box-sizing: border-box;
}
.modal-form-group.custom-dropdown {
  flex-direction: row;
  align-items: center;
  gap: 0;
  min-height: 48px;
  width: 100%;
  position: relative;
}
.modal-icon {
  margin-right: 10px;
  display: flex;
  align-items: center;
}
.custom-dropdown-input {
  width: 100%;
  background: #f6f6f6;
  border: none;
  border-radius: 9px;
  height: 48px;
  padding: 0;
  font-size: 16px;
  color: #222;
  display: flex;
  align-items: center;
  cursor: pointer;
  position: relative;
  box-shadow: none;
  margin-bottom: 0;
  font-family: inherit;
  font-weight: 500;
}
.custom-dropdown-input .fas {
  color: #222;
  font-size: 18px;
  margin-right: 1px;
  position: static;
  left: unset;
  top: unset;
  transform: none;
}
.custom-dropdown-input .fas.fa-chevron-down {
  margin-left: auto;
  color: #888;
}
.custom-dropdown-input span[style] {
  color: #888 !important;
  font-size: 16px;
  font-weight: 500;
  font-family: inherit;
  margin-left: 2px;
}
.modal-form-actions {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  margin-top: 18px;
}
.modal-save-btn {
  border-radius: 22px;
  background: #0164a5;
  color: #fff;
  font-size: 17px;
  font-weight: 500;
  padding: 10px 32px;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}
.modal-save-btn:hover {
  background: #005fa3;
}
.assessment-link {
  background: none;
  border: none;
  color: #0074c2;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  text-align: left;
  padding: 0;
  transition: color 0.18s;
}
.assessment-link:hover {
  color: #005fa3;
  text-decoration: underline;
}
@media (max-width: 1400px) {
  .assessments-card {
    border-radius: 14px;
    max-width: 100%;
    margin-top: 12px; /* Responsive top margin */
  }
  .assessments-header-row {
    padding: 8px 8px 0 8px;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
  }
}
@media (max-width: 900px) {
  .assessments-card {
    border-radius: 10px;
    margin-top: 4px; /* Responsive top margin */
  }
  .assessments-header-row {
    padding: 8px 4px 0 4px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
  }
}
@media (max-width: 600px) {
  .modal-card {
    min-width: 0;
    max-width: 99vw;
    padding: 8px 2vw 8px 2vw;
    border-radius: 10px;
  }
}
</style>
