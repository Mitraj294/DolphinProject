<template>
  <div
    class="schedule-demo-schedule-inputs"
    style="display: flex; gap: 24px; width: 100%; justify-content: flex-start"
  >
    <div
      class="form-box"
      style="width: 338px; min-width: 0"
    >
      <div class="form-input-noicon-wrap">
        <input
          type="date"
          :placeholder="datePlaceholder"
          class="form-input"
          v-model="dateValue"
          @input="$emit('update:date', dateValue)"
        />
      </div>
    </div>
    <div
      class="form-box"
      style="width: 338px; min-width: 0"
    >
      <div class="form-input-noicon-wrap">
        <input
          type="time"
          :placeholder="timePlaceholder"
          class="form-input"
          v-model="timeValue"
          @input="$emit('update:time', timeValue)"
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormDateTime',
  props: {
    date: { type: String, default: '' },
    time: { type: String, default: '' },
    datePlaceholder: { type: String, default: 'MM/DD/YYYY' },
    timePlaceholder: { type: String, default: '00:00' },
  },
  computed: {
    dateValue: {
      get() {
        return this.date;
      },
      set(val) {
        this.$emit('update:date', val);
      },
    },
    timeValue: {
      get() {
        return this.time;
      },
      set(val) {
        this.$emit('update:time', val);
      },
    },
  },
};
</script>

<style scoped>
.form-box {
  position: relative;
  display: flex;
  align-items: center;
  background: #f6f6f6;
  border-radius: 10px;
  border: 1.5px solid #e0e0e0;
  padding: 0;
  min-height: 48px;
  margin-bottom: 0;
  box-sizing: border-box;
  transition: border 0.18s;
  width: 338px;
  min-width: 0;
}
.form-input-noicon-wrap {
  width: 100%;
  height: 48px;
  display: flex;
  align-items: center;
  box-sizing: border-box;
}
.form-input {
  width: 100%;
  height: 44px;
  font-size: 16px;
  border: none;
  outline: none;
  color: #222;
  background: transparent;
  font-family: inherit;
  padding: 0 16px;
  box-sizing: border-box;
}
</style>
