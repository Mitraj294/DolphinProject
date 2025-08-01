<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrganizationAssessmentQuestion;

class OrganizationAssessmentQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            'What is your current role in the organization?',
            'How long have you been a member of the staff?',
            'What are your main responsibilities?',
            'What challenges do you face in your daily work?',
            'What support or resources would help you perform better?',
        ];
        foreach ($questions as $text) {
            OrganizationAssessmentQuestion::create(['text' => $text]);
        }
    }
}
