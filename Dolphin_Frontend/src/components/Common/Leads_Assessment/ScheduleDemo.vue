<template>
  <MainLayout>
    <div class="page">
      <div class="schedule-demo-outer">
        <div class="schedule-demo-card">
          <h2 class="schedule-demo-title">{{ titleText }}</h2>
          <div class="schedule-demo-desc">
            Lorem Ipsum is simply dummy text of the printing and typesetting
            industry.
          </div>
          <form class="schedule-demo-form">
            <FormRow>
              <div class="schedule-demo-field">
                <FormLabel>Select Organization</FormLabel>
                <FormDropdown
                  v-model="organization"
                  icon="fas fa-building"
                >
                  <option value="">Select</option>
                  <!-- Add more options here -->
                </FormDropdown>
              </div>
              <div class="schedule-demo-field">
                <FormLabel>Select Admin</FormLabel>
                <FormDropdown
                  v-model="admin"
                  icon="fas fa-user-tie"
                >
                  <option value="">Select</option>
                  <!-- Add more options here -->
                </FormDropdown>
              </div>
              <div class="schedule-demo-field">
                <FormLabel>Select User</FormLabel>
                <FormDropdown
                  v-model="user"
                  icon="fas fa-user"
                >
                  <option value="">Select</option>
                  <!-- Add more options here -->
                </FormDropdown>
              </div>
            </FormRow>
            <FormRow>
              <div class="schedule-demo-field">
                <FormLabel>Subject</FormLabel>
                <FormInput
                  v-model="subject"
                  placeholder="Type here"
                />
              </div>
              <div class="schedule-demo-field">
                <FormLabel>Select Method</FormLabel>
                <FormDropdown
                  v-model="method"
                  icon="fas fa-cogs"
                >
                  <option value="">Select</option>
                  <!-- Add more options here -->
                </FormDropdown>
              </div>
              <div class="schedule-demo-field schedule-demo-schedule-field">
                <FormLabel>Schedule</FormLabel>
                <div class="schedule-demo-schedule-inputs">
                  <FormInput
                    v-model="date"
                    type="date"
                    placeholder="MM/DD/YYYY"
                  />
                  <FormInput
                    v-model="time"
                    type="time"
                    placeholder="00:00"
                  />
                </div>
              </div>
            </FormRow>
            <!-- Button moved outside FormRow for proper right alignment -->
            <div class="schedule-demo-actions">
              <button
                type="submit"
                class="btn btn-primary"
              >
                {{ submitText }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import {
  FormInput,
  FormDropdown,
  FormRow,
  FormLabel,
} from '@/components/Common/Common_UI/Form';
export default {
  name: 'ScheduleDemo',
  components: { MainLayout, FormInput, FormDropdown, FormRow, FormLabel },
  data() {
    return {
      organization: '',
      admin: '',
      user: '',
      subject: '',
      method: '',
      date: '',
      time: '',
    };
  },
  computed: {
    titleText() {
      const mode = this.$route && this.$route.query && this.$route.query.mode;
      return mode === 'followup' ? 'Schedule Follow up' : 'Schedule Demo';
    },
    submitText() {
      const mode = this.$route && this.$route.query && this.$route.query.mode;
      return mode === 'followup' ? 'Schedule Follow up' : 'Schedule Demo';
    },
  },
};
</script>

<style scoped>
.schedule-demo-outer {
  width: 100%;

  min-width: 260px;

  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  background: none !important;
  padding: 0;
}
.schedule-demo-card {
  width: 100%;

  min-width: 0;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 0 auto;
  box-sizing: border-box;
  padding: 32px 32px 24px 32px;
  display: flex;
  flex-direction: column;
  gap: 32px;
  position: relative;
}
@media (max-width: 600px) {
  .schedule-demo-card {
    padding: 8px;
  }
}
.schedule-demo-title {
  font-size: 22px;
  font-weight: 600;
  margin-top: 0;
  margin-bottom: 8px;
  text-align: left;
  color: #222;
}
.schedule-demo-desc {
  font-size: 16px;
  color: #222;
  margin-bottom: 24px;
  text-align: left;
}
.schedule-demo-form {
  width: 100%;
}
.schedule-demo-row {
  display: flex;
  gap: 18px;
  margin-bottom: 18px;
  /* Prevent column stacking on zoom/resize above mobile breakpoint */
  flex-direction: row !important;
}
.schedule-demo-field {
  flex: 1 1 0;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.schedule-demo-field label {
  color: #222;
  font-size: 15px;
  font-weight: 400;
  text-align: left;
}
.schedule-demo-field input,
.schedule-demo-field select {
  background: #fafafa;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 15px;
  color: #222;
  outline: none;
  transition: border 0.2s;
}
/* Schedule input wrappers to match class training style */
.schedule-demo-schedule-inputs {
  display: flex;
  gap: 8px;
  width: 100%;
}
.schedule-demo-schedule-inputs .form-box {
  flex: 1 1 0;
  min-width: 0;
  background: #f6f6f6;
  border-radius: 9px;

  height: 48px;
  display: flex;
  align-items: center;
  box-sizing: border-box;
  position: relative;
}
.schedule-demo-schedule-inputs .form-input-noicon-wrap {
  width: 100%;
}
.schedule-demo-schedule-inputs input.form-input[type='date'],
.schedule-demo-schedule-inputs input.form-input[type='time'] {
  width: 100%;
  background: #fafafa;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 14px 10px 44px;
  font-size: 15px;
  color: #222;
  outline: none;
  transition: border 0.2s;
  box-sizing: border-box;
}
.schedule-demo-schedule-inputs input.form-input[type='date']::placeholder,
.schedule-demo-schedule-inputs input.form-input[type='time']::placeholder {
  color: #888;
  opacity: 1;
}
.schedule-demo-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 18px;
}
</style>
