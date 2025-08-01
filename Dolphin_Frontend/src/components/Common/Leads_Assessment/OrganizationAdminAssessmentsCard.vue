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
        <button
          class="btn btn-primary"
          @click="showCreateModal = true"
        >
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
              <ul
                v-if="item.questions && item.questions.length"
                style="
                  margin: 6px 0 0 0;
                  padding-left: 18px;
                  font-size: 14px;
                  color: #555;
                "
              >
                <li
                  v-for="(q, qidx) in item.questions"
                  :key="q.id || qidx"
                >
                  {{ q.text }}
                </li>
              </ul>
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
    <!-- Create Assessment Modal -->
    <div
      v-if="showCreateModal"
      class="modal-overlay"
    >
      <div class="modal-card">
        <button
          class="modal-close"
          @click="closeCreateModal"
        >
          &times;
        </button>
        <div class="modal-title">Create Assessment</div>
        <form
          class="modal-form"
          @submit.prevent="submitAssessment"
        >
          <div class="modal-form-row">
            <div class="modal-form-group">
              <input
                v-model="newAssessment.name"
                type="text"
                placeholder="Assessment Name"
                required
                style="
                  width: 100%;
                  background: transparent;
                  border: none;
                  font-size: 17px;
                "
              />
            </div>
          </div>
          <div
            class="modal-form-row"
            style="flex-direction: column; align-items: stretch; gap: 10px"
          >
            <label style="font-weight: 500; margin-bottom: 4px"
              >Questions</label
            >
            <div
              v-for="(q, idx) in newAssessment.questions"
              :key="idx"
              style="display: flex; gap: 8px; align-items: center"
            >
              <input
                v-model="q.text"
                type="text"
                :placeholder="`Question ${idx + 1}`"
                required
                style="
                  flex: 1;
                  background: #f6f6f6;
                  border-radius: 7px;
                  border: none;
                  padding: 8px 12px;
                  font-size: 15px;
                "
              />
              <button
                type="button"
                @click="removeQuestion(idx)"
                style="
                  background: none;
                  border: none;
                  color: #e74c3c;
                  font-size: 20px;
                  cursor: pointer;
                "
              >
                &times;
              </button>
            </div>
            <button
              type="button"
              @click="addQuestion"
              style="
                margin-top: 6px;
                background: #f5f5f5;
                border: none;
                border-radius: 7px;
                padding: 6px 18px;
                font-size: 15px;
                cursor: pointer;
              "
            >
              + Add Question
            </button>
          </div>
          <div class="modal-form-actions">
            <button
              type="submit"
              class="modal-save-btn"
            >
              Create
            </button>
            <button
              type="button"
              class="btn btn-secondary"
              @click="closeCreateModal"
              style="margin-left: 12px"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
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
import axios from 'axios';
import storage from '@/services/storage';
export default {
  name: 'OrganizationAdminAssessmentsCard',
  components: { Pagination, ScheduleAssessmentModal },
  data() {
    return {
      assessments: [],
      showScheduleModal: false,
      selectedAssessment: null,
      showCreateModal: false,
      newAssessment: {
        name: '',
        questions: [{ text: '' }],
      },
      // Pagination state
      pageSize: 10,
      currentPage: 1,
      showPageDropdown: false,
      // Dropdown state for modal (future use)
      showGroupsDropdown: false,
      showMembersDropdown: false,
      loading: false,
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
    addQuestion() {
      this.newAssessment.questions.push({ text: '' });
    },
    removeQuestion(idx) {
      if (this.newAssessment.questions.length > 1) {
        this.newAssessment.questions.splice(idx, 1);
      }
    },
    closeCreateModal() {
      this.showCreateModal = false;
      this.newAssessment = { name: '', questions: [{ text: '' }] };
    },
    async submitAssessment() {
      this.loading = true;
      try {
        const authToken = storage.get('authToken');
        const res = await axios.post(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/assessments',
          {
            name: this.newAssessment.name,
            questions: this.newAssessment.questions,
          },
          { headers: { Authorization: `Bearer ${authToken}` } }
        );
        if (res.data && res.data.assessment) {
          this.assessments.unshift(res.data.assessment);
        }
        this.closeCreateModal();
      } catch (e) {
        alert('Failed to create assessment.');
      } finally {
        this.loading = false;
      }
    },
    async mounted() {
      // Fetch assessments from backend
      this.loading = true;
      try {
        const authToken = storage.get('authToken');
        const res = await axios.get(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/assessments',
          { headers: { Authorization: `Bearer ${authToken}` } }
        );
        // If backend returns an array of assessments
        if (Array.isArray(res.data.assessments)) {
          this.assessments = res.data.assessments;
        } else if (Array.isArray(res.data)) {
          this.assessments = res.data;
        } else {
          this.assessments = [];
        }
      } catch (e) {
        this.assessments = [];
      } finally {
        this.loading = false;
      }
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
