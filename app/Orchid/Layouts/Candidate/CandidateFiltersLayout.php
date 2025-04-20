<?php
declare(strict_types=1);

namespace App\Orchid\Layouts\Candidate;

use Orchid\Screen\Layouts\Selection;

class CandidateFiltersLayout extends Selection
{
    public $template = self::TEMPLATE_LINE;

    /**
     * No specific filters; enables sorting functionality.
     *
     * @return array<string>
     */
    public function filters(): array
    {
        return [];
    }
}