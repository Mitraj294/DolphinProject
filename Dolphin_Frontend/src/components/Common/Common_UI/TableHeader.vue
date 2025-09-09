<template>
  <colgroup>
    <col
      v-for="(col, idx) in columns"
      :key="'col-' + (col.key || idx)"
      :class="col.width || col.minWidth"
      :style="getColumnStyle(col)"
    />
  </colgroup>
  <thead>
    <tr>
      <th
        v-for="(col, idx) in columns"
        :key="col.key || idx"
        :class="[
          idx === 0 ? 'rounded-th-left' : '',
          idx === columns.length - 1 ? 'rounded-th-right' : '',
          col.width || col.minWidth,
          col.class || '',
        ]"
        :style="getColumnStyle(col)"
        :role="col.sortable ? 'button' : null"
        :tabindex="col.sortable ? 0 : null"
        scope="col"
        @click="col.sortable === true ? $emit('sort', col.key) : null"
        @keyup.enter="col.sortable === true ? $emit('sort', col.key) : null"
      >
        <span
          :class="[
            'org-th-content',
            col.sortable ? 'org-th-sortable' : '',
            activeSortKey === col.key ? 'sorted' : '',
          ]"
        >
          {{ col.label }}
          <img
            v-if="col.sortable === true"
            src="@/assets/images/up-down.svg"
            :class="[
              'org-th-sort',
              activeSortKey === col.key ? (sortAsc ? 'asc' : 'desc') : '',
            ]"
            alt="Sort"
          />
        </span>
      </th>
    </tr>
  </thead>
</template>

<script>
export default {
  name: 'TableHeader',
  emits: ['sort'],
  props: {
    columns: {
      type: Array,
      required: true,
    },

    activeSortKey: {
      type: String,
      default: null,
    },

    sortAsc: {
      type: Boolean,
      default: true,
    },
  },
  methods: {
    getColumnStyle(col) {
      let style = {};

      // Handle explicit width property
      if (col.width) {
        style.width =
          typeof col.width === 'number' ? col.width + 'px' : col.width;
      }

      // Handle minWidth property
      if (col.minWidth) {
        style.minWidth =
          typeof col.minWidth === 'number' ? col.minWidth + 'px' : col.minWidth;
      }

      // Handle style property (CSS string)
      if (col.style) {
        // Parse CSS string and add to style object
        const cssRules = col.style.split(';').filter((rule) => rule.trim());
        cssRules.forEach((rule) => {
          const [property, value] = rule.split(':').map((s) => s.trim());
          if (property && value) {
            // Convert kebab-case to camelCase for Vue style binding
            const camelProperty = property.replace(/-([a-z])/g, (g) =>
              g[1].toUpperCase()
            );
            style[camelProperty] = value;
          }
        });
      }

      return Object.keys(style).length > 0 ? style : null;
    },
  },
};
</script>

<style scoped>
.org-th-content {
  display: block;
  text-align: left;
  font-size: 14px;
  font-weight: 600;
  color: #888;
}
.org-th-sort-btn {
  background: none;
  border: none;
  display: inline-flex;
  align-items: center;
  vertical-align: middle;
  cursor: pointer;
  height: 1em;
  line-height: 1;
}
.org-th-sort {
  width: 1em;
  height: 1em;
  min-width: 16px;
  min-height: 16px;
  max-width: 18px;
  max-height: 18px;
  margin-left: 2px;
  opacity: 0.7;
  transition: opacity 0.15s;
}

.rounded-th-left {
  padding-left: 20px !important;
}
</style>
