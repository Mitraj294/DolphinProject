<template>
  <div
    class="modal-form-group custom-dropdown"
    style="position: relative"
  >
    <span class="modal-icon"><i :class="icon"></i></span>
    <input
      type="text"
      :placeholder="placeholder"
      readonly
      :value="selectedItems.map((item) => item.name).join(', ')"
      style="
        background: transparent;
        border: none;
        outline: none;
        font-size: 16px;
        color: #222;
        width: 100%;
      "
      @click="toggleDropdown"
    />
    <i
      class="fas fa-chevron-down"
      style="margin-left: auto; color: #888; cursor: pointer"
      @click="toggleDropdown"
    ></i>
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
        v-for="item in filteredItems"
        :key="item.id"
        class="dropdown-item"
        @click.stop
      >
        <span>{{ item.name }}</span>
        <span
          class="dropdown-checkbox"
          :class="{ checked: selectedItems.some((i) => i.name === item.name) }"
          @click="toggleItem(item)"
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
        item.name.toLowerCase().includes(this.search.toLowerCase())
      );
    },
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
      } else {
        this.$emit('update:selectedItems', [...this.selectedItems, item]);
      }
    },
  },
};
</script>

<style scoped>
.modal-form-group.custom-dropdown {
  flex-direction: row;
  align-items: center;
  gap: 0;
  min-height: 48px;
  width: 100%;
  background: #f6f6f6;
  border-radius: 9px;
  padding: 0 16px;
  height: 48px;
  display: flex;
  margin-bottom: 0;
  box-sizing: border-box;
}
.modal-icon {
  margin-right: 10px;
  display: flex;
  align-items: center;
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
