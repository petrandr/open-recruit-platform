<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogNotificationToDatabase
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event): void
    {
        // Only log mail channel notifications
        if ($event->channel !== 'mail') {
            return;
        }
        try {
            // Use toDatabase if available, else fallback to toArray
            if (method_exists($event->notification, 'toDatabase')) {
                $data = $event->notification->toDatabase($event->notifiable);
            } else {
                $data = $event->notification->toArray($event->notifiable);
            }
            $event->notifiable->notifications()->create([
                'id'      => (string) Str::uuid(),
                'type'    => get_class($event->notification),
                'data'    => $data,
                'read_at' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to log notification to DB: ' . $e->getMessage());
        }

        try {
            if (method_exists($event->notification, 'postNotificationSentAction')) {
                $event->notification->postNotificationSentAction();
            }
        } catch (\Throwable $e) {
            Log::error('Failed to run post notification actions: ' . $e->getMessage());
        }
    }
}
