import axios from 'axios';
import storage from '@/services/storage';

export default {
  methods: {
    async sendNotification() {
      try {
        const apiUrl = process.env.VUE_APP_API_URL || '/api';
        const token = storage.get('authToken');
        // Collect data from modal
        let scheduled_at;
        if (this.scheduledDate && this.scheduledTime) {
          let time = this.scheduledTime;
          if (time.length === 5) time += ':00';
          const local = new Date(`${this.scheduledDate}T${time}`);
          const pad = (n) => String(n).padStart(2, '0');
          const YYYY = local.getUTCFullYear();
          const MM = pad(local.getUTCMonth() + 1);
          const DD = pad(local.getUTCDate());
          const hh = pad(local.getUTCHours());
          const mm = pad(local.getUTCMinutes());
          const ss = pad(local.getUTCSeconds());
          scheduled_at = `${YYYY}-${MM}-${DD} ${hh}:${mm}:${ss}`;
        }
        const payload = {
          organization_ids: this.selectedOrganizations.map((org) => org.id),
          group_ids: this.selectedGroups.map((group) => group.id),
          body: this.$el.querySelector('.modal-textarea')
            ? this.$el.querySelector('.modal-textarea').value
            : '',
        };
        if (scheduled_at) payload.scheduled_at = scheduled_at;
        await axios.post(apiUrl + '/announcements/send', payload, {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (this.isAlive) {
          this.showSendModal = false;
          this.$toast &&
            this.$toast.add &&
            this.$toast.add({
              severity: 'success',
              summary: 'Success',
              detail: 'Announcement sent!',
              life: 3000,
            });
        }
      } catch (err) {
        if (this.isAlive && this.$toast && this.$toast.add) {
          this.$toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to send announcement',
            life: 4000,
          });
        }
      }
    },
  },
};
