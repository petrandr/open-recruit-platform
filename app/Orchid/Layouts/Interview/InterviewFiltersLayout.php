<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Interview;

use App\Orchid\Filters\Interview\CandidateFilter;
use App\Orchid\Filters\Interview\InterviewerFilter;
use App\Orchid\Filters\Interview\JobFilter;
use App\Orchid\Filters\Interview\StatusFilter;
use Orchid\Screen\Layouts\Selection;

class InterviewFiltersLayout extends Selection
{
    public $template = self::TEMPLATE_LINE;

    public function filters(): array
    {
        return [
            CandidateFilter::class,
            JobFilter::class,
            InterviewerFilter::class,
            StatusFilter::class,
        ];
    }
}
