<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\JobListing;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use App\Models\JobListing;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Components\Cells\DateTimeSplit;

class JobListingListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    public $target = 'jobs';

    /**
     * Table columns.
     *
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))
                ->sort()
                ->render(fn(JobListing $job) => $job->id),

            TD::make('title', __('Title'))
                ->sort()
                ->render(function (JobListing $job) {
                    return Link::make($job->title)
                        ->route('platform.jobs.view', $job->id);
                }),
            TD::make('applications', __('Applications'))
                ->render(fn(JobListing $job) => Link::make((string)$job->applications_count)
                    ->route('platform.applications', ['job_id' => $job->id])
                ),

            TD::make('location', __('Location'))
                ->sort()
                ->render(fn(JobListing $job) => $job->location),

            TD::make('status', __('Status'))
                ->sort()
                ->render(function (JobListing $job) {
                    $status = $job->status;
                    $color = match ($status) {
                        'active' => 'success',
                        'draft' => 'secondary',
                        'inactive' => 'warning',
                        'disable', 'disabled' => 'danger',
                        default => 'secondary',
                    };
                    // Render a larger badge with increased padding and font size
                    return "<span class=\"badge bg-{$color} status-badge\">" . ucfirst(
                            $status
                        ) . "</span>";
                }),

            TD::make('date_opened', __('Date Opened'))
                ->sort()
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (JobListing $job) {
                    // Base dropdown
                    $dropdown = DropDown::make()->icon('bs.three-dots-vertical');
                    // Core actions
                    $items = [
                        Link::make(__('Edit'))
                            ->icon('bs.pencil')
                            ->route('platform.jobs.edit', $job->id),
                    ];

                    $items[] = Button::make(__('Delete'))
                        ->icon('bs.trash3')
                        ->confirm(__('Are you sure you want to delete this job listing?'))
                        ->method('removeJobListing', ['id' => $job->id]);


                    // Status change options, hiding current status
                    $statuses = [
                        'active' => ['Set Active', 'bs.check-circle'],
                        'inactive' => ['Set Inactive', 'bs.pause-circle'],
                        'draft' => ['Set Draft', 'bs.pencil'],
                        'disable' => ['Disable', 'bs.slash-circle'],
                    ];
                    foreach ($statuses as $key => [$label, $icon]) {
                        if ($job->status !== $key) {
                            $items[] = Button::make(__($label))
                                ->icon($icon)
                                ->method('changeJobStatus', ['id' => $job->id, 'status' => $key]);
                        }
                    }
                    return $dropdown->list($items);
                }),
        ];
    }
}
