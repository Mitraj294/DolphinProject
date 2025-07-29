<template>
  <div
    class="form-box"
    :class="{ 'form-box--error': error }"
  >
    <slot />
  </div>
</template>

<script>
export default {
  name: 'FormBox',
  props: {
    error: { type: Boolean, default: false },
  },
};
</script>

<style scoped>
.form-box {
  background: #f6f6f6;
  border-radius: 10px;
  border: 1.5px solid #e0e0e0;
  padding: 0 16px;
  min-height: 48px;
  display: flex;
  align-items: center;
  margin-bottom: 0;
  box-sizing: border-box;
  transition: border 0.18s;
}
.form-box--error {
  border-color: #e74c3c;
}
</style>
