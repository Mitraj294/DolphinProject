<template>
  <div
    class="common-modal-overlay"
    v-if="visible"
  >
    <div class="common-modal-card">
      <button
        class="common-modal-close"
        @click="$emit('close')"
      >
        &times;
      </button>
      <h2 class="common-modal-title"><slot name="title"></slot></h2>
      <form
        class="common-modal-form"
        @submit.prevent="$emit('submit')"
      >
        <slot></slot>
        <div class="common-modal-actions">
          <slot name="actions"></slot>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CommonModal',
  props: {
    visible: { type: Boolean, default: false },
  },
};
</script>

<style scoped>
/* Modal and dropdown styles moved from OrgActionButtons.vue */
.common-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.13);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}
.common-modal-card {
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 4px 32px 0 rgba(33, 150, 243, 0.1);
  padding: 40px 48px 32px 48px;
  min-width: 600px;
  max-width: 700px;
  width: 100%;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
.common-modal-close {
  position: absolute;
  top: 24px;
  right: 32px;
  background: none;
  border: none;
  font-size: 32px;
  color: #888;
  cursor: pointer;
  z-index: 10;
}
.common-modal-title {
  font-size: 26px;
  font-weight: 600;
  margin-bottom: 32px;
  color: #222;
}
.common-modal-form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.common-modal-actions {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  margin-top: 18px;
}
.common-modal-form-row {
  display: flex;
  gap: 24px;
  width: 100%;
}
.common-modal-form-group,
.common-modal-form-group.custom-dropdown {
  flex: 1 1 0;
  min-width: 0;
  background: #f6f6f6;
  border-radius: 9px;
  padding: 0 16px;
  height: 48px;
  display: flex;
  align-items: center;
  margin-bottom: 0;
  box-sizing: border-box;
}
.common-modal-form-group.custom-dropdown {
  flex-direction: row;
  align-items: center;
  gap: 0;
  min-height: 48px;
  width: 100%;
}
.common-modal-icon {
  margin-right: 10px;
  display: flex;
  align-items: center;
}
.common-modal-form-group input,
.common-modal-form-group select {
  border: none;
  background: transparent;
  outline: none;
  font-size: 16px;
  color: #222;
  width: 100%;
  height: 44px;
  padding: 0;
  font-family: inherit;
}
.common-modal-form-group select {
  appearance: none;
  cursor: pointer;
}
.common-modal-save-btn {
  border-radius: 22px;
  background: #0164a5;
  color: #fff;
  font-size: 17px;
  font-weight: 500;
  padding: 10px 32px;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}
.common-modal-save-btn:hover {
  background: #005fa3;
}
.common-custom-dropdown {
  position: relative;
  width: 100%;
}
.common-custom-dropdown-input {
  width: 100%;
  background: #f6f6f6;
  border: none;
  border-radius: 9px;
  height: 48px;
  padding: 0;
  font-size: 16px;
  color: #222;
  display: flex;
  align-items: center;
  cursor: pointer;
  position: relative;
  box-shadow: none;
  margin-bottom: 0;
  font-family: inherit;
  font-weight: 500;
}
.common-custom-dropdown-input .fas {
  color: #222;
  font-size: 18px;
  margin-right: 1px;
  position: static;
  left: unset;
  top: unset;
  transform: none;
}
.common-custom-dropdown-input .fas.fa-chevron-down {
  margin-left: auto;
  color: #888;
}
.common-custom-dropdown-input span[style] {
  color: #888 !important;
  font-size: 16px;
  font-weight: 500;
  font-family: inherit;
  margin-left: 2px;
}
.common-selected-tags-below {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: flex-start;
  gap: 10px 18px;
  margin-top: 10px;
  margin-bottom: 0;
  min-height: 0;
  width: 100%;
  box-sizing: border-box;
  white-space: normal;
}
.common-selected-tag {
  background: #f6f6f6;
  border-radius: 22px;
  padding: 8px 24px 8px 16px;
  font-size: 17px;
  color: #222;
  display: flex;
  align-items: center;
  gap: 10px;
  border: none;
  box-shadow: none;
  min-height: 32px;
  font-weight: 500;
  white-space: nowrap;
  margin-bottom: 0;
}
.common-selected-tag .fas {
  cursor: pointer;
  margin-left: 10px;
  color: #bbb;
  font-size: 17px;
  transition: color 0.15s;
}
.common-selected-tag .fas:hover {
  color: #0164a5;
}
@media (max-width: 900px) {
  .common-modal-form_group.custom-dropdown {
    padding: 8px 6px 12px 6px;
    min-height: 60px;
    border-radius: 14px;
  }
  .common-custom-dropdown-input {
    height: 38px;
    font-size: 15px;
    padding: 0 10px 0 28px;
    border-radius: 8px;
  }
  .common-selected-tags-below {
    gap: 8px 10px;
    min-height: 24px;
  }
  .common-selected-tag {
    padding: 5px 14px 5px 10px;
    font-size: 13px;
    border-radius: 12px;
    min-height: 22px;
  }
  .common-selected-tag .fas {
    font-size: 13px;
    margin-left: 6px;
  }
}
.common-dropdown-list {
  position: absolute;
  top: 54px;
  left: 0;
  width: 100%;
  min-width: 0;
  max-width: 100%;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(33, 150, 243, 0.08);
  border: 1px solid #eee;
  z-index: 10;
  max-height: 220px;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 8px 0 8px 0;
  box-sizing: border-box;
}
.common-dropdown-search {
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
.common-dropdown-item {
  width: 100%;
  min-width: 0;
  max-width: 100%;
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
.common-dropdown-item:hover {
  background: #f6f6f6;
}
.common-dropdown-checkbox {
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
.common-dropdown-checkbox.checked {
  background: #f6f6f6;
  border-color: #888;
}
.common-dropdown-checkbox.checked:after {
  content: '\2713';
  color: #888;
  font-size: 13px;
  font-weight: bold;
}
.common-dropdown-checkbox:after {
  content: '';
}
.common-custom-dropdown-input .common-selected-tags {
  margin-top: 0;
}
.common-custom-dropdown-input .common-selected-tag {
  background: #f6f6f6;
  color: #222;
  font-size: 15px;
  border-radius: 9px;
  padding: 2px 10px;
  margin-right: 4px;
}
.common-custom-dropdown-input .common-selected-tag .fas {
  color: #bbb;
  font-size: 13px;
  margin-left: 4px;
}
.common-custom-dropdown-input .common-selected-tag:last-child {
  margin-right: 0;
}
.common-custom-dropdown-input .fas.fa-chevron-down {
  position: static;
  margin-left: auto;
  color: #888;
}
</style>
