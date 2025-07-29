<?php
declare(strict_types=1);

namespace App\Orchid\Screens\JobListing;

use App\Models\JobListing;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Sight;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Actions\Button;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Display a full view of a job listing.
 */
class JobListingViewScreen extends Screen
{
    /**
     * The job model instance.
     *
     * @var JobListing
     */
    public $job;

    /**
     * Query data for the screen.
     *
     * @param JobListing $job
     * @return array<string, mixed>
     */
    public function query(JobListing $job): iterable
    {
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
        // Load related counts and questions
        $job->loadCount('applications');
        $job->load(['screeningQuestions', 'industry']);
        return [
            'job' => $job,
        ];
    }

    /**
     * Screen name shown in header.
     */
    public function name(): ?string
    {
        return __('Job Details');
    }

    /**
     * Screen description shown under header.
     */
    public function description(): ?string
    {
        return __('Full view of the job position details.');
    }

    /**
     * Permissions required to access this screen.
     */
    public function permission(): ?iterable
    {
        return ['platform.jobs'];
    }

    /**
     * Action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Back to Jobs'))
                ->icon('bs.arrow-left')
                ->route('platform.jobs'),
            Link::make(__('Edit'))
                ->icon('bs.pencil')
                ->route('platform.jobs.edit', $this->job->id)
                ->canSee(auth()->user()->hasAccess('platform.jobs.edit')),
            // Change job status
            Button::make(__('Activate'))
                ->icon('bs.check2-circle')
                ->method('changeJobStatus', [
                    'id'     => $this->job->id,
                    'status' => 'active',
                ])
                ->confirm(__('Are you sure you want to activate this job position?'))
                ->novalidate()
                ->canSee(auth()->user()->hasAccess('platform.jobs.edit')),
            Button::make(__('Set Inactive'))
                ->icon('bs.pause-circle')
                ->method('changeJobStatus', [
                    'id'     => $this->job->id,
                    'status' => 'inactive',
                ])
                ->confirm(__('Are you sure you want to set this job position to inactive?'))
                ->novalidate()
                ->canSee(auth()->user()->hasAccess('platform.jobs.edit')),
            Button::make(__('Disable'))
                ->icon('bs.x-circle')
                ->method('changeJobStatus', [
                    'id'     => $this->job->id,
                    'status' => 'disable',
                ])
                ->confirm(__('Are you sure you want to disable this job position?'))
                ->novalidate()
                ->canSee(auth()->user()->hasAccess('platform.jobs.edit')),
        ];
    }

    /**
     * Screen layout.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        // General information
        $general = [
            Sight::make('title', __('Title')),
            Sight::make('industry.name', __('Industry'))
                ->render(fn(JobListing $job) => optional($job->industry)->name ?? '-'),
            Sight::make('slug', __('Slug')),
            Sight::make('short_description', __('Short Description'))
                ->render(fn(JobListing $job) => $job->short_description),
            Sight::make('headline', __('Headline'))
                ->render(fn(JobListing $job) => $job->headline),
            Sight::make('job_type', __('Job Type')),
            Sight::make('workplace', __('Workplace'))
                ->render(fn(JobListing $job) =>
                    is_array($job->workplace)
                        ? implode(', ', $job->workplace)
                        : $job->workplace
                ),
            Sight::make('location', __('Location')),
            Sight::make('status', __('Status'))
                ->render(fn(JobListing $job) =>
                    '<span class="badge bg-' . match ($job->status) {
                        'active'   => 'success',
                        'draft'    => 'secondary',
                        'inactive' => 'warning',
                        'disabled' => 'danger',
                        default    => 'secondary',
                    } . '">' . ucfirst($job->status) . '</span>'
                ),
            Sight::make('date_opened', __('Date Opened'))
                ->render(fn(JobListing $job) =>
                    $job->date_opened
                        ? $job->date_opened->format('Y-m-d')
                        : '-'
                ),
            Sight::make('applications_count', __('Applications'))
                ->render(fn(JobListing $job) => $job->applications_count),
        ];

        // Detailed responsibilities
        $details = [
            Sight::make('responsibilities', __('Responsibilities'))
                ->render(fn(JobListing $job) => $job->responsibilities),
            Sight::make('requirements', __('Requirements'))
                ->render(fn(JobListing $job) => $job->requirements),
            Sight::make('bonus', __('Bonus'))
                ->render(fn(JobListing $job) => $job->bonus),
            Sight::make('benefits', __('Benefits'))
                ->render(fn(JobListing $job) => $job->benefits),
        ];

        // Screening questions
        $screening = [];
        foreach ($this->job->screeningQuestions as $question) {
            $screening[] = Sight::make('question_' . $question->id, $question->question_text)
                ->render(fn() =>
                    $question->question_type === 'boolean'
                        ? ($question->min_value ? __('Yes') : __('No'))
                        : ($question->min_value !== null ? $question->min_value : '-')
                );
        }

        return [
            Layout::legend('job', $general)->title(__('General Information')),
            Layout::legend('job', $details)->title(__('Details & Perks')),
            Layout::legend('job', $screening)->title(__('Screening Questions'))
        ];
    }
    /**
     * Change the status of this job listing.
     *
     * @param Request $request
     */
    public function changeJobStatus(Request $request): void
    {
        $job    = JobListing::findOrFail($request->get('id'));
        $status = $request->get('status');
        $job->update(['status' => $status]);
        Toast::info(__('Job status changed to :status', ['status' => ucfirst($status)]));
    }
}
