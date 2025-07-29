<template>
  <MainLayout>
    <div class="page">
      <OrganizationTable />
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import OrganizationTable from './OrganizationTable.vue';
export default {
  name: 'Organizations',
  components: { MainLayout, OrganizationTable },
};
</script>

<style></style>
