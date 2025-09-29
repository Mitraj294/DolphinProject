<template>
  <MainLayout>
    <div class="page">
      <div class="send-agreement-table-outer">
        <div class="send-agreement-table-card">
          <div class="send-agreement-table-header">
            <div class="send-agreement-title">Send Agreement/Payment Link</div>
          </div>

          <form
            class="send-agreement-form"
            @submit.prevent="handleSendAgreement"
          >
            <FormRow>
              <div class="send-agreement-field">
                <FormLabel>To</FormLabel>
                <FormInput
                  v-model="to"
                  type="email"
                  placeholder="meet@gmail.com"
                />
              </div>
              <div class="send-agreement-field">
                <FormLabel>Subject</FormLabel>
                <FormInput
                  v-model="subject"
                  type="text"
                  placeholder="Type here"
                />
              </div>
            </FormRow>

            <div class="send-agreement-label">Editable Template</div>

            <div class="send-agreement-template-box">
              <Editor
                v-model="templateContent"
                :init="tinymceConfigSelfHosted"
                @onInit="onTinyMCEInit"
              />
            </div>

            <div class="send-agreement-link-actions-row">
              <div class="send-agreement-actions">
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="sending"
                >
                  {{ sending ? 'Sending...' : 'Send Agreement' }}
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
  name: 'SendAgreement',
  components: { MainLayout, Editor, FormInput, FormRow, FormLabel },
  data() {
    return {
      leadId: null,
      to: '',
      recipientName: '',
      subject: 'Agreement and Payment Link',
      templateContent: '',
      sending: false,
      registrationLink: '',
      priceId: null,
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
    // Only store leadId for optional context; do not auto-prefill templates here.
    const leadId = this.$route.params.id || this.$route.query.lead_id || null;
    this.leadId = leadId;
    console.log('Lead ID (if provided):', leadId);
    if (leadId) {
      console.log('Loading initial lead data for ID:', leadId);
      this.loadInitialLeadData(leadId);
    }
  },
  // Keep a watcher so changing the 'to' field fetches the server template.
  watch: {
    to(newEmail, oldEmail) {
      if (newEmail && newEmail !== oldEmail) {
        this.fetchServerTemplate();
      }
    },
  },
  methods: {
    // Load lead and default template when leadId is present
    async loadInitialLeadData(leadId) {
      try {
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');
        const res = await axios.get(`${API_BASE_URL}/api/leads/${leadId}`, {
          headers: token ? { Authorization: `Bearer ${token}` } : {},
        });

        const leadObj = res.data?.lead;
        const leadDefaultTemplate = res.data?.defaultTemplate;

        if (leadObj) {
          this.leadId = leadObj.id || this.leadId;
          this.to = leadObj.email || this.to;
          this.recipientName = `${leadObj.first_name || ''} ${
            leadObj.last_name || ''
          }`.trim();
        }

        if (leadDefaultTemplate) {
          this.templateContent = String(leadDefaultTemplate);
        }

        // Fallback: if no default template available but we have an email, fetch server template
        if (!leadDefaultTemplate && this.to) {
          this.updateRegistrationLink();
          await this.fetchServerTemplate();
        }
      } catch (e) {
        console.error('Failed to load initial lead data for agreement:', e);
      }
    },

    updateRegistrationLink() {
      if (this.to) {
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
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
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
        console.error(
          'Failed to fetch server template for agreement:',
          e?.message || e
        );
      }
    },
    // Send the agreement email to the specified recipient.
    async handleSendAgreement() {
      if (this.sending) return;
      this.sending = true;
      try {
        const name = this.computeRecipientName();
        const payload = this.buildPayload(name);

        await axios.post(
          `${process.env.VUE_APP_API_BASE_URL}/api/leads/send-agreement`,
          payload
        );

        this.$toast.add({
          severity: 'success',
          summary: 'Agreement Sent',
          detail: 'Agreement/payment link sent successfully!',
          life: 3500,
        });
      } catch (error) {
        const detail = this.formatSendErrorDetail(error);
        console.error('Send Agreement Error:', error);
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

      if (this.priceId) payload.price_id = this.priceId;

      return payload;
    },

    formatSendErrorDetail(error) {
      let detail = 'Failed to send agreement email.';
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
.send-agreement-table-outer {
  width: 100%;

  min-width: 260px;

  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  background: none !important;
  padding: 0;
}
.send-agreement-table-card {
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
  .send-agreement-table-card {
    padding: 8px;
  }
}
.send-agreement-table-header {
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
.send-agreement-title {
  font-size: 22px;
  font-weight: 600;
  margin-top: 0;
  margin-bottom: 8px;
  text-align: left;
  color: #222;
}
.send-agreement-desc {
  font-size: 16px;
  color: #222;
  margin-bottom: 24px;
  text-align: left;
}
.send-agreement-form {
  width: 100%;
  min-width: 240px;
}
.send-agreement-row {
  display: flex;
  gap: 18px;
  margin-bottom: 18px;
}
.send-agreement-field {
  flex: 1 1 0;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.send-agreement-field label {
  color: #222;
  font-size: 15px;
  font-weight: 400;
  text-align: left;
}
.send-agreement-field input,
.send-agreement-field select {
  background: #fff;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 15px;
  color: #222;
  outline: none;
  transition: border 0.2s;
}
.send-agreement-label {
  font-size: 15px;
  color: #222;
  margin-bottom: 8px;
  margin-top: 18px;
  text-align: left;
}
.send-agreement-template-box {
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
.send-agreement-link-actions-row {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 0;
  margin-top: 0;
  width: 100%;
  justify-content: flex-end;
}
.send-agreement-actions {
  margin-left: 0;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

@media (max-width: 900px) {
  .send-agreement-row {
    flex-direction: column;
    gap: 18px;
  }
}
</style>
