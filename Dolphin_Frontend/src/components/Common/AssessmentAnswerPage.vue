<template>
  <div class="assessment-answer-page">
    <div class="assessment-card">
      <Toast />
      <h2 class="assessment-title">{{ assessment?.name }}</h2>
      <form @submit.prevent="submitAnswers">
        <div
          v-for="q in assessment?.questions || []"
          :key="q.assessment_question_id"
          class="question-block"
        >
          <label
            :for="'q-' + q.assessment_question_id"
            class="question-label"
            >{{ q.text }}</label
          >
          <input
            v-model="answers[q.assessment_question_id]"
            :id="'q-' + q.assessment_question_id"
            type="text"
            class="question-input"
            required
          />
        </div>
        <button
          type="submit"
          :disabled="loading"
          class="submit-btn"
        >
          <span v-if="loading">Submitting...</span>
          <span v-else>Submit</span>
        </button>
        <!-- Toast notifications will be used for success/error messages -->
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

export default {
  name: 'AssessmentAnswerPage',
  components: { Toast },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      assessment: null,
      answers: {},
      loading: false,
    };
  },
  async created() {
    const token = this.$route.params.token;
    try {
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      const res = await axios.get(
        `${API_BASE_URL}/api/assessment/answer/${token}`
      );
      this.assessment = res.data.assessment;
      for (const q of this.assessment.questions) {
        this.answers[q.assessment_question_id] = '';
      }
    } catch (e) {
      this.toast.add({
        severity: 'error',
        summary: 'Invalid or expired link.',
        life: 3500,
      });
    }
  },
  methods: {
    async submitAnswers() {
      this.loading = true;
      const token = this.$route.params.token;
      const API_BASE_URL =
        process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
      try {
        // Map answers to include organization_assessment_question_id
        const answersPayload = this.assessment.questions.map((q) => ({
          assessment_question_id: q.assessment_question_id,
          organization_assessment_question_id: q.question_id, // question_id is org_assessment_question_id from backend
          answer: this.answers[q.assessment_question_id],
        }));
        const payload = { answers: answersPayload };
        const res = await axios.post(
          `${API_BASE_URL}/api/assessment/answer/${token}`,
          payload
        );
        this.toast.add({
          severity: 'success',
          summary: 'Thank you for your submission!',
          life: 3500,
        });
      } catch (e) {
        this.toast.add({
          severity: 'error',
          summary: 'Submission failed.',
          life: 3500,
        });
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Modern card style */
.assessment-answer-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f7fa;
}
.assessment-card {
  background: #fff;
  padding: 2.5rem 2rem 2rem 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
  max-width: 480px;
  width: 100%;
}
.assessment-title {
  text-align: center;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 2rem;
  color: #1a237e;
  letter-spacing: 1px;
}
.question-block {
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
}
.question-label {
  font-weight: 500;
  text-align: left;
  margin-bottom: 0.5rem;
  color: #333;
}
.question-input {
  padding: 0.6rem 1rem;
  border: 1px solid #cfd8dc;
  border-radius: 6px;
  font-size: 1rem;
  background: #f9fafb;
  transition: border 0.2s;
}
.question-input:focus {
  border-color: #1976d2;
  outline: none;
  background: #fff;
}
.submit-btn {
  width: 100%;
  padding: 0.75rem 0;
  background: linear-gradient(90deg, #1976d2 0%, #42a5f5 100%);
  color: #fff;
  font-size: 1.1rem;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 1rem;
  transition: background 0.2s, box-shadow 0.2s;
  box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
}
.submit-btn:disabled {
  background: #b0bec5;
  cursor: not-allowed;
}
/* Removed unused success/error classes. Toast notifications are now used. */
</style>
