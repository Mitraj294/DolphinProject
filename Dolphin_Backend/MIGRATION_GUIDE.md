# Database Migration Guide - Baseline Schema

This guide explains how to apply the new baseline migration that replaces all 74 previous migrations.

## What Changed?

All previous incremental migrations (74 files from 2025-07-17 through 2025-10-08) have been **squashed** into a single baseline migration: `2025_01_01_000000_create_baseline_schema.php`

### Benefits:
- ✅ Eliminates migration order issues
- ✅ Fixes schema drift problems
- ✅ Creates clean, consistent schema
- ✅ Faster deployment on new environments
- ✅ Easier to maintain and understand
- ✅ Single source of truth for database schema

### What was archived:
All old migration files have been moved to `database/migrations_archive/` for reference but are no longer executed.

---

## For Fresh Database Installations (New Deployments)

If you're setting up DolphinProject for the first time, the process is simple:

### Steps:

1. **Configure your database connection** in `.env`:
   ```bash
   DB_CONNECTION=mysql
   DB_HOST=your-database-host
   DB_PORT=3306
   DB_DATABASE=dolphin
   DB_USERNAME=your-username
   DB_PASSWORD=your-password
   ```

2. **Run migrations**:
   ```bash
   php artisan migrate
   ```

3. **Install Passport** (for API authentication):
   ```bash
   php artisan passport:install
   ```

4. **Done!** Your database is now set up with the complete schema.

---

## For Existing Database Installations (Migration from Old Schema)

⚠️ **IMPORTANT**: Back up your database before proceeding!

If you have an existing DolphinProject database with data, follow these steps carefully:

### Prerequisites:

1. ✅ Backup your database
2. ✅ Have access to your database server
3. ✅ Review the new baseline migration to understand schema changes

### Option A: Fresh Migration (Recommended for Development/Testing)

Best for: Development environments, testing, or when data can be re-seeded

```bash
# 1. Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# 2. Drop all tables and migrations
php artisan migrate:fresh

# 3. Your database is now on the new baseline schema
```

### Option B: Transition from Old Migrations (For Production)

Best for: Production environments with existing data

#### Step 1: Backup Everything
```bash
# Create database backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Backup migration table specifically
mysqldump -u username -p database_name migrations > migrations_backup.sql
```

#### Step 2: Verify Current State
```bash
# Check which migrations are currently applied
php artisan migrate:status

# Verify all tables exist
php artisan tinker
>>> Schema::hasTable('users')
>>> Schema::hasTable('organizations')
# ... check other important tables
```

#### Step 3: Archive Old Migration Records

**Option 3A: Clean Slate Approach** (Safest, requires brief downtime)

```bash
# 1. Ensure all old migrations have run
php artisan migrate

# 2. Stop your application (to prevent concurrent changes)

# 3. Truncate the migrations table
php artisan db:seed --class=TruncateMigrationsSeeder
# OR manually:
mysql -u username -p database_name -e "TRUNCATE TABLE migrations;"

# 4. Run the baseline migration
php artisan migrate

# 5. Start your application
```

**Option 3B: Side-by-Side Approach** (No downtime, more complex)

```bash
# 1. Mark the baseline as already run without executing it
php artisan migrate --pretend

# 2. Manually insert the baseline migration record
mysql -u username -p database_name -e "INSERT INTO migrations (migration, batch) VALUES ('2025_01_01_000000_create_baseline_schema', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations AS m));"

# 3. Archive old migration records (optional)
mysql -u username -p database_name -e "UPDATE migrations SET migration = CONCAT('archived_', migration) WHERE migration != '2025_01_01_000000_create_baseline_schema';"
```

#### Step 4: Verify Schema

After migration, verify your schema matches expectations:

```bash
# Check table structure
php artisan tinker

# Verify important tables
>>> Schema::hasTable('users')
>>> Schema::hasTable('organizations')
>>> Schema::hasTable('assessments')
>>> Schema::hasTable('announcements')

# Check for required columns
>>> Schema::hasColumn('users', 'organization_id')
>>> Schema::hasColumn('leads', 'sales_person_id')
>>> Schema::hasColumn('announcements', 'dispatched_at')

# Verify foreign keys (example for MySQL)
>>> DB::select("SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE='FOREIGN KEY' AND TABLE_SCHEMA='dolphin'")
```

#### Step 5: Test Your Application

1. Run your test suite:
   ```bash
   php artisan test
   ```

2. Verify critical functionality:
   - User login/registration
   - Organization creation
   - Assessment creation
   - Lead management
   - Announcement system

---

## Database Schema Overview

The new baseline migration creates the following table groups:

### Core Laravel Tables
- `cache`, `cache_locks`
- `jobs`, `job_batches`, `failed_jobs`
- `sessions`
- `password_reset_tokens`
- `notifications` (Laravel's queued notifications)

### OAuth/Passport Tables
- `oauth_auth_codes`
- `oauth_access_tokens`
- `oauth_refresh_tokens`
- `oauth_clients`
- `oauth_device_codes`

### Location Reference Tables
- `countries`
- `states` (with country relationship)
- `cities` (with state relationship)

### User Management
- `users` (with soft deletes, organization relationship)
- `roles`
- `user_roles` (pivot table)
- `user_details` (extended user information)

### Organization Management
- `organizations` (with location relationships)
- `subscriptions` (Stripe payment tracking)

### Lead Management
- `leads` (with location and sales person relationships)

### Member & Group Management
- `groups`
- `members` (with soft deletes)
- `member_roles`
- `group_member` (pivot table)
- `member_member_role` (pivot table)

### Assessment System
- `assessments`
- `organization_assessment_questions`
- `assessment_question` (pivot table)
- `assessment_schedules`
- `scheduled_emails`
- `assessment_answer_tokens`
- `assessment_answer_links`
- `assessment_question_answers`
- `questions` (legacy)
- `answers` (legacy)

### Announcement System
- `announcements`
- `announcement_organization` (pivot table)
- `announcement_admin` (pivot table)
- `announcement_group` (pivot table)

### Guest Access
- `guest_links`

---

## Troubleshooting

### Issue: "Table already exists" error

**Solution**: Your database has tables from old migrations. Use Option B above for existing databases.

### Issue: Foreign key constraint errors

**Solution**: 
1. Check that all parent tables exist before child tables
2. Verify foreign key columns match parent table primary key types
3. Ensure there's no orphaned data referencing non-existent records

```bash
# Check for orphaned records (example)
SELECT * FROM users WHERE organization_id IS NOT NULL AND organization_id NOT IN (SELECT id FROM organizations);
```

### Issue: Migration rollback fails

**Solution**: The baseline migration has a complete `down()` method. If it fails:

```bash
# Manually drop all tables (CAUTION: Data loss!)
php artisan db:wipe

# Then re-run migration
php artisan migrate
```

### Issue: Application errors after migration

**Solution**:
1. Clear application cache: `php artisan cache:clear`
2. Clear config cache: `php artisan config:clear`
3. Regenerate optimized files: `php artisan optimize`
4. Check model relationships match new schema

---

## Rolling Back (Emergency)

If you need to revert to old migrations:

1. **Restore database from backup**:
   ```bash
   mysql -u username -p database_name < backup_YYYYMMDD.sql
   ```

2. **Restore old migration files**:
   ```bash
   cp database/migrations_archive/* database/migrations/
   rm database/migrations/2025_01_01_000000_create_baseline_schema.php
   ```

3. **Verify migrations table**:
   ```bash
   php artisan migrate:status
   ```

---

## Getting Help

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable query logging in `.env`: `DB_LOG_QUERIES=true`
3. Review the baseline migration: `database/migrations/2025_01_01_000000_create_baseline_schema.php`
4. Compare against archived migrations: `database/migrations_archive/`

---

## Post-Migration Checklist

After successfully migrating:

- [ ] Verify all tables exist: `php artisan tinker` → `Schema::hasTable(...)`
- [ ] Run test suite: `php artisan test`
- [ ] Check application logs for errors: `tail -f storage/logs/laravel.log`
- [ ] Test critical user flows (login, create organization, etc.)
- [ ] Verify data integrity (counts, relationships)
- [ ] Document any schema customizations you made
- [ ] Update your deployment documentation
- [ ] Train team on new migration process

---

## Additional Notes

### Why Squash Migrations?

The original 74 migrations had several problems:
- **Order dependencies**: Some migrations assumed others ran first
- **Schema drift**: Incremental changes led to inconsistencies
- **Deployment issues**: Complex migration chains caused failures
- **Maintenance burden**: Hard to understand final schema state

### Future Migrations

Going forward:
- New schema changes will be added as **new migration files** after the baseline
- The baseline should remain untouched
- If major schema changes are needed, consider creating a new baseline and archiving everything again

### Testing Migrations

Always test migrations in a staging environment:

```bash
# Create test database
php artisan db:create dolphin_test

# Run migrations
php artisan migrate --env=testing

# Run tests
php artisan test
```

---

Last Updated: 2025-01-01
Migration Baseline Version: 2025_01_01_000000
