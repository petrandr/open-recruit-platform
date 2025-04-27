<?php declare(strict_types=1);

namespace App\Orchid\Layouts\NotificationLog;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Illuminate\Notifications\DatabaseNotification;
use Orchid\Screen\Actions\Link;
use Illuminate\Support\Str;
use App\Models\Candidate;
use App\Models\User;

class NotificationLogListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    public $target = 'notifications';

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
                ->render(fn(DatabaseNotification $notification) => $notification->id),

            TD::make('type', __('Type'))
                ->filter(TD::FILTER_TEXT)
                ->render(fn(DatabaseNotification $notification) => class_basename($notification->type)),

            TD::make('notifiable', __('Notifiable'))
                ->filter(TD::FILTER_TEXT)
                ->render(function (DatabaseNotification $notification) {
                    $notifiable = $notification->notifiable;
                    // Determine display name
                    $name = optional($notifiable)->name
                        ?? optional($notifiable)->full_name
                        ?? sprintf('%s #%s', class_basename($notification->notifiable_type), $notification->notifiable_id);
                    // Link to appropriate route if User or Candidate
                    if ($notifiable instanceof Candidate) {
                        return Link::make($name)
                            ->route('platform.candidates.view', $notifiable->id);
                    }
                    if ($notifiable instanceof User) {
                        return Link::make($name)
                            ->route('platform.systems.users.edit', $notifiable->id);
                    }
                    return $name;
                }),

            TD::make('data', __('Data'))
                ->render(fn(DatabaseNotification $notification) => Str::limit(json_encode($notification->data), 50)),

            TD::make('read_at', __('Read At'))
                ->render(fn(DatabaseNotification $notification) => $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : '-'),

            TD::make('created_at', __('Date'))
                ->sort()
                ->render(fn(DatabaseNotification $notification) => $notification->created_at->format('Y-m-d H:i:s')),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('80px')
                ->render(fn(DatabaseNotification $notification) => Link::make(__('View'))
                    ->route('platform.notification.log', $notification->id)
                    ->icon('bs.eye')
                ),
        ];
    }
}