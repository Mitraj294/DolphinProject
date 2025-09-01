<template>
  <MainLayout>
    <div class="page">
      <div class="lead-capture-outer">
        <div class="lead-capture-card">
          <h3 class="lead-capture-card-title">Add Permission</h3>
          <form
            class="lead-capture-form"
            @submit.prevent="handleAddPermission"
          >
            <FormRow>
              <div>
                <FormLabel>Organization Name</FormLabel>
                <FormDropdown
                  v-model="form.organization"
                  icon="fas fa-building"
                >
                  <option
                    disabled
                    value=""
                  >
                    Select
                  </option>
                  <option
                    v-for="org in organizations"
                    :key="org"
                    :value="org"
                  >
                    {{ org }}
                  </option>
                </FormDropdown>
              </div>
              <div>
                <FormLabel>Email</FormLabel>
                <FormDropdown
                  v-model="form.email"
                  icon="fas fa-envelope"
                >
                  <option
                    disabled
                    value=""
                  >
                    Select
                  </option>
                  <option
                    v-for="mail in emails"
                    :key="mail"
                    :value="mail"
                  >
                    {{ mail }}
                  </option>
                </FormDropdown>
              </div>
              <div>
                <FormLabel>Role</FormLabel>
                <FormDropdown
                  v-model="form.role"
                  icon="fas fa-user-tag"
                >
                  <option
                    disabled
                    value=""
                  >
                    Select
                  </option>
                  <option
                    v-for="role in roles"
                    :key="role"
                    :value="role"
                  >
                    {{ role }}
                  </option>
                </FormDropdown>
              </div>
            </FormRow>
            <div class="lead-capture-actions">
              <button
                type="submit"
                class="btn btn-primary"
              >
                Add Permission
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
  FormBox,
  FormPassword,
} from '@/components/Common/Common_UI/Form';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
export default {
  name: 'AddPermission',
  components: {
    MainLayout,
    FormRow,
    FormLabel,
    FormInput,
    FormDropdown,
    FormBox,
    FormPassword,
    Toast,
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      form: {
        organization: '',
        email: '',
        role: '',
      },
      organizations: ['Dynamic Systems', 'Flexi-Finders', 'Testing'],
      emails: [
        'bob.b@example.com',
        'meetrajinho4@gmail.com',
        'aaliyah@dolphin.org',
        'clarence@dolphin.org',
      ],
      roles: ['Admin', 'Support', 'Sales'],
    };
  },
  methods: {
    handleAddPermission() {
      // Placeholder for add permission logic
      this.toast.add({
        severity: 'success',
        summary: 'Permission Added',
        detail: `Permission added for ${this.form.organization}, ${this.form.email}, ${this.form.role}`,
        life: 0,
      });
    },
  },
};
</script>

<style scoped>
.page {
  width: 100%;
  padding: 0 32px;
  box-sizing: border-box;
  overflow-x: hidden;
}
.lead-capture-outer {
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
.lead-capture-card {
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
.lead-capture-card-title {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 24px;
  text-align: left;
  width: 100%;
}
.lead-capture-form {
  width: 100%;
}
.lead-capture-actions {
  display: flex;
  justify-content: flex-end;
  gap: 18px;
}
@media (max-width: 1400px) {
  .page {
    padding: 0 12px;
  }
  .lead-capture-outer {
    margin: 12px auto;
    max-width: 100%;
  }
  .lead-capture-card {
    max-width: 100%;
    border-radius: 14px;
    padding: 18px 8px 12px 8px;
  }
}
@media (max-width: 900px) {
  .page {
    padding: 0 4px;
    max-width: 100vw;
    overflow-x: hidden;
  }
  .lead-capture-outer {
    margin: 4px auto;
    max-width: 100%;
  }
  .lead-capture-card {
    padding: 8px 2vw 8px 2vw;
    border-radius: 10px;
  }
}
</style>
