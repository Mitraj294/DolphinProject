<template>
  <div class="assessment-answer-page">
    <img
      src="@/assets/images/Lines.svg"
      alt="Lines"
      class="bg-lines"
    />
    <img
      src="@/assets/images/Image.svg"
      alt="Illustration"
      class="bg-illustration"
    />
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
      this.group_id = res.data.group ? res.data.group.id : null;
      this.member_id = res.data.member ? res.data.member.id : null;
      for (const q of this.assessment.questions) {
        this.answers[q.assessment_question_id] = '';
      }
    } catch (e) {
      this.$router.replace({
        name: 'ThanksPage',
        query: { already: '1' },
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
        const payload = {
          answers: answersPayload,
          group_id: this.group_id,
          member_id: this.member_id,
        };
        const res = await axios.post(
          `${API_BASE_URL}/api/assessment/answer/${token}`,
          payload
        );
        this.$router.replace({
          name: 'ThanksPage',
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
/* Login/Register style background and card */
.assessment-answer-page {
  position: relative;
  width: 100vw;
  height: 100vh;
  background: #f8f9fb;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}
.bg-lines {
  position: absolute;
  left: 0;
  top: 0;
  width: 250px;
  height: auto;
  z-index: 0;
}
.bg-illustration {
  position: absolute;
  right: 0;
  bottom: 0;
  width: 300px;
  height: auto;
  z-index: 0;
}
.assessment-card {
  position: relative;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  padding: 48px 48px 32px 48px;
  text-align: center;
  z-index: 1;
  max-width: 480px;
  width: 100%;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.assessment-title {
  text-align: center;
  font-size: 2rem;
  font-weight: 600;
  color: #234056;
  margin-bottom: 8px;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
}
.question-block {
  margin-bottom: 24px;
  display: flex;
  flex-direction: column;
  width: 100%;
}
.question-label {
  font-weight: 500;
  text-align: left;
  margin-bottom: 0.5rem;
  color: #333;
}
.question-input {
  padding: 12px 16px;
  border: 1.5px solid #e0e0e0;
  border-radius: 12px;
  font-size: 1rem;
  background: #f9fafb;
  transition: border-color 0.18s;
  outline: none;
  box-sizing: border-box;
}
.question-input:focus {
  border-color: #0074c2;
  background: #fff;
}
.submit-btn {
  width: 100%;
  padding: 14px;
  background: #0074c2;
  color: #fff;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  margin-bottom: 32px;
  margin-top: 8px;
  transition: background 0.2s;
  box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
}
.submit-btn:disabled {
  background: #b0bec5;
  cursor: not-allowed;
}

@media (max-width: 1200px) {
  .bg-lines {
    width: 180px;
    left: 1vw;
    top: 8vh;
  }
  .bg-illustration {
    width: 220px;
    right: 1vw;
    bottom: 8vh;
  }
  .assessment-card {
    padding: 32px;
    max-width: 400px;
  }
}
@media (max-width: 768px) {
  .bg-lines {
    width: 120px;
    left: -20px;
    top: -20px;
  }
  .bg-illustration {
    width: 150px;
    right: -20px;
    bottom: -20px;
  }
  .assessment-card {
    padding: 24px;
    margin: 0 16px;
  }
  .assessment-title {
    font-size: 1.8rem;
  }
  .question-input {
    font-size: 0.9rem;
  }
  .submit-btn {
    font-size: 1rem;
    padding: 12px;
  }
}
/* Modern card style */
.assessment-answer-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f7fa;
}
.bg-lines {
  position: absolute;
  left: 0;
  top: 0;
  width: 250px;
  height: auto;
  z-index: 0;
}

.bg-illustration {
  position: absolute;
  right: 0;
  bottom: 0;
  width: 300px;
  height: auto;
  z-index: 0;
}
.assessment-card {
  background: #fff;
  padding: 2.5rem 2rem 2rem 2rem;
  border-radius: 16px;
  box-shadow: 18px 20px 20px 20px rgba(0, 0, 0, 0.08);
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

@media (max-width: 1200px) {
  .bg-lines {
    width: 180px;
    left: 1vw;
    top: 8vh;
  }
  .bg-illustration {
    width: 220px;
    right: 1vw;
    bottom: 8vh;
  }
}
@media (max-width: 768px) {
  .bg-lines {
    width: 120px;
    left: -20px;
    top: -20px;
  }
  .bg-illustration {
    width: 150px;
    right: -20px;
    bottom: -20px;
  }
}
</style>
