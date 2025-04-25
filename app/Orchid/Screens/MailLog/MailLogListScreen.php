<?php
declare(strict_types=1);

namespace App\Orchid\Screens\MailLog;

use Orchid\Screen\Screen;
use App\Models\MailLog;
use App\Orchid\Layouts\MailLog\MailLogListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class MailLogListScreen extends Screen
{
    /**
     * Screen name displayed in header.
     */
    public function name(): ?string
    {
        return __('Mail & Notification Logs');
    }

    /**
     * Screen description.
     */
    public function description(): ?string
    {
        return __('All mails and notifications sent through the system');
    }

    /**
     * Permission required to view this screen.
     */
    public function permission(): ?iterable
    {
        return ['platform.mail-logs'];
    }

    /**
     * Query data for the screen.
     *
     * @return array<string, mixed>
     */
    public function query(): iterable
    {
        return [
            'mailLogs' => MailLog::orderBy('id', 'desc')->paginate(),
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
                ->confirm(__('Are you sure you want to clear all logs?'))
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
            MailLogListLayout::class,
        ];
    }

    /**
     * Clear all mail log entries.
     */
    public function clear(Request $request): void
    {
        MailLog::query()->delete();
        Toast::info(__('All mail & notification logs have been cleared.'));
    }
}