# Schema differences report
Source: dolphin_db (left) vs dolphin_db_test (right)

## Table: announcement_admin
- No differences

## Table: announcement_group
- No differences

## Table: announcement_organization
- No differences

## Table: announcements
- No differences

## Table: answers
- No differences

## Table: assessment_answer_links
- No differences

## Table: assessment_answer_tokens
- No differences

## Table: assessment_question
- No differences

## Table: assessment_question_answers
- Columns with differing definitions:
  - assessment_question_id:
      - dolphin_db: bigint unsigned | null=NO | default=NULL | extra=
      - dolphin_db_test: bigint unsigned | null=YES | default=NULL | extra=

## Table: assessment_schedules
- No differences

## Table: assessments
- No differences

## Table: cache
- No differences

## Table: cache_locks
- No differences

## Table: cities
- No differences

## Table: countries
- No differences

## Table: failed_jobs
- No differences

## Table: group_member
- No differences

## Table: groups
- No differences

## Table: guest_links
- No differences

## Table: job_batches
- No differences

## Table: jobs
- No differences

## Table: jobs_backup
- No differences

## Table: leads
- No differences

## Table: member_member_role
- No differences

## Table: member_roles
- No differences

## Table: members
- No differences

## Table: migrations
- No differences

## Table: notifications
- No differences

## Table: oauth_access_tokens
- No differences

## Table: oauth_auth_codes
- No differences

## Table: oauth_clients
- No differences

## Table: oauth_device_codes
- No differences

## Table: oauth_refresh_tokens
- No differences

## Table: organization_assessment_questions
- No differences

## Table: organizations
- Columns only in `dolphin_db_test`:
  - source: varchar(255) | null=YES | default=NULL | extra=
  - address1: varchar(255) | null=YES | default=NULL | extra=
  - address2: varchar(255) | null=YES | default=NULL | extra=
  - country_id: bigint unsigned | null=YES | default=NULL | extra=
  - state_id: bigint unsigned | null=YES | default=NULL | extra=
  - city_id: bigint unsigned | null=YES | default=NULL | extra=
  - zip: varchar(255) | null=YES | default=NULL | extra=
  - main_contact: varchar(255) | null=YES | default=NULL | extra=
  - admin_email: varchar(255) | null=YES | default=NULL | extra=
  - admin_phone: varchar(255) | null=YES | default=NULL | extra=

## Table: password_reset_tokens
- No differences

## Table: questions
- No differences

## Table: roles
- No differences

## Table: scheduled_emails
- No differences

## Table: sessions
- No differences

## Table: states
- No differences

## Table: subscriptions
- No differences

## Table: user_details
- No differences

## Table: user_roles
- No differences

## Table: users
- Columns only in `dolphin_db_test`:
  - role: varchar(255) | null=NO | default=user | extra=
- Columns with differing definitions:
  - created_at:
      - dolphin_db: datetime | null=YES | default=NULL | extra=
      - dolphin_db_test: timestamp | null=YES | default=NULL | extra=
  - updated_at:
      - dolphin_db: datetime | null=YES | default=NULL | extra=
      - dolphin_db_test: timestamp | null=YES | default=NULL | extra=
  - deleted_at:
      - dolphin_db: datetime | null=YES | default=NULL | extra=
      - dolphin_db_test: timestamp | null=YES | default=NULL | extra=

## Foreign key differences
Patch diff of FK lists (dolphin_db vs dolphin_db_test):
```diff
--- fk_dolphin_db.txt	2025-10-10 11:33:09.918851121 +0530
+++ fk_dolphin_db_test.txt	2025-10-10 11:33:16.610101519 +0530
@@ -1,9 +1,5 @@
 announcement_admin	admin_id	users	id	announcement_admin_admin_id_foreign
-announcement_admin	announcement_id	announcements	id	announcement_admin_announcement_id_foreign
-announcement_group	announcement_id	announcements	id	announcement_group_announcement_id_foreign
 announcement_group	group_id	groups	id	announcement_group_group_id_foreign
-announcement_organization	announcement_id	announcements	id	announcement_organization_announcement_id_foreign
-announcement_organization	organization_id	organizations	id	announcement_organization_organization_id_foreign
 answers	user_id	users	id	answers_user_id_foreign
 assessment_question	assessment_id	assessments	id	assessment_question_assessment_id_foreign
 assessment_question	question_id	organization_assessment_questions	id	assessment_question_question_id_foreign
@@ -25,9 +21,6 @@
 scheduled_emails	group_id	groups	id	scheduled_emails_group_id_foreign
 scheduled_emails	member_id	members	id	scheduled_emails_member_id_foreign
 subscriptions	user_id	users	id	subscriptions_user_id_foreign
-user_details	city_id	cities	id	user_details_city_id_foreign
-user_details	country_id	countries	id	user_details_country_id_foreign
-user_details	state_id	states	id	user_details_state_id_foreign
 user_details	user_id	users	id	user_details_user_id_foreign
 user_roles	role_id	roles	id	user_roles_role_id_foreign
 user_roles	user_id	users	id	user_roles_user_id_foreign
```