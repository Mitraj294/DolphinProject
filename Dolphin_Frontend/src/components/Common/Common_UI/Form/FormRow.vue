<template>
  <div class="form-row">
    <slot />
  </div>
</template>

<script>
export default {
  name: 'FormRow',
};
</script>

<style scoped>
.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
  width: 100%;
  margin-bottom: 18px;
}
</style>
