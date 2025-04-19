<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\JobListing;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use App\Models\JobListing;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
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
                ->render(fn (JobListing $job) => $job->id),

            TD::make('title', __('Title'))
                ->sort()
                ->render(fn (JobListing $job) => $job->title),
            TD::make('applications', __('Applications'))
                ->render(fn (JobListing $job) =>
                    Link::make((string) $job->applications_count)
                        ->route('platform.applications', ['job_id' => $job->id])
                ),

            TD::make('location', __('Location'))
                ->sort()
                ->render(fn (JobListing $job) => $job->location),

            TD::make('status', __('Status'))
                ->sort()
                ->render(fn (JobListing $job) => ucfirst($job->status)),

            TD::make('date_opened', __('Date Opened'))
                ->sort()
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (JobListing $job) => Button::make(__('Delete'))
                    ->icon('bs.trash3')
                    ->confirm(__('Are you sure you want to delete this job listing?'))
                    ->method('removeJobListing', ['id' => $job->id])
                ),
        ];
    }
}