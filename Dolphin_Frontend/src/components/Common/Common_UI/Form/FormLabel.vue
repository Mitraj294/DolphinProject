<template>
  <label class="form-label">
    <slot />
  </label>
</template>

<script>
export default {
  name: 'FormLabel',
};
</script>

<style scoped>
.form-label {
  color: #222;
  font-size: 15px;
  font-weight: 400;
  text-align: left;
  margin-bottom: 6px;
  display: block;
}
</style>
