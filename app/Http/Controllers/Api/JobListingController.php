<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\JsonResponse;

class JobListingController extends Controller
{
    /**
     * Display a listing of active jobs.
     */
    public function index(): JsonResponse
    {
        $jobs = JobListing::where('status', 'active')
            ->select('id', 'title', 'short_description', 'status', 'date_opened', 'job_type', 'workplace', 'location', 'created_at', 'updated_at')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($jobs);
    }

    /**
     * Display the specified job if active or inactive.
     *
     * @param JobListing $job
     */
    public function show(JobListing $job): JsonResponse
    {
        if (!in_array($job->status, ['active', 'inactive'], true)) {
            abort(404);
        }

        $job->load(['screeningQuestions']);

        return response()->json($job);
    }
}
