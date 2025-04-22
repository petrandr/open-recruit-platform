<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Candidate;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use App\Models\Candidate;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Components\Cells\DateTimeSplit;

class CandidateListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    public $target = 'candidates';

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
                ->filter(Input::make())
                ->render(fn (Candidate $candidate) => $candidate->id),

            TD::make('full_name', __('Full Name'))
                ->sort()
                ->filter(Input::make())
                ->render(fn (Candidate $candidate) => Link::make($candidate->full_name)
                    ->route('platform.candidates.view', $candidate)
                ),

            TD::make('email', __('Email'))
                ->sort()
                ->filter(Input::make())
                ->render(fn (Candidate $candidate) => $candidate->email),
            // Number of applications per candidate
            TD::make('applications_count', __('Applications'))
                ->sort()
                ->render(function (Candidate $candidate) {
                    return Link::make((string) $candidate->applications_count)
                        ->route('platform.applications', ['applicant_id' => $candidate->id]);
                }),

            TD::make('created_at', __('Created'))
                ->sort()
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Candidate $candidate) => Button::make(__('Delete'))
                    ->icon('bs.trash3')
                    ->confirm(__('Are you sure you want to delete this candidate?'))
                    ->method('removeCandidate', ['id' => $candidate->id])
                ),
        ];
    }
}
