<?php declare(strict_types=1);

namespace App\Orchid\Screens\NotificationLog;

use Orchid\Screen\Screen;
use Illuminate\Notifications\DatabaseNotification;
use App\Orchid\Layouts\NotificationLog\NotificationLogListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class NotificationLogListScreen extends Screen
{
    /**
     * Screen title.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return __('Notification Logs');
    }

    /**
     * Screen description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return __('Logs of database notifications');
    }

    /**
     * Permissions for viewing this screen.
     *
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        // Use the existing activity logs permission
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
            'notifications' => DatabaseNotification::orderBy('created_at', 'desc')->paginate(),
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
            Link::make(__('Clear All'))
                ->icon('bs.trash3')
                ->confirm(__('Are you sure you want to clear all notification logs?'))
                ->method('clear'),
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
            NotificationLogListLayout::class,
        ];
    }

    /**
     * Clear all notification logs.
     *
     * @param Request $request
     * @return void
     */
    public function clear(Request $request): void
    {
        DatabaseNotification::query()->delete();
        Toast::info(__('All notification logs have been cleared.'));
    }
}