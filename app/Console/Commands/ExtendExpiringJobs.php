<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobListing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExtendExpiringJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:extend-expiring 
                            {--dry-run : Show what would be extended without making changes}
                            {--days=1 : Number of days before expiration to extend (default: 1)}
                            {--extend-by=30 : Number of days to extend by (default: 30)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extend job listings that are about to expire';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $daysBefore = (int) $this->option('days');
        $extendBy = (int) $this->option('extend-by');

        Log::info('Job extension task started', [
            'dry_run' => $dryRun,
            'days_before' => $daysBefore,
            'extend_by' => $extendBy
        ]);

        // Calculate the target date (jobs expiring within the specified days)
        $targetDate = Carbon::now()->addDays($daysBefore)->toDateString();

        // Find active jobs that expire on or before the target date
        $expiringJobs = JobListing::where('status', 'active')
            ->whereDate('valid_until', '<=', $targetDate)
            ->get();

        if ($expiringJobs->isEmpty()) {
            $message = 'No jobs found that are expiring within the next ' . $daysBefore . ' day(s).';
            $this->info($message);
            Log::info($message);
            return Command::SUCCESS;
        }

        $this->info('Found ' . $expiringJobs->count() . ' job(s) expiring within the next ' . $daysBefore . ' day(s):');

        $extendedCount = 0;

        foreach ($expiringJobs as $job) {
            $oldDate = $job->valid_until->format('Y-m-d');
            $newDate = $job->valid_until->addDays($extendBy)->format('Y-m-d');

            if ($dryRun) {
                $this->line("  [DRY RUN] Job #{$job->id} '{$job->title}' - Would extend from {$oldDate} to {$newDate}");
            } else {
                $job->update(['valid_until' => $newDate]);
                $this->line("  ✓ Extended Job #{$job->id} '{$job->title}' from {$oldDate} to {$newDate}");
                Log::info('Job extended', [
                    'job_id' => $job->id,
                    'job_title' => $job->title,
                    'old_date' => $oldDate,
                    'new_date' => $newDate,
                    'extended_by_days' => $extendBy
                ]);
                $extendedCount++;
            }
        }

        if ($dryRun) {
            $message = "Dry run completed. {$expiringJobs->count()} job(s) would be extended by {$extendBy} days.";
            $this->info("\n" . $message);
            $this->comment('Run without --dry-run to actually extend the jobs.');
            Log::info($message, ['total_jobs' => $expiringJobs->count()]);
        } else {
            $message = "Successfully extended {$extendedCount} job(s) by {$extendBy} days.";
            $this->info("\n" . $message);
            Log::info('Job extension task completed', [
                'extended_count' => $extendedCount,
                'total_found' => $expiringJobs->count(),
                'extend_by_days' => $extendBy
            ]);
        }

        return Command::SUCCESS;
    }
}
