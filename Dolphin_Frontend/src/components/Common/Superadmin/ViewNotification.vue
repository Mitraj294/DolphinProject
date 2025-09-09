<template>
  <div
    v-if="showDetailModal && selectedNotification"
    class="modal-overlay"
    @click.self="closeDetail"
  >
    <div class="modal-card">
      <button
        class="modal-close-btn"
        @click="closeDetail"
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
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
