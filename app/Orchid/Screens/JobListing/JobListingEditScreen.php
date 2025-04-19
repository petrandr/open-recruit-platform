<?php

declare(strict_types=1);

namespace App\Orchid\Screens\JobListing;

use App\Models\JobListing;
use App\Orchid\Layouts\JobListing\JobListingGeneralLayout;
use App\Orchid\Layouts\JobListing\JobListingDescriptionLayout;
use App\Orchid\Layouts\JobListing\JobListingDetailedLayout;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

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
     * @param JobListing $job
     * @return array<string, mixed>
     */
    public function query(JobListing $job): iterable
    {
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
     * Permissions required to access.
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
            Link::make(__('Jobs List'))
                ->icon('bs.list')
                ->route('platform.jobs'),
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('saveJob'),
            Button::make(__('Remove'))
                ->icon('bs.trash3')
                ->method('removeJob')
                ->canSee($this->job->exists)
                ->confirm(__('Are you sure you want to delete this job position?')),
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
            Layout::block(JobListingGeneralLayout::class)
                ->title(__('General Information'))
                ->description(__('Basic details of the job position.')),

            Layout::block(JobListingDescriptionLayout::class)
                ->title(__('Description'))
                ->description(__('Overview and headline of the job.')),

            Layout::block(JobListingDetailedLayout::class)
                ->title(__('Details & Perks'))
                ->description(__('In-depth responsibilities, requirements, and benefits.')),
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
            'job.date_opened'       => 'nullable|date',
            'job.responsibilities'  => 'required|string',
            'job.requirements'      => 'required|string',
            'job.bonus'             => 'nullable|string',
            'job.benefits'          => 'nullable|string',
            'job.status'            => ['required', Rule::in(['draft', 'active', 'inactive', 'disable'])],
        ]);

        $job->fill($request->input('job'))->save();
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