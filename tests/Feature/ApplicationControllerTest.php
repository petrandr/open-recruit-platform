<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\JobListing;
use App\Models\Candidate;
use App\Models\JobScreeningQuestion;
use App\Models\JobApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful creation of a new application.
     */
    public function testStoreValidApplication(): void
    {
        Storage::fake();
        $job = JobListing::factory()->create(['status' => 'active']);
        // Create screening questions for this job
        $yesNoQuestion = JobScreeningQuestion::factory()->create([
            'job_id'        => $job->id,
            'question_type' => 'yes/no',
        ]);
        $numberQuestion = JobScreeningQuestion::factory()->create([
            'job_id'        => $job->id,
            'question_type' => 'number',
        ]);

        $data = [
            'job_id'                  => $job->id,
            'first_name'       => 'John',
            'last_name'        => 'Doe',
            'email'            => 'john.doe@example.com',
            'mobile_number'    => '1234567890',
            'cv'               => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            'city'             => 'Metropolis',
            'country'          => 'Freedonia',
            'notice_period'    => '2 weeks',
            'desired_salary'   => '55000',
            'salary_currency'  => 'USD',
            'linkedin_profile' => 'https://linkedin.com/in/johndoe',
            'github_profile'   => 'https://github.com/johndoe',
            'how_heard'        => 'Friend',
            'utm_params'       => json_encode(['utm_source' => 'newsletter', 'utm_medium' => 'email']),
            // Include answers to screening questions
            'question_' . $yesNoQuestion->id  => 'yes',
            'question_' . $numberQuestion->id => '42',
        ];

        $response = $this->postJson('/api/applications', $data);
        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'application_id']);

        $appId = $response->json('application_id');

        // Assert candidate record created
        $this->assertDatabaseHas('candidates', [
            'email' => 'john.doe@example.com',
        ]);

        // Assert job application record created
        $this->assertDatabaseHas('job_applications', [
            'id'           => $appId,
            'job_id'       => $job->id,
            'applicant_id' => Candidate::where('email', 'john.doe@example.com')->first()->id,
            'status'       => 'submitted',
        ]);

        // Assert CV was stored at the path recorded in the database
        $application = JobApplication::find($appId);

        Storage::disk($application->cv_disk)
            ->assertExists($application->cv_path);
    }

    /**
     * Test validation failure when job is inactive.
     */
    public function testStoreFailsForInactiveJob(): void
    {
        Storage::fake();
        $job = JobListing::factory()->create(['status' => 'inactive']);

        $data = [
            'job_id' => $job->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'mobile_number' => '1234567890',
            'cv' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            'city' => 'Metropolis',
            'country' => 'Freedonia',
            'desired_salary' => '55000',
            'salary_currency' => 'USD',
        ];

        $response = $this->postJson('/api/applications', $data);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['job_id']);
    }

    /**
     * Test validation failure when required fields are missing.
     */
    public function testStoreFailsForMissingFields(): void
    {
        Storage::fake();
        $response = $this->postJson('/api/applications', []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'job_id', 'first_name', 'last_name', 'email',
                     'mobile_number', 'cv', 'city', 'country',
                     'desired_salary', 'salary_currency',
                 ]);
    }
}
