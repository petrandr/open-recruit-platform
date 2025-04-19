<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use function Spatie\Activitylog\activity;

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
        Model::created(function (Model $model) {
            if ($model instanceof Activity) {
                return;
            }
            activity()
                ->performedOn($model)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $model->getAttributes()])
                ->log('created');
        });

        Model::updated(function (Model $model) {
            if ($model instanceof Activity) {
                return;
            }
            activity()
                ->performedOn($model)
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => $model->getAttributes(),
                    'old' => $model->getOriginal(),
                ])
                ->log('updated');
        });

        Model::deleted(function (Model $model) {
            if ($model instanceof Activity) {
                return;
            }
            activity()
                ->performedOn($model)
                ->causedBy(auth()->user())
                ->withProperties(['attributes' => $model->getAttributes()])
                ->log('deleted');
        });
    }
}
