<?php

declare(strict_types=1);

namespace App\Orchid\Screens\JobListing;

use App\Models\JobListing;
use App\Orchid\Layouts\JobListing\JobListingGeneralLayout;
use App\Orchid\Layouts\JobListing\JobListingDescriptionLayout;
use App\Orchid\Layouts\JobListing\JobListingDetailedLayout;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
// use App\Orchid\Layouts\JobListing\JobListingScreeningLayout; // removed repeater dependency
use Orchid\Support\Facades\Toast;
use Orchid\Platform\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JobListingEditScreen extends Screen
{
    /**
     * The job model.
     *
     * @var JobListing
     */
    public $job;

    /**
     * Query data for the screen.
     *
     * @return array<string, mixed>
     */
    public function query(): iterable
    {
        // Retrieve route parameter for job (model or ID)
        $routeParam = request()->route()->parameter('job');
        if ($routeParam instanceof JobListing) {
            $job = $routeParam;
        } elseif (!empty($routeParam)) {
            $job = JobListing::find($routeParam);
        }
        // Fallback to new model for creation
        if (empty($job)) {
            $job = new JobListing();
        } else {
            // Restrict to interviews for accessible jobs
            $roleIds = auth()->user()->roles()->pluck('id')->toArray();
            $accessibleJob = JobListing::where('id', $job->id)
                ->whereHas('roles', function ($q) use ($roleIds) {
                    $q->whereIn('roles.id', $roleIds);
                })
                ->first();

            if (!$accessibleJob) {
                // Throw 403 Access Denied
                throw new HttpException(403, 'Access Denied');
            }
            // Eager-load relations and application count for editing
            $job->load(['screeningQuestions', 'roles']);
            $job->loadCount('applications');
        }
        // Prepare selected role IDs (empty for new job)
        $job->setAttribute('roles', $job->roles->pluck('id')->toArray());

        return [
            'job' => $job,
        ];
    }

    /**
     * Screen name in header.
     */
    public function name(): ?string
    {
        return $this->job->exists ? __('Edit Job') : __('Create Job');
    }

    /**
     * Screen description.
     */
    public function description(): ?string
    {
        return $this->job->exists
            ? __('Modify job position details')
            : __('Fill in details to create a new job position');
    }

    /**
     * Permissions required to access create or edit operations.
     */
    public function permission(): ?iterable
    {
        // Allow create or edit depending on job existence
        if ($this->job) {
            return ['platform.jobs.edit'];
        }
        return ['platform.jobs.create'];
    }

    /**
     * Action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Jobs List'))
                ->icon('bs.list')
                ->route('platform.jobs'),
            Button::make(__('Remove'))
                ->icon('bs.trash3')
                ->method('removeJob')
                ->canSee(
                    $this->job->exists &&
                    $this->job->applications_count === 0 &&
                    auth()->user()->hasAccess('platform.jobs.delete')
                )
                ->confirm(__('Are you sure you want to delete this job position?')),
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('saveJob')
                ->canSee(!$this->job->exists),
        ];
    }

    /**
     * Screen layout.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            // General job sections first
            Layout::block(JobListingGeneralLayout::class)
                ->title(__('General Information'))
                ->description(__('Basic details of the job position.'))
                ->commands([
                    Button::make(__('Save'))
                        ->icon('bs.check-circle')
                        ->method('saveJob')
                        ->canSee($this->job->exists),
                ]),
            Layout::block(JobListingDescriptionLayout::class)
                ->title(__('Description'))
                ->description(__('Overview and headline of the job.'))
                ->commands([
                    Button::make(__('Save'))
                        ->icon('bs.check-circle')
                        ->method('saveJob')
                        ->canSee($this->job->exists),
                ]),
            Layout::block(JobListingDetailedLayout::class)
                ->title(__('Details & Perks'))
                ->description(__('Responsibilities, requirements, and perks.'))
                ->commands([
                    Button::make(__('Save'))
                        ->icon('bs.check-circle')
                        ->method('saveJob')
                        ->canSee($this->job->exists),
                ]),
            // Autocomplete datalist for question suggestions
            Layout::view('partials.screening-questions-datalist'),
            // Screening questions repeater at bottom
            Layout::block([
                Layout::view('partials.job-screening-questions'),
            ])
                ->title(__('Screening Questions'))
                ->description(__('Add or select questions for applicants.'))
                ->commands([
                    Button::make(__('Save'))
                        ->icon('bs.check-circle')
                        ->method('saveJob')
                        ->canSee($this->job->exists),
                ]),
        ];
    }

    /**
     * Save job.
     *
     * @param JobListing $job
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveJob(JobListing $job, Request $request)
    {
        $validated = $request->validate([
            'job.title'             => 'required|string|max:255',
            'job.short_description' => 'required|string',
            'job.headline'          => 'required|string',
            'job.job_type'          => ['required', Rule::in(['Full-Time', 'Part-Time', 'Contract'])],
            'job.workplace'         => ['required', 'array'],
            'job.workplace.*'       => [Rule::in(['On-Site', 'Hybrid', 'Remote'])],
            'job.location'          => 'required|string|max:255',
            'job.industry_id'       => ['required', 'integer', 'exists:industries,id'],
            'job.date_opened'       => 'nullable|date',
            'job.responsibilities'  => 'required|string',
            'job.requirements'      => 'required|string',
            'job.bonus'             => 'nullable|string',
            'job.benefits'          => 'nullable|string',
            'job.status'            => ['required', Rule::in(['draft', 'active', 'inactive', 'disable'])],
            'job.who_to_notify'     => 'nullable|array',
            'job.who_to_notify.*'   => 'exists:users,id',
            // Roles allowed to access this job
            'job.roles'             => 'nullable|array',
            'job.roles.*'           => 'integer|exists:roles,id',
            'job.application_received_template_id' => 'nullable|integer|exists:notification_templates,id',
        ]);

        // Prepare job data and always generate slug from title
        $data = $validated['job'];
        $baseSlug = Str::slug($data['title']);
        $slug     = $baseSlug;
        $counter  = 1;
        while (DB::table('job_listings')
            ->where('slug', $slug)
            ->where('id', '<>', $job->id)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }
        $data['slug'] = $slug;
        $job->fill($data)->save();
        // Sync allowed roles (automatically include admin role if it exists)
        $selectedRoles = $request->input('job.roles', []);
        // Ensure admin role always assigned
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $selectedRoles = array_unique(
                array_merge(
                    is_array($selectedRoles) ? $selectedRoles : [],
                    [$adminRole->id]
                )
            );
        }
        $job->roles()->sync($selectedRoles);

        // Handle screening questions
        $screeningData = $request->input('screeningQuestions', []);
        // Track IDs we processed
        $processed = [];
        foreach ($screeningData as $data) {
            $text = trim($data['question_text'] ?? '');
            if ($text === '') {
                continue;
            }
            $type = $data['question_type'] ?? 'number';
            $value = $data['min_value'] ?? null;
            // Update existing or create new
            if (!empty($data['id']) && $sq = \App\Models\JobScreeningQuestion::find($data['id'])) {
                $sq->update([
                    'question_text' => $text,
                    'question_type' => $type,
                    'min_value'     => $value,
                ]);
            } else {
                $sq = $job->screeningQuestions()->create([
                    'question_text' => $text,
                    'question_type' => $type,
                    'min_value'     => $value,
                ]);
            }
            $processed[] = $sq->id;
        }
        // Unassign any screening questions not in the processed list
        if (!empty($processed)) {
            $job->screeningQuestions()
                ->whereNotIn('id', $processed)
                ->update(['job_id' => null]);
        }

        Toast::info(__('Job was saved.'));
        return redirect()->route('platform.jobs');
    }

    /**
     * Remove a job listing.
     *
     * @param JobListing $job
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeJob(JobListing $job)
    {
        if ($job->applications()->exists()) {
            Toast::warning(__('Cannot delete job with existing applications.'));
            return redirect()->route('platform.jobs');
        }

        $job->delete();
        Toast::info(__('Job was removed.'));

        return redirect()->route('platform.jobs');
    }
}
