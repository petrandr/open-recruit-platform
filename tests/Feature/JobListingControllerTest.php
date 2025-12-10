<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\JobListing;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JobListingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexOnlyReturnsActiveAndValidJobs(): void
    {
        // Create active jobs with valid dates
        JobListing::factory()->count(2)->create([
            'status' => 'active',
            'valid_until' => now()->addDays(30)
        ]);
        
        // Create inactive job (should not appear)
        JobListing::factory()->count(1)->create([
            'status' => 'inactive',
            'valid_until' => now()->addDays(30)
        ]);
        
        // Create draft job (should not appear)
        JobListing::factory()->count(1)->create([
            'status' => 'draft',
            'valid_until' => now()->addDays(30)
        ]);
        
        // Create expired active job (should not appear)
        JobListing::factory()->count(1)->create([
            'status' => 'active',
            'valid_until' => now()->subDays(1)
        ]);

        $response = $this->getJson('/api/jobs');

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonStructure([
                     '*' => ['id', 'title', 'status', 'valid_until'],
                 ]);

        // Ensure all returned jobs are active and valid
        foreach ($response->json() as $job) {
            $this->assertEquals('active', $job['status']);
            $this->assertNotNull($job['valid_until']);
        }
    }

    public function testShowReturnsActiveJob(): void
    {
        $job = JobListing::factory()->create([
            'status' => 'active',
            'valid_until' => now()->addDays(30)
        ]);

        $this->getJson("/api/jobs/{$job->id}")
             ->assertStatus(200)
             ->assertJson([
                 'id' => $job->id, 
                 'status' => 'active',
                 'valid_until' => $job->valid_until->toISOString()
             ]);
    }

    public function testShowReturnsInactiveJob(): void
    {
        $job = JobListing::factory()->create([
            'status' => 'inactive',
            'valid_until' => now()->addDays(30)
        ]);

        $this->getJson("/api/jobs/{$job->id}")
             ->assertStatus(200)
             ->assertJson([
                 'id' => $job->id, 
                 'status' => 'inactive',
                 'valid_until' => $job->valid_until->toISOString()
             ]);
    }

    public function testShowReturns404ForOtherStatuses(): void
    {
        foreach (['draft', 'disable'] as $status) {
            $job = JobListing::factory()->create([
                'status' => $status,
                'valid_until' => now()->addDays(30)
            ]);

            $this->getJson("/api/jobs/{$job->id}")
                 ->assertStatus(404);
        }
    }

    public function testShowReturns404ForExpiredJobs(): void
    {
        $expiredJob = JobListing::factory()->create([
            'status' => 'active',
            'valid_until' => now()->subDays(1)
        ]);

        $this->getJson("/api/jobs/{$expiredJob->id}")
             ->assertStatus(404);
    }
}