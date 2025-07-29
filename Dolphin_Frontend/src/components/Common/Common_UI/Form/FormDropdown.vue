<template>
  <FormBox :error="error">
    <template v-if="icon">
      <span class="form-input-icon">
        <i :class="icon"></i>
      </span>
      <select
        v-bind="$attrs"
        v-model="inputValue"
        :disabled="disabled"
        class="form-dropdown with-icon"
        @change="$emit('update:modelValue', inputValue)"
      >
        <slot />
      </select>
    </template>
    <template v-else>
      <div class="form-dropdown-noicon-wrap">
        <select
          v-bind="$attrs"
          v-model="inputValue"
          :disabled="disabled"
          class="form-dropdown"
          @change="$emit('update:modelValue', inputValue)"
        >
          <slot />
        </select>
      </div>
    </template>
    <span class="form-dropdown-chevron">
      <i class="fas fa-chevron-down"></i>
    </span>
  </FormBox>
</template>

<script>
import FormBox from './FormBox.vue';
export default {
  name: 'FormDropdown',
  components: { FormBox },
  props: {
    modelValue: [String, Number],
    icon: { type: String, default: '' },
    error: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
  },
  computed: {
    inputValue: {
      get() {
        return this.modelValue;
      },
      set(val) {
        this.$emit('update:modelValue', val);
      },
    },
  },
};
</script>

<style scoped>
.form-dropdown {
  border: none;
  background: transparent;
  outline: none;
  font-size: 16px;
  color: #222;
  width: 100%;
  height: 44px;
  padding: 0 36px 0 16px; /* always left padding for selection box */
  font-family: inherit;
  appearance: none;
  cursor: pointer;
  box-sizing: border-box;
}
.form-dropdown.with-icon {
  padding-left: 36px; /* left space for icon inside the box */
}
.form-dropdown-noicon-wrap {
  width: 100%;
  height: 44px;
  display: flex;
  align-items: center;
  padding-left: 0; /* remove left space for dropdown list */
  box-sizing: border-box;
}
.form-dropdown:disabled {
  background: #f0f0f0;
  color: #aaa;
}
.form-input-icon {
  margin-right: 10px;
  margin-left: 12px;
  color: #888;
  font-size: 16px;
  display: flex;
  align-items: center;
  position: absolute;
  left: 0;
  height: 100%;
}
.form-dropdown-chevron {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #888;
  font-size: 16px;
  display: flex;
  align-items: center;
  pointer-events: none;
  height: 100%;
  background: none;
  padding: 0;
}
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
}
</style>
