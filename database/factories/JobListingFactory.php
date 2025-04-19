<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\JobListing;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobListing>
 */
class JobListingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = JobListing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'             => fake()->sentence(3),
            'short_description' => fake()->sentence(5),
            'headline'          => fake()->paragraph(),
            'location'          => fake()->country() . ' / ' . fake()->city(),
            'job_type'          => fake()->randomElement(['Full-Time', 'Part-Time', 'Contract']),
            'workplace'         => [fake()->randomElement(['On-Site', 'Hybrid', 'Remote'])],
            'status'            => fake()->randomElement(['active', 'inactive', 'draft', 'disable']),
            'date_opened'       => fake()->date(),
            'responsibilities'  => fake()->paragraph(),
            'requirements'      => fake()->paragraph(),
            'bonus'             => fake()->sentence(),
            'benefits'          => fake()->sentence(),
        ];
    }
}