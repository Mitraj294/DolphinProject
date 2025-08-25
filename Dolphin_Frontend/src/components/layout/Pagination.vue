<template>
  <footer class="footer-box">
    <div class="footer-controls split-footer-controls">
      <div class="notifications-page-size-dropdown">
        <button
          class="notifications-page-size-btn"
          @click="$emit('togglePageDropdown')"
          ref="pageSizeBtn"
        >
          {{ pageSize }}/page
          <img
            src="@/assets/images/VectorDown.svg"
            class="notifications-page-size-arrow"
            alt="Dropdown"
          />
        </button>
        <div
          v-if="showPageDropdown"
          class="notifications-page-size-menu"
          ref="pageSizeMenu"
        >
          <div
            v-for="size in pageSizes"
            :key="size"
            class="notifications-page-size-option"
            :class="{ selected: pageSize === size }"
            @click="$emit('selectPageSize', size)"
          >
            {{ size }}/page
          </div>
        </div>
      </div>
      <div class="notifications-pagination">
        <button
          class="notifications-pagination-btn prev"
          :disabled="currentPage === 1"
          @click="$emit('goToPage', currentPage - 1)"
        >
          <img
            src="@/assets/images/VectorLeft.svg"
            alt="Previous"
            class="pagination-btn-icon"
          />
          <span class="pagination-btn-text prev-text">Previous</span>
        </button>
        <template v-for="pageItem in computedPaginationPages">
          <button
            v-if="pageItem !== '...'"
            :key="pageItem + '-page'"
            :class="[
              'notifications-pagination-page',
              { selected: pageItem === currentPage },
            ]"
            @click="$emit('goToPage', pageItem)"
          >
            {{ pageItem }}
          </button>
          <span
            v-else
            :key="'ellipsis-' + Math.random()"
            class="notifications-pagination-page"
            >...</span
          >
        </template>
        <button
          class="notifications-pagination-btn next"
          :disabled="currentPage === totalPages"
          @click="$emit('goToPage', currentPage + 1)"
        >
          <span class="pagination-btn-text next-text">Next</span>
          <img
            src="@/assets/images/VectorRight.svg"
            alt="Next"
            class="pagination-btn-icon"
          />
        </button>
      </div>
    </div>
  </footer>
</template>

<script>
import '@/assets/global.css';
export default {
  name: 'Pagination',
  props: {
    withPagination: {
      type: Boolean,
      default: false,
    },
    pageSize: Number,
    pageSizes: Array,
    showPageDropdown: Boolean,
    currentPage: Number,
    totalPages: {
      type: Number,
      default: 10,
    },
    paginationPages: Array,
  },
  computed: {
    computedPaginationPages() {
      const total = this.totalPages || 10;
      const current = this.currentPage || 1;
      if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
      }
      if (current <= 3) {
        return [1, 2, 3, 4, '...', total - 1, total];
      }
      if (current === 4) {
        return [1, 3, 4, 5, '...', total - 1, total];
      }
      if (current === 5) {
        return [1, 4, 5, 6, '...', total - 1, total];
      }
      if (current === 6) {
        return [1, 5, 6, 7, '...', total - 1, total];
      }
      if (current === 7) {
        return [1, 2, '...', 6, 7, 8, total, total - 1].filter(
          (v, i, arr) => arr.indexOf(v) === i
        );
      }
      if (current === total - 2) {
        return [1, 2, '...', total - 3, total - 2, total - 1, total];
      }
      if (current === total - 1) {
        return [1, 2, '...', total - 2, total - 1, total];
      }
      if (current === total) {
        return [1, 2, '...', total - 2, total - 1, total];
      }
      return [
        1,
        2,
        '...',
        current - 1,
        current,
        current + 1,
        '...',
        total - 1,
        total,
      ].filter((v, i, arr) => arr.indexOf(v) === i);
    },
  },
  mounted() {
    document.addEventListener('mousedown', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('mousedown', this.handleClickOutside);
  },
  methods: {
    handleClickOutside(e) {
      if (!this.showPageDropdown) return;
      const menu = this.$refs.pageSizeMenu;
      const btn = this.$refs.pageSizeBtn;
      if (menu && !menu.contains(e.target) && btn && !btn.contains(e.target)) {
        this.$emit('togglePageDropdown');
      }
    },
  },
};
</script>

<style scoped>
.footer-box {
  width: 100%;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0 0 18px 18px;
  box-sizing: border-box;
  padding: 0;
  position: sticky;
  bottom: 0;
  left: 0;
  min-height: 64px;
  box-shadow: none;
  overflow: visible !important;
  margin: 0;
  z-index: 10;
}
.footer-controls {
  width: 100%;
  max-width: 1400px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0;
  padding: 0 24px;
  box-sizing: border-box;
  overflow-x: visible !important;
}
.split-footer-controls {
  justify-content: space-between;
}
.notifications-page-size-dropdown {
  display: flex;
  align-items: flex-start;
  position: relative;
}
.notifications-page-size-btn {
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 16px;
  padding: 0 14px;
  font-size: 13px;
  color: #222;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  height: 32px;
  min-width: 70px;
  box-sizing: border-box;
  position: relative;
  z-index: 2;
}
.notifications-page-size-arrow {
  width: 13px;
  height: 13px;
  margin-left: 4px;
}
.notifications-page-size-menu {
  left: 50% !important;
  transform: translateX(-50%);
  min-width: 100px !important;
  width: auto !important;
  max-width: 90vw;
  position: absolute;
  left: 0;
  top: 100%;
  min-width: 100%;
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.08);
  z-index: 3;
  display: flex;
  flex-direction: column;
  padding: 4px 0;
  margin-top: 4px;
}
.notifications-page-size-option {
  padding: 7px 14px;
  font-size: 12px;
  color: #222;
  cursor: pointer;
  transition: background 0.18s;
  border-radius: 7px;
}
.notifications-page-size-option.selected,
.notifications-page-size-option:hover {
  background: #f0f8ff;
  color: #0074c2;
}
.notifications-pagination {
  display: flex;
  align-items: center;
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 16px;
  padding: 0;
  box-sizing: border-box;
  margin-left: auto;
  margin-right: 0;
  width: auto;
  max-width: 100%;
  overflow-x: auto;
  white-space: nowrap;
}
.notifications-pagination > * {
  display: inline-block;
}
.notifications-pagination::-webkit-scrollbar {
  display: none;
}
.notifications-pagination-btn {
  border: none;
  background: #fff;
  font-size: 13px;
  color: #222;
  padding: 0 14px;
  height: 32px;
  min-width: 90px; /* Ensures all pagination buttons have a minimum width */
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  border-right: 1px solid #e0e0e0;
}
.notifications-pagination-btn.prev,
.notifications-pagination-btn.next {
  width: 100px; /* Fixed width for both buttons */
  min-width: 100px;
  padding-left: 0;
  padding-right: 0;
  justify-content: center;
}
.notifications-pagination-btn.prev img {
  margin-right: 8px;
  margin-left: 0;
}
.notifications-pagination-btn.next img {
  margin-left: 8px;
  margin-right: 0;
}
.notifications-pagination-btn:first-child {
  border-top-left-radius: 16px;
  border-bottom-left-radius: 16px;
}
.notifications-pagination-btn:last-child {
  border-top-right-radius: 16px;
  border-bottom-right-radius: 16px;
  border-right: none;
}
.notifications-pagination-btn[disabled] {
  color: #888;
  background: #fff;
  cursor: default;
}
/* Add left border to the first pagination page after prev button */
.notifications-pagination-page {
  border: none;
  background: #fff;
  font-size: 13px;
  color: #222;
  padding: 0 14px;
  height: 32px;
  min-width: 32px;
  display: flex;
  align-items: center;
  cursor: pointer;
  transition: background 0.15s;
  border-right: 1px solid #e0e0e0;
  border-left: 1px solid #e0e0e0;
  border-radius: 0;
  font-family: inherit;
  font-weight: 400;
  margin: 0;
  box-sizing: border-box;
  flex-shrink: 0;
  white-space: nowrap;
}
/* Remove left border for the very first page if it's the first child (to avoid double border if needed) */
.notifications-pagination > .notifications-pagination-page:first-of-type {
  border-left: none;
}
.notifications-pagination-btn.prev img {
  margin-right: 8px;
  margin-left: 0;
}
.notifications-pagination-btn.next img {
  margin-left: 8px;
  margin-right: 0;
}
.notifications-pagination-btn:first-child {
  border-top-left-radius: 16px;
  border-bottom-left-radius: 16px;
}
.notifications-pagination-btn:last-child {
  border-top-right-radius: 16px;
  border-bottom-right-radius: 16px;
  border-right: none;
}
.notifications-pagination-btn[disabled] {
  color: #888;
  background: #fff;
  cursor: default;
}
.notifications-pagination-page.selected {
  background: #f5f5f5;
  font-weight: 600;
  color: #222;
}
.notifications-pagination-page:last-child {
  border-right: none;
}
.notifications-pagination-ellipsis {
  padding: 0 14px;
  color: #888;
  font-size: 13px;
  display: flex;
  align-items: center;
  background: #fff;
  border-right: 1px solid #e0e0e0;
  height: 32px;
  box-sizing: border-box;
}
.pagination-btn-text {
  display: inline-flex;
  align-items: center;
  height: 32px;
  line-height: 32px;
  font-size: 13px;
}
@media (max-width: 900px) {
  .footer-box {
    margin: 12px;
    overflow: visible !important;
  }
  .footer-controls,
  .footer-controls-flex {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 0 8px;
    overflow-x: visible !important;
  }
  .notifications-page-size-dropdown,
  .center-footer-logo-block,
  .notifications-pagination {
    align-self: center;
    justify-content: center;
    width: 100%;
    display: flex;
    flex-direction: row;
  }
  .footer-logo-vertical.responsive-footer-logo {
    width: 22px;
    height: 22px;
  }
  .footer-text-vertical {
    font-size: 12px;
  }
  .center-footer-logo-block {
    margin: 8px 0 0 0;
  }
  .notifications-pagination {
    flex-wrap: nowrap;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    white-space: nowrap;
    width: auto;
    margin-left: auto;
    margin-right: auto;
    justify-content: center;
  }
  .notifications-pagination > * {
    display: inline-block;
  }
  .notifications-pagination-btn {
    height: 32px;
    min-height: 32px;
    max-height: 32px;
    font-size: 13px;
    gap: 4px;
    align-items: center;
    justify-content: center;
    /* Remove any margin-top from img to keep icon vertically centered */
  }
  .pagination-btn-text {
    height: 32px;
    line-height: 32px;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .notifications-pagination-btn img {
    height: 18px;
    width: 18px;
    object-fit: contain;
    display: inline-block;
    margin-top: 0; /* Ensure icon is vertically centered */
    vertical-align: middle;
  }
}
@media (max-width: 600px) {
  .notifications-page-size-menu {
    left: 50% !important;
    transform: translateX(-50%);
    min-width: 100px !important;
    width: auto !important;
    max-width: 90vw;
  }
  .notifications-pagination {
    flex-wrap: nowrap;
    max-width: 100vw;
    min-width: 0;
    overflow-x: auto;
    white-space: nowrap;
    width: auto;
    margin-left: auto;
    margin-right: auto;
    justify-content: center;
  }
  .notifications-pagination-btn.prev .prev-text,
  .notifications-pagination-btn.next .next-text {
    display: none;
  }
  .notifications-pagination-btn.prev,
  .notifications-pagination-btn.next {
    width: 40px;
    min-width: 40px;
    padding: 0;
    justify-content: center;
  }
  .pagination-btn-icon {
    height: 18px;
    width: 18px;
    display: inline-block;
    margin: 0 auto;
  }
}
</style>
