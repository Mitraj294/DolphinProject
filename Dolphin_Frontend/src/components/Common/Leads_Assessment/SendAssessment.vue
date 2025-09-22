<template>
  <MainLayout>
    <div class="page">
      <div class="send-assessment-table-outer">
        <div class="send-assessment-table-card">
          <div class="send-assessment-table-header">
            <div class="send-assessment-title">Send Assessment</div>
          </div>

          <form
            class="send-assessment-form"
            @submit.prevent="handleSendAssessment"
          >
            <FormRow>
              <div class="send-assessment-field">
                <FormLabel>To</FormLabel>
                <FormInput
                  v-model="to"
                  type="email"
                  placeholder="meet@gmail.com"
                />
              </div>
              <div class="send-assessment-field">
                <FormLabel>Subject</FormLabel>
                <FormInput
                  v-model="subject"
                  type="text"
                  placeholder="Type here"
                />
              </div>
            </FormRow>

            <div class="send-assessment-label">Editable Template</div>

            <div class="send-assessment-template-box">
              <Editor
                v-model="templateContent"
                :init="tinymceConfigSelfHosted"
                @onInit="onTinyMCEInit"
              />
            </div>

            <div class="send-assessment-link-actions-row">
              <div class="send-assessment-actions">
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="sending"
                >
                  {{ sending ? 'Sending...' : 'Send Assessment' }}
                </button>
              </div>
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
  FormInput,
  FormRow,
  FormLabel,
} from '@/components/Common/Common_UI/Form';
import Editor from '@tinymce/tinymce-vue';
import axios from 'axios';

import 'tinymce/tinymce';
import 'tinymce/themes/silver';
import 'tinymce/icons/default';

import 'tinymce/models/dom';

// Import plugins
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/insertdatetime';
import 'tinymce/plugins/media';
import 'tinymce/plugins/table';
import 'tinymce/plugins/wordcount';
import 'tinymce/plugins/help';

export default {
  name: 'SendAssessment',
  components: { MainLayout, Editor, FormInput, FormRow, FormLabel },
  data() {
    return {
      leadId: null,
      to: '',
      recipientName: '',
      subject: 'Complete Your Registration',
      templateContent: '',
      sending: false,
      registrationLink: '',
      tinymceConfigSelfHosted: {
        height: 500,

        // Set the base URL for TinyMCE assets
        base_url: '/tinymce',
        suffix: '.min',

        // Skin configuration
        skin_url: '/tinymce/skins/ui/oxide',
        content_css: '/tinymce/skins/content/default/content.css',

        menubar: 'edit view insert format tools table help',

        // Only FREE plugins (no premium features)
        plugins: [
          'advlist',
          'autolink',
          'lists',
          'link',
          'image',
          'charmap',
          'preview',
          'anchor',
          'searchreplace',
          'visualblocks',
          'code',
          'fullscreen',
          'insertdatetime',
          'media',
          'table',
          'wordcount',
          'help',
        ],

        // Simplified toolbar with only free features
        toolbar:
          'undo redo | formatselect | ' +
          'bold italic underline strikethrough | ' +
          'alignleft aligncenter alignright alignjustify | ' +
          'bullist numlist outdent indent | ' +
          'link image table | ' +
          'code preview fullscreen | help',

        // Email template settings
        valid_elements: '*[*]',
        cleanup: false,
        convert_urls: false,
        remove_script_host: false,
        relative_urls: false,

        // Basic formatting (free features only)
        block_formats:
          'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6; Preformatted=pre',

        // UI settings
        branding: false,
        statusbar: false,
        elementpath: false,
        resize: 'both',
        promotion: false,

        content_style:
          'body { font-family: Arial, sans-serif; font-size: 14px; margin: 20px; }',

        // GPL license for self-hosted
        license_key: 'gpl',
      },
    };
  },
  // mounted() now only handles the initial trigger for loading data.
  mounted() {
    console.log('=== TINYMCE COMPONENT MOUNTED ===');
    const leadId = this.$route.params.id || this.$route.query.lead_id || null;
    // store leadId so generated registration links can include it and enable full prefill
    this.leadId = leadId;
    console.log('Lead ID from route:', leadId);

    if (leadId) {
      console.log('Loading initial lead data for ID:', leadId);
      this.loadInitialLeadData(leadId);
    }
  },
  watch: {
    to(newEmail, oldEmail) {
      console.log('=== TO EMAIL WATCHER ===');
      console.log('Email changed from:', oldEmail, 'to:', newEmail);
      if (newEmail && newEmail !== oldEmail) {
        console.log('Fetching server template for new email');
        this.fetchServerTemplate();
      }
    },
  },
  methods: {
    // The main logic for loading the initial lead from the URL.
    async loadInitialLeadData(leadId) {
      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');
        const res = await axios.get(`${API_BASE_URL}/api/leads/${leadId}`, {
          headers: token ? { Authorization: `Bearer ${token}` } : {},
        });

        const leadObj = res.data?.lead;
        const leadDefaultTemplate = res.data?.defaultTemplate;

        console.log('=== LEAD DATA LOADED ===');
        console.log('Lead object:', leadObj);
        console.log('Default template exists:', !!leadDefaultTemplate);

        if (leadObj && leadDefaultTemplate) {
          // keep lead id for registration link prefill
          this.leadId = leadObj.id || this.leadId;
          this.to = leadObj.email || '';
          this.recipientName = `${leadObj.first_name || ''} ${
            leadObj.last_name || ''
          }`.trim();

          this.templateContent = String(leadDefaultTemplate);
          console.log('Template content set for TinyMCE');
        }
      } catch (e) {
        console.error('Failed to load initial lead data:', e);
        this.templateContent = '<p>Error: Could not load lead data.</p>';
      }
    },

    updateRegistrationLink() {
      if (this.to) {
        // include lead_id when available so frontend register page can fetch full lead data
        const base = `${window.location.origin}/register`;
        const params = new URLSearchParams();
        params.set('email', this.to);
        if (this.leadId) params.set('lead_id', String(this.leadId));
        this.registrationLink = `${base}?${params.toString()}`;
      } else {
        this.registrationLink = '';
      }
    },

    async fetchServerTemplate() {
      if (!this.to) return;

      this.updateRegistrationLink();

      try {
        const API_BASE_URL =
          process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000';
        const params = {
          registration_link: this.registrationLink,
          name: this.recipientName,
        };

        const res = await axios.get(
          `${API_BASE_URL}/api/email-template/lead-registration`,
          { params }
        );
        let html = res?.data ? String(res.data) : '';

        // Parse the full HTML response to extract only the body content.
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const container = doc.querySelector('.email-container');
        if (container) html = container.innerHTML;

        this.templateContent = html;
      } catch (e) {
        console.error('Failed to fetch server template:', e?.message || e);
        this.templateContent =
          '<p>Error: Could not load the email template.</p>';
      }
    },

    async handleSendAssessment() {
      if (this.sending) return;
      this.sending = true;
      try {
        const name = this.computeRecipientName();
        const payload = this.buildPayload(name);

        await axios.post(
          `${
            process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000'
          }/api/leads/send-assessment`,
          payload
        );

        this.$toast.add({
          severity: 'success',
          summary: 'Assessment Sent',
          detail: 'Assessment email sent successfully!',
          life: 3500,
        });
      } catch (error) {
        const detail = this.formatSendErrorDetail(error);
        console.error('Send Assessment Error:', error);
        this.$toast.add({
          severity: 'error',
          summary: 'Send Error',
          detail,
          life: 3500,
        });
      } finally {
        this.sending = false;
      }
    },

    computeRecipientName() {
      return (
        this.recipientName ||
        (this.$route.params &&
          (this.$route.params.contact || this.$route.params.name)) ||
        this.$route.query.contact ||
        this.$route.query.name ||
        ''
      );
    },

    buildPayload(name) {
      const payload = {
        to: this.to,
        subject: this.subject,
        body: this.templateContent,
        registration_link: this.registrationLink,
        name,
      };

      const leadId =
        (this.$route.params && this.$route.params.id) ||
        (this.$route.query && this.$route.query.lead_id);
      if (leadId) payload.lead_id = leadId;

      return payload;
    },

    formatSendErrorDetail(error) {
      let detail = 'Failed to send assessment email.';
      if (error?.response?.data) {
        const data = error.response.data;
        if (typeof data === 'string') {
          detail += ` ${data}`;
        } else if (data.error) {
          detail += ` ${data.error}`;
        } else if (data.message) {
          detail += ` ${data.message}`;
        } else {
          detail += ` ${JSON.stringify(data)}`;
        }
      } else if (error?.message) {
        detail += ` ${error.message}`;
      } else {
        detail += ' An unknown error occurred.';
      }
      return detail;
    },

    onTinyMCEInit(event, editor) {
      console.log('TinyMCE initialized:', editor);
    },
  },
};
</script>

<style scoped>
.send-assessment-table-outer {
  width: 100%;

  min-width: 260px;

  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  background: none !important;
  padding: 0;
}
.send-assessment-table-card {
  width: 100%;

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

  position: relative;
}
@media (max-width: 600px) {
  .send-assessment-table-card {
    padding: 8px;
  }
}
.send-assessment-table-header {
  width: 100%;
  display: flex;
  align-items: center;
  padding: 0 0 18px 0;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  min-height: 0;
  box-sizing: border-box;
}
.send-assessment-title {
  font-size: 22px;
  font-weight: 600;
  margin-top: 0;
  margin-bottom: 8px;
  text-align: left;
  color: #222;
}
.send-assessment-desc {
  font-size: 16px;
  color: #222;
  margin-bottom: 24px;
  text-align: left;
}
.send-assessment-form {
  width: 100%;
  min-width: 240px;
}
.send-assessment-row {
  display: flex;
  gap: 18px;
  margin-bottom: 18px;
}
.send-assessment-field {
  flex: 1 1 0;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.send-assessment-field label {
  color: #222;
  font-size: 15px;
  font-weight: 400;
  text-align: left;
}
.send-assessment-field input,
.send-assessment-field select {
  background: #fff;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 15px;
  color: #222;
  outline: none;
  transition: border 0.2s;
}
.send-assessment-label {
  font-size: 15px;
  color: #222;
  margin-bottom: 8px;
  margin-top: 18px;
  text-align: left;
}
.send-assessment-template-box {
  background: #fafafa;
  border-radius: 12px;
  border: 1.5px solid #e0e0e0;
  box-shadow: 0 1px 8px 0 rgba(33, 150, 243, 0.06);
  padding: 18px;
  margin-bottom: 18px;
  min-height: 180px;
  height: auto;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 18px;
  overflow: hidden;
}
.send-assessment-link-actions-row {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 0;
  margin-top: 0;
  width: 100%;
  justify-content: flex-end;
}
.send-assessment-actions {
  margin-left: 0;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

@media (max-width: 900px) {
  .send-assessment-row {
    flex-direction: column;
    gap: 18px;
  }
}
</style>
