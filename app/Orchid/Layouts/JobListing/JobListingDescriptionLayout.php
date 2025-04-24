<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\JobListing;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Quill;

class JobListingDescriptionLayout extends Rows
{
    /**
     * Define the fields for job description.
     *
     * @return \Orchid\Screen\Field[]
     */
    public function fields(): array
    {
        return [
            Quill::make('job.headline')
                ->title(__('Full Headline'))
                ->required(),
        ];
    }
}
