<?php

declare(strict_types=1);

namespace App\Orchid\Screens\JobListing;

use App\Models\JobListing;
use App\Orchid\Layouts\JobListing\JobListingListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class JobListingListScreen extends Screen
{
    /**
     * Screen name displayed in header.
     */
    public function name(): ?string
    {
        return 'Job Listings';
    }

    /**
     * Screen description displayed under the header.
     */
    public function description(): ?string
    {
        return 'List of all job listings available in the system.';
    }

    /**
     * Query data for the screen.
     *
     * @return array<string, mixed>
     */
    public function query(): iterable
    {
        return [
            'jobs' => JobListing::withCount('applications')
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    /**
     * Permission required to view this screen.
     *
     * @return array|string|null
     */
    public function permission(): ?iterable
    {
        return ['platform.jobs'];
    }

    /**
     * Action buttons for the screen.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->route('platform.jobs.create'),
        ];
    }

    /**
     * Layout elements for the screen.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            JobListingListLayout::class,
        ];
    }

    /**
     * Remove a job listing.
     */
    public function removeJobListing(Request $request): void
    {
        $job = JobListing::findOrFail($request->get('id'));
        if ($job->applications()->exists()) {
            Toast::warning(__('Cannot delete job with existing applications.'));
        } else {
            $job->delete();
            Toast::info(__('Job listing was removed.'));
        }
    }
}