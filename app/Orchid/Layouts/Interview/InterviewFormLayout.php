<?php
declare(strict_types=1);

namespace App\Orchid\Layouts\Interview;

use App\Orchid\Fields\Ckeditor;
use App\Support\Interview;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\TextArea;
use App\Models\User;

class InterviewFormLayout extends Rows
{
    /**
     * Get form fields for editing an interview.
     *
     * @return array<\Orchid\Screen\Field>
     */
    public function fields(): array
    {
        return [
            // Hidden application ID
            Input::make('interview.application_id')
                ->type('hidden'),

            // Display application info (precomputed in screen query)
            Input::make('application_info')
                ->type('text')
                ->title(__('Application'))
                ->disabled(),

            // Interviewer picker
            Relation::make('interview.interviewer_id')
                ->fromModel(User::class, 'name')
                ->title(__('Interviewer')),

            // Scheduled datetime
            DateTimer::make('interview.scheduled_at')
                ->title(__('Scheduled At'))
                ->enableTime(),

            // Status
            Select::make('interview.status')
                ->title(__('Status'))
                ->options(collect(Interview::statuses())
                    ->mapWithKeys(fn($meta, $key) => [$key => $meta['label']])
                    ->toArray())
                ->required(),

            // Interview round
            Select::make('interview.round')
                ->title(__('Round'))
                ->empty(__('Select a round'), '')
                ->options(collect(Interview::rounds())
                    ->mapWithKeys(fn($meta, $key) => [$key => $meta['label']])
                    ->toArray()
                ),

            // Mode
            Select::make('interview.mode')
                ->title(__('Mode'))
                ->options(collect(Interview::modes())
                    ->mapWithKeys(fn($meta, $key) => [$key => $meta['label']])
                    ->toArray()),

            // Location or video link
            Input::make('interview.location')
                ->title(__('Location or Link')),

            // Duration in minutes
            Input::make('interview.duration_minutes')
                ->type('number')
                ->title(__('Duration (minutes)')),

            // Comments/feedback
            Ckeditor::make('interview.comments')
                ->title(__('Comments'))
        ];
    }
}
