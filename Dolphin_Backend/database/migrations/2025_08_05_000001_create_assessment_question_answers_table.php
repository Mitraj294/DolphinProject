<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        if (! Schema::hasTable('assessment_question_answers')) {
            Schema::create('assessment_question_answers', function (Blueprint $table) {
                $table->id();

                // The assessment this answer belongs to
                $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');

            // The organization-level question template (static question text)
            $table->unsignedBigInteger('organization_assessment_question_id');
            $table->foreign('organization_assessment_question_id', 'aqa_org_q_id_fk')
                ->references('id')->on('organization_assessment_questions')->onDelete('cascade');

                // Link to the assessment-specific question entry. Make NOT NULL to match dolphin_db
                $table->foreignId('assessment_question_id')->constrained('assessment_question')->onDelete('cascade');

                // The member who answered (if applicable)
                $table->foreignId('member_id')->constrained('members')->onDelete('cascade');

                // The group this answer applies to; make nullable to allow member-only answers
                $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('cascade');

                $table->text('answer');
                $table->timestamps();

                // Unique per assessment/question/member/group combination
                $table->unique(['assessment_id', 'organization_assessment_question_id', 'member_id', 'group_id'], 'aqa_unique');
            });
        }
    }

    public function down() {
        Schema::dropIfExists('assessment_question_answers');
    }
};
