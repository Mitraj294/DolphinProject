<template>
  <!-- Notification detail modal: shows recipients and read-status for an announcement -->
  <div
    v-if="visible"
    class="modal-overlay"
    @click.self="$emit('close')"
  >
    <div
      class="modal-card"
      style="max-width: 900px; width: 90%"
    >
      <!-- Close button -->
      <button
        class="modal-close-btn"
        @click="$emit('close')"
      >
        &times;
      </button>

      <!-- Header -->
      <div class="modal-title">Notification Detail</div>
      <div class="modal-desc">
        Details for the selected notification / announcement.
      </div>

      <div>
        <br />

        <!-- Title, scheduled time and status -->
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

          <!-- Status badges -->
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

        <!-- Organization recipients -->
        <div class="NotificationDetailBodyForOrganizations">
          <div
            v-if="organizationRecipients.length"
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
                        { label: 'User Name', key: 'name', minWidth: '200px' },
                        { label: 'Emails', key: 'email', minWidth: '200px' },
                        { label: 'Read At', key: 'read_at', minWidth: '200px' },
                      ]"
                    />
                    <tbody>
                      <tr v-if="!organizationRecipients.length">
                        <td
                          colspan="4"
                          style="text-align: center; padding: 20px"
                        >
                          No recipients found.
                        </td>
                      </tr>
                      <tr
                        v-for="r in organizationRecipients"
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

        <!-- Admin recipients -->
        <div class="NotificationDetailBodyForAdmins">
          <div
            v-if="adminRecipients.length"
            class="NotificationDetailBodyForAdmins"
          >
            <div class="modal-titleTABLE">Admin Notification Details</div>
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
                        { label: 'User Name', key: 'name', minWidth: '200px' },
                        { label: 'Emails', key: 'email', minWidth: '200px' },
                        { label: 'Read At', key: 'read_at', minWidth: '200px' },
                      ]"
                    />
                    <tbody>
                      <tr v-if="!adminRecipients.length">
                        <td
                          colspan="3"
                          style="text-align: center; padding: 20px"
                        >
                          No admins targeted.
                        </td>
                      </tr>
                      <tr
                        v-for="a in adminRecipients"
                        :key="a.id"
                      >
                        <td>{{ a.name }}</td>
                        <td>{{ a.email }}</td>
                        <td>
                          <span>{{
                            a.read_at ? formatDateTime(a.read_at) : ' - '
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

        <!-- Group recipients -->
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
                        { label: 'Group Name', key: 'name', minWidth: '200px' },
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
// NotificationDetail.vue
// Purpose: show recipients and metadata for a selected notification/announcement.
// Notes:
// - Component accepts either `announcement` or `selectedNotification` (fallback).
// - `notifications` prop (array) may contain per-user read timestamps keyed by notifiable_id.
// - Computed properties normalize orgs, admins, and groups for display.

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
    // prefer the explicit announcement prop
    announcementEffective() {
      return this.announcement || this.selectedNotification || null;
    },

    // short preview of the announcement body for the header
    announcementBodyShort() {
      const raw =
        this.announcementEffective && (this.announcementEffective.body || '');
      return String(raw || '').slice(0, 120);
    },

    // scheduled time (or sent time) to show in the header
    announcementScheduledAt() {
      return (
        (this.announcementEffective &&
          (this.announcementEffective.scheduled_at ||
            this.announcementEffective.sent_at)) ||
        ''
      );
    },

    // simple status derivation used by the header badges
    announcementStatus() {
      const a = this.announcementEffective || {};
      const hasSent = !!(a.sent_at || a.sent_at === 0);
      const hasScheduled = !!(a.scheduled_at || a.scheduled_at === 0);
      if (hasSent) return 'sent';
      if (hasScheduled) return 'scheduled';
      return 'failed';
    },

    // map notifications by notifiable_id for quick lookup of read_at
    notificationsMap() {
      const map = new Map();
      try {
        (this.notifications || []).forEach((n) => {
          if (n && n.notifiable_id) map.set(Number(n.notifiable_id), n);
        });
      } catch (err) {
        // Defensive: do not throw if the prop is malformed; return empty map instead
        // eslint-disable-next-line no-console
        console.warn('notificationsMap parse error', err);
      }
      return map;
    },

    // combined recipients (organizations + admins), attach read timestamps when available
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
        const notif = this.notificationsMap.get(rid);
        r.read_at = notif ? notif.read_at : r.read_at || null;
      });

      return combined;
    },

    // normalize organization recipients from announcement.organizations
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

        // prefer read_at embedded in notifications array when available
        const notif = this.notificationsMap.get(Number(userId));
        const read_at = notif ? notif.read_at : org.read_at ?? null;

        if (userId && !recipients.has(Number(userId))) {
          recipients.set(Number(userId), {
            id: Number(userId),
            organization_name: orgName,
            name: `${first || ''} ${last || ''}`.trim(),
            email: email,
            read_at: read_at,
          });
        }
      });

      return Array.from(recipients.values());
    },

    // normalize admins from announcement.admins
    adminRecipients() {
      const recipients = new Map();
      const announcement = this.announcementEffective;
      if (!announcement) return [];

      (announcement.admins || []).forEach((admin) => {
        if (admin && admin.id && !recipients.has(admin.id)) {
          const notif = this.notificationsMap.get(Number(admin.id));
          recipients.set(admin.id, {
            id: admin.id,
            name:
              admin.name ||
              `${admin.first_name || ''} ${admin.last_name || ''}`.trim(),
            email: admin.email,
            read_at: notif ? notif.read_at : null,
          });
        }
      });

      return Array.from(recipients.values());
    },

    // flatten groups for display. If `groups` prop is present, prefer that; otherwise derive from announcement.groups
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

      return ag.map((g) => ({
        id: g.id,
        name: g.name || `Group ${g.id}`,
        organization_id: g.organization_id,
        organization_name: g.organization_name,
        org_contact_email: g.org_contact_email,
      }));
    },
  },

  methods: {
    // Format dates into a readable local-time string. If formatting fails, return the raw input.
    formatDateTime(dt) {
      if (!dt) return '—';
      try {
        // Helper: produce a Date object in the user's local timezone.
        const toLocalDate = (input) => {
          // Numbers: treat as ms (or sec if 10-digit)
          if (typeof input === 'number') {
            const n = String(input).length === 10 ? input * 1000 : input;
            return new Date(Number(n));
          }

          if (typeof input !== 'string') return new Date(input);

          const s = input.trim();

          // If ISO with timezone (Z or ±hh:mm) -> Date will parse correctly and convert to local
          const isoTz = /\d{4}-\d{2}-\d{2}T.*(Z|[+-]\d{2}:?\d{2})$/i;
          if (isoTz.test(s)) return new Date(s);

          // Common SQL datetime 'YYYY-MM-DD HH:MM:SS[.sss]' -> treat as UTC
          const sqlMatch = s.match(
            /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}):(\d{2})(?:\.(\d+))?)?$/
          );
          if (sqlMatch) {
            const y = Number(sqlMatch[1]);
            const mo = Number(sqlMatch[2]) - 1;
            const dayNum = Number(sqlMatch[3]);
            const hh = Number(sqlMatch[4] || 0);
            const mm = Number(sqlMatch[5] || 0);
            const ss = Number(sqlMatch[6] || 0);
            const ms = sqlMatch[7]
              ? Math.floor(Number('0.' + sqlMatch[7]) * 1000)
              : 0;
            // Construct a UTC timestamp then convert to local Date
            return new Date(Date.UTC(y, mo, dayNum, hh, mm, ss, ms));
          }

          // Fallback: let Date parse it (may be treated as local)
          return new Date(s);
        };

        const d = toLocalDate(dt);
        if (!d || isNaN(d.getTime())) return dt;

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
      } catch (err) {
        // eslint-disable-next-line no-console
        console.warn('formatDateTime failed', err);
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
