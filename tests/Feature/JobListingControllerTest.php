<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\JobListing;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JobListingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexOnlyReturnsActiveJobs(): void
    {
        // Create jobs with various statuses
        JobListing::factory()->count(2)->create(['status' => 'active']);
        JobListing::factory()->count(1)->create(['status' => 'inactive']);
        JobListing::factory()->count(1)->create(['status' => 'draft']);

        $response = $this->getJson('/api/jobs');

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonStructure([
                     '*' => ['id', 'title', 'status'],
                 ]);

        // Ensure all returned jobs are active
        foreach ($response->json() as $job) {
            $this->assertEquals('active', $job['status']);
        }
    }

    public function testShowReturnsActiveJob(): void
    {
        $job = JobListing::factory()->create(['status' => 'active']);

        $this->getJson("/api/jobs/{$job->id}")
             ->assertStatus(200)
             ->assertJson(['id' => $job->id, 'status' => 'active']);
    }

    public function testShowReturnsInactiveJob(): void
    {
        $job = JobListing::factory()->create(['status' => 'inactive']);

        $this->getJson("/api/jobs/{$job->id}")
             ->assertStatus(200)
             ->assertJson(['id' => $job->id, 'status' => 'inactive']);
    }

    public function testShowReturns404ForOtherStatuses(): void
    {
        foreach (['draft', 'disable'] as $status) {
            $job = JobListing::factory()->create(['status' => $status]);

            $this->getJson("/api/jobs/{$job->id}")
                 ->assertStatus(404);
        }
    }
}