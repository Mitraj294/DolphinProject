# PostgreSQL Database Management - Web Interface Guide

## 🌐 Web-Based Database Viewers (Like phpMyAdmin for PostgreSQL)

### **Option 1: Adminer (Recommended - Easiest)** ⭐

Adminer is like phpMyAdmin but supports PostgreSQL.

#### **Setup with Docker:**

```bash
# Start Adminer
docker-compose -f docker-compose.adminer.yml up -d

# Open in browser
http://localhost:8080
```

#### **Login Details:**
- **System:** PostgreSQL
- **Server:** `dpg-d3jlcimr433s739dgco0-a.oregon-postgres.render.com`
- **Username:** `dolphin123`
- **Password:** `CufoUH0IXfeUWyb4TXMXFpHoyQ7K7gNm`
- **Database:** `dolphin_db_s1zc`

#### **Or use Public Adminer:**
Visit: https://adminer.cs.ui.ac.id/ (public instance)

---

### **Option 2: pgAdmin (Most Powerful)** 🔧

Professional PostgreSQL management tool.

#### **Setup with Docker:**

```bash
# Start pgAdmin
docker-compose -f docker-compose.pgadmin.yml up -d

# Open in browser
http://localhost:5050
```

#### **Login:**
- **Email:** `admin@dolphin.local`
- **Password:** `admin123`

#### **Add Server:**
1. Right-click "Servers" → "Register" → "Server"
2. **General Tab:**
   - Name: `Render Dolphin DB`
3. **Connection Tab:**
   - Host: `dpg-d3jlcimr433s739dgco0-a.oregon-postgres.render.com`
   - Port: `5432`
   - Database: `dolphin_db_s1zc`
   - Username: `dolphin123`
   - Password: `CufoUH0IXfeUWyb4TXMXFpHoyQ7K7gNm`
4. Click "Save"

---

### **Option 3: DBeaver (Desktop App - Free)** 💻

Cross-platform database tool (like phpMyAdmin but desktop).

#### **Download:**
https://dbeaver.io/download/

#### **Setup:**
1. Download and install DBeaver
2. New Connection → PostgreSQL
3. Enter connection details:
   - Host: `dpg-d3jlcimr433s739dgco0-a.oregon-postgres.render.com`
   - Port: `5432`
   - Database: `dolphin_db_s1zc`
   - Username: `dolphin123`
   - Password: `CufoUH0IXfeUWyb4TXMXFpHoyQ7K7gNm`
4. Click "Test Connection" → "Finish"

---

### **Option 4: TablePlus (Desktop - Beautiful UI)** ✨

Modern, native database client.

#### **Download:**
https://tableplus.com/

#### **Free Alternative:**
https://www.beekeeperstudio.io/ (Open source)

---

### **Option 5: Render Shell (Built-in Terminal)** 🖥️

Access database directly from Render Dashboard.

#### **Steps:**
1. Go to: https://dashboard.render.com
2. Select your `dolphin-db` database
3. Click **"Shell"** tab
4. Run SQL queries directly:

```sql
-- List all tables
\dt

-- Count users
SELECT COUNT(*) FROM users;

-- View all roles
SELECT * FROM roles;

-- View organizations
SELECT * FROM organizations;
```

---

### **Option 6: Laravel Tinker (In Your App)** 🔨

Query your database using Laravel's ORM.

```bash
cd Dolphin_Backend
php artisan tinker
```

```php
// Count users
User::count();

// Get all roles
Role::all();

// Get organizations
\App\Models\Organization::all();

// Query specific data
User::where('email', 'sdolphin632@gmail.com')->first();
```

---

## 🚀 Quick Start (Recommended)

### **Use Adminer (Fastest):**

```bash
# 1. Start Adminer
docker-compose -f docker-compose.adminer.yml up -d

# 2. Open browser
open http://localhost:8080
# Or visit: http://localhost:8080

# 3. Login with:
# System: PostgreSQL
# Server: dpg-d3jlcimr433s739dgco0-a.oregon-postgres.render.com
# Username: dolphin123
# Password: CufoUH0IXfeUWyb4TXMXFpHoyQ7K7gNm
# Database: dolphin_db_s1zc

# 4. Browse your tables!
```

---

## 📊 Connection Details Summary

### **Your Render PostgreSQL Database:**

```
Host:     dpg-d3jlcimr433s739dgco0-a.oregon-postgres.render.com
Port:     5432
Database: dolphin_db_s1zc
Username: dolphin123
Password: CufoUH0IXfeUWyb4TXMXFpHoyQ7K7gNm
```

### **Connection String:**
```
postgresql://dolphin123:CufoUH0IXfeUWyb4TXMXFpHoyQ7K7gNm@dpg-d3jlcimr433s739dgco0-a.oregon-postgres.render.com/dolphin_db_s1zc
```

---

## 🛠️ Comparison Table

| Tool | Type | Ease of Use | Features | Best For |
|------|------|-------------|----------|----------|
| **Adminer** | Web | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | Quick viewing |
| **pgAdmin** | Web/Desktop | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Advanced users |
| **DBeaver** | Desktop | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | Developers |
| **TablePlus** | Desktop | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | Mac/Windows users |
| **Render Shell** | Web | ⭐⭐⭐ | ⭐⭐ | Quick queries |
| **Laravel Tinker** | CLI | ⭐⭐⭐ | ⭐⭐⭐⭐ | Developers |

---

## 🔒 Security Notes

⚠️ **Never commit database credentials to Git!**

The connection details above are already in your code. Make sure to:
1. Use environment variables in production
2. Rotate passwords regularly
3. Don't share credentials publicly
4. Use SSL connections (already enabled on Render)

---

## 🎯 My Recommendation

**For Quick Viewing (Like phpMyAdmin):**
→ Use **Adminer** (just run the docker-compose command)

**For Development & Advanced Features:**
→ Use **DBeaver** or **TablePlus** (desktop apps)

**For Quick Queries:**
→ Use **Render Shell** (built into Render Dashboard)

---

## 📝 Example Queries to Run

Once connected, try these:

```sql
-- View all tables
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'public';

-- Count records in each table
SELECT 
  schemaname,
  tablename,
  (SELECT COUNT(*) FROM users) as users_count,
  (SELECT COUNT(*) FROM roles) as roles_count,
  (SELECT COUNT(*) FROM organizations) as organizations_count;

-- View all roles
SELECT * FROM roles;

-- View all users with their roles
SELECT u.email, r.name as role
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id;
```

---

**Ready to view your database!** Just run the Adminer docker-compose command and open http://localhost:8080 🚀
