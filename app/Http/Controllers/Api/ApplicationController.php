<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\Candidate;
use App\Models\JobApplication;
use App\Models\JobApplicationAnswer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewApplicationNotification;
use App\Models\User;

class ApplicationController extends Controller
{
    /**
     * Store a new job application.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_id'           => [
                'required',
                Rule::exists('job_listings', 'id')->where('status', 'active'),
            ],
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'email'            => 'required|email|max:255',
            'mobile_number'    => 'required|string|max:255',
            'cv'               => 'required|file|mimes:pdf,doc,docx,odt,rtf|max:5120',
            'city'             => 'required|string|max:255',
            'country'          => 'required|string|max:255',
            'notice_period'    => 'nullable|string|max:50',
            'desired_salary'   => 'required|numeric',
            'salary_currency'  => 'required|string|max:10',
            'linkedin_profile' => 'nullable|url',
            'github_profile'   => 'nullable|url',
            'how_heard'        => 'nullable|string|max:255',
            'utm_params'       => 'nullable|json',
        ]);

        $validated['status'] = 'submitted';

        if ($request->hasFile('cv')) {
            $disk = config('platform.attachment.disk');
            $path = 'cvs/' . date('Y') . '/' . date('m');
            $fileName = Str::uuid()->toString() . '_' . $request->file('cv')->getClientOriginalName();
            $validated['cv_path'] = $request->file('cv')->storeAs($path, $fileName, $disk);
            $validated['cv_disk'] = $disk;
        }

        $candidate = Candidate::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'mobile_number' => $validated['mobile_number'],
            ]
        );
        $validated['applicant_id'] = $candidate->id;

        $validated['submitted_at'] = now();

        $application = JobApplication::create($validated);

        $job = JobListing::with('screeningQuestions')->find($validated['job_id']);
        $questions = $job->screeningQuestions->keyBy('id');

        foreach ($request->all() as $key => $value) {
            if (Str::startsWith($key, 'question_')) {
                $questionId = (int) str_replace('question_', '', $key);
                if (! $questions->has($questionId)) {
                    continue;
                }
                $question = $questions->get($questionId);

                if ($question->question_type === 'yes/no') {
                    if (! in_array(strtolower($value), ['yes', 'no'], true)) {
                        continue;
                    }
                } elseif ($question->question_type === 'number') {
                    if (! is_numeric($value)) {
                        continue;
                    }
                }
                JobApplicationAnswer::create([
                    'application_id' => $application->id,
                    'question_id'    => $questionId,
                    'answer_text'    => $value,
                ]);
            }
        }

        $utmData = json_decode($validated['utm_params'] ?? '{}', true) ?? [];
        if (! empty($utmData)) {
            $application->tracking()->create($utmData);
        }

        // Send notification to designated users, failures won't block response
        $notifyIds = $job->who_to_notify ?? [];
        if (! empty($notifyIds)) {
            $users = User::whereIn('id', $notifyIds)->get();
            try {
                Notification::send($users, new NewApplicationNotification($application));
            } catch (\Throwable $e) {
                Log::error('New application notification failed', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message'         => 'Application created successfully.',
            'application_id'  => $application->id,
        ], 201);
    }
}
