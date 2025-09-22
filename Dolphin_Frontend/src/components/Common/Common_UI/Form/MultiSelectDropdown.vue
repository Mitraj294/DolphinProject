<template>
  <div
    class="form-box"
    ref="dropdownRoot"
  >
    <div>
      <span class="form-input-icon">
        <i :class="icon"></i>
      </span>
    </div>
    <div
      class="selected-container"
      @click="toggleDropdown"
    >
      <template v-if="selectedItems && selectedItems.length">
        <span
          v-for="(s, idx) in selectedItems"
          :key="idx"
          class="selected-chip"
          @click.stop
        >
          <span class="chip-label">{{ labelFor(s) }}</span>
          <button
            class="chip-remove"
            @click.stop.prevent="removeSelected(s)"
          >
            ×
          </button>
        </span>
      </template>
      <template v-else>
        <span class="selected-placeholder">{{
          placeholder || 'Select...'
        }}</span>
      </template>
    </div>
    <Button
      class="form-dropdown-chevron"
      @click.stop="toggleDropdown"
      @keydown.enter.prevent="toggleDropdown"
      @keydown.space.prevent="toggleDropdown"
      type="button"
    >
      <i
        class="fas fa-chevron-down"
        aria-hidden="true"
      ></i>
    </Button>
    <teleport to="body">
      <div
        v-if="showDropdown"
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
    </teleport>
  </div>
</template>

<script>
import Button from 'primevue/button';

export default {
  name: 'MultiSelectDropdown',
  components: {
    Button,
  },
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
      dropdownStyle: {},
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
    // Build a readable string for selected items; handles primitives, objects, id references,
    // nested shapes, and circular objects via a safe stringify fallback.
    selectedLabelString() {
      if (!Array.isArray(this.selectedItems) || this.selectedItems.length === 0)
        return '';

      const safeStringify = (obj) => {
        try {
          const seen = new WeakSet();
          return JSON.stringify(
            obj,
            (k, v) => {
              if (v && typeof v === 'object') {
                if (seen.has(v)) return '[Circular]';
                seen.add(v);
              }
              return v;
            },
            2
          );
        } catch (e) {
          console.error('Error stringifying object', e);
          try {
            if (obj && typeof obj === 'object') {
              const parts = Object.keys(obj)
                .slice(0, 4)
                .map((k) => `${k}:${String(obj[k])}`);
              return parts.join(' ');
            }
          } catch (e2) {
            console.error('Error in fallback stringify', e2);
          }
          return String(obj);
        }
      };

      const commonLabelKeys = [
        this.optionLabel,
        'name',
        'label',
        'title',
        'role',
        'display_name',
        'text',
      ];

      const labels = this.selectedItems
        .map((s) => {
          if (s === null || s === undefined) return '';
          // primitive selected (id or label)
          if (typeof s === 'string' || typeof s === 'number') {
            // try to resolve to option label by id
            const opt = this.options.find((o) => o[this.optionValue] === s);
            if (
              opt &&
              (typeof opt[this.optionLabel] === 'string' ||
                typeof opt[this.optionLabel] === 'number')
            )
              return opt[this.optionLabel];
            return String(s);
          }

          // object selected: try common label keys and nested shapes
          if (typeof s === 'object') {
            for (const key of commonLabelKeys) {
              if (s && Object.hasOwn(s, key)) {
                const v = s[key];
                if (typeof v === 'string' && v.trim()) return v.trim();
                if (typeof v === 'number') return String(v);
              }
            }

            // check nested common shapes (e.g., s.role.name)
            for (const key of ['role', 'user', 'data']) {
              if (s[key] && typeof s[key] === 'object') {
                const nested = s[key];
                const nestedVal = nested[this.optionLabel];
                if (nestedVal !== undefined) {
                  if (
                    typeof nestedVal === 'string' ||
                    typeof nestedVal === 'number'
                  ) {
                    return String(nestedVal);
                  }
                  return safeStringify(nestedVal);
                }
                if (nested.name !== undefined) {
                  if (
                    typeof nested.name === 'string' ||
                    typeof nested.name === 'number'
                  ) {
                    return String(nested.name);
                  }
                  return safeStringify(nested.name);
                }
              }
            }

            // resolve via id lookup against options
            if (s[this.optionValue] !== undefined) {
              const opt = this.options.find(
                (o) => o[this.optionValue] === s[this.optionValue]
              );
              if (
                opt &&
                (typeof opt[this.optionLabel] === 'string' ||
                  typeof opt[this.optionLabel] === 'number')
              )
                return opt[this.optionLabel];
            }

            // Try to find any string value on the object
            try {
              const vals = Object.values(s || {});
              const strVal = vals.find(
                (v) => typeof v === 'string' && v.trim()
              );
              if (strVal) return strVal.trim();
            } catch (e) {
              console.error('Error extracting string from object', e);
            }

            // Fallback to safe stringification
            return safeStringify(s);
          }
          return String(s);
        })
        .filter((x) => x !== '');

      return labels.join(', ');
    },
  },
  mounted() {
    document.addEventListener('mousedown', this.handleClickOutside);
    window.addEventListener('resize', this.updateDropdownPosition);
    window.addEventListener('scroll', this.updateDropdownPosition, true);
  },
  beforeDestroy() {
    document.removeEventListener('mousedown', this.handleClickOutside);
    window.removeEventListener('resize', this.updateDropdownPosition);
    window.removeEventListener('scroll', this.updateDropdownPosition, true);
  },
  methods: {
    labelFor(s) {
      if (s === null || s === undefined) return '';

      if (this.isPrimitive(s)) {
        return this.labelForPrimitive(s);
      }

      if (typeof s === 'object') {
        return this.labelForObject(s);
      }

      return String(s);
    },

    isPrimitive(val) {
      return typeof val === 'string' || typeof val === 'number';
    },

    labelForPrimitive(val) {
      const opt = this.options.find((o) => o[this.optionValue] === val);
      if (opt && this.isPrimitive(opt[this.optionLabel])) {
        return String(opt[this.optionLabel]);
      }
      return String(val);
    },

    labelForObject(obj) {
      const lbl = obj[this.optionLabel] || obj.name || obj.label || obj.title;
      if (lbl !== undefined) {
        return this.stringifyLabel(lbl);
      }

      if (obj[this.optionValue] !== undefined) {
        const opt = this.options.find(
          (o) => o[this.optionValue] === obj[this.optionValue]
        );
        if (opt && opt[this.optionLabel]) {
          return opt[this.optionLabel];
        }
      }

      return JSON.stringify(obj);
    },

    stringifyLabel(lbl) {
      if (this.isPrimitive(lbl)) return String(lbl);
      try {
        return JSON.stringify(lbl);
      } catch {
        return String(lbl);
      }
    },
    removeSelected(item) {
      const newSelected = this.selectedItems.filter((i) => {
        const a = typeof i === 'object' ? i[this.optionValue] : i;
        const b = typeof item === 'object' ? item[this.optionValue] : item;
        return a !== b;
      });
      this.$emit('update:selectedItems', newSelected);
    },
    toggleDropdown() {
      this.showDropdown = !this.showDropdown;
      if (this.showDropdown)
        this.$nextTick(() => this.updateDropdownPosition());
    },
    toggleItem(item) {
      // Compare items by the configured optionValue (defaults to 'id')
      const idx = this.selectedItems.findIndex(
        (i) => i[this.optionValue] === item[this.optionValue]
      );
      if (idx > -1) {
        this.$emit(
          'update:selectedItems',
          this.selectedItems.filter(
            (i) => i[this.optionValue] !== item[this.optionValue]
          )
        );
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
      const dropdownEl = this.$refs.dropdownEl;
      const clickedInsideRoot = root && root.contains(event.target);
      const clickedInsideDropdown =
        dropdownEl && dropdownEl.contains(event.target);
      if (!clickedInsideRoot && !clickedInsideDropdown) {
        this.showDropdown = false;
      }
    },
    updateDropdownPosition() {
      const root = this.$refs.dropdownRoot;
      const el = this.$refs.dropdownEl;
      if (!root || !el) return;
      const rect = root.getBoundingClientRect();
      const top = rect.bottom + window.scrollY + 6;
      const left = rect.left + window.scrollX;
      const width = rect.width;
      this.dropdownStyle = {
        position: 'absolute',
        top: `${top}px`,
        left: `${left}px`,
        width: `${width}px`,
        zIndex: 99999,
      };
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

.selected-container {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: nowrap;
  margin: 0 36px;
  overflow-x: auto;
  overflow-y: hidden;
  padding: 6px 0;

  min-height: 44px;

  -webkit-overflow-scrolling: touch;
  white-space: nowrap;
}
.selected-chip {
  display: inline-flex;
  align-items: center;
  flex: 0 0 auto;
  background: #0074c2;
  border-radius: 18px;
  padding: 6px 10px;
  font-size: 14px;
  font-weight: 400;
  color: #ffffff;
}
.chip-label {
  max-width: 160px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.chip-remove {
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
  padding: 0 4px;
}
.selected-container::-webkit-scrollbar {
  height: 8px;
}
.selected-container::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.12);
  border-radius: 8px;
}
.selected-container::-webkit-scrollbar-track {
  background: transparent;
}
.selected-placeholder {
  color: #9a9a9a;
  font-size: 14px;
  padding-left: 2px;
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
  pointer-events: auto;
  cursor: pointer;
  height: 100%;
  background: none;
  border: none;
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
