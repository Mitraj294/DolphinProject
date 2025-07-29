<template>
  <MainLayout
    :navbarTitle="orgName"
    sidebarActive="organization"
  >
    <div class="page">
      <div class="org-detail-outer">
        <div class="org-detail-main-card">
          <div class="org-detail-main-card-header">
            <button
              class="btn btn-primary"
              @click="$router.push(`/organizations/${orgName}/edit`)"
            >
              <img
                src="@/assets/images/EditWhite.svg"
                alt="Edit"
                class="org-edit-icon"
              />
              Edit Details
            </button>
          </div>
          <div class="org-detail-main-cols">
            <!-- Main details: Organization and Admin, side by side -->
            <div
              class="org-detail-main-cols-group org-detail-main-cols-group--row"
            >
              <div class="org-detail-col org-detail-col-left">
                <h3 class="org-detail-section-title">Organization Detail</h3>
                <div class="org-detail-list-card org-detail-list-card--box">
                  <div class="org-detail-list-row">
                    <span>Organization Name</span><b>{{ orgName }}</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Organization Size</span><b>250+ Employees (Large)</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Contract Start</span><b>Jun 18, 2024</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Contract End</span><b>Jun 18, 2025</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Source</span><b>Google</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Address</span>
                    <b>153 Maggie Loop<br />Pottsville, Arkansas(AR), 72858</b>
                  </div>
                </div>
              </div>
              <div class="org-detail-col org-detail-col-right">
                <h3 class="org-detail-section-title">Admin Detail</h3>
                <div class="org-detail-list-card org-detail-list-card--box">
                  <div class="org-detail-list-row">
                    <span>Main Contact</span><b>Aaliyah Moss</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Admin Email</span><b>aaliyah@dolphin.org</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Admin Phone #</span><b>313-586-7462</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Sales Person</span><b>John</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Last Contacted</span><b>Dec 15, 2024</b>
                  </div>
                  <div class="org-detail-list-row">
                    <span>Certified Staff</span><b>2</b>
                  </div>
                </div>
              </div>
            </div>
            <!-- Lower boxes: Org Chart Type and Billing Status, side by side -->
            <div
              class="org-detail-main-cols-group org-detail-main-cols-group--row"
            >
              <div
                class="org-detail-box org-detail-box--half org-detail-box-flex"
              >
                <div class="org-detail-box-info">
                  <div class="org-detail-box-label">Org Chart Type</div>
                  <div class="org-detail-box-value">
                    Functional/Role - Based
                  </div>
                </div>
                <div class="org-detail-box-action">
                  <button class="org-view-btn custom-view-btn">
                    <img
                      src="@/assets/images/Chart.svg"
                      alt="View Chart"
                      class="org-view-btn-icon"
                    />
                    View Chart
                  </button>
                </div>
              </div>
              <div
                class="org-detail-box org-detail-box--half org-detail-box-flex"
              >
                <div class="org-detail-box-info">
                  <div class="org-detail-box-label">Billing Status</div>
                  <div class="org-detail-box-value">Standard ($2500/Year)</div>
                </div>
                <div class="org-detail-box-action">
                  <button
                    class="org-view-btn custom-view-btn"
                    @click="
                      $router.push({
                        name: 'BillingDetails',
                        query: { orgName },
                      })
                    "
                  >
                    <img
                      src="@/assets/images/Billing Status view details.svg"
                      alt="View Details"
                      class="org-view-btn-icon"
                    />
                    View Details
                  </button>
                </div>
              </div>
            </div>
            <!-- Current Algorithm row, left aligned, right empty for symmetry -->
            <div
              class="org-detail-main-cols-group org-detail-main-cols-group--row"
            >
              <div
                class="org-detail-box org-detail-box--half org-detail-box-algo org-detail-box-flex"
              >
                <div class="org-detail-box-info">
                  <div class="org-detail-box-label">Current Algorithm</div>
                </div>
                <div class="org-detail-box-action org-detail-box-action-algo">
                  <select class="org-algo-select">
                    <option>Dolphin 1.0</option>
                  </select>
                  <button class="org-algo-btn custom-view-btn">Assigned</button>
                </div>
              </div>
              <div
                class="org-detail-box org-detail-box--half org-detail-box-empty"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
export default {
  name: 'OrganizationDetail',
  components: { MainLayout },
  props: ['orgName'],
};
</script>

<style scoped>
.org-detail-outer {
  width: 100%;
  max-width: 1200px;
  min-width: 0;
  margin: 48px auto 48px auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  background: none !important;
  padding: 0 8px;
}

.org-detail-main-card {
  width: 100%;
  max-width: 1200px;
  min-width: 0;
  background: #fff;
  border-radius: 20px;
  border: 1px solid #ebebeb;
  box-sizing: border-box;
  overflow: visible;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 0 auto;
  padding: 24px 20px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  position: relative;
}

.org-detail-main-card-header {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 8px;
  min-height: 0;
}

.org-detail-main-cols {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
  justify-content: center;
  align-items: stretch;
  margin-bottom: 0;
  padding-left: 16px;
  padding-right: 16px;
  box-sizing: border-box;
}
.org-detail-main-cols-group {
  display: flex;
  flex-direction: row;
  gap: 20px;
  width: 100%;
  align-items: stretch;
}
.org-detail-main-cols-group--row {
  margin-top: 0;
  margin-bottom: 0;
}

.org-detail-col {
  flex: 1 1 0;
  min-width: 0;
  max-width: 100%;
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
  margin: 0;
  height: 100%;
}

.org-detail-section-title {
  font-family: 'Helvetica Neue LT Std', Helvetica, Arial, sans-serif;
  font-weight: 600;
  font-size: 20px;
  color: #222;
  margin-bottom: 18px;
  margin-top: 0;
  text-align: left;
  width: 100%;
}

.org-detail-list-card--box {
  border-radius: 16px;
  background: #f8f8f8;
  padding: 18px 24px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 17px;
  margin: 0;
  box-sizing: border-box;
  width: 100%;
  min-width: 0;
  max-width: 100%;
  min-height: 180px;
  justify-content: flex-start;
  height: 100%;
}

.org-detail-list-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0;
  flex-wrap: wrap;
  word-break: break-word;
  padding: 2px 0; /* Less vertical padding for compactness */
}

.org-detail-list-row span {
  color: #555; /* Darker shade */
  font-weight: 400;
  min-width: 160px;
  text-align: left;
  font-size: 19px; /* Increased font size */
  font-family: 'Inter', Arial, sans-serif;
  line-height: 1.7;
  letter-spacing: 0.01em;
  flex: 1 1 50%;
}

.org-detail-list-row b {
  color: #222;
  font-weight: 600;
  text-align: left; /* Add this */
  word-break: break-word;
  font-size: 17px; /* Increased font size */
  font-family: 'Inter', Arial, sans-serif;
  line-height: 1.7;
  letter-spacing: 0.01em;
  flex: 1 1 50%;
  justify-content: flex-start; /* Change from flex-end to flex-start */
  display: flex;
}

.org-edit-btn {
  border-radius: 29.01px;
  background: #0164a5;
  color: #fff;
  border: none;
  padding: 8px 24px 8px 16px;
  font-size: 15px;
  font-family: 'Helvetica Neue LT Std', Helvetica, Arial, sans-serif;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.2s, border 0.2s;
  box-shadow: none;
}
.org-edit-btn:hover {
  background: #005fa3;
}

.org-edit-icon {
  width: 18px;
  height: 18px;
  margin-right: 6px;
  display: inline-block;
  vertical-align: middle;
}

.org-detail-row {
  display: flex;
  gap: 0;
  margin-bottom: 0;
  justify-content: flex-start;
  width: 100%;
  flex-wrap: wrap;
}

.org-detail-row--split {
  display: flex;
  gap: 32px; /* Add gap for visible space between columns */
  width: 100%;
  margin: 16px 0 0 0;
  justify-content: center;
  align-items: stretch;
}

.org-detail-box {
  border-radius: 20px;
  background: #fafafa;
  padding: 24px 44px;
  min-width: 0;
  width: 100%;
  box-sizing: border-box;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  position: relative;
}

.org-detail-box--half,
.org-detail-box-algo {
  flex: 1 1 0;
  min-width: 0;
  max-width: 100%;
  width: 100%;
  background: #fafafa;
  border-radius: 20px;
  padding: 24px 32px;
  box-sizing: border-box;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
}

.org-detail-box--half.org-detail-box-flex,
.org-detail-box--half.org-detail-box-algo.org-detail-box-flex {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.org-detail-box-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
}

.org-detail-box-action {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  min-width: 140px;
}
.org-detail-box-action-algo {
  gap: 16px;
}

.org-detail-box-empty {
  background: transparent;
  border: none;
  box-shadow: none;
  pointer-events: none;
}

.org-detail-box-label {
  color: #888;
  font-size: 16px;
  font-weight: 400;
  margin-bottom: 2px;
  margin-top: 0;
}

.org-detail-box-value {
  color: #222;
  font-weight: 700;
  font-size: 20px;
  margin-bottom: 16px;
  margin-top: 2px;
  letter-spacing: 0.01em;
  font-family: 'Inter', Arial, sans-serif;
}

.org-detail-algo-row {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-top: 8px;
  flex-wrap: wrap;
  width: 100%;
  justify-content: flex-start;
}

/* Button and select tweaks for visual match */
.org-view-btn,
.custom-view-btn,
.org-algo-btn {
  border-radius: 999px;
  background: #fff;
  border: 1px solid #e0e0e0;
  color: #0164a5;
  font-weight: 500;
  font-size: 15px;
  padding: 7px 22px 7px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: 0;
  margin-top: 0;
  box-shadow: none;
  transition: background 0.18s, border 0.18s;
}

.org-view-btn:hover,
.custom-view-btn:hover,
.org-algo-btn:hover {
  background: #f5faff;
  border: 1px solid #bcbcbc;
}

.org-view-btn-icon {
  width: 18px;
  height: 18px;
  margin-right: 6px;
  display: inline-block;
  vertical-align: middle;
}

.org-algo-select {
  border-radius: 8px;
  border: 1.5px solid #e0e0e0;
  font-size: 15px;
  padding: 7px 32px 7px 12px;
  color: #222;
  outline: none;
  background: #fff
    url('data:image/svg+xml;utf8,<svg fill="%23888" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7.293 7.293a1 1 0 011.414 0L10 8.586l1.293-1.293a1 1 0 111.414 1.414l-2 2a1 1 0 01-1.414 0l-2-2a1 1 0 010-1.414z"/></svg>')
    no-repeat right 10px center/18px 18px;
  appearance: none;
  min-width: 120px;
}

/* Responsive styles to match other pages */
@media (max-width: 1400px) {
  .org-detail-outer {
    margin: 12px;
    max-width: 100%;
    padding: 0 4px;
  }
  .org-detail-main-card {
    max-width: 100%;
    border-radius: 14px;
    padding: 12px 4px 8px 4px;
  }
  .org-detail-main-cols {
    gap: 12px;
    padding-left: 6px;
    padding-right: 6px;
  }
  .org-detail-list-card--box {
    padding: 12px 10px;
  }
  .org-detail-main-cols-group {
    flex-direction: column;
    gap: 12px;
  }
}

@media (max-width: 900px) {
  .org-detail-outer {
    margin: 4px;
    max-width: 100%;
    padding: 0 2px;
  }
  .org-detail-main-card {
    padding: 4px 0 4px 0;
    border-radius: 8px;
  }
  .org-detail-main-cols {
    gap: 6px;
    padding-left: 2px;
    padding-right: 2px;
  }
  .org-detail-main-cols-group {
    flex-direction: column;
    gap: 6px;
  }
  .org-detail-col {
    padding-left: 12px;
    padding-right: 12px;
    box-sizing: border-box;
  }
  .org-detail-list-card--box {
    padding: 8px 8px;
    border-radius: 8px;
    font-size: 15px;
    min-height: 120px;
  }
}
</style>
