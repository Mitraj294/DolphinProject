<template>
  <div class="user-assessment-outer">
    <div class="user-assessment-card">
      <template v-if="!submitted">
        <div class="user-assessment-header">
          <div class="user-assessment-title">
            {{ currentQuestion.question || `Question ${step}` }}
          </div>
        </div>
        <div class="user-assessment-table-container">
          <div class="user-assessment-words-grid">
            <label
              v-for="option in currentQuestion.options"
              :key="option"
              class="user-assessment-checkbox-label"
              :class="{ checked: currentSelectedWords.includes(option) }"
            >
              <span class="user-assessment-checkbox-custom"></span>
              <input
                type="checkbox"
                :value="option"
                v-model="selectedWords[step - 1]"
              />
              {{ option }}
            </label>
          </div>
        </div>
        <div class="user-assessment-footer">
          <div style="flex: 1; display: flex; align-items: center">
            <span class="user-assessment-step-btn">
              Question {{ step }} of {{ totalSteps }}
            </span>
          </div>
          <div
            style="
              flex: 1;
              display: flex;
              justify-content: flex-end;
              align-items: center;
              gap: 12px;
            "
          >
            <button
              v-if="step > 1"
              class="user-assessment-back-btn"
              @click="goToBack"
            >
              Back
            </button>
            <button
              v-if="step < totalSteps"
              class="user-assessment-next-btn"
              :disabled="!canProceed"
              @click="goToNext"
            >
              Next
            </button>
            <button
              v-else
              class="user-assessment-next-btn"
              :disabled="!canProceed"
              @click="handleSubmit"
            >
              Submit
            </button>
          </div>
        </div>
      </template>
      <template v-else>
        <div class="user-assessment-success-card">
          <div class="user-assessment-success-icon">
            <svg
              width="80"
              height="80"
              viewBox="0 0 80 80"
              fill="none"
            >
              <circle
                cx="40"
                cy="40"
                r="40"
                fill="#2ECC40"
              />
              <path
                d="M25 42l13 13 17-23"
                stroke="#fff"
                stroke-width="5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
          <div class="user-assessment-success-title">
            Assessment submitted successfully and processed!
          </div>
          <div class="user-assessment-success-desc">
            Lorem Ipsum is simply dummy text of the printing and typesetting
            industry. Lorem Ipsum has been the industry's standard dummy text
            ever since the 1500s, when an unknown printer took a galley of type
            and scrambled it to make a type specimen book. It has survived not
            only five centuries, but also the leap into electronic typesetting,
            remaining essentially unchanged
          </div>
          <button
            v-if="isSubscribed"
            class="user-assessment-success-btn"
            @click="goToManageSubscription"
          >
            Manage Subscription
          </button>
          <button
            v-else
            class="user-assessment-success-btn"
            @click="explorePlans"
          >
            Explore Subscriptions
          </button>
          <div style="margin-top: 16px"></div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || '';

export default {
  name: 'UserAssessment',
  setup() {
    const router = useRouter();
    const step = ref(1);
    const questions = ref([]);
    const selectedWords = ref([]); // Array of arrays, one per question
    const submitted = ref(false);

    // Track user subscription status
    const isSubscribed = ref(false); // Default: not subscribed

    const totalSteps = computed(() => questions.value.length);
    const currentQuestion = computed(
      () => questions.value[step.value - 1] || { question: '', options: [] }
    );
    const currentSelectedWords = computed(
      () => selectedWords.value[step.value - 1] || []
    );
    const canProceed = computed(() => {
      // Require at least one word selected for the current question
      return (
        selectedWords.value[step.value - 1] &&
        selectedWords.value[step.value - 1].length > 0
      );
    });

    // Fetch questions, answers, and subscription status from backend
    const fetchQuestionsAndAnswers = async () => {
      try {
        const storage = require('@/services/storage').default;
        const authToken = storage.get('authToken');
        const headers = {};
        if (authToken) {
          headers['Authorization'] = `Bearer ${authToken}`;
        }
        // Fetch questions
        const resQ = await axios.get(`${API_BASE_URL}/api/questions`, {
          headers,
        });
        if (Array.isArray(resQ.data)) {
          questions.value = resQ.data;
          // Initialize selectedWords array
          selectedWords.value = resQ.data.map(() => []);
        }
        // Fetch previous answers
        const resA = await axios.get(`${API_BASE_URL}/api/answers`, {
          headers,
        });
        if (Array.isArray(resA.data)) {
          // Map answers to selectedWords by question_id
          resA.data.forEach((ans) => {
            const idx = questions.value.findIndex(
              (q) => String(q.id) === String(ans.question_id)
            );
            if (idx !== -1 && Array.isArray(ans.answer)) {
              selectedWords.value[idx] = ans.answer;
            }
          });
        }

        // Fetch subscription status
        try {
          const resSub = await axios.get(
            `${API_BASE_URL}/api/subscription/status`,
            {
              headers,
            }
          );
          // Adjust this logic based on your backend response
          isSubscribed.value = !!(
            resSub.data &&
            (resSub.data.active ||
              resSub.data.status === 'active' ||
              resSub.data.subscribed)
          );
        } catch (e) {
          isSubscribed.value = false;
        }
      } catch (error) {
        if (error.response && error.response.status === 401) {
          router.push('/login');
        } else {
          alert('Failed to load assessment questions or answers.');
        }
      }
    };

    // Navigation
    const goToNext = () => {
      if (step.value < totalSteps.value && canProceed.value) {
        step.value++;
      }
    };
    const goToBack = () => {
      if (step.value > 1) {
        step.value--;
      }
    };

    // Submit
    const handleSubmit = async () => {
      if (!canProceed.value) return;
      const storage = require('@/services/storage').default;
      const authToken = storage.get('authToken');
      if (!authToken) {
        alert('You must be logged in to submit an assessment.');
        router.push('/login');
        return;
      }
      // Build answers array as expected by backend
      const answersPayload = questions.value.map((q, idx) => ({
        question_id: q.id,
        answer: selectedWords.value[idx] || [],
      }));
      try {
        await axios.post(
          `${API_BASE_URL}/api/answers`,
          { answers: answersPayload },
          {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `Bearer ${authToken}`,
            },
          }
        );
        submitted.value = true;
      } catch (error) {
        let errorMessage = 'Failed to submit assessment. Please try again.';
        if (error.response && error.response.status === 401) {
          errorMessage = 'Your session has expired. Please log in again.';
          router.push('/login');
        }
        alert(errorMessage);
      }
    };

    onMounted(fetchQuestionsAndAnswers);

    // Success page navigation handlers
    const goToManageSubscription = () => {
      router.push({ name: 'ManageSubscription' });
    };

    const explorePlans = () => {
      router.push({ name: 'SubscriptionPlans' });
    };

    return {
      step,
      questions,
      selectedWords,
      submitted,
      totalSteps,
      currentQuestion,
      currentSelectedWords,
      canProceed,
      goToNext,
      goToBack,
      handleSubmit,
      goToManageSubscription,
      explorePlans,
      isSubscribed,
    };
  },
};
</script>

<style scoped>
/* Success page styles */
.assessment-success {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 48px 24px;
}

.success-icon {
  margin-bottom: 24px;
}

.success-title {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  margin-bottom: 24px;
}

.success-text {
  max-width: 800px;
  color: #666;
  line-height: 1.6;
  margin-bottom: 32px;
}

.manage-subscription-btn {
  background: #0074c2;
  color: white;
  border: none;
  border-radius: 999px;
  padding: 12px 24px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  margin-bottom: 32px;
  transition: background 0.2s;
}

.manage-subscription-btn:hover {
  background: #005fa3;
}

.copyright-text {
  color: #787878;
  font-size: 14px;
}

/* --- Base layout and card structure (matches Leads/OrganizationTable/Notifications) --- */
.user-assessment-outer {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
  margin: 64px auto 64px auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
}

.user-assessment-card {
  width: 100%;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 0 auto;
  box-sizing: border-box;
  min-width: 0;
  max-width: 1400px;
  display: flex;
  flex-direction: column;
  gap: 0;
  position: relative;
  padding: 0;
}

.user-assessment-header {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 46px 0 24px;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  min-height: 64px;
  box-sizing: border-box;
}

.user-assessment-title {
  font-size: 18px;
  font-weight: 600;
  text-align: center;
  width: 100%;
}

.user-assessment-table-container {
  width: 100%;
  box-sizing: border-box;
  padding: 0 24px 24px 24px;
  background: #fff;
  border-bottom-left-radius: 24px;
  border-bottom-right-radius: 24px;
  margin-top: 32px;
}

.user-assessment-words-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px 24px;
  margin: 0 auto 32px auto;
  max-width: 900px;
}

.user-assessment-word-cell {
  display: flex;
  align-items: center;
}

.user-assessment-checkbox-label {
  display: flex;
  align-items: center;
  background: #f8f9fb;
  border-radius: 10px;
  padding: 12px 18px;
  font-size: 16px;
  font-weight: 500;
  color: #222;
  cursor: pointer;
  border: 2px solid #f8f9fb;
  transition: border 0.18s, background 0.18s;
  width: 100%;
  user-select: none;
}
.user-assessment-checkbox-label.checked {
  background: #e6f0fa;
  border: 2px solid #0074c2;
}
.user-assessment-checkbox-label input[type='checkbox'] {
  display: none;
}
.user-assessment-checkbox-custom {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  border: 2px solid #bbb;
  background: #fff;
  margin-right: 12px;
  display: inline-block;
  position: relative;
}
.user-assessment-checkbox-label.checked .user-assessment-checkbox-custom {
  border: 2px solid #0074c2;
  background: #0074c2;
}
.user-assessment-checkbox-label.checked .user-assessment-checkbox-custom:after {
  content: '';
  display: block;
  position: absolute;
  left: 6px;
  top: 2px;
  width: 6px;
  height: 12px;
  border: solid #fff;
  border-width: 0 3px 3px 0;
  transform: rotate(45deg);
}

.user-assessment-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 24px;
  padding: 0 24px 24px 24px;
}

.user-assessment-step-btn {
  background: #f5f5f5;
  border: none;
  border-radius: 999px;
  padding: 8px 24px;
  font-size: 15px;
  color: #888;
  font-weight: 500;
  cursor: default;
}

.user-assessment-next-btn {
  background: #0074c2;
  color: #fff;
  border: none;
  border-radius: 999px;
  padding: 10px 32px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.18s;
}
.user-assessment-next-btn:hover {
  background: #005fa3;
}
.user-assessment-back-btn {
  background: #fff;
  color: #222;
  border: 1.5px solid #e0e0e0;
  border-radius: 999px;
  padding: 10px 32px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;

  transition: background 0.18s, border 0.18s;
}
.user-assessment-back-btn:hover {
  background: #f5f5f5;
  border: 1.5px solid #0074c2;
}

/* Success Card */
.user-assessment-success-card {
  background: #fff;
  border-radius: 24px;
  box-shadow: none;
  padding: 48px 32px 40px 32px;
  max-width: 700px;
  width: 100%;
  text-align: center;
  margin: 0 auto;
}
.user-assessment-success-icon {
  margin-bottom: 32px;
}
.user-assessment-success-title {
  font-size: 2rem;
  font-weight: 600;
  margin-bottom: 24px;
  color: #234056;
}
.user-assessment-success-desc {
  font-size: 1.1rem;
  color: #444;
  margin-bottom: 32px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}
.user-assessment-success-btn {
  background: #0074c2;
  color: #fff;
  border: none;
  border-radius: 999px;
  padding: 12px 36px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.18s;
}
.user-assessment-success-btn:hover {
  background: #005fa3;
}

/* --- Responsive styles to match base pages --- */
@media (max-width: 1400px) {
  .user-assessment-outer {
    margin: 12px;
    max-width: 100%;
  }
  .user-assessment-card {
    border-radius: 14px;
    max-width: 100%;
  }
  .user-assessment-header {
    padding: 8px 8px 0 8px;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
  }
  .user-assessment-table-container {
    padding: 0 8px 8px 8px;
    border-bottom-left-radius: 14px;
    border-bottom-right-radius: 14px;
  }
  .user-assessment-footer {
    padding: 0 18px 18px 18px;
  }
  .user-assessment-success-card {
    border-radius: 14px;
    padding: 18px 8px 18px 8px;
    max-width: 100%;
  }
  .user-assessment-words-grid {
    gap: 12px 12px;
  }
}
@media (max-width: 900px) {
  .user-assessment-outer {
    margin: 4px;
    max-width: 100%;
  }
  .user-assessment-card {
    border-radius: 10px;
  }
  .user-assessment-header {
    padding: 8px 4px 0 4px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
  }
  .user-assessment-table-container {
    padding: 0 4px 4px 4px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
  }
  .user-assessment-footer {
    padding: 0 14px 14px 14px;
  }
  .user-assessment-success-card {
    border-radius: 10px;
    padding: 8px 4px 8px 4px;
    max-width: 100%;
  }
  .user-assessment-words-grid {
    grid-template-columns: 1fr;
    gap: 8px 8px;
  }
}
</style>
