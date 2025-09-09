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
        class="modal-close-btn"
        @click="$emit('close')"
      >
        &times;
      </button>
      <div class="modal-title">Scheduled Assessment Details</div>
      <div class="modal-desc">
        Details for the selected scheduled assessment.
      </div>
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
          <span v-if="scheduleDetails.emails && scheduleDetails.emails.length">
            <span
              v-if="
                filteredEmails.length && filteredEmails.every((e) => e.sent)
              "
              class="status-green"
              >Sent</span
            >
            <span
              v-else-if="
                filteredEmails.length &&
                filteredEmails.some((e) => !e.sent) &&
                filteredEmails.some((e) => e.sent)
              "
              class="status-yellow"
              >Scheduled</span
            >
            <span
              v-else-if="
                filteredEmails.length && filteredEmails.every((e) => !e.sent)
              "
            >
              <span
                v-if="
                  filteredEmails.some((e) => {
                    const [date, time] = (e.send_at || '').split(' ');
                    const [year, month, day] = date ? date.split('-') : [];
                    const [hour, min, sec] = time ? time.split(':') : [];
                    const sendAtUtc = Date.UTC(
                      Number(year),
                      Number(month) - 1,
                      Number(day),
                      Number(hour),
                      Number(min),
                      Number(sec)
                    );
                    const nowUtc = Date.now();
                    return sendAtUtc >= nowUtc;
                  })
                "
                class="status-yellow"
                >Scheduled</span
              >
              <span
                v-else
                class="status-red"
                >Failed</span
              >
            </span>
            <span
              v-else
              class="status-yellow"
              >Scheduled</span
            >
          </span>

          <span v-else>
            <span
              v-if="
                (() => {
                  const [year, month, day] = (
                    scheduleDetails.schedule.date || ''
                  ).split('-');
                  const [hour, min, sec] = (
                    scheduleDetails.schedule.time || ''
                  ).split(':');
                  const schedAtUtc = Date.UTC(
                    Number(year),
                    Number(month) - 1,
                    Number(day),
                    Number(hour),
                    Number(min),
                    Number(sec)
                  );
                  const nowUtc = Date.now();
                  return schedAtUtc >= nowUtc;
                })()
              "
              class="status-yellow"
              >Scheduled</span
            >
            <span
              v-else
              class="status-red"
              >Failed</span
            >
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
              <table
                v-if="filteredEmails && filteredEmails.length"
                class="recipient-table compact"
                style="width: 100%; min-width: 500px"
              >
                <thead>
                  <tr>
                    <th style="width: 30%">Group</th>
                    <th style="width: 30%">Name</th>
                    <th style="width: 40%">Email</th>
                  </tr>
                </thead>
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
                          e.member_id && memberDetailMap[e.member_id]
                            ? memberDetailMap[e.member_id].name
                            : e.recipient_email || e.email || e.to || 'Unknown'
                        }}
                      </td>
                      <td>
                        {{
                          e.member_id && memberDetailMap[e.member_id]
                            ? memberDetailMap[e.member_id].email
                            : e.recipient_email || e.email || e.to || ''
                        }}
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div v-else>
        <em>No schedule details found.</em>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ScheduleDetailsModal',
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
  computed: {
    memberDetailMap() {
      const map = {};
      (this.allMembers || []).forEach((m) => {
        const first = (m.first_name || m.name || '').toString().trim();
        const last = (m.last_name || '').toString().trim();
        let name = first;
        if (last) name = name ? `${name} ${last}` : last;
        if (!name) name = m.email || `Member ${m.id}`;

        map[m.id] = {
          name,
          email: m.email || '',
        };
      });
      return map;
    },
    filteredEmails() {
      if (!this.scheduleDetails || !this.scheduleDetails.emails) return [];
      const schedule = this.scheduleDetails.schedule;
      if (!schedule) return [];
      // Only include emails for this assessment; guard against falsy entries
      return (this.scheduleDetails.emails || []).filter(
        (e) => e && e.assessment_id === this.scheduleDetails.assessment.id
      );
    },
    groupedEmails() {
      // Prefer schedule.group_ids so we display all scheduled groups even if
      // there are no explicit email rows for some members in that group.
      const list = this.filteredEmails || [];
      const schedule =
        (this.scheduleDetails && this.scheduleDetails.schedule) || null;
      const map = new Map();

      const parseArrayField = (v) => {
        if (!v) return [];
        if (Array.isArray(v)) return v.map((x) => Number(x));
        try {
          const p = JSON.parse(v);
          if (Array.isArray(p)) return p.map((x) => Number(x));
        } catch (e) {
          console.warn('Failed to parse array field:', e);
          return v
            .toString()
            .replace(/\[|\]|\s+/g, '')
            .split(',')
            .filter(Boolean)
            .map((x) => Number(x));
        }
        return [];
      };

      const scheduleGroupIds = schedule
        ? parseArrayField(schedule.group_ids)
        : [];

      if (scheduleGroupIds && scheduleGroupIds.length) {
        scheduleGroupIds.forEach((gid) => {
          const gobj = (this.allGroups || []).find(
            (gg) => Number(gg.id) === Number(gid)
          );
          const gname = (gobj && (gobj.name || gobj.group)) || `Group ${gid}`;
          const items = [];

          // include any explicit email rows that reference this group
          list.forEach((e) => {
            const egids = new Set();
            if (e.group_id) egids.add(Number(e.group_id));
            if (e.group_ids) {
              try {
                const parsed = Array.isArray(e.group_ids)
                  ? e.group_ids
                  : JSON.parse(e.group_ids);
                if (Array.isArray(parsed))
                  parsed.forEach((x) => egids.add(Number(x)));
              } catch {
                console.warn('Failed to parse email group_ids field');
              }
            }
            if (egids.has(Number(gid))) items.push(e);
          });

          // Determine members that actually belong to this group using the
          // group's pivot data (gobj.members) or by scanning allMembers for
          // members whose group_ids/group_id include this group.
          let groupMemberIds = [];
          if (gobj && Array.isArray(gobj.members) && gobj.members.length) {
            groupMemberIds = gobj.members.map((m) =>
              Number(m.id || m.member_id || m)
            );
          }
          // fallback: inspect allMembers for association fields
          if (!groupMemberIds.length) {
            groupMemberIds = (this.allMembers || [])
              .filter((m) => {
                const mgids = Array.isArray(m.group_ids) ? m.group_ids : [];
                return (
                  mgids.some((mgid) => Number(mgid) === Number(gid)) ||
                  Number(m.group_id) === Number(gid)
                );
              })
              .map((m) => Number(m.id));
          }

          // Add members belonging to this group (avoid duplicates)
          (groupMemberIds || []).forEach((mid) => {
            const already = items.some(
              (i) => Number(i.member_id) === Number(mid)
            );
            if (!already) items.push({ member_id: mid });
          });

          map.set(gid, { id: gid, name: gname, items });
        });
        return Array.from(map.values());
      }

      // fallback: group by explicit group on email rows
      list.forEach((e) => {
        const gid = e.group_id || e.group || 'ungrouped';
        const gname =
          e.group_name || e.group || (gid === 'ungrouped' ? 'Ungrouped' : null);
        if (!map.has(gid))
          map.set(gid, { id: gid, name: gname || 'Group', items: [] });
        map.get(gid).items.push(e);
      });
      return Array.from(map.values());
    },
  },
  methods: {
    formatLocalDateTime(dateStr, timeStr) {
      if (!dateStr) return '';

      try {
        const { year, month, day } = this.parseDateString(dateStr);
        const { hour, minute, second } = this.parseTimeString(timeStr);

        const dt = new Date(year, month, day, hour, minute, second);
        if (isNaN(dt.getTime())) {
          return dateStr + (timeStr ? ' ' + timeStr : '');
        }

        return this.formatDisplayTime(dt);
      } catch {
        return dateStr + (timeStr ? ' ' + timeStr : '');
      }
    },

    parseDateString(dateStr) {
      const dpart = (dateStr || '').toString().trim();
      const dmatch = dpart.match(/^(\d{4})-(\d{2})-(\d{2})$/);

      if (dmatch) {
        return {
          year: Number(dmatch[1]),
          month: Number(dmatch[2]) - 1,
          day: Number(dmatch[3]),
        };
      }

      const alt = new Date(dpart);
      if (isNaN(alt.getTime())) {
        throw new Error('Invalid date');
      }

      return {
        year: alt.getFullYear(),
        month: alt.getMonth(),
        day: alt.getDate(),
      };
    },

    parseTimeString(timeStr) {
      const tpart = (timeStr || '').toString().trim();
      const tmatch = tpart.match(/^(\d{2}):(\d{2})(?::(\d{2}))?$/);

      if (tmatch) {
        return {
          hour: Number(tmatch[1]),
          minute: Number(tmatch[2]),
          second: tmatch[3] ? Number(tmatch[3]) : 0,
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
      hr = hr % 12;
      hr = hr || 12;
      return `${dayNum} ${mon},${yr} ${hr}:${min} ${ampm}`;
    },
  },
};
</script>
