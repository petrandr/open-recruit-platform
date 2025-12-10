<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\JobListing;
use Carbon\Carbon;

class ExtendExpiringJobsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_extends_jobs_expiring_tomorrow(): void
    {
        // Create a job expiring tomorrow
        $job = JobListing::factory()->create([
            'status' => 'active',
            'valid_until' => Carbon::now()->addDay(),
        ]);

        $originalDate = $job->valid_until->copy();

        $this->artisan('jobs:extend-expiring')
            ->expectsOutput('Found 1 job(s) expiring within the next 1 day(s):')
            ->expectsOutput('Successfully extended 1 job(s) by 30 days.')
            ->assertExitCode(0);

        $job->refresh();
        $this->assertEquals(
            $originalDate->addDays(30)->toDateString(),
            $job->valid_until->toDateString()
        );
    }

    public function test_dry_run_does_not_extend_jobs(): void
    {
        $job = JobListing::factory()->create([
            'status' => 'active',
            'valid_until' => Carbon::now()->addDay(),
        ]);

        $originalDate = $job->valid_until->copy();

        $this->artisan('jobs:extend-expiring', ['--dry-run' => true])
            ->expectsOutput('Dry run completed. 1 job(s) would be extended by 30 days.')
            ->assertExitCode(0);

        $job->refresh();
        $this->assertEquals($originalDate->toDateString(), $job->valid_until->toDateString());
    }

    public function test_ignores_inactive_jobs(): void
    {
        JobListing::factory()->create([
            'status' => 'inactive',
            'valid_until' => Carbon::now()->addDay(),
        ]);

        $this->artisan('jobs:extend-expiring')
            ->expectsOutput('No jobs found that are expiring within the next 1 day(s).')
            ->assertExitCode(0);
    }

    public function test_ignores_jobs_not_expiring_soon(): void
    {
        JobListing::factory()->create([
            'status' => 'active',
            'valid_until' => Carbon::now()->addDays(5),
        ]);

        $this->artisan('jobs:extend-expiring')
            ->expectsOutput('No jobs found that are expiring within the next 1 day(s).')
            ->assertExitCode(0);
    }

    public function test_custom_days_and_extension_options(): void
    {
        $job = JobListing::factory()->create([
            'status' => 'active',
            'valid_until' => Carbon::now()->addDays(2),
        ]);

        $originalDate = $job->valid_until->copy();

        $this->artisan('jobs:extend-expiring', [
            '--days' => 3,
            '--extend-by' => 60
        ])
            ->expectsOutput('Found 1 job(s) expiring within the next 3 day(s):')
            ->expectsOutput('Successfully extended 1 job(s) by 60 days.')
            ->assertExitCode(0);

        $job->refresh();
        $this->assertEquals(
            $originalDate->addDays(60)->toDateString(),
            $job->valid_until->toDateString()
        );
    }
}
