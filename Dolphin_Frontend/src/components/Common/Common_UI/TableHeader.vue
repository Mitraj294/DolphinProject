<template>
  <thead>
    <tr>
      <th
        v-for="(col, idx) in columns"
        :key="col.key || idx"
        :class="[
          idx === 0 ? 'rounded-th-left' : '',
          idx === columns.length - 1 ? 'rounded-th-right' : '',
        ]"
      >
        <span class="org-th-content">
          {{ col.label }}
          <button
            v-if="col.sortable === true"
            class="org-th-sort-btn"
            type="button"
            @click="$emit('sort', col.key)"
          >
            <img
              src="@/assets/images/up-down.svg"
              class="org-th-sort"
              alt="Sort"
            />
          </button>
        </span>
      </th>
    </tr>
  </thead>
</template>

<script>
export default {
  name: 'TableHeader',
  props: {
    columns: {
      type: Array,
      required: true,
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
