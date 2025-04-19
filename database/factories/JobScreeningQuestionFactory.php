<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\JobScreeningQuestion;
use App\Models\JobListing;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobScreeningQuestion>
 */
class JobScreeningQuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = JobScreeningQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $questionType = fake()->randomElement(['number', 'yes/no']);
        $minValue = $questionType === 'yes/no'
            ? 'yes'
            : (string) fake()->numberBetween(1, 100);

        return [
            'job_id'         => JobListing::inRandomOrder()->value('id'),
            'question_text'  => fake()->sentence(),
            'question_type'  => $questionType,
            'min_value'      => $minValue,
        ];
    }
}