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

        foreach ($constraints as $name => $cfg) {
            [$table, $column, $refTable, $refCol, $onDelete] = $cfg;

            // check whether constraint exists
            $exists = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = ?", [$dbName, $table, $column, $refTable, $refCol]);

            if ($exists) {
                continue;
            }

            // Add FK using raw SQL because Laravel's Schema builder won't create with the exact name easily
            $constraintSql = sprintf(
                'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`) ON DELETE %s',
                $table,
                $name,
                $column,
                $refTable,
                $refCol,
                strtoupper($onDelete)
            );

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

        foreach ($constraintNames as $name) {
            // Find the table for the constraint
            $row = DB::selectOne("SELECT TABLE_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND CONSTRAINT_NAME = ?", [DB::getDatabaseName(), $name]);
            if (! $row) {
                continue;
            }
            $table = $row->TABLE_NAME;
            DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $name));
        }
    }
}
