<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\Candidate;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = JobApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id'          => JobListing::inRandomOrder()->value('id'),
            'applicant_id'    => Candidate::inRandomOrder()->value('id'),
            'cv_path'         => fake()->url(),
            'cv_disk'         => null,
            'city'            => fake()->city(),
            'country'         => fake()->country(),
            'status'          => fake()->randomElement(['submitted', 'under review', 'accepted', 'rejected']),
            'rejection_sent'  => fake()->boolean(),
            'notice_period'   => fake()->randomElement(['2 weeks', '1 month', '3 months']),
            'desired_salary'  => fake()->randomFloat(2, 30000, 100000),
            'salary_currency' => 'USD',
            'linkedin_profile'=> fake()->url(),
            'github_profile'  => fake()->url(),
            'how_heard'       => fake()->sentence(3),
            'submitted_at'    => fake()->dateTime(),
        ];
    }
}