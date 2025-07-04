<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\JobListing;

use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Relation;
use App\Models\User;
use Orchid\Platform\Models\Role;

class JobListingGeneralLayout extends Rows
{
    /**
     * Define the fields for general job information.
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
                ->placeholder(__('Enter job title')),

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

            Input::make('job.location')
                ->type('text')
                ->required()
                ->title(__('Location'))
                ->placeholder(__('City, Country')),

            DateTimer::make('job.date_opened')
                ->title(__('Date Opened'))
                ->enableTime(false),

            Select::make('job.status')
                ->options([
                    'draft'    => __('Draft'),
                    'active'   => __('Active'),
                    'inactive' => __('Inactive'),
                    'disable'  => __('Disabled'),
                ])
                ->required()
                ->title(__('Status')),

            Input::make('job.short_description')
                ->title(__('Short Description'))
                ->type('text')
                ->required()
                ->title(__('Short Description')),

            Relation::make('job.who_to_notify')
                ->title(__('Notification Recipients'))
                ->fromModel(User::class, 'name')
                ->multiple(),
            // Roles allowed to access this job
            Select::make('job.roles')
                ->title(__('Allowed Roles'))
                ->multiple()
                ->options(
                    // Exclude admin role from selectable options
                    Role::where('slug', '<>', 'admin')
                        ->pluck('name', 'id')
                        ->toArray()
                )
                ->help(__('Select roles permitted to access this job applications')),

        ];
    }
}
