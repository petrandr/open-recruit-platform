<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Application;

use App\Orchid\Filters\Application\CandidateFilter;
use App\Orchid\Filters\Application\JobFilter;
use App\Orchid\Filters\Application\StatusFilter;
use App\Orchid\Filters\Application\SubmittedDateFilter;
use Orchid\Screen\Layouts\Selection;

class ApplicationFiltersLayout extends Selection
{
    public $template = self::TEMPLATE_LINE;
    /**
     * Get filters for applications.
     *
     * @return array|string[]
     */
    public function filters(): array
    {
        return [
            CandidateFilter::class,
            JobFilter::class,
            StatusFilter::class,
            SubmittedDateFilter::class,
        ];
    }
}
