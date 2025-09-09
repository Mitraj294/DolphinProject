<template>
  <div
    v-if="visible"
    class="modal-overlay"
    @click.self="$emit('close')"
  >
    <div
      class="modal-card"
      style="max-width: 900px; width: 90%"
    >
      <button
        class="modal-close-btn"
        @click="$emit('close')"
      >
        &times;
      </button>
      <div class="modal-title">Group Details</div>
      <div class="modal-desc">Details for the selected group.</div>

 

    <div class="group-details">
      <div class="group-header-row">
        <div
          class="group-info"
          style="
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
          "
        >
          <div class="group-value">
            {{ group ? group.name : '—' }}
          </div>
          <div
            class="meta-item"
            style="text-align: right"
          >
            <div class="meta-label">Created</div>
            <div class="meta-value">
              {{ group ? formatDate(group.created_at) : '—' }}
            </div>
          </div>
        </div>
        <div class="group-meta">
          <div class="meta-item">
            <div class="meta-label">Members</div>
            <div class="meta-value">{{ members.length }}</div>
          </div>
        </div>
      </div>
</br>
      <div class="group-section">
        <h4 class="section-title">Members</h4>
        <div
          v-if="members.length === 0"
          class="no-data"
        >
          No members found for this group.
        </div>

        <div
          v-else
          class="detail-row"
        >
          <div class="detail-table"
          style="
            width: 100% !important;
            max-width: 800px !important;
            margin: 0 !important;
          ">
            <div class="recipient-table-wrap">
              <table class="recipient-table compact">
                <thead>
                  <tr>
                    <th style="width: 120px">Name</th>
                    <th style="width: 120px">Email</th>
                    <th style="width: 90px">Role</th>
                    <th style="width: 90px">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(m, idx) in members"
                    :key="m.id"
                  >
                    <td>
                      {{
                        m.first_name || m.last_name
                          ? (
                              (m.first_name || '') +
                              ' ' +
                              (m.last_name || '')
                            ).trim()
                          : m.email || 'Unknown'
                      }}
                    </td>
                    <td>{{ m.email || '' }}</td>
                    <td>
                      {{
                        Array.isArray(m.memberRoles) && m.memberRoles.length
                          ? m.memberRoles.map((r) => r.name || r).join(', ')
                          : m.member_role || ''
                      }}
                    </td>
                    <td>
                      <button
                        class="btn-view"
                        @click="
                          $router.push({
                            path: '/my-organization/members',
                            query: { member_id: m.id },
                          })
                        "
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
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
</template>

<script>
import axios from 'axios';
import storage from '@/services/storage';

export default {
  name: 'GroupDetails',
  components: {},
  props: {
    visible: { type: Boolean, required: true },
    groupId: { type: [Number, String], required: false, default: null },
  },
  data() {
    return {
      group: null,
      members: [],
    };
  },
  watch: {
    visible(v) {
      if (v) this.fetchGroup();
    },
    groupId() {
      if (this.visible) this.fetchGroup();
    },
  },
  methods: {
    async fetchGroup() {
      // don't attempt fetch if no groupId provided
      if (!this.groupId) {
        this.group = null;
        this.members = [];
        return;
      }

      try {
        const authToken = storage.get('authToken');
        const headers = {};
        if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const res = await axios.get(
          `${API_BASE_URL}/api/groups/${this.groupId}`,
          { headers }
        );
        const data = res && res.data ? res.data : null;
        // Expecting the backend to return group and members arrays
        this.group = data && data.group ? data.group : data;
        // Normalize members array to ensure memberRoles shape
        this.members =
          data && data.members
            ? data.members.map((m) => {
                m.memberRoles = Array.isArray(m.memberRoles)
                  ? m.memberRoles.map((r) =>
                      typeof r === 'object' ? r : { id: r, name: String(r) }
                    )
                  : (m.member_role_ids || []).map((id) => ({
                      id,
                      name: String(id),
                    }));
                return m;
              })
            : [];
      } catch (e) {
        console.error('Error fetching group details:', e);
        this.group = null;
        this.members = [];
      }
    },
    formatDate(dt) {
      if (!dt) return '—';
      try {
        const d = new Date(dt);
        if (isNaN(d.getTime())) return dt;
        const day = d.getDate();
        const months = [
          'Jan',
          'Feb',
          'Mar',
          'Apr',
          'May',
          'Jun',
          'Jul',
          'Aug',
          'Sep',
          'Oct',
          'Nov',
          'Dec',
        ];
        const month = months[d.getMonth()];
        const year = d.getFullYear();
        let hours = d.getHours();
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        if (hours === 0) hours = 12;
        return `${day} ${month},${year} ${hours}:${minutes} ${ampm}`;
      } catch (e) {
        console.warn('Error formatting date:', e);
        return dt;
      }
    },
  },
};/////////
//heyy just testing
</script>

<style scoped>
@import '@/assets/modelcssnotificationandassesment.css';
@import '@/assets/global.css';
@import '@/assets/table.css';

</style>
