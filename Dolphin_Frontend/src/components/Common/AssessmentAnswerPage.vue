<template>
  <div class="assessment-answer-page">
    <div class="assessment-card">
      <h2 class="assessment-title">{{ assessment?.name }}</h2>
      <form @submit.prevent="submitAnswers">
        <div
          v-for="q in assessment?.questions || []"
          :key="q.id"
          class="question-block"
        >
          <label
            :for="'q-' + q.id"
            class="question-label"
            >{{ q.text }}</label
          >
          <input
            v-model="answers[q.id]"
            :id="'q-' + q.id"
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
        <div
          v-if="success"
          class="success"
        >
          Thank you for your submission!
        </div>
        <div
          v-if="error"
          class="error"
        >
          {{ error }}
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  name: 'AssessmentAnswerPage',
  data() {
    return {
      assessment: null,
      answers: {},
      loading: false,
      success: false,
      error: '',
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
        this.answers[q.id] = '';
      }
    } catch (e) {
      this.error = 'Invalid or expired link.';
    }
  },
  methods: {
    async submitAnswers() {
      this.loading = true;
      this.error = '';
      const token = this.$route.params.token;
      try {
        const payload = {
          answers: Object.entries(this.answers).map(
            ([question_id, answer]) => ({
              question_id,
              answer,
            })
          ),
        };
        await axios.post(
          `${API_BASE_URL}/api/assessment/answer/${token}`,
          payload
        );
        this.success = true;
      } catch (e) {
        this.error = 'Submission failed.';
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
.success {
  color: #388e3c;
  margin-top: 1.5rem;
  text-align: center;
  font-weight: 500;
}
.error {
  color: #d32f2f;
  margin-top: 1.5rem;
  text-align: center;
  font-weight: 500;
}
</style>
