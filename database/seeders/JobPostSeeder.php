<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;
use App\Models\JobListing;
use App\Models\Candidate;
use App\Models\JobApplication;
use App\Models\JobScreeningQuestion;
use App\Models\JobApplicationAnswer;

class JobPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ($this->command->confirm('Do you want to truncate all job posting tables?', false)) {
            // Disable foreign key checks for truncation
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            JobApplicationAnswer::truncate();
            JobApplication::truncate();
            JobScreeningQuestion::truncate();
            Candidate::truncate();
            JobListing::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Create sample data
        JobListing::factory()->count(20)->create();
        Candidate::factory()->count(50)->create();
        JobApplication::factory()->count(100)->create();
        JobScreeningQuestion::factory()->count(60)->create();

        // Generate answers for each application
        foreach (JobApplication::all() as $application) {
            $job = $application->jobListing;
            if ($job) {
                foreach ($job->screeningQuestions as $question) {
                    $faker = FakerFactory::create();
                    if ($question->question_type === 'yes/no') {
                        $answer_text = $faker->randomElement(['yes', 'no']);
                    } else {
                        $answer_text = (string) $faker->numberBetween(1, 150);
                    }

                    JobApplicationAnswer::create([
                        'application_id' => $application->id,
                        'question_id'    => $question->id,
                        'answer_text'    => $answer_text,
                    ]);
                }
            }
        }
    }
}