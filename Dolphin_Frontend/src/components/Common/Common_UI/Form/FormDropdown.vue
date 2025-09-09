<template>
  <div
    class="form-box"
    ref="dropdownRoot"
  >
    <span
      v-if="icon"
      class="form-input-icon"
    >
      <i :class="icon"></i>
    </span>
    <input
      type="text"
      :placeholder="placeholder"
      readonly
      :value="selectedLabel"
      class="form-input-with-icon"
      @click="toggleDropdown"
      :disabled="disabled"
      :style="inputStyle"
    />
    <span class="form-dropdown-chevron">
      <i class="fas fa-chevron-down"></i>
    </span>
    <teleport
      to="body"
      v-if="showDropdown"
    >
      <div
        ref="dropdownEl"
        class="dropdown-list"
        :style="dropdownStyle"
      >
        <input
          class="dropdown-search"
          placeholder="Search"
          v-model="search"
        />
        <div
          v-for="option in filteredOptions"
          :key="option.value"
          class="dropdown-item"
          @click="selectOption(option)"
        >
          <span>{{ option.text }}</span>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script>
export default {
  name: 'FormDropdown',
  props: {
    modelValue: [String, Number],
    options: { type: Array, required: false, default: null }, // If not provided, will use slot
    icon: { type: String, default: '' },
    placeholder: { type: String, default: 'Select' },
    disabled: { type: Boolean, default: false },
    // allow callers to override horizontal padding in px (left/right)
    paddingLeft: { type: [Number, String], default: 36 },
    paddingRight: { type: [Number, String], default: 36 },
  },
  data() {
    return {
      showDropdown: false,
      search: '',
      dropdownStyle: {},
    };
  },
  computed: {
    selectedLabel() {
      if (this.options && this.options.length) {
        const found = this.options.find((opt) => opt.value === this.modelValue);
        return found ? found.text : this.placeholder;
      }
      // fallback for slot
      return this.placeholder;
    },
    inputStyle() {
      // ensure numeric value and append 'px'
      const left = Number(this.paddingLeft) || 36;
      const right = Number(this.paddingRight) || 36;
      return { padding: `0 ${right}px 0 ${left}px` };
    },
    filteredOptions() {
      let opts = [];
      if (this.options && this.options.length) {
        opts = this.options;
      } else if (this.$slots.default) {
        // Parse slot options
        const slotNodes = this.$slots.default();
        opts = slotNodes
          .filter(
            (node) =>
              node.type === 'option' &&
              node.props &&
              node.props.value !== undefined
          )
          .map((node) => {
            let childText = '';
            if (typeof node.children === 'string') {
              childText = node.children;
            } else if (Array.isArray(node.children)) {
              childText = node.children.join('');
            } else {
              console.warn('Unsupported slot child type in FormDropdown');
            }
            return {
              value: node.props.value,
              text: childText,
            };
          });
      } else;

      if (!this.search) return opts;
      return opts.filter((opt) =>
        (opt.text || '').toLowerCase().includes(this.search.toLowerCase())
      );
    },
  },
  methods: {
    toggleDropdown() {
      if (this.disabled) return;
      this.showDropdown = !this.showDropdown;
      if (this.showDropdown) {
        // update position on open
        this.$nextTick(() => this.updateDropdownPosition());
      }
    },
    selectOption(option) {
      this.$emit('update:modelValue', option.value);
      this.$emit('change', option.value); // Always emit change event
      this.showDropdown = false;
      this.search = '';
    },
    handleClickOutside(event) {
      if (!this.showDropdown) return;
      const root = this.$refs.dropdownRoot;
      const dropdownEl = this.$refs.dropdownEl;
      const clickedInsideRoot = root && root.contains(event.target);
      const clickedInsideDropdown =
        dropdownEl && dropdownEl.contains(event.target);
      if (!clickedInsideRoot && !clickedInsideDropdown) {
        this.showDropdown = false;
      }
    },
    updateDropdownPosition() {
      // position the teleported dropdown relative to the input's root element
      const root = this.$refs.dropdownRoot;
      const el = this.$refs.dropdownEl;
      if (!root || !el) return;
      const rect = root.getBoundingClientRect();
      const top = rect.bottom + window.scrollY + 6; // small offset
      const left = rect.left + window.scrollX;
      const width = rect.width;
      // apply fixed-positioning so it stays in viewport during scroll
      this.dropdownStyle = {
        position: 'absolute',
        top: `${top}px`,
        left: `${left}px`,
        width: `${width}px`,
        zIndex: 99999,
      };
    },
  },
  mounted() {
    document.addEventListener('mousedown', this.handleClickOutside);
    // keep position updated on resize/scroll
    window.addEventListener('resize', this.updateDropdownPosition);
    window.addEventListener('scroll', this.updateDropdownPosition, true);
  },
  watch: {
    showDropdown(newVal) {
      if (newVal) this.$nextTick(() => this.updateDropdownPosition());
    },
  },
  beforeUnmount() {
    document.removeEventListener('mousedown', this.handleClickOutside);
    window.removeEventListener('resize', this.updateDropdownPosition);
    window.removeEventListener('scroll', this.updateDropdownPosition, true);
  },
};
</script>

<style scoped>
/* ...existing styles from MultiSelectDropdown.vue... */
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
  width: 100%;
}
.form-input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #888;
  font-size: 16px;
  display: flex;
  align-items: center;
  height: 100%;
  pointer-events: none;
}
.form-input-with-icon {
  width: 100%;
  height: 44px;
  font-size: 16px;
  border: none;
  outline: none;
  color: #222;
  background: transparent;
  font-family: inherit;
  padding: 0 36px 0 16px; /* left for icon, right for chevron */
  box-sizing: border-box;
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
.dropdown-list {
  position: absolute;
  top: 54px;
  left: 0;
  width: 100%;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(33, 150, 243, 0.08);
  border: 1px solid #eee;
  z-index: 10;
  max-height: 240px;
  overflow-y: auto;
  padding: 8px 0 8px 0;
  box-sizing: border-box;
}
.dropdown-search {
  width: 96%;
  margin: 0 2% 12px 2%;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  font-size: 15px;
  outline: none;
  background: #f6f6f6;
  box-sizing: border-box;
}
.dropdown-item {
  width: 100%;
  padding: 8px 16px;
  font-size: 15px;
  color: #222;
  cursor: pointer;
  transition: background 0.15s;
  background: #fff;
  border: none;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  box-sizing: border-box;
}
.dropdown-item:hover {
  background: #f6f6f6;
}
</style>
