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
                  placeholder="recipient@example.com"
                />
              </div>
              <div class="send-agreement-field">
                <FormLabel>Subject</FormLabel>
                <FormInput
                  v-model="subject"
                  type="text"
                  placeholder="Type subject"
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

// Plugins
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

      tinymceConfigSelfHosted: {
        height: 500,
        base_url: '/tinymce',
        suffix: '.min',
        skin_url: '/tinymce/skins/ui/oxide',
        content_css: '/tinymce/skins/content/default/content.css',
        menubar: 'edit view insert format tools table help',
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
        toolbar:
          'undo redo | formatselect | bold italic underline strikethrough | ' +
          'alignleft aligncenter alignright alignjustify | ' +
          'bullist numlist outdent indent | link image table | ' +
          'code preview fullscreen | help',
        valid_elements: '*[*]',
        cleanup: false,
        convert_urls: false,
        remove_script_host: false,
        relative_urls: false,
        block_formats:
          'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6; Preformatted=pre',
        branding: false,
        statusbar: false,
        elementpath: false,
        resize: 'both',
        promotion: false,
        content_style:
          'body { font-family: Arial, sans-serif; font-size: 14px; margin: 20px; }',
        license_key: 'gpl',
      },
    };
  },
  mounted() {
    const leadId = this.$route.params.id || this.$route.query.lead_id || null;
    this.leadId = leadId;

    if (leadId) {
      this.loadInitialLeadData(leadId);
    } else {
      // If no lead ID, fetch a generic template
      this.fetchServerTemplate();
    }
  },
  watch: {
    to(newEmail, oldEmail) {
      if (newEmail && newEmail !== oldEmail && !this.leadId) {
        this.fetchServerTemplate();
      }
    },
  },
  methods: {
    async loadInitialLeadData(leadId) {
      try {
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const storage = require('@/services/storage').default;
        const token = storage.get('authToken');
        const res = await axios.get(`${API_BASE_URL}/api/leads/${leadId}`, {
          headers: token ? { Authorization: `Bearer ${token}` } : {},
        });

        const leadObj = res.data?.lead;
        if (leadObj) {
          this.to = leadObj.email || '';
          this.recipientName = `${leadObj.first_name || ''} ${
            leadObj.last_name || ''
          }`.trim();
          // Now fetch the template with the lead's data
          this.fetchServerTemplate();
        }
      } catch (e) {
        console.error('Failed to load initial lead data:', e);
        this.templateContent = '<p>Error: Could not load lead data.</p>';
      }
    },

    async fetchServerTemplate() {
      try {
        const API_BASE_URL = process.env.VUE_APP_API_BASE_URL;
        const name =
          this.recipientName ||
          this.to.substring(0, this.to.indexOf('@')) ||
          '';

        // Use a magic-login preview URL so the template contains a usable
        // link that will be replaced server-side with a one-time token.
        // The backend expects query params: email, lead_id, price_id, guest_token
        const frontendBase = 'http://127.0.0.1:8080';
        const previewMagicLink = `${frontendBase}/magic-login-and-redirect?token=PREVIEW_TOKEN&email=${encodeURIComponent(
          name || ''
        )}&lead_id=${this.leadId || ''}&price_id=`;
        const params = {
          // backend will replace {{magic_link}} placeholder with real link
          checkout_url: previewMagicLink,
          name: name,
        };

        const res = await axios.get(
          `${API_BASE_URL}/api/email-template/lead-agreement`,
          { params }
        );
        let html = res?.data ? String(res.data) : '';

        // Extract only the body content for the editor
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

    async handleSendAgreement() {
      if (this.sending) return;
      this.sending = true;
      try {
        const name =
          this.recipientName ||
          this.to.substring(0, this.to.indexOf('@')) ||
          '';

        // Ensure the template contains the real checkout link instead of placeholders
        // Replace href="#" or href="#0" placeholders inserted by the editor with the placeholder.
        // Use a function replacement to preserve the surrounding quote character.
        const bodyWithLinks = String(this.templateContent).replace(
          /href=(['"])#(?:0)?\1/g,
          (match, quote) => {
            return `href=${quote}{{magic_link}}${quote}`;
          }
        );

        const payload = {
          to: this.to,
          subject: this.subject,
          body: bodyWithLinks,
          name: name,
          // include lead id so backend can attach token to the correct user/lead
          checkout_url: '{{magic_link}}',
        };

        if (this.leadId) payload.lead_id = this.leadId;

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
        let detail = 'Failed to send agreement email.';
        if (error?.response?.data?.error) {
          detail += ` ${error.response.data.error}`;
        } else if (error?.message) {
          detail += ` ${error.message}`;
        } else {
          throw error;
        }
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
    onTinyMCEInit(event, editor) {
      console.log('TinyMCE initialized:', editor);
    },
  },
};
</script>

<style scoped>
/* same CSS as your original */
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
  background: #fff;
  border-radius: 24px;
  border: 1px solid #ebebeb;
  box-shadow: 0 2px 16px 0 rgba(33, 150, 243, 0.04);
  margin: 0 auto;
  padding: 32px;
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
}
.send-agreement-title {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 8px;
  text-align: left;
  color: #222;
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
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.send-agreement-link-actions-row {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 24px;
}
</style>
