<template>
  <CommonModal
    :visible="visible"
    @close="$emit('close')"
    modalMaxWidth="800px"
  >
    <template #title>
      <div style="text-align: left">
        <div style="font-size: 18px; font-weight: 600">Group Details</div>
        <div class="modal-desc">Details for the selected group.</div>
      </div>
    </template>

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
          <div class="detail-table">
            <div class="recipient-table-wrap">
              <table class="recipient-table compact">
                <thead>
                  <tr>
                    <th style="width: 30%">Name</th>
                    <th style="width: 30%">Email</th>
                    <th style="width: 20%">Role</th>
                    <th style="width: 20%">Actions</th>
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
                          : m.member_role || 'Member'
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
  </CommonModal>
</template>

<script>
import CommonModal from '@/components/Common/Common_UI/CommonModal.vue';
import axios from 'axios';
import storage from '@/services/storage';

export default {
  name: 'GroupDetails',
  components: { CommonModal },
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
        return dt;
      }
    },
  },
};
</script>

<style scoped>
.common-modal-title {
  font-size: 26px;
  font-weight: 600;
  margin-bottom: 32px;
  color: #222;
  text-align: left;
}
.group-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  gap: 18px;
}
.group-info .group-label {
  font-size: 13px;
  color: #666;
}
.group-info .group-value {
  font-size: 20px;
  font-weight: 600;
  color: #0b5fa5;
}
.group-meta {
  display: flex;
  gap: 18px;
  align-items: center;
}
.meta-item .meta-label {
  font-size: 12px;
  color: #888;
}
.meta-item .meta-value {
  font-size: 16px;
  font-weight: 600;
}
.group-section {
  margin-top: 18px;
}
.section-title {
  font-size: 16px;
  margin-bottom: 8px;
  color: #222;
}
.details-table {
  width: 100%;
  border-collapse: collapse;
}
.details-table th,
.details-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #f0f0f0;
  text-align: left;
}
.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-top: 8px;
}
.details-row {
  display: flex;
  gap: 8px;
  align-items: center;
  background: #f9f9f9;
  padding: 8px 12px;
  border-radius: 8px;
}
.details-row .k {
  width: 120px;
  color: #666;
}
.details-row .v {
  color: #222;
  font-weight: 500;
}
.no-data {
  padding: 12px;
  background: #ffffff;
  border-radius: 8px;
  color: #666;
}

/* Recipient table compact style (used across admin cards) */
.recipient-table-wrap {
  overflow: auto;
}
.recipient-table.compact {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.recipient-table.compact th,
.recipient-table.compact td {
  padding: 10px 12px;
  border-bottom: 1px solid #f0f0f0;
  text-align: left;
}
.recipient-table.compact thead {
  background: transparent;
}
.recipient-table.compact thead tr {
  border-bottom: 1px solid #e9eef3;
}
.recipient-table.compact thead th {
  background: transparent;
  color: #222;
  font-weight: 600;
  padding: 12px 14px;
  text-align: left;
  vertical-align: middle;
}
.group-cell {
  vertical-align: top;
  font-weight: 600;
  color: #0b5fa5;
  background: #fbfdff;
}
</style>
