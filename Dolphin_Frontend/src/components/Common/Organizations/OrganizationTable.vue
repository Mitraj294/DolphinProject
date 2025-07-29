<template>
  <div class="table-outer">
    <div class="table-card">
      <div class="table-search-bar">
        <input
          class="org-search"
          placeholder="Search Organization Name"
          v-model="search"
        />
      </div>
      <div class="table-container">
        <table class="table">
          <TableHeader
            :columns="[
              { label: 'Organizations Name', key: 'name', sortable: true },
              { label: 'Size', key: 'size' },
              { label: 'Admin Name', key: 'admin' },
              { label: 'Contract Start', key: 'contractStart', sortable: true },
              { label: 'Contract End', key: 'contractEnd', sortable: true },
              { label: 'Last Login', key: 'lastLogin' },
              { label: 'Action', key: 'action' },
            ]"
            @sort="sortBy"
          />
          <tbody>
            <tr
              v-for="org in paginatedOrganizations"
              :key="org.name"
            >
              <td>{{ org.name }}</td>
              <td>{{ org.size }}</td>
              <td>{{ org.admin }}</td>
              <td>{{ org.contractStart }}</td>
              <td>{{ org.contractEnd }}</td>
              <td>{{ org.lastLogin }}</td>
              <td>
                <button
                  class="btn-view"
                  @click="goToDetail(org)"
                >
                  <img
                    src="@/assets/images/Detail.svg"
                    alt="View"
                    class="btn-view-icon"
                  />
                  View Detail
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <Pagination
      :withPagination="true"
      :pageSize="pageSize"
      :pageSizes="[10, 25, 100]"
      :showPageDropdown="showPageDropdown"
      :currentPage="currentPage"
      :totalPages="totalPages"
      :paginationPages="paginationPages"
      @togglePageDropdown="showPageDropdown = !showPageDropdown"
      @selectPageSize="selectPageSize"
      @goToPage="goToPage"
    />
  </div>
</template>

<script>
import Pagination from '@/components/layout/Pagination.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
export default {
  name: 'OrganizationTable',
  components: { Pagination, TableHeader },
  data() {
    return {
      search: '',
      showPageDropdown: false,
      pageSize: 10,
      currentPage: 1,
      sortKey: '',
      sortAsc: true,
      organizations: [
        {
          name: 'Alpha Group',
          size: 'Large',
          admin: 'Alice Adams',
          contractStart: 'Feb 1, 2024',
          contractEnd: 'Feb 1, 2025',
          lastLogin: 'Feb 2, 2025 at 9:00 am',
        },
        {
          name: 'Beta Solutions',
          size: 'Small',
          admin: 'Bob Brown',
          contractStart: 'Mar 10, 2024',
          contractEnd: 'Mar 10, 2025',
          lastLogin: 'Mar 11, 2025 at 10:30 am',
        },
        {
          name: 'Crest Corp',
          size: 'Large',
          admin: 'Cathy Clark',
          contractStart: 'Apr 5, 2024',
          contractEnd: 'Apr 5, 2025',
          lastLogin: 'Apr 6, 2025 at 11:15 am',
        },
        {
          name: 'Delta Dynamics',
          size: 'Small',
          admin: 'David Duke',
          contractStart: 'May 12, 2024',
          contractEnd: 'May 12, 2025',
          lastLogin: 'May 13, 2025 at 8:45 am',
        },
        {
          name: 'Echo Enterprises',
          size: 'Large',
          admin: 'Eve Evans',
          contractStart: 'Jun 18, 2024',
          contractEnd: 'Jun 18, 2025',
          lastLogin: 'Jun 19, 2025 at 10:20 am',
        },
        {
          name: 'Flexi-Finders',
          size: 'Large',
          admin: 'Aaliyah Moss',
          contractStart: 'Jun 18, 2024',
          contractEnd: 'Jun-18-2025',
          lastLogin: 'Feb 18, 2025 at 10:20 am',
        },
        {
          name: 'Gamma Group',
          size: 'Small',
          admin: 'Gina Green',
          contractStart: 'Jul 7, 2024',
          contractEnd: 'Jul 7, 2025',
          lastLogin: 'Jul 8, 2025 at 2:00 pm',
        },
        {
          name: 'Helix Holdings',
          size: 'Large',
          admin: 'Hank Hill',
          contractStart: 'Aug 15, 2024',
          contractEnd: 'Aug 15, 2025',
          lastLogin: 'Aug 16, 2025 at 3:30 pm',
        },
        {
          name: 'Interim Solutions',
          size: 'Small',
          admin: 'Clarence Reed',
          contractStart: 'Jan 1, 2024',
          contractEnd: 'Dec 31, 2024',
          lastLogin: 'Jan 4, 2025 at 4:40 pm',
        },
        {
          name: 'Jupiter Jobs',
          size: 'Large',
          admin: 'Julia James',
          contractStart: 'Sep 20, 2024',
          contractEnd: 'Sep 20, 2025',
          lastLogin: 'Sep 21, 2025 at 5:10 pm',
        },
        {
          name: 'Kappa Konsulting',
          size: 'Small',
          admin: 'Karl King',
          contractStart: 'Oct 2, 2024',
          contractEnd: 'Oct 2, 2025',
          lastLogin: 'Oct 3, 2025 at 6:00 pm',
        },
        {
          name: 'Lambda Labs',
          size: 'Large',
          admin: 'Laura Lane',
          contractStart: 'Nov 11, 2024',
          contractEnd: 'Nov 11, 2025',
          lastLogin: 'Nov 12, 2025 at 7:20 pm',
        },
        {
          name: 'Matrix Media',
          size: 'Small',
          admin: 'Mona Moore',
          contractStart: 'Dec 25, 2024',
          contractEnd: 'Dec 25, 2025',
          lastLogin: 'Dec 26, 2025 at 8:00 am',
        },
        {
          name: 'Nova Networks',
          size: 'Large',
          admin: 'Nina North',
          contractStart: 'Jan 3, 2025',
          contractEnd: 'Jan 3, 2026',
          lastLogin: 'Jan 4, 2026 at 9:30 am',
        },
        {
          name: 'Omega Org',
          size: 'Small',
          admin: 'Oscar Owen',
          contractStart: 'Feb 14, 2025',
          contractEnd: 'Feb 14, 2026',
          lastLogin: 'Feb 15, 2026 at 10:10 am',
        },
        {
          name: 'Prime Partners',
          size: 'Large',
          admin: 'Paula Price',
          contractStart: 'Mar 8, 2025',
          contractEnd: 'Mar 8, 2026',
          lastLogin: 'Mar 9, 2026 at 11:50 am',
        },
        {
          name: 'QuickStaff',
          size: 'Large',
          admin: 'Mary Brucker',
          contractStart: 'Jan 1, 2025',
          contractEnd: 'Dec 31, 2025',
          lastLogin: 'Nov 4, 2024 at 12:10 pm',
        },
        {
          name: 'Rocket Resources',
          size: 'Small',
          admin: 'Rita Ray',
          contractStart: 'Apr 17, 2025',
          contractEnd: 'Apr 17, 2026',
          lastLogin: 'Apr 18, 2026 at 12:30 pm',
        },
        {
          name: 'Sigma Systems',
          size: 'Large',
          admin: 'Sam Smith',
          contractStart: 'May 22, 2025',
          contractEnd: 'May 22, 2026',
          lastLogin: 'May 23, 2026 at 1:40 pm',
        },
        {
          name: 'Titan Tech',
          size: 'Small',
          admin: 'Tina Turner',
          contractStart: 'Jun 30, 2025',
          contractEnd: 'Jun 30, 2026',
          lastLogin: 'Jul 1, 2026 at 2:20 pm',
        },
        {
          name: 'Umbrella United',
          size: 'Large',
          admin: 'Uma Underwood',
          contractStart: 'Jul 19, 2025',
          contractEnd: 'Jul 19, 2026',
          lastLogin: 'Jul 20, 2026 at 3:00 pm',
        },
        {
          name: 'Vega Ventures',
          size: 'Small',
          admin: 'Victor Voss',
          contractStart: 'Aug 8, 2025',
          contractEnd: 'Aug 8, 2026',
          lastLogin: 'Aug 9, 2026 at 4:10 pm',
        },
        {
          name: 'WaveWorks',
          size: 'Large',
          admin: 'Wendy White',
          contractStart: 'Sep 14, 2025',
          contractEnd: 'Sep 14, 2026',
          lastLogin: 'Sep 15, 2026 at 5:30 pm',
        },
        {
          name: 'Xeno Xperts',
          size: 'Small',
          admin: 'Xander Xu',
          contractStart: 'Oct 27, 2025',
          contractEnd: 'Oct 27, 2026',
          lastLogin: 'Oct 28, 2026 at 6:40 pm',
        },
        {
          name: 'Yield Yard',
          size: 'Large',
          admin: 'Yara Young',
          contractStart: 'Nov 5, 2025',
          contractEnd: 'Nov 5, 2026',
          lastLogin: 'Nov 6, 2026 at 7:50 pm',
        },
        {
          name: 'Zenith Zone',
          size: 'Small',
          admin: 'Zane Zeller',
          contractStart: 'Dec 12, 2025',
          contractEnd: 'Dec 12, 2026',
          lastLogin: 'Dec 13, 2026 at 8:00 am',
        },
        {
          name: 'Atlas Associates',
          size: 'Large',
          admin: 'Ava Allen',
          contractStart: 'Jan 15, 2024',
          contractEnd: 'Jan 15, 2025',
          lastLogin: 'Jan 16, 2025 at 9:10 am',
        },
        {
          name: 'Bright Bridge',
          size: 'Small',
          admin: 'Ben Brooks',
          contractStart: 'Feb 22, 2024',
          contractEnd: 'Feb 22, 2025',
          lastLogin: 'Feb 23, 2025 at 10:20 am',
        },
        {
          name: 'Clever Crew',
          size: 'Large',
          admin: 'Cara Carter',
          contractStart: 'Mar 29, 2024',
          contractEnd: 'Mar 29, 2025',
          lastLogin: 'Mar 30, 2025 at 11:30 am',
        },
        {
          name: 'Dynamic Devs',
          size: 'Small',
          admin: 'Derek Dean',
          contractStart: 'Apr 6, 2024',
          contractEnd: 'Apr 6, 2025',
          lastLogin: 'Apr 7, 2025 at 12:40 pm',
        },
        {
          name: 'Elite Experts',
          size: 'Large',
          admin: 'Ella East',
          contractStart: 'May 13, 2024',
          contractEnd: 'May 13, 2025',
          lastLogin: 'May 14, 2025 at 1:50 pm',
        },
        {
          name: 'Alpha Group',
          size: 'Large',
          admin: 'Alice Adams',
          contractStart: 'Feb 1, 2024',
          contractEnd: 'Feb 1, 2025',
          lastLogin: 'Feb 2, 2025 at 9:00 am',
        },
        {
          name: 'Beta Solutions',
          size: 'Small',
          admin: 'Bob Brown',
          contractStart: 'Mar 10, 2024',
          contractEnd: 'Mar 10, 2025',
          lastLogin: 'Mar 11, 2025 at 10:30 am',
        },
        {
          name: 'Crest Corp',
          size: 'Large',
          admin: 'Cathy Clark',
          contractStart: 'Apr 5, 2024',
          contractEnd: 'Apr 5, 2025',
          lastLogin: 'Apr 6, 2025 at 11:15 am',
        },
        {
          name: 'Delta Dynamics',
          size: 'Small',
          admin: 'David Duke',
          contractStart: 'May 12, 2024',
          contractEnd: 'May 12, 2025',
          lastLogin: 'May 13, 2025 at 8:45 am',
        },
        {
          name: 'Echo Enterprises',
          size: 'Large',
          admin: 'Eve Evans',
          contractStart: 'Jun 18, 2024',
          contractEnd: 'Jun 18, 2025',
          lastLogin: 'Jun 19, 2025 at 10:20 am',
        },
        {
          name: 'Flexi-Finders',
          size: 'Large',
          admin: 'Aaliyah Moss',
          contractStart: 'Jun 18, 2024',
          contractEnd: 'Jun-18-2025',
          lastLogin: 'Feb 18, 2025 at 10:20 am',
        },
        {
          name: 'Gamma Group',
          size: 'Small',
          admin: 'Gina Green',
          contractStart: 'Jul 7, 2024',
          contractEnd: 'Jul 7, 2025',
          lastLogin: 'Jul 8, 2025 at 2:00 pm',
        },
        {
          name: 'Helix Holdings',
          size: 'Large',
          admin: 'Hank Hill',
          contractStart: 'Aug 15, 2024',
          contractEnd: 'Aug 15, 2025',
          lastLogin: 'Aug 16, 2025 at 3:30 pm',
        },
        {
          name: 'Interim Solutions',
          size: 'Small',
          admin: 'Clarence Reed',
          contractStart: 'Jan 1, 2024',
          contractEnd: 'Dec 31, 2024',
          lastLogin: 'Jan 4, 2025 at 4:40 pm',
        },
        {
          name: 'Jupiter Jobs',
          size: 'Large',
          admin: 'Julia James',
          contractStart: 'Sep 20, 2024',
          contractEnd: 'Sep 20, 2025',
          lastLogin: 'Sep 21, 2025 at 5:10 pm',
        },
        {
          name: 'Kappa Konsulting',
          size: 'Small',
          admin: 'Karl King',
          contractStart: 'Oct 2, 2024',
          contractEnd: 'Oct 2, 2025',
          lastLogin: 'Oct 3, 2025 at 6:00 pm',
        },
        {
          name: 'Lambda Labs',
          size: 'Large',
          admin: 'Laura Lane',
          contractStart: 'Nov 11, 2024',
          contractEnd: 'Nov 11, 2025',
          lastLogin: 'Nov 12, 2025 at 7:20 pm',
        },
        {
          name: 'Matrix Media',
          size: 'Small',
          admin: 'Mona Moore',
          contractStart: 'Dec 25, 2024',
          contractEnd: 'Dec 25, 2025',
          lastLogin: 'Dec 26, 2025 at 8:00 am',
        },
        {
          name: 'Nova Networks',
          size: 'Large',
          admin: 'Nina North',
          contractStart: 'Jan 3, 2025',
          contractEnd: 'Jan 3, 2026',
          lastLogin: 'Jan 4, 2026 at 9:30 am',
        },
        {
          name: 'Omega Org',
          size: 'Small',
          admin: 'Oscar Owen',
          contractStart: 'Feb 14, 2025',
          contractEnd: 'Feb 14, 2026',
          lastLogin: 'Feb 15, 2026 at 10:10 am',
        },
        {
          name: 'Prime Partners',
          size: 'Large',
          admin: 'Paula Price',
          contractStart: 'Mar 8, 2025',
          contractEnd: 'Mar 8, 2026',
          lastLogin: 'Mar 9, 2026 at 11:50 am',
        },
        {
          name: 'QuickStaff',
          size: 'Large',
          admin: 'Mary Brucker',
          contractStart: 'Jan 1, 2025',
          contractEnd: 'Dec 31, 2025',
          lastLogin: 'Nov 4, 2024 at 12:10 pm',
        },
        {
          name: 'Rocket Resources',
          size: 'Small',
          admin: 'Rita Ray',
          contractStart: 'Apr 17, 2025',
          contractEnd: 'Apr 17, 2026',
          lastLogin: 'Apr 18, 2026 at 12:30 pm',
        },
        {
          name: 'Sigma Systems',
          size: 'Large',
          admin: 'Sam Smith',
          contractStart: 'May 22, 2025',
          contractEnd: 'May 22, 2026',
          lastLogin: 'May 23, 2026 at 1:40 pm',
        },
        {
          name: 'Titan Tech',
          size: 'Small',
          admin: 'Tina Turner',
          contractStart: 'Jun 30, 2025',
          contractEnd: 'Jun 30, 2026',
          lastLogin: 'Jul 1, 2026 at 2:20 pm',
        },
        {
          name: 'Umbrella United',
          size: 'Large',
          admin: 'Uma Underwood',
          contractStart: 'Jul 19, 2025',
          contractEnd: 'Jul 19, 2026',
          lastLogin: 'Jul 20, 2026 at 3:00 pm',
        },
        {
          name: 'Vega Ventures',
          size: 'Small',
          admin: 'Victor Voss',
          contractStart: 'Aug 8, 2025',
          contractEnd: 'Aug 8, 2026',
          lastLogin: 'Aug 9, 2026 at 4:10 pm',
        },
        {
          name: 'WaveWorks',
          size: 'Large',
          admin: 'Wendy White',
          contractStart: 'Sep 14, 2025',
          contractEnd: 'Sep 14, 2026',
          lastLogin: 'Sep 15, 2026 at 5:30 pm',
        },
        {
          name: 'Xeno Xperts',
          size: 'Small',
          admin: 'Xander Xu',
          contractStart: 'Oct 27, 2025',
          contractEnd: 'Oct 27, 2026',
          lastLogin: 'Oct 28, 2026 at 6:40 pm',
        },
        {
          name: 'Yield Yard',
          size: 'Large',
          admin: 'Yara Young',
          contractStart: 'Nov 5, 2025',
          contractEnd: 'Nov 5, 2026',
          lastLogin: 'Nov 6, 2026 at 7:50 pm',
        },
        {
          name: 'Zenith Zone',
          size: 'Small',
          admin: 'Zane Zeller',
          contractStart: 'Dec 12, 2025',
          contractEnd: 'Dec 12, 2026',
          lastLogin: 'Dec 13, 2026 at 8:00 am',
        },
        {
          name: 'Atlas Associates',
          size: 'Large',
          admin: 'Ava Allen',
          contractStart: 'Jan 15, 2024',
          contractEnd: 'Jan 15, 2025',
          lastLogin: 'Jan 16, 2025 at 9:10 am',
        },
        {
          name: 'Bright Bridge',
          size: 'Small',
          admin: 'Ben Brooks',
          contractStart: 'Feb 22, 2024',
          contractEnd: 'Feb 22, 2025',
          lastLogin: 'Feb 23, 2025 at 10:20 am',
        },
        {
          name: 'Clever Crew',
          size: 'Large',
          admin: 'Cara Carter',
          contractStart: 'Mar 29, 2024',
          contractEnd: 'Mar 29, 2025',
          lastLogin: 'Mar 30, 2025 at 11:30 am',
        },
        {
          name: 'Dynamic Devs',
          size: 'Small',
          admin: 'Derek Dean',
          contractStart: 'Apr 6, 2024',
          contractEnd: 'Apr 6, 2025',
          lastLogin: 'Apr 7, 2025 at 12:40 pm',
        },
        {
          name: 'Elite Experts',
          size: 'Large',
          admin: 'Ella East',
          contractStart: 'May 13, 2024',
          contractEnd: 'May 13, 2025',
          lastLogin: 'May 14, 2025 at 1:50 pm',
        },
        {
          name: 'Alpha Group',
          size: 'Large',
          admin: 'Alice Adams',
          contractStart: 'Feb 1, 2024',
          contractEnd: 'Feb 1, 2025',
          lastLogin: 'Feb 2, 2025 at 9:00 am',
        },
        {
          name: 'Beta Solutions',
          size: 'Small',
          admin: 'Bob Brown',
          contractStart: 'Mar 10, 2024',
          contractEnd: 'Mar 10, 2025',
          lastLogin: 'Mar 11, 2025 at 10:30 am',
        },
        {
          name: 'Crest Corp',
          size: 'Large',
          admin: 'Cathy Clark',
          contractStart: 'Apr 5, 2024',
          contractEnd: 'Apr 5, 2025',
          lastLogin: 'Apr 6, 2025 at 11:15 am',
        },
        {
          name: 'Delta Dynamics',
          size: 'Small',
          admin: 'David Duke',
          contractStart: 'May 12, 2024',
          contractEnd: 'May 12, 2025',
          lastLogin: 'May 13, 2025 at 8:45 am',
        },
        {
          name: 'Echo Enterprises',
          size: 'Large',
          admin: 'Eve Evans',
          contractStart: 'Jun 18, 2024',
          contractEnd: 'Jun 18, 2025',
          lastLogin: 'Jun 19, 2025 at 10:20 am',
        },
        {
          name: 'Flexi-Finders',
          size: 'Large',
          admin: 'Aaliyah Moss',
          contractStart: 'Jun 18, 2024',
          contractEnd: 'Jun-18-2025',
          lastLogin: 'Feb 18, 2025 at 10:20 am',
        },
        {
          name: 'Gamma Group',
          size: 'Small',
          admin: 'Gina Green',
          contractStart: 'Jul 7, 2024',
          contractEnd: 'Jul 7, 2025',
          lastLogin: 'Jul 8, 2025 at 2:00 pm',
        },
        {
          name: 'Helix Holdings',
          size: 'Large',
          admin: 'Hank Hill',
          contractStart: 'Aug 15, 2024',
          contractEnd: 'Aug 15, 2025',
          lastLogin: 'Aug 16, 2025 at 3:30 pm',
        },
        {
          name: 'Interim Solutions',
          size: 'Small',
          admin: 'Clarence Reed',
          contractStart: 'Jan 1, 2024',
          contractEnd: 'Dec 31, 2024',
          lastLogin: 'Jan 4, 2025 at 4:40 pm',
        },
        {
          name: 'Jupiter Jobs',
          size: 'Large',
          admin: 'Julia James',
          contractStart: 'Sep 20, 2024',
          contractEnd: 'Sep 20, 2025',
          lastLogin: 'Sep 21, 2025 at 5:10 pm',
        },
        {
          name: 'Kappa Konsulting',
          size: 'Small',
          admin: 'Karl King',
          contractStart: 'Oct 2, 2024',
          contractEnd: 'Oct 2, 2025',
          lastLogin: 'Oct 3, 2025 at 6:00 pm',
        },
        {
          name: 'Lambda Labs',
          size: 'Large',
          admin: 'Laura Lane',
          contractStart: 'Nov 11, 2024',
          contractEnd: 'Nov 11, 2025',
          lastLogin: 'Nov 12, 2025 at 7:20 pm',
        },
        {
          name: 'Matrix Media',
          size: 'Small',
          admin: 'Mona Moore',
          contractStart: 'Dec 25, 2024',
          contractEnd: 'Dec 25, 2025',
          lastLogin: 'Dec 26, 2025 at 8:00 am',
        },
        {
          name: 'Nova Networks',
          size: 'Large',
          admin: 'Nina North',
          contractStart: 'Jan 3, 2025',
          contractEnd: 'Jan 3, 2026',
          lastLogin: 'Jan 4, 2026 at 9:30 am',
        },
        {
          name: 'Omega Org',
          size: 'Small',
          admin: 'Oscar Owen',
          contractStart: 'Feb 14, 2025',
          contractEnd: 'Feb 14, 2026',
          lastLogin: 'Feb 15, 2026 at 10:10 am',
        },
        {
          name: 'Prime Partners',
          size: 'Large',
          admin: 'Paula Price',
          contractStart: 'Mar 8, 2025',
          contractEnd: 'Mar 8, 2026',
          lastLogin: 'Mar 9, 2026 at 11:50 am',
        },
        {
          name: 'QuickStaff',
          size: 'Large',
          admin: 'Mary Brucker',
          contractStart: 'Jan 1, 2025',
          contractEnd: 'Dec 31, 2025',
          lastLogin: 'Nov 4, 2024 at 12:10 pm',
        },
        {
          name: 'Rocket Resources',
          size: 'Small',
          admin: 'Rita Ray',
          contractStart: 'Apr 17, 2025',
          contractEnd: 'Apr 17, 2026',
          lastLogin: 'Apr 18, 2026 at 12:30 pm',
        },
        {
          name: 'Sigma Systems',
          size: 'Large',
          admin: 'Sam Smith',
          contractStart: 'May 22, 2025',
          contractEnd: 'May 22, 2026',
          lastLogin: 'May 23, 2026 at 1:40 pm',
        },
        {
          name: 'Titan Tech',
          size: 'Small',
          admin: 'Tina Turner',
          contractStart: 'Jun 30, 2025',
          contractEnd: 'Jun 30, 2026',
          lastLogin: 'Jul 1, 2026 at 2:20 pm',
        },
        {
          name: 'Umbrella United',
          size: 'Large',
          admin: 'Uma Underwood',
          contractStart: 'Jul 19, 2025',
          contractEnd: 'Jul 19, 2026',
          lastLogin: 'Jul 20, 2026 at 3:00 pm',
        },
        {
          name: 'Vega Ventures',
          size: 'Small',
          admin: 'Victor Voss',
          contractStart: 'Aug 8, 2025',
          contractEnd: 'Aug 8, 2026',
          lastLogin: 'Aug 9, 2026 at 4:10 pm',
        },
        {
          name: 'WaveWorks',
          size: 'Large',
          admin: 'Wendy White',
          contractStart: 'Sep 14, 2025',
          contractEnd: 'Sep 14, 2026',
          lastLogin: 'Sep 15, 2026 at 5:30 pm',
        },
        {
          name: 'Xeno Xperts',
          size: 'Small',
          admin: 'Xander Xu',
          contractStart: 'Oct 27, 2025',
          contractEnd: 'Oct 27, 2026',
          lastLogin: 'Oct 28, 2026 at 6:40 pm',
        },
        {
          name: 'Yield Yard',
          size: 'Large',
          admin: 'Yara Young',
          contractStart: 'Nov 5, 2025',
          contractEnd: 'Nov 5, 2026',
          lastLogin: 'Nov 6, 2026 at 7:50 pm',
        },
        {
          name: 'Zenith Zone',
          size: 'Small',
          admin: 'Zane Zeller',
          contractStart: 'Dec 12, 2025',
          contractEnd: 'Dec 12, 2026',
          lastLogin: 'Dec 13, 2026 at 8:00 am',
        },
        {
          name: 'Atlas Associates',
          size: 'Large',
          admin: 'Ava Allen',
          contractStart: 'Jan 15, 2024',
          contractEnd: 'Jan 15, 2025',
          lastLogin: 'Jan 16, 2025 at 9:10 am',
        },
        {
          name: 'Bright Bridge',
          size: 'Small',
          admin: 'Ben Brooks',
          contractStart: 'Feb 22, 2024',
          contractEnd: 'Feb 22, 2025',
          lastLogin: 'Feb 23, 2025 at 10:20 am',
        },
        {
          name: 'Clever Crew',
          size: 'Large',
          admin: 'Cara Carter',
          contractStart: 'Mar 29, 2024',
          contractEnd: 'Mar 29, 2025',
          lastLogin: 'Mar 30, 2025 at 11:30 am',
        },
        {
          name: 'Dynamic Devs',
          size: 'Small',
          admin: 'Derek Dean',
          contractStart: 'Apr 6, 2024',
          contractEnd: 'Apr 6, 2025',
          lastLogin: 'Apr 7, 2025 at 12:40 pm',
        },
        {
          name: 'Elite Experts',
          size: 'Large',
          admin: 'Ella East',
          contractStart: 'May 13, 2024',
          contractEnd: 'May 13, 2025',
          lastLogin: 'May 14, 2025 at 1:50 pm',
        },
      ],
    };
  },
  computed: {
    filteredOrganizations() {
      let orgs = this.organizations;
      if (this.search) {
        orgs = orgs.filter((org) =>
          org.name.toLowerCase().includes(this.search.toLowerCase())
        );
      }
      if (this.sortKey) {
        orgs = orgs.slice().sort((a, b) => {
          let aVal = a[this.sortKey];
          let bVal = b[this.sortKey];
          // For contractStart and contractEnd, sort as dates
          if (
            this.sortKey === 'contractStart' ||
            this.sortKey === 'contractEnd'
          ) {
            // Try to parse as Date, fallback to string compare
            const aDate = new Date(aVal);
            const bDate = new Date(bVal);
            if (!isNaN(aDate) && !isNaN(bDate)) {
              return this.sortAsc ? aDate - bDate : bDate - aDate;
            }
          }
          // Default: string compare
          if (aVal < bVal) return this.sortAsc ? -1 : 1;
          if (aVal > bVal) return this.sortAsc ? 1 : -1;
          return 0;
        });
      }
      return orgs;
    },
    totalPages() {
      return Math.ceil(this.filteredOrganizations.length / this.pageSize) || 1;
    },
    paginatedOrganizations() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredOrganizations.slice(start, start + this.pageSize);
    },
    paginationPages() {
      const pages = [];
      if (10 <= 7) {
        for (let i = 1; i <= 10; i++) pages.push(i);
      } else {
        pages.push(1, 2, 3, '...', 8, 9, 10);
      }
      return pages;
    },
  },
  methods: {
    goToDetail(org) {
      this.$router.push({
        name: 'OrganizationDetail',
        params: { orgName: org.name },
      });
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
    sortBy(key) {
      if (this.sortKey === key) {
        this.sortAsc = !this.sortAsc;
      } else {
        this.sortKey = key;
        this.sortAsc = true;
      }
    },
  },
};
</script>

<style scoped>
/* --- Layout and spacing to match reference page --- */

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

/* --- Pagination and footer spacing --- */
.org-footer-row {
  width: 100%;
  max-width: 1200px;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  margin-top: 18px;
  margin-left: auto;
  margin-right: auto;
  padding-left: 0;
  padding-right: 0;
  box-sizing: border-box;
}

/* Responsive: shrink margin and font on small screens */
@media (max-width: 1400px) {
  .org-search {
    font-size: 13px;
    padding: 8px 16px 8px 32px;
    max-width: 320px;
    border-radius: 12px;
  }
}

@media (max-width: 1200px) {
}

@media (max-width: 900px) {
}

@media (max-width: 600px) {
}

/* --- Sort button and icon --- */
.org-th-sort-btn {
  background: none;
  border: none;

  display: inline-flex;
  align-items: center;
  vertical-align: middle;
  box-shadow: none;
  outline: none;
  cursor: pointer;
  height: 1em;
  line-height: 1;
}
.org-th-sort {
  width: 1em;
  height: 1em;
  min-width: 16px;
  min-height: 16px;
  max-width: 18px;
  max-height: 18px;
  margin-left: 2px;
  margin-right: 0;
  vertical-align: middle;
  display: inline-block;
  border: none;
  background: none;
  box-shadow: none;
  filter: none;
  opacity: 0.7;
  transition: opacity 0.15s;
}
.org-th-sort-btn:hover .org-th-sort,
.org-th-sort-btn:focus .org-th-sort {
  opacity: 1;
}
</style>
