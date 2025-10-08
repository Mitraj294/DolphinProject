# Quick Deployment Guide - Database Migration

⚠️ **For detailed instructions, see [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md)**

## For Fresh Installations (New Deployments)

```bash
# 1. Configure .env with database credentials
# 2. Run migrations
php artisan migrate --force

# 3. Install Passport
php artisan passport:install

# Done! ✅
```

---

## For Existing Production Databases

### ⚠️ CRITICAL: Backup First!

```bash
# Backup your database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

### Recommended Approach for Production

```bash
# 1. Ensure all old migrations have run
php artisan migrate --force

# 2. Stop the application (maintenance mode)
php artisan down

# 3. Truncate migrations table
mysql -u username -p database_name -e "TRUNCATE TABLE migrations;"

# 4. Run the baseline migration
php artisan migrate --force

# 5. Bring application back up
php artisan up

# 6. Verify everything works
php artisan tinker
>>> Schema::hasTable('users')
>>> Schema::hasTable('organizations')
>>> Schema::hasTable('assessments')
```

### Verification Checklist

After migration, verify:

- [ ] All tables exist: `php artisan tinker` → check key tables
- [ ] Foreign keys are intact: Check relationships work
- [ ] Data is accessible: Query critical tables
- [ ] Application starts: `php artisan serve`
- [ ] Key features work: Login, create records, etc.

---

## Rollback Plan

If issues occur:

```bash
# 1. Restore from backup
mysql -u username -p database_name < backup_YYYYMMDD.sql

# 2. Contact development team
```

---

## What Changed?

- **Before**: 74 migration files with order dependencies and drift issues
- **After**: 1 baseline migration that creates complete schema
- **Result**: Faster deployments, no migration conflicts, easier maintenance

---

## Need Help?

1. Read [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) for detailed instructions
2. Check Laravel logs: `storage/logs/laravel.log`
3. Test in staging environment first
4. Contact development team if issues arise

---

**Migration Date**: 2025-01-01  
**Baseline Migration**: `2025_01_01_000000_create_baseline_schema.php`  
**Tables Created**: 44 tables  
**Archived Migrations**: 74 files in `database/migrations_archive/`
