<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\JobListing;

use App\Orchid\Fields\Ckeditor;
use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;

class JobListingDetailedLayout extends Rows
{
    /**
     * Define the detailed fields for job responsibilities, requirements, and perks.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Ckeditor::make('job.responsibilities')
                ->title(__('Responsibilities')),

            Ckeditor::make('job.requirements')
                ->title(__('Requirements')),

            Ckeditor::make('job.bonus')
                ->title(__('Bonus'))
                ->placeholder(__('Optional bonuses')),

            Ckeditor::make('job.benefits')
                ->title(__('Benefits'))
                ->placeholder(__('Optional benefits')),
        ];
    }
}
