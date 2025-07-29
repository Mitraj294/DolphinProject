<template>
  <MainLayout>
    <div class="page">
      <div class="page-content-wrapper">
        <div class="training-resources-page">
          <h2>Training & Resources</h2>
          <p>Welcome to the Training & Resources page.</p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '../layout/MainLayout.vue';
export default {
  name: 'TrainingResources',
  components: { MainLayout },
};
</script>

<style scoped>
.page-content-wrapper {
  min-height: 0;
  padding-bottom: 0;
  margin-bottom: 0;
  background: none;
}

.training-resources-page {
  padding: 32px;
}
</style>
