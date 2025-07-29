<template>
  <MainLayout>
    <div class="page">
      <div class="table-outer">
        <div class="table-card">
          <div class="table-search-bar">
            <input
              class="org-search"
              placeholder="Search Member..."
              v-model="searchQuery"
              @input="onSearch"
            />
          </div>
          <div class="table-container">
            <table class="table">
              <TableHeader
                :columns="[
                  { label: 'Name', key: 'name' },
                  { label: 'Email', key: 'email' },
                  { label: 'Phone Number', key: 'phone' },
                  { label: 'Role', key: 'role' },
                  { label: 'Actions', key: 'actions' },
                ]"
                @sort="sortBy"
              />
              <tbody>
                <tr
                  v-for="(member, idx) in paginatedMembers"
                  :key="member.id"
                >
                  <td>{{ member.name }}</td>
                  <td>{{ member.email }}</td>
                  <td>{{ member.phone }}</td>
                  <td>{{ member.role }}</td>
                  <td>
                    <button
                      class="btn-view"
                      @click="viewMember(member)"
                    >
                      <img
                        src="@/assets/images/Notes.svg"
                        alt="View"
                        class="btn-view-icon"
                      />
                      View
                    </button>
                  </td>
                </tr>
                <tr v-if="paginatedMembers.length === 0">
                  <td
                    colspan="5"
                    class="no-data"
                  >
                    No members found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <Pagination
          :pageSize="pageSize"
          :pageSizes="[10, 25, 100]"
          :showPageDropdown="showPageDropdown"
          :currentPage="currentPage"
          :totalPages="totalPages"
          :paginationPages="paginationPages"
          @goToPage="goToPage"
          @selectPageSize="selectPageSize"
          @togglePageDropdown="showPageDropdown = !showPageDropdown"
        />
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import Pagination from '@/components/layout/Pagination.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
export default {
  name: 'MemberListing',
  components: { MainLayout, Pagination, TableHeader },
  data() {
    return {
      currentPage: 1,
      pageSize: 10,
      searchQuery: '',
      showPageDropdown: false,
      sortKey: '',
      sortAsc: true,
      members: [
        {
          id: 1,
          name: 'Emily Carter',
          email: 'emily@dolphin.org',
          phone: '+91 98765 43210',
          role: 'Manager',
          status: 'Active',
        },
        {
          id: 2,
          name: 'James Parker',
          email: 'james@dolphin.org',
          phone: '+91 91234 56789',
          role: 'CEO',
          status: 'Active',
        },
        {
          id: 3,
          name: 'Sophia Mitchell',
          email: 'sophia@dolphin.org',
          phone: '+91 99887 76655',
          role: 'Owner',
          status: 'Active',
        },
        {
          id: 4,
          name: 'Mason Walker',
          email: 'mason@dolphin.org',
          phone: '+91 90011 22334',
          role: 'Support',
          status: 'Active',
        },
        {
          id: 5,
          name: 'Olivia Bennett',
          email: 'olivia@dolphin.org',
          phone: '+91 88990 11223',
          role: 'Manager',
          status: 'Active',
        },
        {
          id: 6,
          name: 'Benjamin Hayes',
          email: 'benjamin@dolphin.org',
          phone: '+91 77665 54433',
          role: 'CEO',
          status: 'Active',
        },
        {
          id: 7,
          name: 'Ava Richardson',
          email: 'ava@dolphin.org',
          phone: '+91 95555 12345',
          role: 'Owner',
          status: 'Active',
        },
        {
          id: 8,
          name: 'Henry Cooper',
          email: 'henry@dolphin.org',
          phone: '+91 96666 77788',
          role: 'Support',
          status: 'Active',
        },
        {
          id: 9,
          name: 'Isabella Thompson',
          email: 'isabella@dolphin.org',
          phone: '+91 98888 99900',
          role: 'Support',
          status: 'Active',
        },
        {
          id: 10,
          name: 'Daniel Morgan',
          email: 'daniel@dolphin.org',
          phone: '+91 91122 33445',
          role: 'CEO',
          status: 'Active',
        },
      ],
      filteredMembers: [],
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredMembers.length / this.pageSize) || 1;
    },
    paginatedMembers() {
      let sorted = [...this.filteredMembers];
      if (this.sortKey) {
        sorted.sort((a, b) => {
          let aVal = a[this.sortKey];
          let bVal = b[this.sortKey];
          if (aVal < bVal) return this.sortAsc ? -1 : 1;
          if (aVal > bVal) return this.sortAsc ? 1 : -1;
          return 0;
        });
      }
      const start = (this.currentPage - 1) * this.pageSize;
      return sorted.slice(start, start + this.pageSize);
    },
    paginationPages() {
      const total = this.totalPages;
      if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
      } else {
        const pages = [1];
        if (this.currentPage > 4) pages.push('...');
        for (
          let i = Math.max(2, this.currentPage - 1);
          i <= Math.min(total - 1, this.currentPage + 1);
          i++
        ) {
          pages.push(i);
        }
        if (this.currentPage < total - 3) pages.push('...');
        pages.push(total);
        return pages;
      }
    },
  },
  methods: {
    onSearch() {
      const q = this.searchQuery.trim().toLowerCase();
      if (!q) {
        this.filteredMembers = this.members;
      } else {
        this.filteredMembers = this.members.filter(
          (m) =>
            m.name.toLowerCase().includes(q) ||
            m.email.toLowerCase().includes(q) ||
            m.phone.replace(/\s+/g, '').includes(q.replace(/\s+/g, '')) ||
            m.role.toLowerCase().includes(q)
        );
      }
      this.currentPage = 1;
    },
    goToPage(page) {
      if (page === '...' || page < 1 || page > this.totalPages) return;
      this.currentPage = page;
    },
    selectPageSize(size) {
      this.pageSize = size;
      this.currentPage = 1;
      this.showPageDropdown = false;
    },
    viewMember(member) {
      this.$emit('view-member', member);
    },
    sortBy(key) {
      if (this.sortKey === key) {
        this.sortAsc = !this.sortAsc;
      } else {
        this.sortKey = key;
        this.sortAsc = true;
      }
    },
  },
  mounted() {
    this.filteredMembers = this.members;
  },
};
</script>

<style scoped>
.org-search {
  width: 260px;
  padding: 8px 24px 8px 32px;
  border-radius: 12px;
  border: none;
  background: #f8f8f8;
  font-size: 14px;
  outline: none;
  background-image: url('@/assets/images/Search.svg');
  background-repeat: no-repeat;
  background-position: 8px center;
  background-size: 16px 16px;
  margin-left: 0;
  margin-right: auto;
}
.org-search::placeholder {
  margin-left: 4px;
}
@media (max-width: 1400px) {
  .org-search {
    font-size: 13px;
    padding: 8px 16px 8px 32px;
    max-width: 320px;
    border-radius: 12px;
  }
}
@media (max-width: 900px) {
  .org-search {
    font-size: 11px;
    padding: 6px 10px 6px 28px;
    max-width: 180px;
    border-radius: 8px;
  }
}
</style>
