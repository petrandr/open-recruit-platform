<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\JobListing;

use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\TextArea;

class JobListingDetailedLayout extends Rows
{
    /**
     * Define the detailed fields for job responsibilities, requirements, and perks.
     *
     * @return \Orchid\Screen\Field[]
     */
    public function fields(): array
    {
        return [
            Quill::make('job.responsibilities')
                ->title(__('Responsibilities'))
                ->required(),

            Quill::make('job.requirements')
                ->title(__('Requirements'))
                ->required(),

            Quill::make('job.bonus')
                ->title(__('Bonus'))
                ->placeholder(__('Optional bonuses')),

            Quill::make('job.benefits')
                ->title(__('Benefits'))
                ->placeholder(__('Optional benefits')),
        ];
    }
}
