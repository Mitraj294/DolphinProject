<template>
  <!-- Notification Detail Modal -->
  <div
    v-if="visible && selectedNotification"
    class="modal-overlay"
    @click.self="$emit('close')"
  >
    <div class="modal-card">
      <button
        class="modal-close-btn"
        @click="$emit('close')"
      >
        &times;
      </button>
      <div class="modal-title2">Notification Detail</div>
      <div class="modal-desc">Details for the selected notification.</div>
      <div
        class="modal-title2 schedule-header"
        style="font-size: 20px; font-weight: 450"
      >
        <div class="schedule-header-left">
          <div>
            <div
              class="schedule-assessment-name"
              style="
                display: inline-block;
                vertical-align: middle;
                max-width: 200px;
                transition: max-width 0.18s ease, white-space 0.18s ease;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
              "
            >
              {{ selectedNotification.body }}
            </div>
            -
            <div
              class="schedule-assessment-name"
              style="display: inline-block; vertical-align: middle"
            >
              {{
                formatLocalDateTime(
                  selectedNotification.sent_at ||
                    selectedNotification.created_at
                )
              }}
            </div>
          </div>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-table">
          <div class="detail-value">
            <!-- Render recipients as a compact table grouped by group -->
            <div class="recipient-table-wrap">
              <table
                class="recipient-table compact"
                v-if="recipientTableRows.length"
              >
                <thead>
                  <tr>
                    <th style="width: 20%">Category</th>
                    <th style="width: 30%">Name</th>
                    <th style="width: 30%">Email</th>
                    <th style="width: 20%">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(row, idx) in recipientTableRows"
                    :key="idx"
                  >
                    <td
                      class="group-cell"
                      v-if="row.showGroup"
                      :rowspan="row.rowspan"
                    >
                      {{ row.category || '' }}
                    </td>
                    <td>{{ row.name || '' }}</td>
                    <td>{{ row.email || '' }}</td>
                    <td>{{ row.status || '' }}</td>
                  </tr>
                </tbody>
              </table>

              <!-- Fallback: use simple recipient list if no structured rows available -->
              <div
                v-else
                class="recipient-list"
              >
                <div
                  v-for="(r, ri) in detailRecipients"
                  :key="ri"
                  class="recipient-row"
                >
                  <div class="recipient-info">
                    <div class="recipient-name">{{ r.name }}</div>
                    <div
                      class="recipient-email"
                      v-if="r.email"
                    >
                      {{ r.email }}
                    </div>
                  </div>
                  <div class="recipient-status">
                    <span
                      class="recipient-badge"
                      :class="{
                        read: r.status === 'delivered' || r.status === 'read',
                        unread: !(
                          r.status === 'delivered' || r.status === 'read'
                        ),
                      }"
                      >{{
                        r.status === 'delivered' || r.status === 'read'
                          ? 'Read'
                          : 'Yet to read'
                      }}</span
                    >
                  </div>
                </div>
                <div
                  v-if="detailRecipients.length === 0"
                  class="all-recipients"
                >
                  All
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
export default {
  name: 'NotificationDetail',
  emits: ['close'],
  props: {
    visible: {
      type: Boolean,
      default: false,
    },
    selectedNotification: {
      type: Object,
      default: null,
    },
    recipientTableRows: {
      type: Array,
      default: () => [],
    },
    detailRecipients: {
      type: Array,
      default: () => [],
    },
  },
  methods: {
    formatLocalDateTime(dt) {
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
};
</script>

<style scoped>
@import '@/assets/modelcssnotificationandassesment.css';

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.13);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-card {
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 4px 32px rgba(33, 150, 243, 0.08);
  padding: 36px 44px;
  max-width: 720px;
  width: 100%;
  box-sizing: border-box;
  position: relative;
}

.modal-close-btn {
  position: absolute;
  top: 18px;
  right: 18px;
  background: none;
  border: none;
  font-size: 28px;
  color: #888;
  cursor: pointer;
  z-index: 10;
}

.modal-title2 {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 12px;
  color: #222;
}

.modal-desc {
  margin-bottom: 12px;
  color: #000000;
}

.schedule-header {
  margin-bottom: 20px;
}

.schedule-header-left {
  display: flex;
  align-items: center;
}

.schedule-assessment-name {
  color: #222;
}

.detail-row {
  display: flex;
  flex-direction: column;
}

.detail-table {
  display: block;
  width: 100%;
  margin: 14px auto 0 auto;
}

.detail-value {
  color: #222;
}

.recipient-table-wrap {
  width: 100%;
  overflow: auto;
}

.recipient-table.compact {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.recipient-table.compact th,
.recipient-table.compact td {
  padding: 8px 10px;
  border-bottom: 1px solid #eee;
  text-align: left;
}

.recipient-table.compact .group-cell {
  font-weight: 600;
  vertical-align: top;
}

.recipient-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 340px;
  overflow: auto;
}

.recipient-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 10px;
  border-radius: 8px;
  background: transparent;
  border: none;
}

.recipient-info {
  display: flex;
  flex-direction: column;
}

.recipient-name {
  font-weight: 500;
  color: #222;
}

.recipient-email {
  font-size: 13px;
  color: #666;
}

.recipient-status {
  padding: 4px 8px;
  margin-right: 40px;
}

.recipient-badge {
  font-size: 14px;
  padding: 4px 8px;
  border-radius: 12px;
  color: #fff;
}

.recipient-badge.read {
  background: #16a34a; /* green */
}

.recipient-badge.unread {
  background: #dc2626; /* red */
}

.all-recipients {
  color: #222;
  text-align: center;
  padding: 20px;
  font-style: italic;
}

@media (max-width: 700px) {
  .modal-card {
    min-width: 0;
    max-width: calc(100vw - 32px);
    width: calc(98vw - 32px);
    padding: 20px 16px 20px 16px;
    border-radius: 14px;
    margin: 16px;
  }
}

@media (max-width: 500px) {
  .modal-card {
    min-width: 0;
    max-width: calc(100vw - 24px);
    width: calc(98vw - 24px);
    padding: 18px 12px 18px 12px;
    border-radius: 12px;
    margin: 12px;
  }

  .modal-title2 {
    font-size: 20px;
    margin-bottom: 18px;
  }

  .modal-close-btn {
    top: 10px;
    right: 12px;
    font-size: 26px;
  }
}
</style>
