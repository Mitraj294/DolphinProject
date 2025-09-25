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
  beforeRouteEnter(to, from, next) {
    // Check if this assessment has already been completed
    const token = to.params.token;
    const completedAssessments = JSON.parse(
      localStorage.getItem('completedAssessments') || '[]'
    );

    if (completedAssessments.includes(token) || from.path === '/thanks') {
      next('/thanks?already=1');
    } else {
      next();
    }
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

    // Check if we're returning from a completed assessment
    const completedAssessments = JSON.parse(
      localStorage.getItem('completedAssessments') || '[]'
    );
    if (completedAssessments.includes(token)) {
      this.$router.replace('/thanks?already=1');
      return;
    }

    try {
      const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
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
      // Check if the assessment has already been submitted
      if (
        e.response &&
        (e.response.status === 409 ||
          (e.response.data &&
            e.response.data.message &&
            e.response.data.message
              .toLowerCase()
              .includes('already submitted')))
      ) {
        // Mark as completed and replace current route
        this.markAssessmentCompleted(token);
        this.$router.replace('/thanks?already=1');
        return;
      }

      this.toast.add({
        severity: 'error',
        summary: 'Failed to load assessment.',
        life: 3500,
      });
    }
  },
  methods: {
    async submitAnswers() {
      this.loading = true;
      const token = this.$route.params.token;
      const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
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
        await axios.post(
          `${API_BASE_URL}/api/assessment/answer/${token}`,
          payload
        );

        // Mark assessment as completed in localStorage
        this.markAssessmentCompleted(token);

        // Replace current route to prevent back button navigation
        this.$router.replace('/thanks');
      } catch (e) {
        // Check if the assessment has already been submitted during submission attempt
        if (
          e.response &&
          (e.response.status === 409 ||
            (e.response.data &&
              e.response.data.message &&
              e.response.data.message
                .toLowerCase()
                .includes('already submitted')))
        ) {
          // Mark as completed and replace current route to prevent back button navigation
          this.markAssessmentCompleted(token);
          this.$router.replace('/thanks?already=1');
          return;
        }

        this.toast.add({
          severity: 'error',
          summary: 'Submission failed.',
          life: 3500,
        });
      } finally {
        this.loading = false;
      }
    },
    markAssessmentCompleted(token) {
      // Store completed assessment token in localStorage to prevent re-access
      const completedAssessments = JSON.parse(
        localStorage.getItem('completedAssessments') || '[]'
      );
      if (!completedAssessments.includes(token)) {
        completedAssessments.push(token);
        localStorage.setItem(
          'completedAssessments',
          JSON.stringify(completedAssessments)
        );
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
</style>
