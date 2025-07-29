<template>
  <div
    class="dropdown-radio"
    :style="widthStyle + 'margin-right:0;'"
    ref="dropdownRoot"
  >
    <button
      class="dropdown-radio-btn"
      :class="{ open }"
      type="button"
      tabindex="0"
      :style="widthStyle"
      @click="toggleDropdown"
    >
      <span>{{ displayLabel }}</span>
      <img
        class="dropdown-radio-arrow"
        :src="
          open
            ? require('@/assets/images/VectorUp.svg')
            : require('@/assets/images/VectorDown.svg')
        "
        :alt="open ? 'Close' : 'Open'"
      />
    </button>
    <div
      v-if="open"
      class="dropdown-radio-list"
      :style="dropdownListStyle"
    >
      <button
        v-for="opt in options"
        :key="opt.value"
        class="dropdown-radio-option"
        type="button"
        @click="selectOption(opt.value)"
      >
        <span>{{ opt.label }}</span>
        <span
          class="dropdown-radio-circle"
          :class="{ selected: internalValue === opt.value }"
        ></span>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UniversalDropdown',
  props: {
    options: { type: Array, default: () => [] },
    modelValue: { type: String, default: '' },
    dropdownWidth: {
      type: Number,
      default: 240,
    },
  },
  emits: ['update:modelValue'],
  data() {
    return {
      open: false,
      internalValue: this.modelValue || this.options[0]?.value || '',
    };
  },
  computed: {
    widthStyle() {
      return `width: ${this.dropdownWidth}px; min-width: ${this.dropdownWidth}px; max-width: ${this.dropdownWidth}px;`;
    },
    displayLabel() {
      const found = this.options.find((o) => o.value === this.internalValue);
      return found ? found.label : this.options[0]?.label || '';
    },
    dropdownListStyle() {
      return `width: ${this.dropdownWidth}px; min-width: ${this.dropdownWidth}px; max-width: ${this.dropdownWidth}px;`;
    },
  },
  watch: {
    modelValue(val) {
      this.internalValue = val;
    },
    internalValue(val) {
      this.$emit('update:modelValue', val);
    },
  },
  methods: {
    toggleDropdown() {
      this.open = !this.open;
    },
    selectOption(val) {
      this.internalValue = val;
      this.open = false;
    },
    handleClickOutside(e) {
      if (this.open && !this.$el.contains(e.target)) {
        this.open = false;
      }
    },
  },
  mounted() {
    document.addEventListener('mousedown', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('mousedown', this.handleClickOutside);
  },
};
</script>

<style scoped>
.dropdown-radio {
  position: relative;
  display: inline-block;
  cursor: pointer;
  user-select: none;
  overflow: visible !important;
}
.dropdown-radio-btn {
  background: #f0f0f0;
  border: 1px solid #e6e6e6;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 15px;
  color: #222;
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  transition: border 0.2s;
}
.dropdown-radio-btn.open {
  border-color: #0164a5;
}
.dropdown-radio-arrow {
  width: 18px;
  height: 18px;
}
.dropdown-radio-list {
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 9999 !important;
  margin-top: 4px;
  overflow: visible !important;
  position: absolute;
  left: 0;
}
.dropdown-radio-option {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 14px;
  font-size: 15px;
  color: #222;
  background: transparent;
  border: none;
  cursor: pointer;
  width: 100%;
  text-align: left;
}
.dropdown-radio-option:hover {
  background: #f0f0f0;
}
.dropdown-radio-circle {
  width: 12px;
  height: 12px;
  border: 2px solid #0164a5;
  border-radius: 50%;
  position: relative;
  flex-shrink: 0;
}
.dropdown-radio-circle.selected {
  background: #0164a5;
}
</style>
