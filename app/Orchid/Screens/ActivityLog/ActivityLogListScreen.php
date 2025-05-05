<?php
declare(strict_types=1);

namespace App\Orchid\Screens\ActivityLog;

use Orchid\Screen\Screen;
use Spatie\Activitylog\Models\Activity;
use App\Orchid\Layouts\ActivityLog\ActivityLogListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class ActivityLogListScreen extends Screen
{
    /**
     * Screen title.
     */
    public function name(): ?string
    {
        return __('Activity Logs');
    }

    /**
     * Screen description.
     */
    public function description(): ?string
    {
        return __('Audit trail of system events');
    }

    /**
     * Permissions for viewing this screen.
     */
    public function permission(): ?iterable
    {
        return ['platform.activity-logs'];
    }

    /**
     * Query data for the screen.
     *
     * @return array<string, mixed>
     */
    public function query(): iterable
    {
        return [
            // Fetch activities ordered by newest first
            'activities' => Activity::orderBy('id', 'desc')
                ->paginate(),
        ];
    }

    /**
     * Screen action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [

        ];
    }

    /**
     * Screen layout.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            ActivityLogListLayout::class,
        ];
    }

    /**
     * Clear all activity logs.
     */
    public function clear(Request $request): void
    {
        Activity::query()->delete();
        Toast::info(__('All activity logs have been cleared.'));
    }
}
