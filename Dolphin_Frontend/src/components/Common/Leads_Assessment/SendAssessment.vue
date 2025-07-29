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
          <form class="send-assessment-form">
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
              <div class="send-assessment-field">
                <FormLabel>Template</FormLabel>
                <FormDropdown v-model="template">
                  <option value="">Select Template</option>
                  <!-- Add more options here -->
                </FormDropdown>
              </div>
            </FormRow>
            <div class="send-assessment-label">Editable Template</div>
            <div class="send-assessment-template-box">
              <!-- Render dummy data as HTML above the editor -->
              <div
                v-html="templateContent"
                class="dummy-template-preview"
              />
              <!-- Editor below the preview -->
              <quill-editor
                v-model="templateContent"
                :options="editorOptions"
                class="editor-below"
              />
            </div>
            <div class="send-assessment-label">Assessment Link</div>
            <div class="send-assessment-link-actions-row">
              <div class="send-assessment-link-box">
                <a
                  href="#"
                  class="send-assessment-link"
                  >Lorem Ipsum is simply dummy</a
                >
              </div>
              <div class="send-assessment-actions">
                <button
                  type="submit"
                  class="btn btn-primary"
                >
                  Send Assessment
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
  FormDropdown,
  FormRow,
  FormLabel,
} from '@/components/Common/Common_UI/Form';
// Import Quill editor and styles
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import Quill from 'quill';

export default {
  name: 'SendAssessment',
  components: {
    MainLayout,
    QuillEditor,
    FormInput,
    FormDropdown,
    FormRow,
    FormLabel,
  },
  data() {
    return {
      to: '',
      subject: '',
      template: '',
      templateContent: '',
      editorOptions: {
        theme: 'snow',
        modules: {
          toolbar: [
            [{ size: ['small', false, 'large', 'huge'] }], // font size option
            [{ color: [] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['link'],
            [
              { align: '' }, // left
              { align: 'center' }, // center
              { align: 'right' }, // right
              { align: 'justify' }, // justify
            ],
            ['clean'],
          ],
        },
      },
    };
  },
  mounted() {
    // Pre-fill from query params
    this.to = this.$route.query.email || '';
  },
};
</script>

<style scoped>
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
  border: 1px solid #e0e0e0;
  padding: 18px 18px 48px 18px;
  margin-bottom: 18px;
  min-height: 0;
  height: auto;
  position: relative;
  /* Optionally, adjust .send-assessment-template-box for editor styling */
  min-height: 180px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.dummy-template-preview {
  margin-bottom: 12px;
  font-size: 15px;
  color: #222;
  text-align: left; /* Ensure left alignment */
}

.send-assessment-template-box ul {
  margin: 0 0 12px 0;
  padding-left: 18px;
  color: #222;
  font-size: 15px;
  text-align: left;
}
.send-assessment-link-label {
  font-size: 15px;
  color: #222;
  margin-bottom: 8px;
  margin-top: 18px;
  text-align: left;
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
.send-assessment-link-box {
  flex: 0 0 320px;
  max-width: 320px;
  min-width: 180px;
  margin: 0;
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  padding: 8px 14px;
  display: flex;
  align-items: center;
  min-height: 40px;
  box-sizing: border-box;
  overflow: hidden;
}
@media (max-width: 900px) {
  .send-assessment-link-box {
    min-width: 0;
    max-width: 100%;
    width: 100%;
    padding: 8px 8px;
    margin-bottom: 0;
    min-height: 32px;
    max-height: 40px;
    height: auto;
  }
}
.send-assessment-actions {
  margin-left: auto;
  margin-top: 0;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

/* Responsive styles to match other pages */
@media (max-width: 1400px) {
  .send-assessment-table-outer {
    margin: 12px;
    max-width: 100%;
  }
  .send-assessment-table-card {
    max-width: 100%;
    border-radius: 14px;
    padding: 18px 8px 12px 8px;
  }
  .send-assessment-row {
    gap: 12px;
  }
}
@media (max-width: 900px) {
  .send-assessment-table-outer {
    margin: 4px;
    max-width: 100%;
  }
  .send-assessment-table-card {
    padding: 8px 2vw 8px 2vw;
    border-radius: 10px;
  }
  .send-assessment-row {
    flex-direction: column;
    gap: 18px; /* Increased gap for better vertical spacing */
    margin-bottom: 18px; /* Add bottom margin for separation */
  }
  .send-assessment-label {
    margin-top: 18px;
    margin-bottom: 10px; /* Slightly more space below label */
  }
  .send-assessment-template-box {
    margin-bottom: 18px;
    padding: 18px 8px 32px 8px; /* More bottom padding for editor */
    gap: 14px; /* More space between preview and editor */
  }
  .send-assessment-link-actions-row {
    flex-direction: column;
    gap: 18px; /* More space between link and button */
    align-items: stretch;
    justify-content: flex-start;
    margin-bottom: 0;
    margin-top: 0;
  }
  .send-assessment-link-box {
    min-width: 0;
    max-width: 100%;
    width: 100%;
    padding: 8px 8px;
    margin-bottom: 0;
  }
  .send-assessment-actions {
    margin-left: 0;
    justify-content: flex-end;
  }
}
</style>
