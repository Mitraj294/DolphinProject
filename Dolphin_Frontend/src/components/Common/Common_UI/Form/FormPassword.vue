<template>
  <FormBox :error="error">
    <span class="form-input-icon"><i class="fas fa-lock"></i></span>
    <input
      :type="show ? 'text' : 'password'"
      v-bind="$attrs"
      v-model="inputValue"
      :placeholder="placeholder"
      class="form-input"
      @input="$emit('update:modelValue', inputValue)"
    />
    <i
      :class="['fas', show ? 'fa-eye-slash' : 'fa-eye', 'input-eye']"
      @click="show = !show"
    ></i>
  </FormBox>
</template>

<script>
import FormBox from './FormBox.vue';
export default {
  name: 'FormPassword',
  components: { FormBox },
  props: {
    modelValue: String,
    placeholder: { type: String, default: '' },
    error: { type: Boolean, default: false },
  },
  data() {
    return { show: false };
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
.form-input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 16px;
  color: #222;
  width: 100%;
  height: 44px;
  padding: 0 36px 0 32px;
  font-family: inherit;
  box-sizing: border-box;
}
.form-input:disabled {
  background: #f0f0f0;
  color: #aaa;
}
.form-input-icon {
  position: absolute;
  left: 12px;
  color: #888;
  font-size: 18px;
  display: flex;
  align-items: center;
  height: 100%;
  z-index: 2;
  pointer-events: none;
}
.input-eye {
  position: absolute;
  right: 12px;
  color: #888;
  font-size: 18px;
  cursor: pointer;
  z-index: 3;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 0;
}
</style>
