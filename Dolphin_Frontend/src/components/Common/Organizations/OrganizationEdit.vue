<template>
  <MainLayout>
    <div class="page">
      <div class="org-edit-outer">
        <div class="org-edit-card">
          <h2 class="org-edit-title">Edit Details</h2>
          <form
            class="org-edit-form"
            @submit.prevent="updateDetails"
          >
            <FormRow>
              <div>
                <FormLabel>Organization Name</FormLabel>
                <FormInput
                  v-model="form.orgName"
                  placeholder="Enter organization name"
                />
              </div>
              <div>
                <FormLabel>Organization Size</FormLabel>
                <FormDropdown v-model="form.orgSize">
                  <option
                    disabled
                    value=""
                  >
                    Select organization size
                  </option>
                  <option>250+ Employees (Large)</option>
                  <option>100-249 Employees (Medium)</option>
                  <option>1-99 Employees (Small)</option>
                </FormDropdown>
              </div>
              <div>
                <FormLabel>Source</FormLabel>
                <FormDropdown v-model="form.source">
                  <option
                    disabled
                    value=""
                  >
                    Select source
                  </option>
                  <option>Google</option>
                  <option>Friend</option>
                  <option>Other</option>
                </FormDropdown>
              </div>
            </FormRow>
            <FormRow>
              <div>
                <FormLabel>Country</FormLabel>
                <FormDropdown v-model="form.country">
                  <option
                    disabled
                    value=""
                  >
                    Select country
                  </option>
                  <option>India</option>
                  <option>United States</option>
                  <option>Canada</option>
                </FormDropdown>
              </div>
              <div>
                <FormLabel>State</FormLabel>
                <FormDropdown v-model="form.state">
                  <option
                    disabled
                    value=""
                  >
                    Select state
                  </option>
                  <option>Gujarat</option>
                  <option>UP</option>
                  <option>MP</option>
                </FormDropdown>
              </div>
              <div>
                <FormLabel>City</FormLabel>
                <FormInput
                  v-model="form.city"
                  placeholder="Enter city"
                />
              </div>
            </FormRow>
            <FormRow>
              <div>
                <FormLabel>Address</FormLabel>
                <FormInput
                  v-model="form.address1"
                  placeholder="Enter address"
                />
              </div>

              <div>
                <FormLabel>PIN</FormLabel>
                <FormInput
                  v-model="form.zip"
                  placeholder="Enter PIN code"
                />
              </div>
              <div><FormLabel>&nbsp;</FormLabel></div>
            </FormRow>

            <!-- Contract dates row: use 3 columns, last is empty -->
            <FormRow>
              <div>
                <FormLabel>Contract Start</FormLabel>
                <FormInput
                  v-model="form.contractStart"
                  disabled
                />
              </div>
              <div>
                <FormLabel>Contract End</FormLabel>
                <FormInput
                  v-model="form.contractEnd"
                  disabled
                />
              </div>
              <div><FormLabel>&nbsp;</FormLabel></div>
            </FormRow>
            <!-- Divider line -->
            <div class="org-edit-divider"></div>
            <!-- Admin/contact section: 2 rows of 3 fields -->
            <FormRow>
              <div>
                <FormLabel>Main Contact</FormLabel>
                <FormInput
                  v-model="form.mainContact"
                  placeholder="Enter main contact name"
                />
              </div>
              <div>
                <FormLabel>Admin Email</FormLabel>
                <FormInput
                  v-model="form.adminEmail"
                  type="email"
                  disabled
                  placeholder="Admin email"
                />
              </div>
              <div>
                <FormLabel>Admin Phone</FormLabel>
                <FormInput
                  v-model="form.adminPhone"
                  disabled
                  placeholder="Admin phone"
                />
              </div>
            </FormRow>
            <FormRow>
              <div>
                <FormLabel>Sales Person</FormLabel>
                <FormInput
                  v-model="form.salesPerson"
                  placeholder="Enter sales person name"
                />
              </div>
              <div>
                <FormLabel>Last Contacted</FormLabel>
                <FormInput
                  v-model="form.lastContacted"
                  disabled
                  placeholder="Last contacted date"
                />
              </div>
              <div>
                <FormLabel>Certified Staff</FormLabel>
                <FormInput
                  v-model="form.certifiedStaff"
                  type="number"
                  placeholder="Enter number of certified staff"
                />
              </div>
            </FormRow>
            <div class="org-edit-actions">
              <button
                type="button"
                class="org-edit-cancel"
                @click="cancelEdit"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="org-edit-update"
              >
                Update Details
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '@/components/layout/MainLayout.vue';
import {
  FormRow,
  FormLabel,
  FormInput,
  FormDropdown,
} from '@/components/Common/Common_UI/Form';
import axios from 'axios';
import storage from '@/services/storage.js';

export default {
  name: 'OrganizationEdit',
  components: { MainLayout, FormRow, FormLabel, FormInput, FormDropdown },
  data() {
    return {
      form: {
        orgName: '',
        orgSize: '',
        source: '',
        address1: '',
        address2: '',
        city: '',
        state: '',
        zip: '',
        country: '',
        contractStart: '',
        contractEnd: '',
        mainContact: '',
        adminEmail: '',
        adminPhone: '',
        salesPerson: '',
        lastContacted: '',
        certifiedStaff: '',
      },
    };
  },
  mounted() {
    this.fetchOrganization();
  },
  methods: {
    async fetchOrganization() {
      try {
        const authToken = storage.get('authToken');
        const headers = authToken
          ? { Authorization: `Bearer ${authToken}` }
          : {};
        // Get user_id from route or storage (assume orgName param is user_id for this patch)
        const userId = this.$route.params.orgName;
        let res = await axios.get('http://127.0.0.1:8000/api/organizations', {
          headers,
        });
        if (res.data && Array.isArray(res.data)) {
          // Find by user_id instead of org_name
          const found = res.data.find(
            (o) => String(o.user_id) === String(userId)
          );
          if (found) {
            this.orgId = found.id; // Save org id for update
            this.form = {
              orgName: found.org_name || '',
              orgSize: found.org_size || '',
              source: found.source || '',
              address1: found.address1 || '',
              address2: found.address2 || '',
              city: found.city || '',
              state: found.state || '',
              zip: found.zip || '',
              country: found.country || '',
              contractStart: found.contract_start
                ? new Date(found.contract_start).toLocaleDateString()
                : '',
              contractEnd: found.contract_end
                ? new Date(found.contract_end).toLocaleDateString()
                : '',
              mainContact: found.main_contact || '',
              adminEmail: found.admin_email || '',
              adminPhone: found.admin_phone || '',
              salesPerson: found.sales_person || '',
              lastContacted: found.last_contacted || '',
              certifiedStaff: found.certified_staff || '',
            };
          }
        }
      } catch (e) {
        // fallback: leave form as is
      }
    },
    cancelEdit() {
      const id =
        this.orgId || this.$route.params.id || this.$route.params.orgName;
      this.$router.push(`/organizations/${id}`);
    },
    async updateDetails() {
      // Send PUT request to update organization
      try {
        const authToken = storage.get('authToken');
        const headers = authToken
          ? { Authorization: `Bearer ${authToken}` }
          : {};
        // Find org id
        const orgId = this.orgId;
        if (!orgId) {
          alert('Organization not found.');
          return;
        }
        // Prepare payload (convert camelCase to snake_case for backend)
        const payload = {
          org_name: this.form.orgName,
          size: this.form.orgSize,
          source: this.form.source,
          address1: this.form.address1,
          address2: this.form.address2,
          city: this.form.city,
          state: this.form.state,
          zip: this.form.zip,
          country: this.form.country,
          main_contact: this.form.mainContact,
          admin_email: this.form.adminEmail,
          admin_phone: this.form.adminPhone,
          sales_person: this.form.salesPerson,
          certified_staff: this.form.certifiedStaff,
        };
        await axios.put(
          `http://127.0.0.1:8000/api/organizations/${orgId}`,
          payload,
          { headers }
        );
        // Redirect to the organization detail by id (fallback to orgName param)
        const id =
          this.orgId || this.$route.params.id || this.$route.params.orgName;
        this.$router.push(`/organizations/${id}`);
      } catch (e) {
        alert('Failed to update organization.');
      }
    },
    data() {
      return {
        form: {
          orgName: '',
          orgSize: '',
          source: '',
          address1: '',
          address2: '',
          city: '',
          state: '',
          zip: '',
          country: '',
          contractStart: '',
          contractEnd: '',
          mainContact: '',
          adminEmail: '',
          adminPhone: '',
          salesPerson: '',
          lastContacted: '',
          certifiedStaff: '',
        },
        orgId: null, // Store org id for update
      };
    },
  },
};
</script>

<style scoped>
/* --- Layout and spacing to match OrganizationTable/Leads/Notifications --- */
.org-edit-outer {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
  margin: 64px auto 64px auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  background: none !important;
  padding: 0;
}

.org-edit-card {
  width: 100%;
  max-width: 1400px;
  min-width: 0;
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 0 auto;
  box-sizing: border-box;
  padding: 32px 32px 24px 32px;
  display: flex;
  flex-direction: column;
  gap: 32px;
  position: relative;
}

.org-edit-title {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 24px;
  text-align: left;
}

.org-edit-form {
  width: 100%;
}

.org-edit-grid {
  display: grid;
  gap: 18px 24px;
  margin-bottom: 0;
}
.org-edit-grid-3 {
  grid-template-columns: repeat(3, 1fr);
  margin-bottom: 18px;
}
.org-edit-grid-2 {
  grid-template-columns: repeat(2, 1fr);
  margin-bottom: 18px;
}

.org-edit-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.org-edit-field label {
  color: #888;
  font-size: 15px;
  font-weight: 400;
  text-align: left;
}

.org-edit-field input,
.org-edit-field select {
  background: #fff;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 15px;
  color: #222;
  outline: none;
  transition: border 0.2s;
}

.org-edit-field input:disabled {
  color: #bdbdbd;
  background: #f5f5f5;
}

.org-edit-field select {
  appearance: none;
  background: #fff
    url('data:image/svg+xml;utf8,<svg fill="%23888" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7.293 7.293a1 1 0 011.414 0L10 8.586l1.293-1.293a1 1 0 111.414 1.414l-2 2a1 1 0 01-1.414 0l-2-2a1 1 0 010-1.414z"/></svg>')
    no-repeat right 10px center/18px 18px;
}

.org-edit-field select option[disabled][value=''] {
  color: #888;
}

.org-edit-field select:invalid {
  color: #bdbdbd;
}

.org-edit-divider {
  width: 100%;
  border-bottom: 1.5px solid #e0e0e0;
  margin: 24px 0 24px 0;
}

.org-edit-actions {
  display: flex;
  justify-content: flex-end;
  gap: 18px;
}

.org-edit-cancel {
  background: #f5f5f5;
  color: #888;
  border: none;
  border-radius: 24px;
  padding: 10px 32px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.org-edit-cancel:hover {
  background: #e0e0e0;
}
.org-edit-update {
  background: #0074c2;
  color: #fff;
  border: none;
  border-radius: 24px;
  padding: 10px 32px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.org-edit-update:hover {
  background: #005fa3;
}

/* Responsive styles to match other pages */
@media (max-width: 1400px) {
  .org-edit-outer {
    margin: 12px;
    max-width: 100%;
  }
  .org-edit-card {
    max-width: 100%;
    border-radius: 14px;
    padding: 18px 8px 12px 8px;
  }
  .org-edit-grid {
    gap: 12px 8px;
  }
  .org-edit-grid-3,
  .org-edit-grid-2 {
    margin-bottom: 12px;
  }
  .org-edit-divider {
    margin: 16px 0 16px 0;
  }
}

@media (max-width: 900px) {
  .org-edit-outer {
    margin: 4px;
    max-width: 100%;
  }
  .org-edit-card {
    padding: 8px 2vw 8px 2vw;
    border-radius: 10px;
  }
  .org-edit-grid-3,
  .org-edit-grid-2 {
    grid-template-columns: 1fr;
    margin-bottom: 12px;
  }
  .org-edit-divider {
    margin: 12px 0 12px 0;
  }
}
</style>
