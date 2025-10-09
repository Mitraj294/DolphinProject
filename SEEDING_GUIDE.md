# Database Seeding Guide

## ✅ Seed Script Ready!

Your database can now be seeded with all necessary data using the `seed.js` script.

## 📦 What Gets Seeded

The `seed.js` script seeds the following tables:

### 1. **Roles** (7 roles)
- superadmin
- user
- organizationadmin
- dolphinadmin
- salesperson

### 2. **Member Roles** (4 roles)
- Manager
- CEO
- Owner
- Support

### 3. **Organization Assessment Questions** (5 questions)
- What is your current role in the organization?
- How long have you been a member of the staff?
- What are your main responsibilities?
- What challenges do you face in your daily work?
- What support or resources would help you perform better?

### 4. **Assessment Questions** (2 questions with 15 options each)
- Q.1: How other people expect you to be
- Q.2: How you really are

Options: Relaxed, Persuasive, Stable, Charismatic, Individualistic, Optimistic, Conforming, Methodical, Serious, Friendly, Humble, Unrestrained, Competitive, Docile, Restless

## 🚀 How to Run

### Option 1: Node.js Seeder (Quick - for basic data)

```bash
# From the project root
node seed.js
```

**Output:**
```
✓ Connected to database...
📋 Seeding Roles...
✓ Roles: 7 in database
👥 Seeding Member Roles...
✓ Member Roles: 4 in database
...
✅ SEEDING COMPLETE!
```

### Option 2: Laravel Seeders (Complete - for all data including users)

```bash
# From Dolphin_Backend directory
cd Dolphin_Backend
php artisan db:seed
```

This will run:
- `DatabaseSeeder` - Main seeder
- `MemberRoleSeeder` - Member roles
- `OrganizationAssessmentQuestionSeeder` - Organization questions
- `OrganizationSeeder` - Sample organizations

## 👤 Default Super Admin

After running Laravel seeder, you'll have a super admin:

```
Email: sdolphin632@gmail.com
Password: superadmin@123
```

## 🔄 Re-running Seeders

Both seeders are safe to re-run:
- They check for existing data before inserting
- Won't create duplicates
- Idempotent operations

## 📋 Seeder Files Location

Laravel seeders are located in:
```
Dolphin_Backend/database/seeders/
├── DatabaseSeeder.php                          (Main)
├── MemberRoleSeeder.php                        (Member roles)
├── OrganizationAssessmentQuestionSeeder.php    (Org questions)
└── OrganizationSeeder.php                      (Organizations)
```

## 🔧 Environment-Specific Seeding

### Local Development
```bash
# Use Laravel seeders for full local setup
php artisan migrate:fresh --seed
```

### Production (Render)
```bash
# Run Node.js seeder for basic data
node seed.js

# Or run Laravel seeder
php artisan db:seed --force
```

## ⚙️ Customizing Seeders

### Add More Organizations

Edit `Dolphin_Backend/database/seeders/OrganizationSeeder.php`:

```php
DB::table('organizations')->insert([
    [
        'organization_name' => 'Your Org Name',
        'organization_size' => 'Medium',
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);
```

### Add More Roles

Edit `seed.js` or `DatabaseSeeder.php`:

```javascript
// In seed.js
const roles = ['superadmin', 'user', 'customrole'];
```

```php
// In DatabaseSeeder.php
$roles = ['superadmin', 'user', 'customrole'];
```

## 🐛 Troubleshooting

### "Table does not exist"
**Solution:** Run migrations first
```bash
php artisan migrate --force
```

### "Column does not exist"
**Solution:** Check your database schema matches the migrations
```bash
php artisan migrate:status
```

### "Connection refused"
**Solution:** Check database credentials
```bash
# Check .env file
DB_CONNECTION=pgsql
DB_HOST=your-host
DB_DATABASE=your-database
```

## 📊 Verify Seeding

Check what was seeded:

```bash
# Using Laravel Tinker
php artisan tinker
>>> \App\Models\Role::count();
>>> \App\Models\User::count();
>>> \App\Models\MemberRole::count();
```

Or using PostgreSQL:

```bash
psql $DATABASE_URL -c "SELECT COUNT(*) FROM roles;"
psql $DATABASE_URL -c "SELECT COUNT(*) FROM users;"
```

## 🎯 Production Deployment

For Render deployment, seeders can be run after migrations in `docker-entrypoint.sh`:

```bash
# Run migrations
php artisan migrate --force --no-interaction

# Run seeders (optional - only on first deploy)
php artisan db:seed --force --class=DatabaseSeeder
```

**Note:** Comment out seeder call after first deployment to avoid duplicate data.

## ✅ Summary

- ✅ `seed.js` - Quick Node.js seeder for basic tables
- ✅ Laravel Seeders - Complete seeding with users and organizations
- ✅ Both are safe to re-run
- ✅ Works locally and on Render
- ✅ Super admin created automatically

---

**Ready to seed!** Run `node seed.js` or `php artisan db:seed`
