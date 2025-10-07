# Dolphin Project - Files Not in Git Repository

## Summary
This document lists all files that are excluded from git repository via `.gitignore` or are uncommitted.

## Current Git Status
- **Branch:** main
- **Status:** Working tree clean (all tracked files are committed)

---

## Files Ignored by .gitignore

### Backend (.gitignore patterns)
Located at: `Dolphin_Backend/.gitignore`

**Patterns:**
- `*.log` - All log files
- `.DS_Store` - macOS system files
- `.env`, `.env.*` - Environment configuration files (CRITICAL - contains sensitive data)
- `.phpactor.json` - PHP IDE configuration
- `.phpunit.result.cache` - PHPUnit cache
- `/.fleet`, `/.idea`, `/.nova`, `/.vscode`, `/.zed` - IDE configuration folders
- `/node_modules` - Node.js dependencies
- `/public/build`, `/public/hot` - Build artifacts
- `/public/storage` - Public storage symlink
- `/storage/*.key` - OAuth keys (CRITICAL - contains private keys)
- `/storage/pail` - Storage cache
- `/vendor` - PHP Composer dependencies
- `auth.json` - Composer authentication
- `Homestead.json`, `Homestead.yaml` - Laravel Homestead configuration
- `Thumbs.db` - Windows thumbnail cache

### Frontend (.gitignore patterns)
Located at: `Dolphin_Frontend/.gitignore`

**Patterns:**
- `.env` - Environment configuration files
- `node_modules` - Node.js dependencies

---

## Critical Files to Backup (Not in Git)

### 1. Environment Configuration Files
**Location & Purpose:**
- `/Dolphin_Backend/.env` - Backend environment variables (database credentials, API keys, etc.)
- `/Dolphin_Frontend/.env` - Frontend environment variables
- `.env` (root if exists) - Root environment variables

**Why Critical:** Contains database passwords, API keys, encryption keys, mail server credentials

### 2. OAuth Keys
**Location:**
- `/Dolphin_Backend/storage/oauth-private.key` - OAuth private key for authentication
- `/Dolphin_Backend/storage/oauth-public.key` - OAuth public key

**Why Critical:** Used for JWT token generation, if lost users cannot authenticate

### 3. Log Files
**Location:**
- `/Dolphin_Backend/cron_test.log`
- `/Dolphin_Backend/scheduler.log`
- `/Dolphin_Backend/storage/logs/leads_debug.log`
- `/Dolphin_Backend/storage/logs/laravel.log`
- `/Dolphin_Backend/storage/logs/queue-worker.log`

**Purpose:** Debugging and monitoring application behavior

### 4. Dependencies (Can be regenerated but large)
**Location:**
- `/Dolphin_Backend/vendor/` - PHP dependencies (installed via `composer install`)
- `/Dolphin_Backend/node_modules/` - Backend Node.js dependencies
- `/Dolphin_Frontend/node_modules/` - Frontend Node.js dependencies

**Note:** These can be regenerated from `composer.json` and `package.json` but take time to download

### 5. Storage Files
**Location:**
- `/Dolphin_Backend/storage/cookiejar.txt` - Cookie storage
- `/Dolphin_Backend/storage/framework/views/*.php` - Compiled Blade templates (can be regenerated)

### 6. IDE Configuration (Optional)
**If present:**
- `.vscode/` - VS Code settings
- `.idea/` - PhpStorm/WebStorm settings

---

## Files That Should Be Backed Up for Complete Recovery

### CRITICAL (Must backup - cannot be regenerated):
1. `.env` files (all)
2. `storage/oauth-private.key`
3. `storage/oauth-public.key`
4. Database dump (if you have one)

### IMPORTANT (Should backup - helps with debugging):
1. All `*.log` files
2. `storage/cookiejar.txt`

### OPTIONAL (Can regenerate but saves time):
1. `vendor/` folder (Backend PHP dependencies)
2. `node_modules/` folders (JavaScript dependencies)
3. `storage/framework/views/` (Compiled templates)

---

## Backup Solution Created

A `Dolphin_Backup/` folder has been created locally with all critical files:
- ✅ All .env files backed up
- ✅ OAuth keys backed up
- ✅ Log files backed up
- ✅ Storage files backed up

See `Dolphin_Backup/README.md` for complete restore instructions.

**⚠️ IMPORTANT:** The `Dolphin_Backup/` folder is NOT committed to GitHub (it's in `.gitignore`) because:
- It contains sensitive credentials (API keys, database passwords)
- GitHub secret scanning will block commits with secrets
- These files should be backed up separately to secure storage

---

## How to Use This Backup

### Local Backup:
The `Dolphin_Backup/` folder exists on your local machine. You should:
1. **Copy it to secure cloud storage** (Google Drive, Dropbox, etc.)
2. **Create encrypted archive**: `tar -czf dolphin_backup_$(date +%Y%m%d).tar.gz Dolphin_Backup/`
3. **Store on external drive** as additional backup
4. **Never commit to public GitHub** (already in .gitignore)

### After Cloning Repository:
1. Clone repository: `git clone https://github.com/Mitraj294/DolphinProject.git`
2. Copy your saved `Dolphin_Backup/` folder to the project root
3. Restore critical files from `Dolphin_Backup/` folder (see Dolphin_Backup/README.md)
4. Run `composer install` and `npm install`
5. Restore database separately

---

## Recommended Backup Strategy

**For GitHub Storage:**
✅ All code files are committed to GitHub
✅ This documentation file is committed
❌ `Dolphin_Backup/` folder is NOT committed (contains secrets)
❌ Original `.env` files are gitignored for security

**For Secure Storage (Critical Files):**
✅ Store `Dolphin_Backup/` folder in encrypted cloud storage
✅ Keep local copy on external drive
✅ Update regularly after configuration changes
✅ Use password-protected archives

**For Database:**
✅ Export database regularly
✅ Store database backups separately
✅ Use automated backup tools

---

## Generated: October 7, 2025
