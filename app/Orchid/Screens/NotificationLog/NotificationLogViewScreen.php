<?php declare(strict_types=1);

namespace App\Orchid\Screens\NotificationLog;

use Orchid\Screen\Screen;
use Illuminate\Notifications\DatabaseNotification;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Actions\Link;
use Illuminate\Http\Request;

class NotificationLogViewScreen extends Screen
{
    /**
     * Screen title.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return __('Notification Detail');
    }

    /**
     * Screen description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return __('Details of the selected notification entry');
    }

    /**
     * Permissions for accessing this screen.
     *
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        return ['platform.notification-logs'];
    }

    /**
     * Fetch the notification entry.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function query(Request $request): iterable
    {
        $notification = DatabaseNotification::findOrFail($request->route('id'));
        // Stringify the data payload for display
        $dataStr = json_encode($notification->data, JSON_PRETTY_PRINT);
        // Prepare a human-readable notifiable name
        $notifiable = $notification->notifiable;
        $notifiableName = optional($notifiable)->name
            ?? optional($notifiable)->full_name
            ?? sprintf('%s #%s', class_basename($notification->notifiable_type), $notification->notifiable_id);
        return [
            'notification'   => $notification,
            'dataStr'        => $dataStr,
            'notifiableName' => $notifiableName,
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
            Link::make(__('Back to Logs'))
                ->icon('bs.arrow-left')
                ->route('platform.notification.logs'),
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
            Layout::rows([
                Label::make('notification.id')->title(__('ID')),
                Label::make('notification.type')->title(__('Type')),
                Label::make('notifiableName')->title(__('Notifiable')),
                Label::make('notification.data.template_body')->title(__('Notification Message')),
                Label::make('notification.read_at')->title(__('Read At')),
                Label::make('notification.created_at')->title(__('Created At')),
                Label::make('notification.updated_at')->title(__('Updated At')),
            ]),
        ];
    }
}
