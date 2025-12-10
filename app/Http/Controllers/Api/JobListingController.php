<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Http\JsonResponse;

class JobListingController extends Controller
{
    /**
     * Display a listing of active and non-expired jobs.
     * 
     * Returns only jobs with status 'active' and valid_until date in the future.
     * Includes the valid_until field in the response.
     */
    public function index(): JsonResponse
    {
        $jobs = JobListing::where('status', 'active')
            ->valid() // Only include non-expired jobs
            ->with('industry')
            ->select(
                'id',
                'title',
                'slug',
                'short_description',
                'status',
                'date_opened',
                'valid_until',
                'job_type',
                'workplace',
                'location',
                'industry_id',  // needed to load industry relationship
                'created_at',
                'updated_at'
            )
            ->orderBy('id', 'desc')
            ->get();

        // Transform response: replace industry relation with its name under 'industry'
        $payload = $jobs->map(function (JobListing $job) {
            return [
                'id'                => $job->id,
                'title'             => $job->title,
                'slug'              => $job->slug,
                'short_description' => $job->short_description,
                'status'            => $job->status,
                'date_opened'       => $job->date_opened,
                'valid_until'       => $job->valid_until,
                'job_type'          => $job->job_type,
                'workplace'         => $job->workplace,
                'location'          => $job->location,
                'industry'          => optional($job->industry)->name,
                'created_at'        => $job->created_at,
                'updated_at'        => $job->updated_at,
            ];
        })->all();
        return response()->json($payload);
    }

    /**
     * Display the specified job if active or inactive and not expired.
     * 
     * Returns 404 if the job is expired (valid_until date has passed).
     *
     * @param JobListing $job
     */
    public function show(JobListing $job): JsonResponse
    {
        if (!in_array($job->status, ['active', 'inactive'], true)) {
            abort(404);
        }

        // Check if job is expired
        if ($job->isExpired()) {
            abort(404, 'Job listing has expired');
        }

        // Eager load related entities including industry
        $job->load(['screeningQuestions', 'industry']);

        // Transform response: include industry as name only
        $data = $job->toArray();
        $data['industry'] = optional($job->industry)->name;
        unset($data['industry_id']);
        return response()->json($data);
    }
}
