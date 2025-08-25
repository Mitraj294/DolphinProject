<template>
  <!-- Render a colgroup so callers can declare per-column widths when building the columns array.
       Usage example (in parent): { label: 'Email', key: 'email', width: '220px' } or width: 220
       If width is not provided, column size will be determined by content (table-layout: auto).
  -->
  <colgroup>
    <col
      v-for="(col, idx) in columns"
      :key="'col-' + (col.key || idx)"
      :class="col.width ? 'col-fixed' : ''"
      :style="
        col.width
          ? {
              width:
                typeof col.width === 'number' ? col.width + 'px' : col.width,
            }
          : null
      "
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
          col.width ? 'col-fixed' : '',
        ]"
        :role="col.sortable ? 'button' : null"
        :tabindex="col.sortable ? 0 : null"
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
    // Optional: which column is currently sorted (key)
    activeSortKey: {
      type: String,
      default: null,
    },
    // Optional: current sort direction
    sortAsc: {
      type: Boolean,
      default: true,
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

@media (max-width: 1400px) {
  .rounded-th-left {
    padding-left: 20px !important;
  }
}
@media (max-width: 900px) {
  .rounded-th-left {
    border-top-left-radius: 10px;
    border-bottom-left-radius: 10px;
    padding-left: 20px !important;
  }
  .rounded-th-right {
    border-top-right-radius: 10px;
    border-bottom-right-radius: 10px;
  }
}
</style>
