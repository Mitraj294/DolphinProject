<template>
  <MainLayout>
    <div class="page">
      <div class="table-outer">
        <div class="table-card">
          <div class="billing-title">
            Billing Details (Current Subscription)
          </div>
          <div class="billing-plan-box">
            <!-- If there is a current plan, show its details -->
            <template v-if="currentPlan && Object.keys(currentPlan).length">
              <div>
                <div class="plan-name">
                  <span
                    v-if="
                      currentPlan &&
                      (Number(currentPlan.price) === 2500 ||
                        Number(currentPlan.amount) === 2500)
                    "
                    >Standard</span
                  >
                  <span
                    v-else-if="
                      currentPlan &&
                      (Number(currentPlan.price) === 250 ||
                        Number(currentPlan.amount) === 250)
                    "
                    >Basic</span
                  >
                  <span v-else>{{ currentPlan?.name || 'Plan' }}</span>
                </div>
                <div class="plan-price">
                  <span
                    v-if="
                      currentPlan &&
                      (Number(currentPlan.price) === 2500 ||
                        Number(currentPlan.amount) === 2500)
                    "
                    >$2500/Annual</span
                  >
                  <span
                    v-else-if="
                      currentPlan &&
                      (Number(currentPlan.price) === 250 ||
                        Number(currentPlan.amount) === 250)
                    "
                    >$250/Month</span
                  >
                  <span v-else>
                    {{
                      currentPlan?.price
                        ? `${currentPlan.price}`
                        : currentPlan?.amount
                        ? `${currentPlan.amount}`
                        : ''
                    }}
                  </span>
                </div>
              </div>
              <div class="plan-meta">
                <div>
                  Subscription Start :
                  <b>{{
                    currentPlan?.start ? formatDate(currentPlan.start) : 'N/A'
                  }}</b>
                </div>
                <div>
                  Subscription End :
                  <b>{{
                    currentPlan?.end ? formatDate(currentPlan.end) : 'N/A'
                  }}</b>
                </div>
                <div class="plan-next">
                  (Next bill on
                  {{
                    currentPlan?.nextBill
                      ? formatDate(currentPlan.nextBill)
                      : 'N/A'
                  }})
                </div>
              </div>
            </template>

            <!-- If no current plan but billing history exists, show last billing info -->
            <template
              v-else-if="!currentPlan || !Object.keys(currentPlan).length"
            >
              <template v-if="billingHistory && billingHistory.length">
                <div>
                  <div class="plan-name">No active plan</div>
                  <div class="plan-price">-</div>
                </div>
                <div class="plan-meta">
                  <div>
                    Last Subscription End :
                    <b>{{
                      lastBillingItem && lastBillingItem.subscriptionEnd
                        ? formatDate(lastBillingItem.subscriptionEnd)
                        : 'Unknown'
                    }}</b>
                  </div>
                  <div>
                    Last Payment :
                    <b>{{
                      lastBillingItem && lastBillingItem.paymentDate
                        ? formatDate(lastBillingItem.paymentDate)
                        : 'Unknown'
                    }}</b>
                  </div>
                  <div class="plan-next">
                    This plan expired — please renew to reactivate billing.
                  </div>
                </div>
              </template>

              <!-- Neither current plan nor history -->
              <template v-else>
                <div>
                  <div class="plan-name">No plan selected</div>
                  <div class="plan-price">—</div>
                </div>
                <div class="plan-meta">
                  <div>
                    Please select a subscription plan to enable features and
                    billing.
                  </div>
                </div>
              </template>
            </template>
          </div>
          <div class="billing-title">Billing History</div>
          <div class="table-container">
            <div class="table-scroll">
              <table class="table">
                <TableHeader
                  :columns="[
                    {
                      label: 'Payment Method',
                      key: 'paymentMethodType',
                      minWidth: '200px',
                    },
                    {
                      label: 'Payment Date',
                      key: 'paymentDate',
                      minWidth: '200px',
                      sortable: true,
                    },
                    {
                      label: 'Subscription End',
                      key: 'subscriptionEnd',
                      minWidth: '200px',
                      sortable: true,
                    },
                    { label: 'Amount', key: 'amount', minWidth: '200px', sortable: true },
                    { label: 'Download', key: 'invoice', minWidth: '200px' },
                    {
                      label: 'Description',
                      key: 'description',
                      minWidth: '200px',
                    },
                  ]"
                  :active-sort-key="activeSortKey"
                  :sort-asc="sortAsc"
                  @sort="handleSort"
                />
                <tbody>
                  <tr
                    v-for="(item, idx) in sortedBillingHistory"
                    :key="idx"
                  >
                    <td>
                      {{ item.payment_method }}
                    </td>

                    <td>
                      {{ item.paymentDate ? formatDate(item.paymentDate) : '' }}
                    </td>
                    <td>
                      {{
                        item.subscriptionEnd
                          ? formatDate(item.subscriptionEnd)
                          : ''
                      }}
                    </td>
                    <td>{{ item.amount ? `${item.amount}` : '' }}</td>
                    <td>
                      <a
                        v-if="item.pdfUrl"
                        :href="item.pdfUrl"
                        class="receipt-link"
                        target="_blank"
                        rel="noopener"
                        style="margin-left: 8px"
                      >
                        <svg
                          width="16"
                          height="16"
                          fill="none"
                          viewBox="0 0 24 24"
                          style="vertical-align: middle; margin-right: 4px"
                        >
                          <rect
                            x="3"
                            y="3"
                            width="18"
                            height="18"
                            rx="2"
                            stroke="#0074c2"
                            stroke-width="2"
                          />
                          <path
                            d="M7 7h10M7 11h10M7 15h6"
                            stroke="#0074c2"
                            stroke-width="2"
                          />
                        </svg>
                        View Receipt
                      </a>
                    </td>
                    <td>{{ item.description }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import TableHeader from '@/components/Common/Common_UI/TableHeader.vue';
import axios from 'axios';
import storage from '@/services/storage';

const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || '';

export default {
  name: 'BillingDetails',
  components: { MainLayout, TableHeader },
  data() {
    return {
      currentPlan: null,
      billingHistory: [],
      activeSortKey: 'paymentDate',
      sortAsc: false,
    };
  },
  computed: {
    lastBillingItem() {
      if (!this.billingHistory || !this.billingHistory.length) return null;
      // Prefer items with subscriptionEnd or paymentDate; pick the most recent by subscriptionEnd then paymentDate
      const sorted = [...this.billingHistory].sort((a, b) => {
        const ta = a.subscriptionEnd || a.paymentDate || '';
        const tb = b.subscriptionEnd || b.paymentDate || '';
        return new Date(tb) - new Date(ta);
      });
      return sorted[0] || null;
    },
    sortedBillingHistory() {
      if (!this.activeSortKey) return this.billingHistory;
      const sorted = [...this.billingHistory].sort((a, b) => {
        let valA = a[this.activeSortKey];
        let valB = b[this.activeSortKey];

        if (
          this.activeSortKey === 'paymentDate' ||
          this.activeSortKey === 'subscriptionEnd'
        ) {
          valA = valA ? new Date(valA) : 0;
          valB = valB ? new Date(valB) : 0;
        }

        if (valA < valB) return this.sortAsc ? -1 : 1;
        if (valA > valB) return this.sortAsc ? 1 : -1;
        return 0;
      });
      return sorted;
    },
  },
  methods: {
    handleSort(key) {
      if (this.activeSortKey === key) {
        this.sortAsc = !this.sortAsc;
      } else {
        this.activeSortKey = key;
        this.sortAsc = true;
      }
    },
    async fetchBillingDetails() {
      try {
        const authToken = storage.get('authToken');
        const headers = {};
        if (authToken) headers['Authorization'] = `Bearer ${authToken}`;
        // If orgId is supplied via query, request org-specific billing endpoints
        const orgId = this.$route.query.orgId || null;
        const planUrl = orgId
          ? `${API_BASE_URL}/api/billing/current?org_id=${orgId}`
          : `${API_BASE_URL}/api/billing/current`;
        // Fetch current plan
        const planRes = await axios.get(planUrl, { headers });
        this.currentPlan = planRes.data || null;
        // Fetch billing history
        const historyUrl = orgId
          ? `${API_BASE_URL}/api/billing/history?org_id=${orgId}`
          : `${API_BASE_URL}/api/billing/history`;
        const historyRes = await axios.get(historyUrl, { headers });
        this.billingHistory = Array.isArray(historyRes.data)
          ? historyRes.data
          : [];
      } catch (e) {
        console.error('Error fetching billing details:', e);
        this.currentPlan = null;
        this.billingHistory = [];
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      if (isNaN(d)) return '';
      return d.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      });
    },
  },
  mounted() {
    this.fetchBillingDetails();
  },
};
</script>

<style scoped>
.billing-title {
  font-size: 24px;
  font-weight: 600;
  margin: 24px;
  text-align: left;
}
.billing-plan-box {
  background: #f6f6f6;
  border-radius: 12px;
  padding: 24px 32px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;

  box-sizing: border-box;
  margin: 24px 16px 0 16px;
}
.plan-name {
  font-size: 22px;
  font-weight: 500;
  margin-bottom: 8px;
  text-align: left;
}
.plan-price {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 0;
}
.plan-meta {
  text-align: right;
  color: #888;
  font-size: 17px;
  font-weight: 400;
}
.plan-meta b {
  color: #222;
  font-weight: 500;
}
.plan-next {
  font-size: 18px;
  color: #888;
  margin-top: 4px;

  font-weight: 600;
}

.receipt-link {
  color: #0074c2;
  text-decoration: underline;
  font-size: 17px;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Responsive styles to match other pages */
@media (max-width: 1400px) {
  .billing-plan-box {
    padding: 18px 8px;
  }
}
@media (max-width: 900px) {
  .billing-plan-box {
    padding: 16px 8px;
    flex-direction: column;
    gap: 14px;
    align-items: center;
    justify-content: center;
    text-align: center;
  }
}
</style>
