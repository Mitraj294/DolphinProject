<template>
  <div class="modal-overlay">
    <div class="modal-card">
      <button
        class="modal-close"
        @click="$emit('close')"
      >
        &times;
      </button>
      <div class="modal-title">Create Assessment</div>
      <form
        class="modal-form"
        @submit.prevent="handleSubmit"
      >
        <div class="modal-form-row">
          <div
            class="modal-form-group"
            style="
              padding: 0 0;
              background: none;
              border-radius: 0;
              height: auto;
            "
          >
            <input
              v-model="assessment.name"
              type="text"
              placeholder="Assessment Name"
              required
              style="
                width: 100%;
                background: #f6f6f6;
                border-radius: 9px;
                border: 1.5px solid #e0e0e0;
                font-size: 20px;
                padding: 16px 20px;
                box-sizing: border-box;
                font-weight: 500;
                color: #222;
              "
            />
          </div>
        </div>
        <div
          class="modal-form-row"
          style="flex-direction: column; align-items: stretch; gap: 10px"
        >
          <label
            style="
              font-weight: 600;
              margin-bottom: 16px;
              font-size: 22px;
              text-align: left;
              display: block;
              align-self: flex-start;
            "
          >
            Questions
          </label>
          <div
            v-for="q in questions"
            :key="q.id"
            style="width: 100%; margin-bottom: 8px"
          >
            <label
              :for="'q-' + q.id"
              class="user-assessment-checkbox-label"
              :class="{
                checked: assessment.selectedQuestionIds.includes(q.id),
              }"
              style="
                justify-content: flex-start;
                font-size: 18px;
                padding: 18px 24px;
                background: #f8f9fb;
                border-radius: 12px;
                margin-bottom: 0;
                text-align: left;
              "
            >
              <span class="user-assessment-checkbox-custom"></span>
              <input
                type="checkbox"
                :id="'q-' + q.id"
                :value="q.id"
                v-model="assessment.selectedQuestionIds"
              />
              <span
                style="
                  flex: 1;
                  text-align: left;
                  font-size: 18px;
                  font-weight: 500;
                  color: #222;
                "
                >{{ q.text }}</span
              >
            </label>
          </div>
        </div>
        <div class="modal-form-actions">
          <button
            type="submit"
            class="modal-save-btn"
            :disabled="isSubmitting"
          >
            {{ isSubmitting ? 'Creating...' : 'Create' }}
          </button>
          <button
            type="button"
            class="btn btn-secondary"
            @click="$emit('close')"
            style="margin-left: 12px"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import storage from '@/services/storage';

export default {
  name: 'CreateAssessmentModal',
  props: {
    questions: {
      type: Array,
      required: true,
      default: () => [],
    },
  },
  emits: ['close', 'assessment-created'],
  data() {
    return {
      assessment: {
        name: '',
        selectedQuestionIds: [],
      },
      isSubmitting: false,
    };
  },
  methods: {
    resetForm() {
      this.assessment = {
        name: '',
        selectedQuestionIds: [],
      };
      this.isSubmitting = false;
    },
    async handleSubmit() {
      // Validate input
      const selectedQuestions = this.questions.filter((q) =>
        this.assessment.selectedQuestionIds.includes(q.id)
      );

      if (!this.assessment.name || selectedQuestions.length === 0) {
        this.$emit('validation-error', {
          type: 'warn',
          title: 'Missing Data',
          message: 'Please enter a name and select at least one question.',
        });
        return;
      }

      this.isSubmitting = true;

      try {
        const authToken = storage.get('authToken');
        const res = await axios.post(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/assessments',
          {
            name: this.assessment.name,
            question_ids: this.assessment.selectedQuestionIds,
          },
          { headers: { Authorization: `Bearer ${authToken}` } }
        );

        if (res.data && res.data.assessment) {
          this.$emit('assessment-created', res.data.assessment);
          this.resetForm();
          this.$emit('close');
        }
      } catch (e) {
        this.$emit('error', {
          type: 'error',
          title: 'Error',
          message: 'Failed to create assessment.',
        });
      } finally {
        this.isSubmitting = false;
      }
    },
  },
  mounted() {
    this.resetForm();
  },
};
</script>
