<?php
declare(strict_types=1);

namespace App\Orchid\Screens\ActivityLog;

use Orchid\Screen\Screen;
use Spatie\Activitylog\Models\Activity;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Actions\Link;
use Illuminate\Http\Request;

class ActivityLogViewScreen extends Screen
{
    /**
     * Screen title.
     */
    public function name(): ?string
    {
        return __('Activity Log Detail');
    }

    /**
     * Screen description.
     */
    public function description(): ?string
    {
        return __('Details of the selected activity entry');
    }

    /**
     * Permissions for accessing this screen.
     */
    public function permission(): ?iterable
    {
        return ['platform.activity-logs'];
    }

    /**
     * Fetch the activity entry.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function query(Request $request): iterable
    {
        $activity = Activity::findOrFail($request->route('id'));
        return [
            'activity' => $activity,
        ];
    }

    /**
     * Screen action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Back to Logs'))
                ->icon('bs.arrow-left')
                ->route('platform.activity.logs'),
        ];
    }

    /**
     * Screen layout.
     */
    public function layout(): iterable
    {
        return [
            Layout::rows([
                Label::make('activity.id')->title(__('ID')),
                Label::make('activity.log_name')->title(__('Log Name')),
                Label::make('activity.description')->title(__('Description')),
                Label::make('activity.event')->title(__('Event')),
                Label::make('activity.subject_type')->title(__('Subject Type')),
                Label::make('activity.subject_id')->title(__('Subject ID')),
                Label::make('activity.causer_type')->title(__('Causer Type')),
                Label::make('activity.causer_id')->title(__('Causer ID')),
                Label::make('activity.properties')->title(__('Properties')),
                Label::make('activity.created_at')->title(__('Logged At')),
            ]),
        ];
    }
}