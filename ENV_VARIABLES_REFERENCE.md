# 🔐 Environment Variables Reference

Quick reference guide for all environment variables needed for deployment.

---

## 🔧 Backend Environment Variables (Render)

### Core Application Settings

| Variable | Required | Default | Description | Example |
|----------|----------|---------|-------------|---------|
| `APP_NAME` | ✅ | Laravel | Application name | `Dolphin` |
| `APP_ENV` | ✅ | production | Environment type | `production` |
| `APP_DEBUG` | ✅ | false | Debug mode (false in production) | `false` |
| `APP_KEY` | ✅ | - | Laravel encryption key | `base64:randomstring...` |
| `APP_URL` | ✅ | - | Backend URL | `https://dolphin-backend.onrender.com` |
| `FRONTEND_URL` | ✅ | - | Frontend URL for CORS | `https://your-app.netlify.app` |

**Generate APP_KEY:**
```bash
php artisan key:generate --show
```

---

### Database Configuration

| Variable | Required | Default | Description | Notes |
|----------|----------|---------|-------------|-------|
| `DB_CONNECTION` | ✅ | pgsql | Database driver | Use `pgsql` for Render |
| `DB_HOST` | ✅ | - | Database host | Auto-linked from database |
| `DB_PORT` | ✅ | 5432 | Database port | Auto-linked from database |
| `DB_DATABASE` | ✅ | - | Database name | Auto-linked from database |
| `DB_USERNAME` | ✅ | - | Database user | Auto-linked from database |
| `DB_PASSWORD` | ✅ | - | Database password | Auto-linked from database |

**Note:** Render automatically links these when using Blueprint.

---

### Mail Configuration

| Variable | Required | Default | Description | Example |
|----------|----------|---------|-------------|---------|
| `MAIL_MAILER` | ✅ | smtp | Mail driver | `smtp` |
| `MAIL_HOST` | ✅ | - | Mail server host | `smtp.gmail.com` |
| `MAIL_PORT` | ✅ | 587 | Mail server port | `587` |
| `MAIL_USERNAME` | ✅ | - | Mail username/email | `your-email@gmail.com` |
| `MAIL_PASSWORD` | ✅ | - | Mail password | `your-app-password` |
| `MAIL_ENCRYPTION` | ✅ | tls | Encryption method | `tls` or `ssl` |
| `MAIL_FROM_ADDRESS` | ✅ | - | Sender email | `noreply@yourdomain.com` |
| `MAIL_FROM_NAME` | ✅ | - | Sender name | `Dolphin` |

**Gmail Setup:**
1. Enable 2-factor authentication
2. Generate App Password: Account → Security → App passwords
3. Use App Password as `MAIL_PASSWORD`

---

### Stripe Configuration

| Variable | Required | Default | Description | Where to Find |
|----------|----------|---------|-------------|---------------|
| `STRIPE_KEY` | ⚠️ | - | Stripe publishable key | Dashboard → Developers → API keys |
| `STRIPE_SECRET` | ⚠️ | - | Stripe secret key | Dashboard → Developers → API keys |
| `STRIPE_WEBHOOK_SECRET` | ⚠️ | - | Webhook signing secret | Dashboard → Developers → Webhooks |

⚠️ = Required only if using payment features

**Webhook Setup:**
1. Stripe Dashboard → Developers → Webhooks
2. Add endpoint: `https://your-backend.onrender.com/api/stripe/webhook`
3. Copy signing secret

---

### Session & Cache

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `SESSION_DRIVER` | ✅ | database | Session storage | `database` |
| `SESSION_LIFETIME` | ✅ | 120 | Session lifetime (minutes) | `120` |
| `CACHE_STORE` | ✅ | database | Cache driver | `database` |
| `QUEUE_CONNECTION` | ✅ | database | Queue driver | `database` |
| `FILESYSTEM_DISK` | ✅ | local | File storage | `local` |

---

### Logging

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `LOG_CHANNEL` | ✅ | stack | Log channel | `stack` |
| `LOG_LEVEL` | ✅ | error | Log level in production | `error` or `debug` |

---

## 🎨 Frontend Environment Variables (Netlify)

| Variable | Required | Default | Description | Example |
|----------|----------|---------|-------------|---------|
| `VUE_APP_API_BASE_URL` | ✅ | - | Backend API URL | `https://dolphin-backend.onrender.com` |
| `VUE_APP_NAME` | ❌ | Dolphin | Application name | `Dolphin` |
| `VUE_APP_DEBUG` | ❌ | false | Debug mode | `false` |
| `VUE_APP_STRIPE_PUBLIC_KEY` | ⚠️ | - | Stripe public key | `pk_test_...` or `pk_live_...` |

⚠️ = Required only if using Stripe payments

**Important Notes:**
- All Vue.js environment variables MUST start with `VUE_APP_`
- Variables are compiled into the build at build time
- Must redeploy after changing variables
- Never put secrets in frontend (they're visible to users)

---

## 📝 Environment Files

### Backend `.env` File Structure

```bash
# Application
APP_NAME=Dolphin
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-generated-key-here
APP_URL=https://dolphin-backend.onrender.com
FRONTEND_URL=https://your-app.netlify.app

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error

# Database (Auto-configured by Render)
DB_CONNECTION=pgsql
DB_HOST=your-database-host.render.com
DB_PORT=5432
DB_DATABASE=dolphin
DB_USERNAME=dolphin
DB_PASSWORD=your-database-password

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME=Dolphin

# Stripe (if using payments)
STRIPE_KEY=pk_test_your_public_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

### Frontend `.env` File Structure

```bash
VUE_APP_API_BASE_URL=https://dolphin-backend.onrender.com
VUE_APP_NAME=Dolphin
VUE_APP_DEBUG=false
VUE_APP_STRIPE_PUBLIC_KEY=pk_test_your_public_key
```

---

## 🔄 How to Update Environment Variables

### On Render (Backend)

1. Go to Render Dashboard
2. Select your service (dolphin-backend)
3. Go to **Environment** tab
4. Click **Add Environment Variable**
5. Enter key and value
6. Click **Save Changes**
7. Service will automatically redeploy

### On Netlify (Frontend)

1. Go to Netlify Dashboard
2. Select your site
3. Go to **Site settings** → **Environment variables**
4. Click **Add a variable**
5. Enter key and value
6. Click **Create variable**
7. Go to **Deploys** → **Trigger deploy** → **Deploy site**

---

## ⚠️ Security Best Practices

### ✅ DO:
- Use different keys for development and production
- Use strong, random `APP_KEY` (generated by Laravel)
- Use environment-specific Stripe keys (test vs live)
- Enable 2FA on all service accounts
- Rotate secrets regularly
- Use app-specific passwords (Gmail)
- Keep `.env` files in `.gitignore`

### ❌ DON'T:
- Commit `.env` files to Git
- Share environment variables publicly
- Use the same keys across environments
- Put secrets in frontend code
- Use debug mode in production
- Share database credentials

---

## 🧪 Testing Environment Variables

### Backend Test

```bash
# SSH into Render shell
php artisan tinker

# Test database
DB::connection()->getPdo();

# Test config
config('app.name');
config('mail.from.address');
```

### Frontend Test

Open browser console on your Netlify site:
```javascript
// These will be undefined (security)
console.log(process.env.VUE_APP_API_BASE_URL);
```

Make a test API call to verify connection.

---

## 📋 Deployment Checklist

### Before Deployment:

- [ ] All required environment variables identified
- [ ] `APP_KEY` generated
- [ ] Database credentials ready
- [ ] Mail service configured
- [ ] Stripe keys obtained (if needed)
- [ ] `.env.example` files updated

### During Deployment:

- [ ] Environment variables added to Render
- [ ] Environment variables added to Netlify
- [ ] Database connected
- [ ] Services deployed successfully

### After Deployment:

- [ ] Test backend health endpoint
- [ ] Test frontend loads correctly
- [ ] Test API connectivity
- [ ] Test authentication
- [ ] Test email sending (if applicable)
- [ ] Test Stripe webhooks (if applicable)
- [ ] Monitor logs for errors

---

## 🆘 Quick Troubleshooting

| Issue | Check | Solution |
|-------|-------|----------|
| "APP_KEY not set" | Render env vars | Generate and add `APP_KEY` |
| "Database connection failed" | DB env vars | Verify database is linked |
| "CORS error" | `FRONTEND_URL` | Update with correct Netlify URL |
| "Mail not sending" | Mail credentials | Verify SMTP settings |
| "Stripe webhook failed" | Webhook secret | Update `STRIPE_WEBHOOK_SECRET` |
| "API not found" | Frontend env | Verify `VUE_APP_API_BASE_URL` |

---

**Need Help?**
- Check `DEPLOYMENT_GUIDE.md` for detailed instructions
- Review platform docs: [Render Docs](https://render.com/docs) | [Netlify Docs](https://docs.netlify.com)
- Check application logs in respective dashboards

---

**Last Updated:** October 8, 2025
