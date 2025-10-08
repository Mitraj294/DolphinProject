<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal placeholder migration to restore missing class.
 *
 * The original migration file was empty which caused Render's
 * migrator to fail with "Class 'CreateAssessmentsAndQuestionsTables' not found".
 *
 * This placeholder provides the expected class so migrations can continue.
 * If the original migration contained table definitions that are required,
 * restore those definitions here from your backup. For now this is a no-op.
 */
class CreateAssessmentsAndQuestionsTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * Intentionally left empty to avoid breaking the migration sequence.
	 * Restore real schema here if needed.
	 */
	public function up(): void
	{
		// no-op placeholder
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// no-op placeholder
	}
}

