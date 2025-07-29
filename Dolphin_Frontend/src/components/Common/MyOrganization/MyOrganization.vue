<template>
  <MainLayout>
    <div class="page">
      <div class="table-outer">
        <div class="table-card">
          <OrgActionButtons
            @show-add-group="showAddGroupModal = true"
            @show-add-member="showAddMemberModal = true"
          />
          <MemberTable
            :groups="paginatedGroups"
            @view-group="viewGroup"
          />
        </div>
        <Pagination
          :pageSize="pageSize"
          :pageSizes="pageSizes"
          :showPageDropdown="showPageDropdown"
          :currentPage="currentPage"
          :totalPages="totalPages"
          @togglePageDropdown="togglePageDropdown"
          @selectPageSize="selectPageSize"
          @goToPage="goToPage"
        />
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '../../layout/MainLayout.vue';
import MemberTable from './MemberTable.vue';
import OrgActionButtons from './OrgActionButtons.vue';
import Pagination from '../../layout/Pagination.vue';

export default {
  name: 'MyOrganization',
  components: { MainLayout, MemberTable, OrgActionButtons, Pagination },
  data() {
    return {
      groups: [],
      pageSize: 10,
      pageSizes: [10, 25, 100],
      currentPage: 1,
      showPageDropdown: false,
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.groups.length / this.pageSize) || 1;
    },
    paginatedGroups() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.groups.slice(start, start + this.pageSize);
    },
  },
  methods: {
    togglePageDropdown() {
      this.showPageDropdown = !this.showPageDropdown;
    },
    selectPageSize(size) {
      this.pageSize = size;
      this.currentPage = 1;
      this.showPageDropdown = false;
    },
    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    viewGroup(group) {
      alert(`Viewing group: ${group.name}`);
    },
  },
};
</script>

<style scoped>
.icon-btn.view-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  font-size: 15px;
  color: #222;
}
.icon-btn.view-btn:hover .view-label {
  text-decoration: underline;
}
.view-icon {
  width: 18px;
  height: 18px;
  display: inline-block;
  vertical-align: middle;
}
.view-label {
  color: #222;
  text-decoration: underline;
  font-weight: 500;
  font-size: 15px;
  cursor: pointer;
}
</style>
