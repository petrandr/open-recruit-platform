<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\JobListing;

use App\Orchid\Fields\Ckeditor;
use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;

class JobListingDescriptionLayout extends Rows
{
    /**
     * Define the fields for job description.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Ckeditor::make('job.headline')
                ->title(__('Full Headline'))
        ];
    }
}
