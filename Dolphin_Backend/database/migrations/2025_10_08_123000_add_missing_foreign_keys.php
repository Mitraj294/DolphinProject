<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMissingForeignKeys extends Migration
{
    private const ON_DELETE_SET_NULL = 'set null';

    public function up(): void
    {
        $constraints = [
            // constraint_name => [table, column, referenced_table, referenced_column, onDelete]
            'announcement_admin_announcement_id_foreign' => ['announcement_admin','announcement_id','announcements','id','cascade'],
            'announcement_admin_admin_id_foreign' => ['announcement_admin','admin_id','users','id','cascade'],
            'announcement_group_announcement_id_foreign' => ['announcement_group','announcement_id','announcements','id','cascade'],
            'announcement_group_group_id_foreign' => ['announcement_group','group_id','groups','id','cascade'],
            'announcement_organization_announcement_id_foreign' => ['announcement_organization','announcement_id','announcements','id','cascade'],
            'announcement_organization_organization_id_foreign' => ['announcement_organization','organization_id','organizations','id','cascade'],
            'answers_user_id_foreign' => ['answers','user_id','users','id','cascade'],
            'assessment_question_assessment_id_foreign' => ['assessment_question','assessment_id','assessments','id','cascade'],
            'assessment_question_question_id_foreign' => ['assessment_question','question_id','organization_assessment_questions','id','cascade'],
            'assessment_question_answers_assessment_id_foreign' => ['assessment_question_answers','assessment_id','assessments','id','cascade'],
            'assessment_schedules_assessment_id_foreign' => ['assessment_schedules','assessment_id','assessments','id','cascade'],
            'assessments_organization_id_foreign' => ['assessments','organization_id','organizations','id',self::ON_DELETE_SET_NULL],
            'assessments_user_id_foreign' => ['assessments','user_id','users','id',self::ON_DELETE_SET_NULL],
            'group_member_group_id_foreign' => ['group_member','group_id','groups','id','cascade'],
            'group_member_member_id_foreign' => ['group_member','member_id','members','id','cascade'],
            'groups_organization_id_foreign' => ['groups','organization_id','organizations','id','cascade'],
            'guest_links_user_id_foreign' => ['guest_links','user_id','users','id','cascade'],
            'leads_created_by_foreign' => ['leads','created_by','users','id',self::ON_DELETE_SET_NULL],
            'member_member_role_member_id_foreign' => ['member_member_role','member_id','members','id','cascade'],
            'member_member_role_member_role_id_foreign' => ['member_member_role','member_role_id','member_roles','id','cascade'],
            'members_organization_id_foreign' => ['members','organization_id','organizations','id','cascade'],
            'organizations_sales_person_id_foreign' => ['organizations','sales_person_id','users','id',self::ON_DELETE_SET_NULL],
            'organizations_user_id_foreign' => ['organizations','user_id','users','id',self::ON_DELETE_SET_NULL],
            'scheduled_emails_assessment_id_foreign' => ['scheduled_emails','assessment_id','assessments','id','cascade'],
            'scheduled_emails_group_id_foreign' => ['scheduled_emails','group_id','groups','id',self::ON_DELETE_SET_NULL],
            'scheduled_emails_member_id_foreign' => ['scheduled_emails','member_id','members','id',self::ON_DELETE_SET_NULL],
            'subscriptions_user_id_foreign' => ['subscriptions','user_id','users','id','cascade'],
            'user_details_city_id_foreign' => ['user_details','city_id','cities','id',self::ON_DELETE_SET_NULL],
            'user_details_country_id_foreign' => ['user_details','country_id','countries','id',self::ON_DELETE_SET_NULL],
            'user_details_state_id_foreign' => ['user_details','state_id','states','id',self::ON_DELETE_SET_NULL],
            'user_details_user_id_foreign' => ['user_details','user_id','users','id','cascade'],
            'user_roles_role_id_foreign' => ['user_roles','role_id','roles','id','cascade'],
            'user_roles_user_id_foreign' => ['user_roles','user_id','users','id','cascade'],
            'users_organization_id_foreign' => ['users','organization_id','organizations','id',self::ON_DELETE_SET_NULL],
        ];

    $dbName = DB::getDatabaseName();
    $driver = DB::connection()->getDriverName();

        foreach ($constraints as $name => $cfg) {
            [$table, $column, $refTable, $refCol, $onDelete] = $cfg;

            // check whether constraint exists (driver-specific)
            if ($driver === 'pgsql') {
                // Postgres: look up constraint by name in pg_constraint
                $exists = DB::selectOne("SELECT conname FROM pg_constraint WHERE conname = ?", [$name]);
            } else {
                // MySQL and other DBs: look up by constraint name in information_schema.TABLE_CONSTRAINTS
                $exists = DB::selectOne(
                    "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND CONSTRAINT_NAME = ?",
                    [$dbName, $name]
                );
            }

            if ($exists) {
                continue;
            }

            // Build driver-appropriate SQL for adding the FK
            if ($driver === 'pgsql') {
                // Postgres: unquoted identifiers (assumes lower-case names)
                $constraintSql = sprintf(
                    'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s(%s) ON DELETE %s',
                    $table,
                    $name,
                    $column,
                    $refTable,
                    $refCol,
                    strtoupper($onDelete)
                );
            } else {
                // MySQL and others: use backticks for identifiers
                $constraintSql = sprintf(
                    'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`) ON DELETE %s',
                    $table,
                    $name,
                    $column,
                    $refTable,
                    $refCol,
                    strtoupper($onDelete)
                );
            }

            DB::statement($constraintSql);
        }
    }

    public function down(): void
    {
        $constraintNames = [
            'announcement_admin_announcement_id_foreign', 'announcement_admin_admin_id_foreign',
            'announcement_group_announcement_id_foreign','announcement_group_group_id_foreign',
            'announcement_organization_announcement_id_foreign','announcement_organization_organization_id_foreign',
            'answers_user_id_foreign','assessment_question_assessment_id_foreign','assessment_question_question_id_foreign',
            'assessment_question_answers_assessment_id_foreign','assessment_schedules_assessment_id_foreign',
            'assessments_organization_id_foreign','assessments_user_id_foreign','group_member_group_id_foreign',
            'group_member_member_id_foreign','groups_organization_id_foreign','guest_links_user_id_foreign',
            'leads_created_by_foreign','member_member_role_member_id_foreign','member_member_role_member_role_id_foreign',
            'members_organization_id_foreign','organizations_sales_person_id_foreign','organizations_user_id_foreign',
            'scheduled_emails_assessment_id_foreign','scheduled_emails_group_id_foreign','scheduled_emails_member_id_foreign',
            'subscriptions_user_id_foreign','user_details_city_id_foreign','user_details_country_id_foreign',
            'user_details_state_id_foreign','user_details_user_id_foreign','user_roles_role_id_foreign',
            'user_roles_user_id_foreign','users_organization_id_foreign'
        ];

        $driver = DB::connection()->getDriverName();

        foreach ($constraintNames as $name) {
            // Try to find the table for the constraint (MySQL/info_schema first)
            $row = DB::selectOne("SELECT TABLE_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND CONSTRAINT_NAME = ?", [DB::getDatabaseName(), $name]);
            $table = null;
            if ($row) {
                $table = $row->TABLE_NAME;
            } elseif ($driver === 'pgsql') {
                // Postgres: resolve the table name from pg_constraint
                $row = DB::selectOne("SELECT conrelid::regclass::text AS table_name FROM pg_constraint WHERE conname = ?", [$name]);
                if ($row) {
                    $table = $row->table_name ?? $row->TABLE_NAME ?? null;
                }
            }

            if (! $table) {
                continue;
            }

            if ($driver === 'pgsql') {
                DB::statement(sprintf('ALTER TABLE %s DROP CONSTRAINT %s', $table, $name));
            } else {
                DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $name));
            }
        }
    }
}
