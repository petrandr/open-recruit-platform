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
            ->with('industry')
            ->select(
                'id',
                'title',
                'slug',
                'short_description',
                'status',
                'date_opened',
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
     * Display the specified job if active or inactive.
     *
     * @param JobListing $job
     */
    public function show(JobListing $job): JsonResponse
    {
        if (!in_array($job->status, ['active', 'inactive'], true)) {
            abort(404);
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
