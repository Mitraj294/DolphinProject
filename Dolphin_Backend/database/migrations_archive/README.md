# Archived Migrations

This directory contains all historical migrations that have been **replaced** by the baseline migration.

## ⚠️ Important Notice

**These migrations are archived for reference only and should NOT be executed.**

The baseline migration `2025_01_01_000000_create_baseline_schema.php` (located in `database/migrations/`) replaces all migrations in this archive directory.

## What's Archived

This directory contains **74 migration files** from the original DolphinProject database evolution:

- Date Range: 2025-07-17 through 2025-10-08
- Total Files: 74 migrations
- Status: Archived on 2025-01-01

## Migration Timeline

### Initial Setup (July 2025)
- User management tables (users, roles, user_details)
- Organization management
- OAuth/Passport integration
- Session management
- Lead tracking
- Subscription management

### Assessment System (Late July - August 2025)
- Assessment tables and relationships
- Question and answer management
- Scheduling system
- Email scheduling
- Token-based access

### Location Management (August 2025)
- Country, state, city reference tables
- Migration of location fields across tables
- Foreign key relationships

### Member & Group System (July - September 2025)
- Member management with roles
- Group system
- Pivot tables for relationships
- Soft delete support

### Announcement System (August - September 2025)
- Notifications to announcements refactoring
- Pivot tables for targeted announcements
- Scheduling and dispatching support

### Refinements (September - October 2025)
- Schema corrections
- Foreign key improvements
- Nullable field adjustments
- Unique constraint updates
- Guest link system
- Payment method details

## Why These Were Archived

The incremental migration approach led to several issues:

1. **Order Dependencies**: Migrations assumed specific execution order
2. **Schema Drift**: Multiple modifications to same tables caused inconsistencies
3. **Deployment Failures**: Complex chains were fragile in production
4. **SQLite Compatibility**: Some migrations didn't work with SQLite (testing)
5. **Maintenance Burden**: Hard to understand current schema state
6. **Duplicate Logic**: Multiple migrations modifying same structures

## Reference Purpose Only

Keep these files for:
- Historical reference
- Understanding schema evolution
- Troubleshooting legacy data
- Audit and compliance requirements
- Learning what NOT to do in future migrations 😊

## Restoration (Emergency Only)

To restore old migrations (NOT RECOMMENDED):

```bash
# 1. Backup current state first!
mysqldump -u user -p database > backup_before_restore.sql

# 2. Copy migrations back
cp database/migrations_archive/*.php database/migrations/

# 3. Remove baseline migration
rm database/migrations/2025_01_01_000000_create_baseline_schema.php

# 4. Reset database
php artisan migrate:fresh

# 5. Run old migrations
php artisan migrate
```

⚠️ **Warning**: This will likely encounter the same issues that led to archiving these migrations in the first place.

## List of Archived Migrations

<details>
<summary>Click to expand full list of 74 archived migrations</summary>

1. 0001_01_01_000001_create_cache_table.php
2. 0001_01_01_000002_create_jobs_table.php
3. 2025_07_17_104921_create_users_table.php
4. 2025_07_18_000001_create_assessments_questions_answers_tables.php
5. 2025_07_22_053846_create_sessions_table.php
6. 2025_07_22_130000_create_leads_table.php
7. 2025_07_23_062340_create_oauth_auth_codes_table.php
8. 2025_07_23_062341_create_oauth_access_tokens_table.php
9. 2025_07_23_062342_create_oauth_refresh_tokens_table.php
10. 2025_07_23_062343_create_oauth_clients_table.php
11. 2025_07_23_062344_create_oauth_device_codes_table.php
12. 2025_07_23_110332_create_password_reset_tokens_table.php
13. 2025_07_23_124205_create_organizations_table.php
14. 2025_07_24_111503_create_subscriptions_table.php
15. 2025_07_25_000001_make_stripe_subscription_id_nullable_in_subscriptions.php
16. 2025_07_25_120000_update_subscriptions_table_add_invoice_customer_fields.php
17. 2025_07_28_150000_create_roles_table.php
18. 2025_07_28_150001_create_user_roles_table.php
19. 2025_07_29_000001_create_groups_table.php
20. 2025_07_29_000002_create_members_table.php
21. 2025_07_29_000003_create_group_member_table.php
22. 2025_07_31_000001_create_user_details_table.php
23. 2025_07_31_170000_add_user_id_to_groups_and_members.php
24. 2025_07_31_180000_create_member_roles_table.php
25. 2025_07_31_190000_create_assessments_and_questions_tables.php
26. 2025_08_01_000001_create_assessment_schedules_table.php
27. 2025_08_01_120000_create_organization_assessment_questions_table.php
28. 2025_08_01_130000_create_assessments_table.php
29. 2025_08_04_000001_create_assessment_answer_links_table.php
30. 2025_08_04_061434_create_scheduled_emails_table.php
31. 2025_08_05_000000_create_assessment_answer_tokens_table.php
32. 2025_08_05_000001_create_assessment_question_answers_table.php
33. 2025_08_05_123900_add_group_id_to_assessment_answer_tokens_table.php
34. 2025_08_05_150000_create_assessment_question_table.php
35. 2025_08_08_000002_add_assessment_question_id_to_answers_table.php
36. 2025_08_11_140000_add_assessment_id_to_scheduled_emails_table.php
37. 2025_08_12_000001_add_group_id_member_id_to_scheduled_emails_table.php
38. 2025_08_13_055927_update_location_fields_on_all_tables.php
39. 2025_08_18_000000_remove_country_id_from_users_table.php
40. 2025_08_18_000001_add_user_and_organization_id_to_assessments_table.php
41. 2025_08_18_000001_rename_notifications_to_announcements_and_create_pivots.php
42. 2025_08_19_045032_create_notifications_table.php
43. 2025_08_20_000001_make_groups_user_id_nullable.php
44. 2025_08_20_000002_make_members_user_id_nullable.php
45. 2025_08_21_083800_remove_phone_from_users.php
46. 2025_08_21_090000_reorder_users_columns.php
47. 2025_08_21_100000_drop_names_from_user_details.php
48. 2025_08_21_100001_drop_email_from_user_details.php
49. 2025_08_21_100002_drop_org_columns.php
50. 2025_08_22_000001_add_notes_to_leads_table.php
51. 2025_08_22_120000_add_last_login_to_organizations_table.php
52. 2025_08_22_121500_convert_last_contacted_and_drop_last_login.php
53. 2025_08_26_000001_add_status_to_leads_table.php
54. 2025_08_26_000002_add_timestamps_to_leads_table.php
55. 2025_08_29_000000_migrate_member_roles_to_pivot.php
56. 2025_08_29_010000_add_soft_deletes_to_members.php
57. 2025_08_29_020000_make_members_email_unique_with_deleted_at.php
58. 2025_08_29_030000_make_users_email_unique_with_deleted_at.php
59. 2025_09_01_000000_add_soft_deletes_and_nullable_user_id.php
60. 2025_09_01_120000_ensure_passport_personal_client.php
61. 2025_09_08_063406_add_payment_method_details_to_subscriptions_table.php
62. 2025_09_11_000000_add_org_name_to_organizations_table.php
63. 2025_09_11_00000_add_organization_size_to_organizations_table.php
64. 2025_09_11_093407_remove_organization_name&organization_size_from_User_details.php
65. 2025_09_12_000100_add_created_by_to_leads_table.php
66. 2025_09_12_000101_add_sales_person_id_to_organizations_table.php
67. 2025_09_19_000001_add_organization_id_to_users.php
68. 2025_09_19_121022_add_dispatched_at_to_announcements_table.php
69. 2025_09_22_100000_drop_scheduling_tables.php
70. 2025_09_26_000000_make_group_id_nullable_in_assessment_question_answers.php
71. 2025_09_26_053204_recreate_assessment_and_scheduled_email_tables.php
72. 2025_09_29_000000_add_remember_token_to_users.php
73. 2025_09_30_000001_create_guest_links_table.php
74. 2025_10_08_000000_add_assessment_fk_to_schedules.php

</details>

## Future Migrations

Going forward, any new database schema changes should be added as **new migration files** in the `database/migrations/` directory. The baseline migration should remain as-is and serve as the foundation.

If the schema grows too complex again, consider creating a new baseline (e.g., `2026_01_01_000000_create_baseline_schema_v2.php`) and archiving everything once more.

---

**Archive Date**: 2025-01-01  
**Baseline Migration**: 2025_01_01_000000_create_baseline_schema.php  
**Total Archived Migrations**: 74
