<template>
  <div
    class="modal-overlay"
    @click.self="$emit('close')"
  >
    <div
      class="modal-card"
      v-if="scheduleDetails"
      style="max-width: 900px; width: 90%"
    >
      <button
        class="modal-close"
        @click="$emit('close')"
      >
        &times;
      </button>
      <div class="modal-title">Scheduled Assessment Details</div>

      <div class="modal-desc">
        Details for the selected scheduled assessment.
      </div>
      <div class="notifications-controls">
        <div class="notifications-tabs">
          <button
            :class="[
              'notifications-tab-btn-left',
              { active: tab === 'Group Wise' },
            ]"
            @click="tab = 'Group Wise'"
          >
            Group Wise
          </button>
          <button
            :class="[
              'notifications-tab-btn-right',
              { active: tab === 'Member Wise' },
            ]"
            @click="tab = 'Member Wise'"
            min-width="320px"
          >
            Member Wise
          </button>
        </div>
      </div>
      <div v-if="tab === 'Group Wise'">
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
                {{ scheduleDetails.assessment.name }}
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
                {{
                  formatLocalDateTime(
                    scheduleDetails.schedule.date,
                    scheduleDetails.schedule.time
                  )
                }}
              </div>
            </div>
          </div>

          <div class="schedule-header-right">
            <span
              v-if="scheduleStatus === 'sent'"
              :class="['status-green', { active: scheduleStatus === 'sent' }]"
            >
              Sent
            </span>
            <span
              v-if="scheduleStatus === 'scheduled'"
              :class="[
                'status-yellow',
                { active: scheduleStatus === 'scheduled' },
              ]"
            >
              Scheduled
            </span>
            <span
              v-if="scheduleStatus === 'failed'"
              :class="['status-red', { active: scheduleStatus === 'failed' }]"
            >
              Failed
            </span>
          </div>
        </div>

        <div v-if="scheduleDetails && scheduleDetails.schedule">
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
                <div class="table-scroll">
                  <table
                    v-if="groupedEmails && groupedEmails.length"
                    class="recipient-table compact"
                    style="width: 100%; min-width: 500px"
                  >
                    <TableHeader
                      :columns="[
                        { label: 'Group', key: 'group', minWidth: '200px' },
                        { label: 'Members', key: 'members', minWidth: '200px' },
                        { label: 'Email', key: 'email', minWidth: '200px' },
                        {
                          label: 'Member Roles',
                          key: 'member_roles',
                          minWidth: '200px',
                        },
                      ]"
                    />
                    <tbody>
                      <template
                        v-for="(g, gi) in groupedEmails"
                        :key="'group-' + gi"
                      >
                        <tr
                          v-for="(e, ei) in g.items"
                          :key="'email-' + gi + '-' + ei"
                        >
                          <td
                            v-if="ei === 0"
                            :rowspan="g.items.length"
                            class="group-cell"
                          >
                            {{ g.name || 'Ungrouped' }}
                          </td>
                          <td style="padding: 0px 8px !important">
                            {{
                              (e.member_id && memberDetailMap[e.member_id]
                                ? memberDetailMap[e.member_id].name
                                : e.recipient_email || e.email || e.to) ||
                              'Unknown'
                            }}
                          </td>
                          <td>
                            {{
                              (e.member_id && memberDetailMap[e.member_id]
                                ? memberDetailMap[e.member_id].email
                                : e.recipient_email || e.email || e.to) || ''
                            }}
                          </td>
                          <td>
                            {{
                              (e.member_id && memberDetailMap[e.member_id]
                                ? memberDetailMap[e.member_id].rolesDisplay
                                : Array.isArray(e.memberRoles) &&
                                  e.memberRoles.length
                                ? e.memberRoles
                                    .map((r) => (r && r.name) || r)
                                    .join(', ')
                                : Array.isArray(e.member_role_ids) &&
                                  e.member_role_ids.length
                                ? e.member_role_ids
                                    .map((r) => (r && r.name) || r)
                                    .join(', ')
                                : e.member_role) || ''
                            }}
                          </td>
                        </tr>
                      </template>
                    </tbody>
                  </table>
                  <div
                    v-else
                    class="no-data"
                  >
                    <em>No groups found for this schedule.</em>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else>
          <em>No schedule details found.</em>
        </div>
      </div>

      <div v-else-if="tab === 'Member Wise'">
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
                {{ scheduleDetails.assessment.name }}
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
                {{
                  formatLocalDateTime(
                    scheduleDetails.schedule.date,
                    scheduleDetails.schedule.time
                  )
                }}
              </div>
            </div>
          </div>

          <div class="schedule-header-right">
            <span
              v-if="scheduleStatus === 'sent'"
              :class="['status-green', { active: scheduleStatus === 'sent' }]"
            >
              Sent
            </span>
            <span
              v-if="scheduleStatus === 'scheduled'"
              :class="[
                'status-yellow',
                { active: scheduleStatus === 'scheduled' },
              ]"
            >
              Scheduled
            </span>
            <span
              v-if="scheduleStatus === 'failed'"
              :class="['status-red', { active: scheduleStatus === 'failed' }]"
            >
              Failed
            </span>
          </div>
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
              <div class="table-scroll">
                <table
                  v-if="memberWiseRows && memberWiseRows.length"
                  class="recipient-table compact"
                  style="width: 100%; min-width: 500px"
                >
                  <TableHeader
                    :columns="[
                      {
                        label: 'Member Name',
                        key: 'name',
                        minWidth: '200px',
                      },
                      { label: 'Email', key: 'email', minWidth: '200px' },
                      { label: 'Groups', key: 'groups', minWidth: '200px' },
                      {
                        label: 'Member Roles',
                        key: 'rolesDisplay',
                        minWidth: '200px',
                      },
                    ]"
                  />
                  <tbody>
                    <tr
                      v-for="m in memberWiseRows"
                      :key="'memberwise-' + m.id"
                    >
                      <td>{{ m.name }}</td>
                      <td>{{ m.email }}</td>
                      <td>
                        {{
                          m.groups && m.groups.length ? m.groups.join(', ') : ''
                        }}
                      </td>
                      <td>{{ m.rolesDisplay || '' }}</td>
                    </tr>
                  </tbody>
                </table>
                <div
                  v-else
                  class="no-data"
                >
                  <em>No members found for Member Wise view.</em>
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
  name: 'ScheduleDetailsModal',
  components: { TableHeader },

  props: {
    scheduleDetails: {
      type: Object,
      default: null,
    },
    allGroups: {
      type: Array,
      default: () => [],
    },
    allMembers: {
      type: Array,
      default: () => [],
    },
  },
  emits: ['close'],
  data() {
    return {
      tab: 'Group Wise',
    };
  },
  computed: {
    memberDetailMap() {
      const map = {};

      // Priority 1: Use enhanced data from `members_with_details` if available
      if (this.scheduleDetails && this.scheduleDetails.members_with_details) {
        this.scheduleDetails.members_with_details.forEach((m) => {
          const rolesDisplay =
            Array.isArray(m.member_roles) && m.member_roles.length
              ? m.member_roles.map((r) => r.name || r).join(', ')
              : '';

          map[m.id] = {
            name: m.name || 'Unknown',
            email: m.email || '',
            memberRoles: m.member_roles || [],
            rolesDisplay,
          };
        });
      }

      // Priority 2: Fallback to `groups_with_members` data
      if (this.scheduleDetails && this.scheduleDetails.groups_with_members) {
        this.scheduleDetails.groups_with_members.forEach((group) => {
          if (group.members && Array.isArray(group.members)) {
            group.members.forEach((m) => {
              if (!map[m.id]) {
                const rolesDisplay =
                  Array.isArray(m.member_roles) && m.member_roles.length
                    ? m.member_roles.map((r) => r.name || r).join(', ')
                    : '';

                map[m.id] = {
                  name: m.name || 'Unknown',
                  email: m.email || '',
                  memberRoles: m.member_roles || [],
                  rolesDisplay,
                };
              }
            });
          }
        });
      }

      // Priority 3: Fallback to legacy `allMembers` prop for backward compatibility
      if (Object.keys(map).length === 0) {
        (this.allMembers || []).forEach((m) => {
          const first = (m.first_name || m.name || '').toString().trim();
          const last = (m.last_name || '').toString().trim();
          let name = first;
          if (last) name = name ? `${name} ${last}` : last;
          if (!name) name = m.email || `Member ${m.id}`;

          let memberRoles = [];
          if (Array.isArray(m.memberRoles) && m.memberRoles.length) {
            memberRoles = m.memberRoles.map((r) =>
              typeof r === 'object' ? r : { id: r, name: String(r) }
            );
          } else if (
            Array.isArray(m.member_role_ids) &&
            m.member_role_ids.length
          ) {
            memberRoles = m.member_role_ids.map((id) => ({
              id,
              name: String(id),
            }));
          } else {
            memberRoles = [];
          }

          const rolesDisplay =
            memberRoles.length > 0
              ? memberRoles.map((r) => r.name || r).join(', ')
              : m.member_role || '';

          map[m.id] = {
            name,
            email: m.email || '',
            memberRoles,
            rolesDisplay,
          };
        });
      }

      return map;
    },
    filteredEmails() {
      if (!this.scheduleDetails || !this.scheduleDetails.emails) return [];
      const schedule = this.scheduleDetails.schedule;
      if (!schedule) return [];
      return (this.scheduleDetails.emails || []).filter(
        (e) => e && e.assessment_id === this.scheduleDetails.assessment.id
      );
    },
    scheduleStatus() {
      const details = this.scheduleDetails;
      if (!details || !details.schedule) {
        return 'failed';
      }

      const nowUtc = Date.now();
      const schedule = details.schedule || {};

      const scheduleTimestamp = (() => {
        const datePart = (schedule.date || '').trim();
        if (!datePart) return null;

        const [year, month, day] = datePart
          .split('-')
          .map((value) => Number(value));
        if ([year, month, day].some(Number.isNaN)) {
          return null;
        }

        const [hour = 0, minute = 0, second = 0] = (schedule.time || '00:00:00')
          .split(':')
          .map((value) => Number(value));

        const timestamp = Date.UTC(
          Number(year),
          Number(month) - 1,
          Number(day),
          Number(hour) || 0,
          Number(minute) || 0,
          Number(second) || 0
        );

        return Number.isNaN(timestamp) ? null : timestamp;
      })();

      const scheduleInFuture =
        typeof scheduleTimestamp === 'number' && scheduleTimestamp >= nowUtc;

      const emails = (this.filteredEmails || []).filter(Boolean);
      if (emails.length) {
        const allSent = emails.every((email) => !!email.sent);
        if (allSent) {
          return 'sent';
        }

        const someSent = emails.some((email) => !!email.sent);
        if (someSent) {
          return 'scheduled';
        }

        const hasFutureEmail = emails.some((email) => {
          const sendAt = email.send_at || email.scheduled_at || '';
          if (!sendAt) return false;

          const [datePart, timePart = '00:00:00'] = sendAt.trim().split(/\s+/);
          if (!datePart) return false;

          const [year, month, day] = datePart
            .split('-')
            .map((value) => Number(value));
          if ([year, month, day].some(Number.isNaN)) {
            return false;
          }

          const [hour = 0, minute = 0, second = 0] = timePart
            .split(':')
            .map((value) => Number(value));

          const timestamp = Date.UTC(
            Number(year),
            Number(month) - 1,
            Number(day),
            Number(hour) || 0,
            Number(minute) || 0,
            Number(second) || 0
          );

          return !Number.isNaN(timestamp) && timestamp >= nowUtc;
        });

        if (hasFutureEmail || scheduleInFuture) {
          return 'scheduled';
        }

        return 'failed';
      }

      return scheduleInFuture ? 'scheduled' : 'failed';
    },
    groupedEmails() {
      const list = this.filteredEmails || [];
      const schedule =
        (this.scheduleDetails && this.scheduleDetails.schedule) || null;
      const map = new Map();

      const parseArrayField = (v) => {
        if (!v) return [];
        if (Array.isArray(v)) return v.map(Number);
        try {
          const p = JSON.parse(v);
          return Array.isArray(p) ? p.map(Number) : [];
        } catch (e) {
          // eslint-disable-next-line no-console
          console.warn('Failed to parse array field:', e);
          return v
            .toString()
            .replace(/\[|\]|\s+/g, '')
            .split(',')
            .filter(Boolean)
            .map(Number);
        }
      };

      const scheduleGroupIds = schedule
        ? parseArrayField(schedule.group_ids)
        : [];

      if (scheduleGroupIds.length) {
        scheduleGroupIds.forEach((gid) => {
          const gobj = (this.allGroups || []).find(
            (gg) => Number(gg.id) === gid
          );
          const gname = (gobj && (gobj.name || gobj.group)) || `Group ${gid}`;
          const items = [];

          list.forEach((e) => {
            const egids = new Set();
            if (e.group_id) egids.add(Number(e.group_id));
            if (e.group_ids)
              parseArrayField(e.group_ids).forEach((id) => egids.add(id));
            if (egids.has(gid)) items.push(e);
          });

          let groupMemberIds = [];
          if (gobj && Array.isArray(gobj.members) && gobj.members.length) {
            groupMemberIds = gobj.members.map((m) =>
              Number(m.id || m.member_id || m)
            );
          } else {
            groupMemberIds = (this.allMembers || [])
              .filter((m) => {
                const mgids = Array.isArray(m.group_ids) ? m.group_ids : [];
                return (
                  mgids.some((mgid) => Number(mgid) === gid) ||
                  Number(m.group_id) === gid
                );
              })
              .map((m) => Number(m.id));
          }

          (groupMemberIds || []).forEach((mid) => {
            if (!items.some((i) => Number(i.member_id) === mid)) {
              items.push({ member_id: mid });
            }
          });

          map.set(gid, { id: gid, name: gname, items: items.filter(Boolean) });
        });
        return Array.from(map.values());
      }

      list.forEach((e) => {
        const gid = e.group_id || e.group || 'ungrouped';
        const gname =
          e.group_name ||
          e.group ||
          (gid === 'ungrouped' ? 'Ungrouped' : `Group ${gid}`);
        if (!map.has(gid)) map.set(gid, { id: gid, name: gname, items: [] });
        map.get(gid).items.push(e);
      });

      Array.from(map.values()).forEach((v) => {
        v.items = (v.items || []).filter(Boolean);
      });

      return Array.from(map.values());
    },
    memberWiseRows() {
      const rows = [];
      const schedule =
        (this.scheduleDetails && this.scheduleDetails.schedule) || null;

      const parseArrayField = (v) => {
        if (!v) return [];
        if (Array.isArray(v)) return v.map(Number);
        try {
          const p = JSON.parse(v);
          return Array.isArray(p) ? p.map(Number) : [];
        } catch {}
        return v
          .toString()
          .replace(/\[|\]|\s+/g, '')
          .split(',')
          .filter(Boolean)
          .map(Number);
      };

      const memberIds = schedule ? parseArrayField(schedule.member_ids) : [];
      const emailMemberIds = (this.filteredEmails || [])
        .filter(Boolean)
        .map((e) => Number(e.member_id))
        .filter(Boolean);
      const uniqueIds = new Set(memberIds.length ? memberIds : emailMemberIds);

      uniqueIds.forEach((mid) => {
        const detail = this.memberDetailMap[mid] || {
          name: `Member ${mid}`,
          email: '',
        };

        let groups = [];
        const fromAllGroups = (this.allGroups || [])
          .filter(
            (g) =>
              Array.isArray(g.members) &&
              g.members.some((m) => Number(m.id || m.member_id || m) === mid)
          )
          .map((g) => Number(g.id));

        if (fromAllGroups.length) {
          groups = fromAllGroups;
        } else {
          groups = (this.filteredEmails || [])
            .filter(
              (e) => Number(e.member_id) === mid && (e.group_id || e.group)
            )
            .map((e) => Number(e.group_id || e.group));
        }

        const uniqueGroups = [...new Set(groups)];
        const groupNames = uniqueGroups.map((gid) => {
          const gobj = (this.allGroups || []).find(
            (gg) => Number(gg.id) === gid
          );
          return (gobj && (gobj.name || gobj.group)) || `Group ${gid}`;
        });

        rows.push({
          id: mid,
          name: detail.name,
          email: detail.email,
          groups: groupNames,
          rolesDisplay: detail.rolesDisplay || '',
        });
      });

      return rows;
    },
  },
  methods: {
    formatLocalDateTime(dateStr, timeStr) {
      if (!dateStr) return '';
      try {
        const { year, month, day } = this.parseDateString(dateStr);
        const { hour, minute, second } = this.parseTimeString(timeStr);
        const dt = new Date(year, month, day, hour, minute, second);
        return isNaN(dt.getTime())
          ? `${dateStr} ${timeStr || ''}`.trim()
          : this.formatDisplayTime(dt);
      } catch {
        return `${dateStr} ${timeStr || ''}`.trim();
      }
    },
    parseDateString(dateStr) {
      const dmatch = (dateStr || '')
        .toString()
        .trim()
        .match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if (dmatch) {
        return {
          year: Number(dmatch[1]),
          month: Number(dmatch[2]) - 1,
          day: Number(dmatch[3]),
        };
      }
      const alt = new Date(dateStr);
      if (isNaN(alt.getTime())) throw new Error('Invalid date');
      return {
        year: alt.getFullYear(),
        month: alt.getMonth(),
        day: alt.getDate(),
      };
    },
    parseTimeString(timeStr) {
      const tmatch = (timeStr || '')
        .toString()
        .trim()
        .match(/^(\d{2}):(\d{2})(?::(\d{2}))?$/);
      if (tmatch) {
        return {
          hour: Number(tmatch[1]),
          minute: Number(tmatch[2]),
          second: Number(tmatch[3] || 0),
        };
      }
      return { hour: 0, minute: 0, second: 0 };
    },
    formatDisplayTime(dt) {
      const dayNum = String(dt.getDate()).padStart(2, '0');
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
      const mon = months[dt.getMonth()];
      const yr = dt.getFullYear();
      let hr = dt.getHours();
      const min = String(dt.getMinutes()).padStart(2, '0');
      const ampm = hr >= 12 ? 'PM' : 'AM';
      hr = hr % 12 || 12; // Convert hour to 12-hour format
      return `${dayNum} ${mon}, ${yr} ${hr}:${min} ${ampm}`;
    },
  },
};
</script>

<style scoped>
.notifications-controls {
  display: flex;
  flex-direction: row-reverse;
  margin-bottom: 24px;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  box-sizing: border-box;
}
.notifications-tabs {
  display: flex;
  border-radius: 32px;
  background: #f8f8f8;
  overflow: hidden;
  min-width: 240px;
  height: 36px;
}
.notifications-tab-btn-left,
.notifications-tab-btn-right {
  border: none;
  min-width: 150px;
  border-radius: 32px;
  outline: none;
  background: #f8f8f8;
  color: #0f0f0f;
  font-family: 'Helvetica Neue LT Std', Arial, sans-serif;
  font-size: 18px;
  font-weight: 400;
  line-height: 26px;
  letter-spacing: 0.02em;
  flex: 1;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.18s, color 0.18s, border 0.18s, font-weight 0.18s;
  cursor: pointer;
  box-sizing: border-box;
}
.notifications-tab-btn-left.active {
  background: #f6f6f6;
  border: 1.5px solid #dcdcdc;
  border-radius: 32px 0 0 32px;
  color: #0f0f0f;
  font-weight: 500;
  z-index: 1;
}
.notifications-tab-btn-right.active {
  background: #f6f6f6;
  border: 1.5px solid #dcdcdc;
  border-radius: 0 32px 32px 0;
  color: #0f0f0f;
  font-weight: 500;
  z-index: 1;
}
</style>
