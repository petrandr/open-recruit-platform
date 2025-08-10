<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Interview;

use App\Models\Interview;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
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
        return ['platform.interviews', 'platform.my_interviews'];
    }

    public function checkAccess(Request $request): bool
    {
        if (!parent::checkAccess($request)) {
            return false;
        }

        $interviewParam = $request->route('interview');
        $interview = $interviewParam instanceof Interview ? $interviewParam : Interview::find($interviewParam);

        if (!$interview) {
            Toast::warning(__('You do not have permission to access this interview.'));
            throw new HttpResponseException(
                redirect()->route('platform.interviews')
            );
        }

        if (!auth()->user()->hasAccess('platform.interviews')) {
            if (!$interview->interviewer || $interview->interviewer->id !== auth()->id()) {
                Toast::warning(__('You do not have permission to access this interview.'));
                throw new HttpResponseException(
                    redirect()->route('platform.interviews')
                );
            }
        } else {
            // Access control: only allow if job unrestricted or user has matching role
            $userRoleIds = auth()->user()->roles()->pluck('id')->toArray();
            $jobRoleIds = $interview->application->jobListing->roles->pluck('id')->toArray();
            if (empty(array_intersect($jobRoleIds, $userRoleIds))) {
                Toast::warning(__('You do not have permission to access this interview.'));
                throw new HttpResponseException(
                    redirect()->route('platform.interviews')
                );
            }
        }


        return true;
    }

    /**
     * Query data
     */
    public function query(Interview $interview): iterable
    {
        // Load relations including job roles for access control
        $interview->load(['application.candidate', 'application.jobListing.roles', 'interviewer']);

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
            'interview.interviewer_id' => 'required|exists:users,id',
            'interview.scheduled_at' => 'nullable|date',
            'interview.status' => ['required', Rule::in(array_keys(\App\Support\Interview::statuses()))],
            'interview.round' => ['required', Rule::in(array_keys(\App\Support\Interview::rounds()))],
            'interview.mode' => ['required', Rule::in(array_keys(\App\Support\Interview::modes()))],
            'interview.location' => 'nullable|string|max:255',
            'interview.duration_minutes' => 'nullable|integer',
            'interview.comments' => 'nullable|string',
        ]);
        $interview->fill($request->input('interview'));
        $interview->save();
        Toast::info(__('Interview saved successfully.'));
        return redirect()->route('platform.interviews');
    }
}
