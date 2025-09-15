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
      <div class="modal-title">Notification Detail</div>

      <div class="modal-desc">
        Details for the selected notification / announcement.
      </div>

      <div>
        <br />
        <div
          class="modal-title schedule-header"
          style="font-size: 20px; font-weight: 450"
        >
          <div class="schedule-header-left">
            <div>
              <div
                class="schedule-assessment-name"
                style="
                  display: inline-block;
                  vertical-align: middle;
                  max-width: 520px;
                  margin-right: 12px;
                "
              >
                {{ announcementBodyShort }}
              </div>
              -
              <div
                class="schedule-assessment-name"
                style="
                  display: inline-block;
                  vertical-align: middle;
                  margin-left: 12px;
                "
              >
                {{ formatDateTime(announcementScheduledAt) }}
              </div>
            </div>
          </div>
          <div class="schedule-header-right">
            <span
              v-if="announcementStatus === 'sent'"
              :class="[
                'status-green',
                { active: announcementStatus === 'sent' },
              ]"
              >Sent</span
            >
            <span
              v-if="announcementStatus === 'scheduled'"
              :class="[
                'status-yellow',
                { active: announcementStatus === 'scheduled' },
              ]"
              >Scheduled</span
            >
            <span
              v-if="announcementStatus === 'failed'"
              :class="[
                'status-red',
                { active: announcementStatus === 'failed' },
              ]"
              >Failed</span
            >
          </div>
        </div>
        <div class="NotificationDetailBodyForOrganizations">
          <div
            v-if="allRecipients.length"
            class="NotificationDetailBodyForOrganizations"
          >
            <div class="modal-titleTABLE">
              Organization Notification Details
            </div>
            <div class="detail-row">
              <div
                class="detail-table"
                style="
                  width: 100% !important;
                  max-width: 800px !important;
                  margin: 0 !important;
                "
              >
                <div
                  class="recipient-table-wrap"
                  style="
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                    width: 100%;
                  "
                >
                  <table
                    class="recipient-table compact"
                    style="width: 100%; min-width: 500px"
                  >
                    <TableHeader
                      :columns="[
                        {
                          label: 'Organization Name',
                          key: 'organization_name',
                          minWidth: '200px',
                        },
                        {
                          label: 'User Name',
                          key: 'name',
                          minWidth: '200px',
                        },

                        {
                          label: 'Emails',
                          key: 'email',
                          minWidth: '200px',
                        },
                        { label: 'Read At', key: 'read_at', minWidth: '200px' },
                      ]"
                    />
                    <tbody>
                      <tr v-if="!allRecipients.length">
                        <td
                          colspan="4"
                          style="text-align: center; padding: 20px"
                        >
                          No recipients found.
                        </td>
                      </tr>
                      <tr
                        v-for="r in allRecipients"
                        :key="r.id"
                      >
                        <td>{{ r.organization_name }}</td>
                        <td>{{ r.name }}</td>
                        <td>{{ r.email }}</td>

                        <td>
                          <span>{{
                            r.read_at ? formatDateTime(r.read_at) : ' - '
                          }}</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="NotificationDetailBodyForGroups">
          <div
            v-if="groupRows.length"
            class="NotificationDetailBodyForGroups"
          >
            <div class="modal-titleTABLE">Group Notification Details</div>
            <div class="detail-row">
              <div
                class="detail-table"
                style="
                  width: 100% !important;
                  max-width: 800px !important;
                  margin: 0 !important;
                "
              >
                <div
                  class="recipient-table-wrap"
                  style="
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                    width: 100%;
                  "
                >
                  <table
                    class="recipient-table compact"
                    style="width: 100%; min-width: 500px"
                  >
                    <TableHeader
                      :columns="[
                        {
                          label: 'Group Name',
                          key: 'name',
                          minWidth: '200px',
                        },
                        {
                          label: 'Organization Name',
                          key: 'organization_name',
                          minWidth: '200px',
                        },
                        {
                          label: 'Org Contact Email',
                          key: 'org_contact_email',
                          minWidth: '200px',
                        },
                      ]"
                    />
                    <tbody>
                      <tr v-if="!groupRows.length">
                        <td
                          colspan="3"
                          style="text-align: center; padding: 20px"
                        >
                          No groups targeted.
                        </td>
                      </tr>
                      <tr
                        v-for="g in groupRows"
                        :key="g.id"
                      >
                        <td>{{ g.name }}</td>
                        <td>{{ g.organization_name }}</td>
                        <td>{{ g.org_contact_email }}</td>
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
  </div>
</template>

<script>
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';

export default {
  name: 'NotificationDetail',
  components: { TableHeader },
  props: {
    visible: { type: Boolean, default: false },
    announcement: { type: Object, default: null },
    selectedNotification: { type: Object, default: null },
    groups: { type: Array, default: () => [] },
    organizations: { type: Array, default: () => [] },
    notifications: { type: Array, default: () => [] },
  },
  emits: ['close'],
  data() {
    return {};
  },
  computed: {
    announcementEffective() {
      return this.announcement || this.selectedNotification || null;
    },
    announcementBodyShort() {
      const raw =
        this.announcementEffective && (this.announcementEffective.body || '');
      return String(raw || '').slice(0, 120);
    },
    announcementScheduledAt() {
      return (
        (this.announcementEffective &&
          (this.announcementEffective.scheduled_at ||
            this.announcementEffective.sent_at)) ||
        ''
      );
    },
    announcementStatus() {
      // priority: sent > scheduled > failed
      const a = this.announcementEffective || {};
      const hasSent = !!(a.sent_at || a.sent_at === 0);
      const hasScheduled = !!(a.scheduled_at || a.scheduled_at === 0);
      if (hasSent) return 'sent';
      if (hasScheduled) return 'scheduled';
      return 'failed';
    },
    notificationsMap() {
      const map = new Map();
      if (!this.notifications) return map;
      this.notifications.forEach((n) => {
        // notifiable_id is user id
        if (n.notifiable_id) {
          map.set(Number(n.notifiable_id), n);
        }
      });
      return map;
    },
    allRecipients() {
      const orgs = this.organizationRecipients || [];
      const admins = this.adminRecipients || [];

      const combined = [...orgs];
      const ids = new Set(orgs.map((o) => Number(o.id)));
      admins.forEach((a) => {
        const aid = Number(a.id);
        if (!ids.has(aid)) combined.push(a);
      });

      combined.forEach((r) => {
        const rid = Number(r.id);
        const notification = this.notificationsMap.get(rid);
        r.read_at = notification ? notification.read_at : null;
      });

      return combined;
    },
    organizationRecipients() {
      const recipients = new Map();
      const announcement = this.announcementEffective;
      if (!announcement) return [];

      (announcement.organizations || []).forEach((org) => {
        const userId = org.user_id ?? org.user?.id ?? null;
        const orgName = org.name ?? org.organization_name ?? '';
        const email = org.contact_email ?? org.admin_email ?? null;
        const first = org.user_first_name ?? org.user?.first_name ?? '';
        const last = org.user_last_name ?? org.user?.last_name ?? '';

        if (userId && !recipients.has(Number(userId))) {
          recipients.set(Number(userId), {
            id: Number(userId),
            organization_name: orgName,
            name: `${first || ''} ${last || ''}`.trim(),
            email: email,
          });
        }
      });

      return Array.from(recipients.values());
    },
    adminRecipients() {
      const recipients = new Map();
      const announcement = this.announcementEffective;
      if (!announcement) return [];

      (announcement.admins || []).forEach((admin) => {
        if (admin && admin.id && !recipients.has(admin.id)) {
          recipients.set(admin.id, {
            id: admin.id,
            name:
              admin.name ||
              `${admin.first_name || ''} ${admin.last_name || ''}`.trim(),
            email: admin.email,
          });
        }
      });

      return Array.from(recipients.values());
    },
    groupRows() {
      if (Array.isArray(this.groups) && this.groups.length) {
        return this.groups.map((g) => ({
          id: g.id,
          name: g.name || `Group ${g.id}`,
          organization_id: g.organization_id || null,
          organization_name: g.organization_name || '',
          org_contact_email: g.org_contact_email || null,
        }));
      }

      const announcement = this.announcementEffective || {};
      const ag = announcement.groups || [];
      const orgs = announcement.organizations || this.organizations || [];
      const orgMap = new Map();
      orgs.forEach((o) => orgMap.set(Number(o.id), o));

      return ag.map((g) => {
        return {
          id: g.id,
          name: g.name || `Group ${g.id}`,
          organization_id: g.organization_id,
          organization_name: g.organization_name,
          org_contact_email: g.org_contact_email,
        };
      });
    },
  },
  methods: {
    formatDateTime(dt) {
      if (!dt) return '—';
      try {
        const d = new Date(dt);
        if (isNaN(d.getTime())) return dt;
        const day = String(d.getDate()).padStart(2, '0');
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
        const mon = months[d.getMonth()];
        const yr = d.getFullYear();
        let hr = d.getHours();
        const min = String(d.getMinutes()).padStart(2, '0');
        const ampm = hr >= 12 ? 'PM' : 'AM';
        hr = hr % 12;
        hr = hr || 12;
        return `${day} ${mon},${yr} ${hr}:${min} ${ampm}`;
      } catch {
        return dt;
      }
    },
  },
};
</script>

<style scoped>
@import '@/assets/modelcssnotificationandassesment.css';

/* status badges */
.schedule-header-right {
  display: flex;
  gap: 10px;
  align-items: center;
}

.status-green {
  color: #fff;
  background: #28a745;
  font-weight: 600;
  font-size: 18px;
  padding: 4px 16px;
  border-radius: 20px;
  display: inline-block;
  min-width: 150px;
  text-align: center;
}

.status-yellow {
  color: #fff;
  background: #f7c948;
  font-weight: 600;
  font-size: 18px;
  padding: 4px 16px;
  border-radius: 20px;
  display: inline-block;
  min-width: 150px;
  text-align: center;
}

.status-red {
  color: #fff;
  background: #e74c3c;
  font-weight: 600;
  font-size: 18px;
  padding: 4px 16px;
  border-radius: 20px;
  display: inline-block;
  min-width: 150px;

  text-align: center;
}
.status-green.active,
.status-yellow.active,
.status-red.active {
  opacity: 1;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
}
.modal-titleTABLE {
  font-size: 16px;
  font-weight: 400;
  margin: 8px 0;
  color: var(--text);
  text-align: center;
}
</style>
