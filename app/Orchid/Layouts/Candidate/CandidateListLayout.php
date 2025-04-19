<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Candidate;

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
                ->render(fn (Candidate $candidate) => $candidate->id),

            TD::make('first_name', __('First Name'))
                ->sort()
                ->render(fn (Candidate $candidate) => $candidate->first_name),

            TD::make('last_name', __('Last Name'))
                ->sort()
                ->render(fn (Candidate $candidate) => $candidate->last_name),

            TD::make('email', __('Email'))
                ->sort()
                ->render(fn (Candidate $candidate) => $candidate->email),

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