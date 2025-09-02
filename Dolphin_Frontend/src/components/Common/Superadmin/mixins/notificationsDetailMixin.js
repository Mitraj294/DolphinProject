export default {
  computed: {
 
    detailRecipients() {
      const n = this.selectedNotification || {};
      const list = [];
      if (Array.isArray(n.groups) && n.groups.length) {
        n.groups.forEach((g) => {
          if (g.name) list.push({ name: g.name, email: '', status: 'pending' });
          if (Array.isArray(g.members) && g.members.length) {
            g.members.forEach((m) => {
              list.push({
                name: m.name || m.full_name || m.email || 'Unknown',
                email: m.email || '',
                status: m.status || 'pending',
              });
            });
          }
        });
        if (Array.isArray(n.admins) && n.admins.length) {
          n.admins.forEach((a) => {
            list.push({
              name: a.name || a.full_name || a.email || 'Admin',
              email: a.email || '',
              status: 'pending',
            });
          });
        }
        if (Array.isArray(n.organizations) && n.organizations.length) {
          n.organizations.forEach((o) => {
            list.push({
              name: o.name || o.org_name || o.title || `Org ${o.id}`,
              email: '',
              status: 'pending',
            });
          });
        }
      } else if (Array.isArray(n.recipients) && n.recipients.length) {
        n.recipients.forEach((r) => {
          list.push({
            name: r.name || r.full_name || r.email || 'Unknown',
            email: r.email || '',
            status: r.status || r.delivery_status || r.state || 'pending',
          });
        });
      } else if (Array.isArray(n.emails) && n.emails.length) {
        n.emails.forEach((e) => {
          if (typeof e === 'string') list.push({ name: e, email: e, status: 'pending' });
          else if (e && e.email) list.push({ name: e.name || e.email, email: e.email, status: e.status || 'pending' });
        });
      } else if (Array.isArray(n.organization_ids) && n.organization_ids.length && Array.isArray(this.organizations)) {
        n.organization_ids.forEach((oid) => {
          const org = this.organizations.find((o) => o.id === oid || o.org_id === oid || o.id == oid);
          const name = org ? org.org_name || org.name || org.title : String(oid);
          list.push({ name, email: '', status: 'pending' });
        });
      } else if (Array.isArray(n.group_ids) && n.group_ids.length && Array.isArray(this.groups)) {
        n.group_ids.forEach((gid) => {
          const g = this.groups.find((gr) => gr.id === gid || gr.id == gid || gr.group_id === gid);
          const name = g ? g.name || g.group_name || g.title : String(gid);
          list.push({ name, email: '', status: 'pending' });
        });
      } else if (Array.isArray(n.organization_names) && n.organization_names.length) {
        n.organization_names.forEach((on) => list.push({ name: on, email: '', status: 'pending' }));
      } else if (Array.isArray(n.group_names) && n.group_names.length) {
        n.group_names.forEach((gn) => list.push({ name: gn, email: '', status: 'pending' }));
      } else if (Array.isArray(n.member_names) && n.member_names.length) {
        n.member_names.forEach((mn) => list.push({ name: mn, email: '', status: 'pending' }));
      } else if (Array.isArray(n.members) && n.members.length) {
        n.members.forEach((m) => list.push({ name: m.name || m.full_name || m.email || 'Unknown', email: m.email || '', status: m.status || 'pending' }));
      }

      const seen = new Set();
      const deduped = [];
      list.forEach((it) => {
        const key = `${it.name || ''}::${it.email || ''}`;
        if (!seen.has(key)) {
          seen.add(key);
          deduped.push(it);
        }
      });

      return deduped;
    },
    // Provide a lightweight table-row model for the detail modal.
    // If empty, the template will fall back to `detailRecipients` list rendering.
    recipientTableRows() {
      const recs = this.detailRecipients || [];
      if (!recs.length) return [];
      return recs.map((r) => ({
        category: '',
        name: r.name || '',
        email: r.email || '',
        status: r.status || '',
        showGroup: false,
        rowspan: 1,
      }));
    },
    tickSvg() {
      return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17L4 12" stroke="#16A34A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    },
    crossSvg() {
      return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 6L6 18M6 6l12 12" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    },
  },
  methods: {
    isRead(entity) {
      if (!entity) return false;
      if (typeof entity.read === 'boolean') return entity.read;
      if (entity.status === 'read' || entity.status === 'delivered') return true;
      return false;
    },
  },
};
