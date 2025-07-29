<template>
  <MainLayout>
    <div class="page">
      <div class="assessments-table-outer">
        <OrganizationAdminAssessmentsCard v-if="isOrganizationAdmin" />
        <UserAssessment v-else-if="isUser" />
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import OrganizationAdminAssessmentsCard from './OrganizationAdminAssessmentsCard.vue';
import UserAssessment from './UserAssessment.vue';

export default {
  name: 'Assessments',
  components: { MainLayout, OrganizationAdminAssessmentsCard, UserAssessment },
  computed: {
    isOrganizationAdmin() {
      const storage = require('@/services/storage').default;
      const role = storage.get('role') || 'user';
      return role === 'organizationadmin';
    },
    isUser() {
      const storage = require('@/services/storage').default;
      const role = storage.get('role') || 'user';
      return role === 'user';
    },
  },
};
</script>

<style scoped>
.assessments-table-outer {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
  margin: 0 auto 64px auto; /* Only bottom margin */
  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  background: none !important;
  padding: 0 !important;
}

/* Responsive: match other pages */
@media (max-width: 1400px) {
  .assessments-table-outer {
    margin: 0 12px 12px 12px;
    max-width: 100%;
  }
}
@media (max-width: 900px) {
  .assessments-table-outer {
    margin: 0 4px 4px 4px;
    max-width: 100%;
  }
}
</style>
