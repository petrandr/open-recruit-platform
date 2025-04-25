<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\JobApplication;

return new class extends Migration
{
    /**
     * Populate fit_ratio for existing job applications.
     */
    public function up(): void
    {
        JobApplication::with('answers.question')
            ->chunkById(100, function ($applications) {
                foreach ($applications as $application) {
                    try {
                        $ratio = $application->calculateFit()['ratio'] ?? 0;
                        // Update fit_ratio without modifying timestamps
                        DB::table('job_applications')
                            ->where('id', $application->id)
                            ->update(['fit_ratio' => $ratio]);
                    } catch (\Throwable $e) {
                        Log::error(
                            'Failed to populate fit_ratio for application',
                            ['application_id' => $application->id, 'error' => $e->getMessage()]
                        );
                    }
                }
            });
    }

    /**
     * Reverse the data population.
     */
    public function down(): void
    {
        DB::table('job_applications')->update(['fit_ratio' => 0]);
    }
};