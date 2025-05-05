<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Interview;

use App\Models\Interview;
use App\Models\User;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use App\Orchid\Layouts\Interview\InterviewFormLayout;
use Orchid\Support\Facades\Toast;

class InterviewEditScreen extends Screen
{
    /**
     * The interview instance.
     *
     * @var Interview
     */
    public $interview;

    /**
     * Screen name
     */
    public function name(): ?string
    {
        return $this->interview->exists ? __('Edit Interview') : __('Add Interview');
    }

    /**
     * Permissions
     */
    public function permission(): ?iterable
    {
        return ['platform.interviews'];
    }

    /**
     * Query data
     */
    public function query(Interview $interview): iterable
    {
        $interview->load(['application.candidate', 'interviewer']);
        // Precompute application label for display
        $app = $interview->application;
        $candidate = $app->candidate;
        $applicationInfo = sprintf(
            '#%d - %s %s',
            $app->id,
            $candidate->first_name ?? '',
            $candidate->last_name ?? ''
        );
        return [
            'interview' => $interview,
            'application_info' => $applicationInfo,
        ];
    }

    /**
     * Action buttons
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Back to Interviews'))
                ->icon('bs.arrow-left')
                ->route('platform.interviews'),
        ];
    }

    /**
     * Screen layout
     */
    public function layout(): iterable
    {
        return [
            Layout::block(
                InterviewFormLayout::class
            )
            ->title($this->interview->exists ? __('Edit Interview') : __('Add Interview'))
            ->description(__('Modify the interview details below.'))
            ->commands([
                Button::make(__('Save'))
                    ->icon('bs.check2')
                    ->method('save'),
            ]),
        ];
    }

    /**
     * Save handler
     */
    public function save(Request $request, Interview $interview)
    {
        $request->validate([
            'interview.interviewer_id'   => 'nullable|exists:users,id',
            'interview.scheduled_at'      => 'nullable|date',
            'interview.status'            => 'required|in:scheduled,completed,cancelled,no-show',
            'interview.round'             => 'nullable|string|max:255',
            'interview.mode'              => 'nullable|in:in-person,phone,video',
            'interview.location'          => 'nullable|string|max:255',
            'interview.duration_minutes'  => 'nullable|integer',
            'interview.comments'          => 'nullable|string',
        ]);
        $interview->fill($request->input('interview'));
        $interview->save();
        Toast::info(__('Interview saved successfully.'));
        return redirect()->route('platform.interviews');
    }
}
