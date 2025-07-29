<template>
  <MainLayout>
    <Toast />
    <div class="page">
      <OrgAdminGraphs v-if="isOrgAdmin" />
      <UserGraphs v-else-if="isUser" />
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '../../layout/MainLayout.vue';
import OrgAdminGraphs from './OrgAdminGraphs.vue';
import UserGraphs from './UserGraphs.vue';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

export default {
  components: {
    MainLayout,
    OrgAdminGraphs,
    UserGraphs,
    Toast,
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  computed: {
    isOrgAdmin() {
      const role = localStorage.getItem('role');
      return (
        role === 'organizationadmin' ||
        role === 'superadmin' ||
        role === 'Dolphinadmin'
      );
    },
    isUser() {
      const role = localStorage.getItem('role');
      return role === 'user' || role === 'salesperson';
    },
  },
};
</script>
<style scoped></style>
