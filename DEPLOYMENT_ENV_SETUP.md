# 🔐 Environment Variables Setup for Deployment

## Overview

You have `.env` files locally, but for deployment you need to configure environment variables separately on each platform:

---

## 📍 WHERE TO SET ENVIRONMENT VARIABLES

### ❌ DON'T Use Local .env Files
Your local `.env` files:
- `Dolphin_Backend/.env` - Local development only
- `Dolphin_Frontend/.env` - Local development only

These are NOT used in deployment (they're in `.gitignore`).

### ✅ DO Configure on Platforms

#### For Backend → **Render Dashboard**
1. Go to: https://dashboard.render.com
2. Select your service: `dolphin-backend`
3. Click: **Environment** tab
4. Add each variable below

#### For Frontend → **Netlify Dashboard**
1. Go to: https://app.netlify.com
2. Select your site
3. Go to: **Site settings** → **Environment variables**
4. Add each variable below

---

## 🔧 BACKEND Environment Variables (Render)

Add these in **Render Dashboard → Your Service → Environment**:

### 🔴 CRITICAL - Must Set:

```bash
# Laravel App Key (Generate new for production!)
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
```
**How to generate:**
```bash
cd Dolphin_Backend
php artisan key:generate --show
```
Copy the output and paste into Render.

---

```bash
# Frontend URL (Update after deploying frontend)
FRONTEND_URL=https://your-app-name.netlify.app
```
**Update this after deploying frontend!** Replace with your actual Netlify URL.

---

```bash
# Application Settings
APP_NAME=Dolphin
APP_ENV=production
APP_DEBUG=false
```

---

### 📧 MAIL Configuration (Required for sending emails):

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME=Dolphin
```

**For Gmail:**
1. Enable 2-factor authentication on your Google account
2. Go to: https://myaccount.google.com/apppasswords
3. Create "App Password" for "Mail"
4. Use that 16-character password as `MAIL_PASSWORD`

---

### 💳 STRIPE (Only if using payments):

```bash
STRIPE_KEY=pk_test_YOUR_PUBLISHABLE_KEY
STRIPE_SECRET=sk_test_YOUR_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET
```

**Where to find:**
- Go to: https://dashboard.stripe.com/test/apikeys
- Copy "Publishable key" → `STRIPE_KEY`
- Copy "Secret key" → `STRIPE_SECRET`
- For webhook secret: Dashboard → Webhooks → Add endpoint

**Use test keys for testing, live keys for production!**

---

### 🗄️ DATABASE (Auto-configured by Render)

These are **automatically set** when you use the Blueprint:
- `DB_CONNECTION=pgsql`
- `DB_HOST` (from database)
- `DB_PORT` (from database)
- `DB_DATABASE` (from database)
- `DB_USERNAME` (from database)
- `DB_PASSWORD` (from database)

**You don't need to set these manually!**

---

### ⚙️ OTHER (Optional, have defaults):

```bash
LOG_CHANNEL=stack
LOG_LEVEL=error
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

These have sensible defaults in `render.yaml`, but you can override them.

---

## 🎨 FRONTEND Environment Variables (Netlify)

Add these in **Netlify Dashboard → Site settings → Environment variables**:

### 🔴 CRITICAL - Must Set:

```bash
# Backend API URL (Update after deploying backend)
VUE_APP_API_BASE_URL=https://dolphin-backend.onrender.com
```
**Update this after deploying backend!** Replace with your actual Render backend URL.

---

### 💳 STRIPE (Only if using payments):

```bash
VUE_APP_STRIPE_PUBLIC_KEY=pk_test_YOUR_PUBLISHABLE_KEY
```

Use the **same publishable key** from Stripe that you used in backend `STRIPE_KEY`.

---

### ⚙️ OPTIONAL:

```bash
VUE_APP_NAME=Dolphin
VUE_APP_DEBUG=false
```

---

## 📋 DEPLOYMENT CHECKLIST

### Step 1: Deploy Backend First

1. Go to Render → New → Blueprint
2. Connect GitHub → DolphinProject
3. **Add these environment variables:**
   - [ ] `APP_KEY` (generate with artisan)
   - [ ] `APP_NAME=Dolphin`
   - [ ] `APP_ENV=production`
   - [ ] `APP_DEBUG=false`
   - [ ] `FRONTEND_URL` (use placeholder first, update later)
   - [ ] `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`, etc.
   - [ ] `STRIPE_KEY`, `STRIPE_SECRET` (if using Stripe)
4. Click "Apply" and wait for deployment
5. **Copy your backend URL:** `https://dolphin-backend-xxxxx.onrender.com`

---

### Step 2: Deploy Frontend

1. Go to Netlify → New site → Import project
2. Connect GitHub → DolphinProject
3. Configure:
   - Base directory: `Dolphin_Frontend`
   - Build command: `npm run build`
   - Publish directory: `dist`
4. **Add these environment variables:**
   - [ ] `VUE_APP_API_BASE_URL` (use backend URL from Step 1)
   - [ ] `VUE_APP_STRIPE_PUBLIC_KEY` (if using Stripe)
5. Deploy!
6. **Copy your frontend URL:** `https://your-app.netlify.app`

---

### Step 3: Link Them Together

1. **Update Backend** (Render):
   - Go to: Dolphin-backend → Environment
   - Update: `FRONTEND_URL=https://your-app.netlify.app` (from Step 2)
   - Save → Backend redeploys automatically

2. **Verify Frontend** (Netlify):
   - Ensure `VUE_APP_API_BASE_URL` points to correct backend URL
   - If changed, trigger new deploy

---

## 🧪 HOW TO TEST

### Backend Health Check:
```bash
curl https://your-backend.onrender.com/api/health
```

Should return success response.

### Frontend Test:
1. Visit: `https://your-app.netlify.app`
2. Open browser console (F12)
3. Try to login or make API call
4. Check for CORS errors

---

## ❓ QUICK REFERENCE

| What | Where | How |
|------|-------|-----|
| Backend env vars | Render Dashboard → Environment | Add key-value pairs |
| Frontend env vars | Netlify Dashboard → Environment variables | Add key-value pairs |
| Generate APP_KEY | Local terminal | `php artisan key:generate --show` |
| Gmail app password | Google Account → Security → App passwords | Generate new |
| Stripe keys | Stripe Dashboard → Developers → API keys | Copy keys |
| Database credentials | Automatic | Render links them from database |

---

## 🆘 COMMON ISSUES

| Issue | Cause | Solution |
|-------|-------|----------|
| "APP_KEY not set" | Missing in Render | Generate and add APP_KEY |
| "CORS error" | Wrong FRONTEND_URL | Update FRONTEND_URL in Render |
| "API connection failed" | Wrong backend URL | Update VUE_APP_API_BASE_URL in Netlify |
| "Mail not sending" | Wrong credentials | Check MAIL_* variables |
| "Database error" | Not linked | Verify Blueprint created database |

---

## 📝 SUMMARY

**You need to set environment variables in TWO places:**

1. **Render Dashboard** (for backend) - 10-15 variables
2. **Netlify Dashboard** (for frontend) - 1-2 variables minimum

**Your local `.env` files are NOT used in deployment!**

Read `DEPLOYMENT_GUIDE.md` for complete step-by-step instructions.

---

**Created:** October 8, 2025  
**For detailed docs:** See `ENV_VARIABLES_REFERENCE.md`
