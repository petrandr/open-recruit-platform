<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Spatie\Activitylog\Models\Activity;
use App\Models\MailLog;
// Activity helper is available in the global namespace via spatie/laravel-activitylog; no import needed

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('local')) {
            Mail::alwaysTo(env('MAIL_DEV_ADDRESS'));
        }

        // Listen to all Eloquent model create/update/delete events
        Event::listen('eloquent.created: *', function (string $eventName, array $data) {
            $model = $data[0] ?? null;
            if (! $model || $model instanceof Activity) {
                return;
            }
            \activity()
                ->performedOn($model)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $model->getAttributes()])
                ->log('created');
        });

        Event::listen('eloquent.updated: *', function (string $eventName, array $data) {
            $model = $data[0] ?? null;
            if (! $model || $model instanceof Activity) {
                return;
            }
            \activity()
                ->performedOn($model)
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => $model->getAttributes(),
                    'old'        => $model->getOriginal(),
                ])
                ->log('updated');
        });

        Event::listen('eloquent.deleted: *', function (string $eventName, array $data) {
            $model = $data[0] ?? null;
            if (! $model || $model instanceof Activity) {
                return;
            }
            \activity()
                ->performedOn($model)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $model->getAttributes()])
                ->log('deleted');
        });
        // Log all sent mails
        Event::listen(\Illuminate\Mail\Events\MessageSent::class, function (\Illuminate\Mail\Events\MessageSent $event) {
            $message = $event->message;
            // Recipients
            $to = method_exists($message, 'getTo') ? array_keys($message->getTo() ?? []) : [];
            // CC and BCC
            $cc = method_exists($message, 'getCc') ? array_keys($message->getCc() ?? []) : [];
            $bcc = method_exists($message, 'getBcc') ? array_keys($message->getBcc() ?? []) : [];
            // Subject
            $subject = method_exists($message, 'getSubject') ? $message->getSubject() : null;
            // Body
            $body = null;
            if (method_exists($message, 'getHtmlBody')) {
                $body = $message->getHtmlBody();
            } elseif (method_exists($message, 'getTextBody')) {
                $body = $message->getTextBody();
            } elseif (method_exists($message, 'getBody')) {
                $body = $message->getBody();
            }
            // Data from event
            $dataPayload = $event->data ?? null;
            MailLog::create([
                'type' => 'mail',
                'class' => get_class($message),
                'channel' => null,
                'subject' => $subject,
                'recipients' => $to,
                'cc' => $cc,
                'bcc' => $bcc,
                'body' => $body,
                'notifiable_type' => null,
                'notifiable_id' => null,
                'data' => $dataPayload,
                'sent_at' => now(),
            ]);
        });
        // Log all sent notifications
        Event::listen(\Illuminate\Notifications\Events\NotificationSent::class, function (\Illuminate\Notifications\Events\NotificationSent $event) {
            $notifiable = $event->notifiable;
            $notification = $event->notification;
            // Recipients
            $to = $notifiable->routeNotificationFor($event->channel, $notification);
            $recipients = is_array($to) ? $to : [$to];
            // Data from Notification
            $data = method_exists($notification, 'toArray') ? $notification->toArray($notifiable) : null;
            MailLog::create([
                'type' => 'notification',
                'class' => get_class($notification),
                'channel' => $event->channel,
                'subject' => null,
                'recipients' => $recipients,
                'cc' => null,
                'bcc' => null,
                'body' => null,
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->getKey(),
                'data' => $data,
                'sent_at' => now(),
            ]);
        });
    }
}
