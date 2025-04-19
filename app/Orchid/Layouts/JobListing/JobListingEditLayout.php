<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\JobListing;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Layouts\Rows;

class JobListingEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('job.title')
                ->type('text')
                ->required()
                ->title(__('Title'))
                ->placeholder(__('Job title')),

            TextArea::make('job.short_description')
                ->rows(3)
                ->required()
                ->title(__('Short Description'))
                ->placeholder(__('Brief summary of the job')),

            TextArea::make('job.headline')
                ->rows(4)
                ->required()
                ->title(__('Headline'))
                ->placeholder(__('Full description headline')),

            Input::make('job.location')
                ->type('text')
                ->required()
                ->title(__('Location'))
                ->placeholder(__('City, Country')),

            Select::make('job.job_type')
                ->options([
                    'Full-Time' => 'Full-Time',
                    'Part-Time' => 'Part-Time',
                    'Contract'  => 'Contract',
                ])
                ->required()
                ->title(__('Job Type')),

            Select::make('job.workplace')
                ->options([
                    'On-Site' => 'On-Site',
                    'Hybrid'  => 'Hybrid',
                    'Remote'  => 'Remote',
                ])
                ->multiple()
                ->required()
                ->title(__('Workplace')),

            DateTimer::make('job.date_opened')
                ->title(__('Date Opened'))
                ->enableTime(false),

            TextArea::make('job.responsibilities')
                ->rows(3)
                ->title(__('Responsibilities'))
                ->placeholder(__('Job responsibilities')),

            TextArea::make('job.requirements')
                ->rows(3)
                ->title(__('Requirements'))
                ->placeholder(__('Job requirements')),

            TextArea::make('job.bonus')
                ->rows(2)
                ->title(__('Bonus'))
                ->placeholder(__('Additional bonuses')),

            TextArea::make('job.benefits')
                ->rows(2)
                ->title(__('Benefits'))
                ->placeholder(__('Benefits provided')),

            Select::make('job.status')
                ->options([
                    'draft'    => __('Draft'),
                    'active'   => __('Active'),
                    'inactive' => __('Inactive'),
                    'disable'  => __('Disable'),
                ])
                ->required()
                ->title(__('Status')),
        ];
    }
}
