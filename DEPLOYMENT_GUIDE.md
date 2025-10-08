# 🚀 Dolphin Project Deployment Guide

Complete guide for deploying Dolphin on **Render** (Backend) and **Netlify** (Frontend).

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Backend Deployment (Render)](#backend-deployment-render)
3. [Frontend Deployment (Netlify)](#frontend-deployment-netlify)
4. [Environment Variables](#environment-variables)
5. [Post-Deployment Configuration](#post-deployment-configuration)
6. [Troubleshooting](#troubleshooting)

---

## ✅ Prerequisites

- GitHub account with your repository pushed
- [Render account](https://render.com) (free tier available)
- [Netlify account](https://netlify.com) (free tier available)
- Domain name (optional, both platforms provide free subdomains)
- SMTP email service (e.g., Gmail, SendGrid, Mailgun)
- Stripe account (if using payment features)

---

## 🔧 Backend Deployment (Render)

### Step 1: Prepare Your Repository

Ensure these files are in your repository:
- ✅ `render.yaml` (root directory)
- ✅ `Dolphin_Backend/Dockerfile`
- ✅ `Dolphin_Backend/docker-apache.conf`
- ✅ `Dolphin_Backend/docker-entrypoint.sh`

### Step 2: Create Render Account & Connect GitHub

1. Go to [render.com](https://render.com) and sign up
2. Click **"New +"** → **"Blueprint"**
3. Connect your GitHub account
4. Select the `DolphinProject` repository

### Step 3: Configure Blueprint

Render will automatically detect `render.yaml` and:
- Create a **PostgreSQL database** (dolphin-db)
- Create a **Web Service** (dolphin-backend)
- Link them together

### Step 4: Set Environment Variables

In the Render dashboard, go to your **dolphin-backend** service and add these environment variables:

#### Required Variables:

```bash
# Application
APP_NAME=Dolphin
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY_HERE

# Frontend URL (update after deploying frontend)
FRONTEND_URL=https://your-app.netlify.app

# Mail Configuration (example using Gmail)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME=Dolphin

# Stripe (if using payments)
STRIPE_KEY=pk_test_YOUR_PUBLIC_KEY
STRIPE_SECRET=sk_test_YOUR_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET
```

#### Generate APP_KEY:

Run locally to generate:
```bash
cd Dolphin_Backend
php artisan key:generate --show
```
Copy the output and paste it as `APP_KEY` in Render.

### Step 5: Deploy

1. Click **"Apply"** to create services
2. Render will:
   - Create PostgreSQL database
   - Build Docker image
   - Run migrations
   - Generate OAuth keys
   - Deploy your backend

3. Wait for deployment (usually 5-10 minutes)
4. Your backend URL: `https://dolphin-backend.onrender.com`

### Step 6: Verify Backend

Test your backend:
```bash
curl https://dolphin-backend.onrender.com/api/health
```

You should see a success response.

---

## 🎨 Frontend Deployment (Netlify)

### Step 1: Prepare Environment Variables

Create `.env` file in `Dolphin_Frontend/`:

```bash
VUE_APP_API_BASE_URL=https://dolphin-backend.onrender.com
VUE_APP_NAME=Dolphin
VUE_APP_DEBUG=false
VUE_APP_STRIPE_PUBLIC_KEY=pk_test_YOUR_PUBLIC_KEY
```

**Important:** Replace `dolphin-backend.onrender.com` with your actual Render backend URL.

### Step 2: Deploy to Netlify

#### Option A: Using Netlify CLI (Recommended)

1. Install Netlify CLI:
```bash
npm install -g netlify-cli
```

2. Login to Netlify:
```bash
netlify login
```

3. Deploy from project root:
```bash
cd Dolphin_Frontend
netlify init
```

4. Follow the prompts:
   - Create a new site or link existing
   - Build command: `npm run build`
   - Publish directory: `dist`

5. Deploy:
```bash
netlify deploy --prod
```

#### Option B: Using Netlify Dashboard

1. Go to [app.netlify.com](https://app.netlify.com)
2. Click **"Add new site"** → **"Import an existing project"**
3. Connect GitHub and select `DolphinProject`
4. Configure build settings:
   - **Base directory:** `Dolphin_Frontend`
   - **Build command:** `npm run build`
   - **Publish directory:** `dist`
5. Click **"Deploy site"**

### Step 3: Configure Environment Variables in Netlify

In Netlify dashboard:
1. Go to **Site settings** → **Environment variables**
2. Add these variables:
   ```
   VUE_APP_API_BASE_URL=https://dolphin-backend.onrender.com
   VUE_APP_NAME=Dolphin
   VUE_APP_DEBUG=false
   VUE_APP_STRIPE_PUBLIC_KEY=pk_test_YOUR_KEY
   ```
3. Click **"Save"**
4. Trigger a new deploy: **Deploys** → **Trigger deploy** → **Deploy site**

### Step 4: Verify Frontend

Visit your Netlify URL: `https://your-app.netlify.app`

---

## 🔐 Environment Variables

### Backend (Render) - Complete List

```bash
# Application
APP_NAME=Dolphin
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATED_KEY_HERE
APP_URL=https://dolphin-backend.onrender.com
FRONTEND_URL=https://your-app.netlify.app

# Database (Auto-configured by Render)
DB_CONNECTION=pgsql
DB_HOST=<from database>
DB_PORT=<from database>
DB_DATABASE=<from database>
DB_USERNAME=<from database>
DB_PASSWORD=<from database>

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME=Dolphin

# Stripe
STRIPE_KEY=pk_test_YOUR_PUBLIC_KEY
STRIPE_SECRET=sk_test_YOUR_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Frontend (Netlify)

```bash
VUE_APP_API_BASE_URL=https://dolphin-backend.onrender.com
VUE_APP_NAME=Dolphin
VUE_APP_DEBUG=false
VUE_APP_STRIPE_PUBLIC_KEY=pk_test_YOUR_PUBLIC_KEY
```

---

## ⚙️ Post-Deployment Configuration

### 1. Update CORS Settings

Update backend `FRONTEND_URL` in Render:
```bash
FRONTEND_URL=https://your-actual-netlify-url.netlify.app
```

Redeploy backend after updating.

### 2. Configure Custom Domain (Optional)

#### For Backend (Render):
1. Go to **Settings** → **Custom Domain**
2. Add your domain (e.g., `api.yourdomain.com`)
3. Update DNS records as instructed

#### For Frontend (Netlify):
1. Go to **Domain settings** → **Add custom domain**
2. Add your domain (e.g., `www.yourdomain.com`)
3. Update DNS records as instructed

### 3. Setup Stripe Webhooks

1. Go to Stripe Dashboard → **Developers** → **Webhooks**
2. Add endpoint: `https://dolphin-backend.onrender.com/api/stripe/webhook`
3. Select events to listen to
4. Copy webhook secret and add to Render as `STRIPE_WEBHOOK_SECRET`

### 4. SSL Certificates

Both Render and Netlify provide **free SSL certificates** automatically. No configuration needed!

---

## 🐛 Troubleshooting

### Backend Issues

#### 1. "500 Internal Server Error"
**Solution:**
- Check Render logs: Dashboard → Logs
- Verify all environment variables are set
- Ensure `APP_KEY` is generated correctly

#### 2. "Database Connection Failed"
**Solution:**
- Verify database is created in Render
- Check database environment variables are linked correctly
- Database URL format: `postgresql://user:password@host:port/database`

#### 3. "OAuth Keys Not Found"
**Solution:**
- SSH into Render shell (Dashboard → Shell)
- Run: `php artisan passport:keys --force`
- Restart service

#### 4. "Migrations Failed"
**Solution:**
- Check Render logs for specific error
- Verify database connection
- Manually run: `php artisan migrate --force` in Shell

### Frontend Issues

#### 1. "API Connection Failed"
**Solution:**
- Verify `VUE_APP_API_BASE_URL` is correct
- Check CORS settings on backend
- Ensure backend is deployed and running

#### 2. "404 on Page Refresh"
**Solution:**
- Ensure `_redirects` file exists in `public/` folder
- Verify `netlify.toml` redirect rules are correct
- Check build logs in Netlify

#### 3. "Environment Variables Not Working"
**Solution:**
- Variables must start with `VUE_APP_`
- Redeploy after adding variables
- Clear browser cache

### General Issues

#### 1. "CORS Error"
**Solution:**
Backend `config/cors.php` should allow your frontend domain:
```php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:8080'),
],
```

Update `FRONTEND_URL` in Render and redeploy.

#### 2. "Free Tier Limitations"
**Render Free Tier:**
- Service spins down after 15 minutes of inactivity
- First request after inactivity takes ~30 seconds (cold start)
- 750 hours/month free

**Netlify Free Tier:**
- 100GB bandwidth/month
- 300 build minutes/month
- Always active (no cold starts)

#### 3. "Build Failed"
**Backend:**
- Check Dockerfile syntax
- Verify all dependencies in composer.json
- Check Render build logs

**Frontend:**
- Verify Node version compatibility
- Check package.json scripts
- Review Netlify build logs

---

## 📞 Support & Resources

### Documentation
- **Laravel:** https://laravel.com/docs
- **Vue.js:** https://vuejs.org/guide/
- **Render:** https://render.com/docs
- **Netlify:** https://docs.netlify.com

### Monitoring
- **Render Dashboard:** Monitor logs, metrics, deployments
- **Netlify Analytics:** Track traffic and builds
- **Laravel Logs:** Check `storage/logs/laravel.log`

---

## 🎉 Success Checklist

- [ ] Backend deployed on Render
- [ ] Database created and connected
- [ ] Migrations ran successfully
- [ ] OAuth keys generated
- [ ] Frontend deployed on Netlify
- [ ] Environment variables configured on both platforms
- [ ] CORS configured correctly
- [ ] API connection working
- [ ] Authentication working
- [ ] Stripe webhooks configured (if applicable)
- [ ] Custom domains configured (optional)
- [ ] SSL certificates active

---

**Congratulations! Your Dolphin app is now live!** 🐬

For issues or questions, check the troubleshooting section above or consult the respective platform documentation.

---

**Last Updated:** October 8, 2025
