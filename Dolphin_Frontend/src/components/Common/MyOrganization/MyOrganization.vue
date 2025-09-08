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
        <GroupDetails
          :visible="showGroupModal"
          :groupId="selectedGroup ? selectedGroup.id : null"
          @close="closeGroupModal"
          @view-member="
            (m) => {
              this.$emit('view-member', m);
            }
          "
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
import GroupDetails from './GroupDetails.vue';
import axios from 'axios';
import storage from '@/services/storage';

export default {
  name: 'MyOrganization',
  components: {
    MainLayout,
    MemberTable,
    OrgActionButtons,
    Pagination,
    GroupDetails,
  },
  data() {
    return {
      groups: [],
      members: [],
      showGroupModal: false,
      selectedGroup: null,
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
    membersForSelectedGroup() {
      if (!this.selectedGroup) return [];
      // assume member object has group_id or groupId; try both
      const gid = this.selectedGroup.id;
      return this.members.filter(
        (m) => m.group_id === gid || m.groupId === gid || m.group === gid
      );
    },
  },
  methods: {
    async loadGroups() {
      const authToken = storage.get('authToken');
      const headers = {};
      if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
      try {
        const response = await axios.get(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/groups',
          { headers }
        );
        this.groups = response.data;
      } catch (e) {
        this.groups = [];
      }
    },
    async loadMembers() {
      const authToken = storage.get('authToken');
      const headers = {};
      if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
      try {
        const response = await axios.get(
          (process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000') +
            '/api/members',
          { headers }
        );
        this.members = response.data;
      } catch (e) {
        this.members = [];
      }
    },
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
      this.selectedGroup = group || null;
      this.showGroupModal = !!group;
    },
    closeGroupModal() {
      this.showGroupModal = false;
      this.selectedGroup = null;
    },
  },
  mounted() {
    this.loadGroups();
    this.loadMembers();
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
