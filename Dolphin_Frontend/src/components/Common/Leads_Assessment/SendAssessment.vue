<template>
  <MainLayout>
    <div class="page">
      <div class="send-assessment-table-outer">
        <div class="send-assessment-table-card">
          <div class="send-assessment-table-header">
            <div class="send-assessment-title">Send Assessment</div>
          </div>
          <div
            class="send-assessment-desc"
            style="margin-bottom: 18px"
          >
            Lorem Ipsum is simply dummy text of the printing and typesetting
            industry.
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
                v-if="editorReady"
                v-model="templateContent"
                :key="editorKey"
                editorStyle="height: 420px"
                :modules="editorModules"
                @load="onEditorLoad"
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
import Editor from 'primevue/editor';
import axios from 'axios';

export default {
  name: 'SendAssessment',
  components: { MainLayout, Editor, FormInput, FormRow, FormLabel },
  data() {
    return {
      to: '',
      recipientName: '',
      subject: 'Complete Your Registration',
      templateContent: '',
      pendingTemplate: '', // Store template until editor is ready
      sending: false,
      registrationLink: '',
      editorReady: false,
      quillInstance: null,
      // This key is crucial. Changing it will force the editor to re-mount completely.
      editorKey: 0,
      editorModules: {
        toolbar: {
          container: [
            [{ header: [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ color: [] }, { background: [] }],
            [{ align: [] }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote', 'code-block'],
            ['link', 'image'],
            ['clean'],
          ],
          handlers: {
            // Custom handlers can be added here if needed
          },
        },
      },
    };
  },
  // mounted() now only handles the initial trigger for loading data.
  mounted() {
    console.log('=== COMPONENT MOUNTED ===');
    const leadId = this.$route.params.id || this.$route.query.lead_id || null;
    console.log('Lead ID from route:', leadId);

    if (leadId) {
      console.log('Loading initial lead data for ID:', leadId);
      this.loadInitialLeadData(leadId);
    } else {
      console.log('No lead ID, showing blank editor');
      // If no lead is being loaded, just show a blank editor.
      this.editorReady = true;
    }
  },
  watch: {
    templateContent: {
      handler(newValue, oldValue) {
        console.log('=== TEMPLATE CONTENT WATCHER ===');
        console.log('Old value length:', oldValue?.length || 0);
        console.log('New value length:', newValue?.length || 0);
        console.log('New value preview:', newValue?.substring(0, 100));
        console.log('Quill instance exists:', !!this.quillInstance);
      },
      immediate: true,
    },
    to(newEmail, oldEmail) {
      console.log('=== TO EMAIL WATCHER ===');
      console.log('Email changed from:', oldEmail, 'to:', newEmail);
      // Only fetch a new template if the email has actually changed to a new value.
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
        console.log('Template length:', leadDefaultTemplate?.length);
        console.log(
          'Template preview:',
          leadDefaultTemplate?.substring(0, 100)
        );

        if (leadObj && leadDefaultTemplate) {
          this.to = leadObj.email || '';
          this.recipientName = `${leadObj.first_name || ''} ${
            leadObj.last_name || ''
          }`.trim();

          // DEBUGGING STEP: Check the browser console to ensure this log appears and the HTML looks correct.
          console.log(
            'Final HTML template being passed to the editor:',
            leadDefaultTemplate
          );

          // Store the template to load when editor is ready
          this.pendingTemplate = String(leadDefaultTemplate);
          console.log('=== PENDING TEMPLATE SET ===');
          console.log('Pending template length:', this.pendingTemplate.length);
          console.log('Editor ready status before:', this.editorReady);

          // Make editor ready
          this.editorReady = true;
          this.editorKey += 1;
          console.log('=== EDITOR MOUNTING ===');
          console.log('Editor ready set to:', this.editorReady);
          console.log('Editor key incremented to:', this.editorKey);
        } else {
          // If the template is missing from the API for some reason, log it.
          console.error(
            'API response did not contain a lead object or the default template.'
          );
          this.editorReady = true; // Show the blank editor
        }
      } catch (e) {
        console.error('Failed to load initial lead data:', e);
        this.templateContent = '<p>Error: Could not load lead data.</p>';
        this.editorReady = true;
      }
    },

    updateRegistrationLink() {
      if (this.to) {
        this.registrationLink = `${
          window.location.origin
        }/register?email=${encodeURIComponent(this.to)}`;
      } else {
        this.registrationLink = '';
      }
    },

    // This function is now ONLY for fetching the GENERIC template when the user types a new email.
    async fetchServerTemplate() {
      if (!this.to) return;

      this.editorReady = false;
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
      } finally {
        this.editorKey += 1;
        this.$nextTick(() => {
          this.editorReady = true;
        });
      }
    },

    async handleSendAssessment() {
      if (this.sending) return;
      this.sending = true;
      try {
        const name =
          this.recipientName ||
          (this.$route.params &&
            (this.$route.params.contact || this.$route.params.name)) ||
          this.$route.query.contact ||
          this.$route.query.name ||
          '';

        const payload = {
          to: this.to,
          subject: this.subject,
          body: this.templateContent,
          registration_link: this.registrationLink,
          name: name,
        };

        const leadId =
          (this.$route.params && this.$route.params.id) ||
          (this.$route.query && this.$route.query.lead_id);
        if (leadId) payload.lead_id = leadId;

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
        let detail = 'Failed to send assessment email.';
        if (error?.response?.data) {
          const data = error.response.data;
          if (typeof data === 'string') {
            detail += ` ${data}`;
          } else if (data.error) {
            detail += ` ${data.error}`;
          } else if (data.message) {
            detail += ` ${data.message}`;
          }
        } else if (error?.message) {
          detail += ` ${error.message}`;
        }
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

    onEditorLoad(event) {
      console.log('=== EDITOR LOAD EVENT FIRED ===');
      console.log('Editor loaded, Quill instance:', event.instance);
      console.log('Event object:', event);
      console.log('Pending template exists:', !!this.pendingTemplate);
      console.log('Pending template length:', this.pendingTemplate?.length);
      console.log('Current templateContent:', this.templateContent);

      this.quillInstance = event.instance;

      // If we have pending template content, set it now
      if (this.pendingTemplate) {
        console.log('=== SETTING TEMPLATE CONTENT ===');
        console.log('About to set template content');
        console.log('Before - templateContent:', this.templateContent);

        // Store the template for later use
        const templateToSet = this.pendingTemplate;

        // Use multiple timeouts to ensure Quill is ready and prevent it from clearing content
        setTimeout(() => {
          try {
            console.log('Trying direct DOM manipulation method');
            const editorRoot = this.quillInstance.root;

            // First, disable Quill temporarily to prevent interference
            this.quillInstance.disable();

            // Set the content directly
            editorRoot.innerHTML = templateToSet;
            console.log('HTML set on root element');
            console.log(
              'Root innerHTML after setting:',
              editorRoot.innerHTML.substring(0, 200)
            );

            // Re-enable Quill
            this.quillInstance.enable();

            // Force Quill to update and recognize the content
            this.quillInstance.update();
            console.log('Quill update called');

            // Update the v-model after a small delay
            setTimeout(() => {
              this.templateContent = templateToSet;
              console.log('Template content updated via v-model');
              console.log(
                'V-model content length:',
                this.templateContent.length
              );

              // Final verification
              setTimeout(() => {
                console.log('FINAL VERIFICATION:');
                console.log(
                  'Quill root HTML:',
                  this.quillInstance.root.innerHTML.substring(0, 200)
                );
                console.log(
                  'Quill text length:',
                  this.quillInstance.getText().length
                );
                console.log('V-model length:', this.templateContent.length);
              }, 100);
            }, 50);

            console.log('Direct DOM method completed');
          } catch (error) {
            console.error('Error with direct DOM method:', error);

            // Fallback: Try using Quill's API with a different approach
            try {
              console.log('Trying fallback: setContents with plain text');
              const plainText = templateToSet
                .replace(/<br[^>]*>/gi, '\n')
                .replace(/<\/p>/gi, '\n\n')
                .replace(/<[^>]*>/g, '')
                .replace(/&amp;/g, '&')
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .trim();

              this.quillInstance.setText(plainText);
              this.templateContent = plainText;
              console.log('Plain text fallback completed');
            } catch (error2) {
              console.error('Error with fallback method:', error2);
              // Final v-model update
              this.templateContent = templateToSet;
            }
          }
        }, 300); // Even longer delay to ensure Quill is fully ready

        // Clear pending template
        this.pendingTemplate = '';
        console.log('Pending template cleared');

        // Force Quill to update if needed
        this.$nextTick(() => {
          console.log('=== NEXT TICK AFTER TEMPLATE SET ===');
          console.log(
            'Quill delta contents:',
            this.quillInstance?.getContents()
          );
          console.log(
            'Quill HTML contents:',
            this.quillInstance?.root?.innerHTML
          );
        });
      } else {
        console.log('=== NO PENDING TEMPLATE ===');
        console.log('No pending template to set');
      }
    },
  },
};
</script>

<style scoped>
/* Your existing styles are fine */
.send-assessment-table-outer {
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
.send-assessment-table-card {
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
  justify-content: flex-start;
}
.send-assessment-actions {
  margin-left: 0;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

@media (max-width: 1400px) {
  .send-assessment-table-outer {
    margin: 12px;
    max-width: 100%;
  }
}
@media (max-width: 900px) {
  .send-assessment-row {
    flex-direction: column;
    gap: 18px;
  }
}

/* Hide the default PrimeVue toolbar to prevent duplicate toolbars */
:deep(.p-editor-toolbar) {
  display: none !important;
}
</style>
