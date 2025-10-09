# Deploying Dolphin to Render - Docker Setup

## 🚀 Quick Fix Applied

Your deployment was failing because Render was looking for a Dockerfile. I've now added Docker support for reliable deployment.

## 📦 New Files Created

1. **`Dockerfile`** - Builds your Laravel backend in a Docker container
2. **`docker-entrypoint.sh`** - Handles startup: migrations, Passport, queue worker
3. **`.dockerignore`** - Optimizes Docker build size
4. **`render.yaml`** - Updated to use Docker runtime

## 🔧 How to Deploy

### Step 1: Commit and Push

```bash
git add .
git commit -m "Add Docker support for Render deployment"
git push origin main
```

### Step 2: Trigger Redeploy on Render

Go to your Render service dashboard and click **"Manual Deploy" → "Clear build cache & deploy"**

Or Render will automatically detect the changes and redeploy.

### Step 3: Configure Environment Variables

In Render Dashboard → Your Service → Environment tab:

**Critical Variables to Set:**

```env
APP_KEY=                    # Generate: php artisan key:generate --show
APP_URL=                    # Your Render URL: https://dolphinproject-1.onrender.com
FRONTEND_URL=              # Your Netlify URL
FRONTEND_URLS=             # All allowed origins (comma-separated)

# Database (auto-configured from dolphin-db)
DB_CONNECTION=pgsql
DB_HOST=(from database)
DB_PORT=(from database)
DB_DATABASE=(from database)
DB_USERNAME=(from database)
DB_PASSWORD=(from database)

# Mail
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@dolphin.app

# Stripe
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

# Gemini AI
GEMINI_API_KEY=
GEMINI_MODEL=gemini-pro
```

## 🔍 What the Docker Setup Does

1. **Base Image**: PHP 8.2-FPM
2. **Installs**: PostgreSQL client, required PHP extensions
3. **Composer**: Installs dependencies
4. **Entrypoint Script**:
   - Waits for database
   - Runs migrations
   - Sets up Passport
   - Starts queue worker
   - Starts web server on PORT (Render provides this)

## 🐛 Troubleshooting

### Build Still Failing?

Check Render logs for specific errors. Common issues:

1. **Missing APP_KEY**: Generate locally and add to environment
2. **Database not ready**: Entrypoint script waits for it
3. **Memory limits**: Free tier has 512MB RAM

### After Successful Deploy

1. Check logs: `https://dashboard.render.com/web/YOUR-SERVICE-ID/logs`
2. Test health endpoint: `https://your-app.onrender.com/api/health`
3. Update frontend: Set `VUE_APP_API_BASE_URL` in Netlify

### Database Connection Issues

The database variables are auto-configured via `render.yaml`:

```yaml
DB_HOST:
  fromDatabase:
    name: dolphin-db
    property: host
```

Make sure the `dolphin-db` database service is created first.

## 📊 Deployment Flow

```
GitHub Push
    ↓
Render detects Dockerfile
    ↓
Builds Docker image
    ↓
Runs docker-entrypoint.sh
    ↓
  - Waits for database
  - Runs migrations
  - Sets up Passport
  - Starts queue worker
  - Starts web server
    ↓
Service goes live! 🎉
```

## 🔐 Security Checklist

Before going live:

- [ ] `APP_DEBUG=false` (already set in render.yaml)
- [ ] Strong `APP_KEY` generated
- [ ] Production Stripe keys (not test keys)
- [ ] CORS configured with specific origins
- [ ] Database credentials secure (auto-managed by Render)
- [ ] HTTPS enabled (automatic on Render)

## 📈 Next Steps

1. **Deploy** - Push code and let Render build
2. **Configure** - Set environment variables
3. **Test** - Check `/api/health` endpoint
4. **Update Frontend** - Point to Render backend URL
5. **Stripe** - Update webhook URL

## 🆘 Still Having Issues?

Check:
- Render logs for detailed error messages
- Database connection (should be auto-configured)
- Environment variables are all set
- `APP_KEY` is generated and set

---

**Files Reference:**
- `Dockerfile` - Docker build configuration
- `docker-entrypoint.sh` - Startup script
- `render.yaml` - Render service configuration
- `Dolphin_Backend/.env.render` - Environment variables template
