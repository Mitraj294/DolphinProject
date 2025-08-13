<template>
  <div
    class="form-box"
    ref="dropdownRoot"
  >
    <span class="form-input-icon">
      <i :class="icon"></i>
    </span>
    <input
      type="text"
      :placeholder="placeholder"
      readonly
      :value="selectedItems.map((item) => item[optionLabel]).join(', ')"
      class="form-input-with-icon"
      @click="toggleDropdown"
    />
    <span class="form-dropdown-chevron">
      <i class="fas fa-chevron-down"></i>
    </span>
    <div
      v-if="showDropdown"
      class="dropdown-list"
    >
      <input
        class="dropdown-search"
        placeholder="Search"
        v-model="search"
      />
      <div
        v-if="enableSelectAll"
        class="dropdown-item"
        @click="toggleSelectAll"
      >
        <span><strong>Select All</strong></span>
        <span
          class="dropdown-checkbox"
          :class="{ checked: isAllSelected }"
        ></span>
      </div>
      <div
        v-for="item in filteredItems"
        :key="item[optionValue]"
        class="dropdown-item"
        @click="toggleItem(item)"
      >
        <span>{{ item[optionLabel] }}</span>
        <span
          class="dropdown-checkbox"
          :class="{
            checked: selectedItems.some(
              (i) => i[optionValue] === item[optionValue]
            ),
          }"
        ></span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MultiSelectDropdown',
  props: {
    options: { type: Array, required: true },
    selectedItems: { type: Array, required: true },
    placeholder: { type: String, default: '' },
    icon: { type: String, default: 'fas fa-users' },
    optionLabel: { type: String, default: 'name' },
    optionValue: { type: String, default: 'id' },
    enableSelectAll: { type: Boolean, default: false },
  },
  data() {
    return {
      showDropdown: false,
      search: '',
    };
  },
  computed: {
    filteredItems() {
      if (!this.search) return this.options;
      return this.options.filter((item) =>
        (item[this.optionLabel] || '')
          .toLowerCase()
          .includes(this.search.toLowerCase())
      );
    },
    isAllSelected() {
      if (!this.filteredItems.length) return false;
      return this.filteredItems.every((item) =>
        this.selectedItems.some(
          (i) => i[this.optionValue] === item[this.optionValue]
        )
      );
    },
  },
  mounted() {
    document.addEventListener('mousedown', this.handleClickOutside);
  },
  beforeDestroy() {
    document.removeEventListener('mousedown', this.handleClickOutside);
  },
  methods: {
    toggleDropdown() {
      this.showDropdown = !this.showDropdown;
    },
    toggleItem(item) {
      const idx = this.selectedItems.findIndex((i) => i.name === item.name);
      if (idx > -1) {
        this.$emit(
          'update:selectedItems',
          this.selectedItems.filter((i) => i.name !== item.name)
        );
        // Uncheck select all if any item is unchecked
        // (handled by computed isAllSelected)
      } else {
        this.$emit('update:selectedItems', [...this.selectedItems, item]);
      }
    },
    toggleSelectAll() {
      if (this.isAllSelected) {
        // Unselect all filtered
        const filteredIds = this.filteredItems.map((i) => i[this.optionValue]);
        const newSelected = this.selectedItems.filter(
          (i) => !filteredIds.includes(i[this.optionValue])
        );
        this.$emit('update:selectedItems', newSelected);
      } else {
        // Select all filtered
        // Merge with already selected (avoid duplicates)
        const merged = [...this.selectedItems];
        this.filteredItems.forEach((item) => {
          if (
            !merged.some((i) => i[this.optionValue] === item[this.optionValue])
          ) {
            merged.push(item);
          }
        });
        this.$emit('update:selectedItems', merged);
      }
    },
    handleClickOutside(event) {
      if (!this.showDropdown) return;
      const root = this.$refs.dropdownRoot;
      if (root && !root.contains(event.target)) {
        this.showDropdown = false;
      }
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
  padding: 0 36px 0 36px; /* left for icon, right for chevron */
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
.modal-icon {
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
  max-height: 220px;
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
  justify-content: space-between;
  box-sizing: border-box;
}
.dropdown-item:hover {
  background: #f6f6f6;
}
.dropdown-checkbox {
  width: 18px;
  height: 18px;
  border-radius: 4px;
  border: 1.5px solid #bbb;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s, border 0.15s;
}
.dropdown-checkbox.checked {
  background: #f6f6f6;
  border-color: #888;
}
.dropdown-checkbox.checked:after {
  content: '\2713';
  color: #888;
  font-size: 13px;
  font-weight: bold;
}
.dropdown-checkbox:after {
  content: '';
}
</style>
