<template>
  <FormBox :error="error">
    <template v-if="icon">
      <span class="form-input-icon">
        <i :class="icon"></i>
      </span>
      <input
        v-bind="$attrs"
        :type="type"
        :placeholder="placeholder"
        v-model="inputValue"
        :readonly="readonly"
        :disabled="disabled"
        :class="icon ? 'form-input with-icon' : 'form-input'"
        @input="$emit('update:modelValue', inputValue)"
      />
    </template>
    <template v-else>
      <div class="form-input-noicon-wrap">
        <input
          v-bind="$attrs"
          :type="type"
          :placeholder="placeholder"
          v-model="inputValue"
          :readonly="readonly"
          :disabled="disabled"
          class="form-input"
          @input="$emit('update:modelValue', inputValue)"
        />
      </div>
    </template>
  </FormBox>
</template>

<script>
import FormBox from './FormBox.vue';
export default {
  name: 'FormInput',
  components: { FormBox },
  props: {
    modelValue: [String, Number],
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    icon: { type: String, default: '' },
    error: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
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
  methods: {
    focus() {
      this.$nextTick(() => {
        const el = this.$el && this.$el.querySelector('input');
        if (el && typeof el.focus === 'function') el.focus();
      });
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

  font-family: inherit;
  box-sizing: border-box;
}
.form-input.with-icon {
  padding-left: 40px;
}
.form-input-noicon-wrap {
  width: 100%;
  height: 44px;
  display: flex;
  align-items: center;
  box-sizing: border-box;
}
.form-input:disabled {
  background: #f0f0f0;
  color: #aaa;
}
.form-input-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #888;
  font-size: 18px;
  display: flex;
  align-items: center;
  height: 44px;
  pointer-events: none;
  z-index: 2;
}
</style>
