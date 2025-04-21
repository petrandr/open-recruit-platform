<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Spatie\Activitylog\Models\Activity;
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
    }
}
