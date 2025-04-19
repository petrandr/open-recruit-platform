<?php
declare(strict_types=1);

namespace App\Orchid\Layouts\ActivityLog;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Spatie\Activitylog\Models\Activity;
use Orchid\Screen\Actions\Link;

class ActivityLogListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    public $target = 'activities';

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
                ->render(fn(Activity $activity) => $activity->id),

            TD::make('log_name', __('Log Name'))
                ->filter(TD::FILTER_TEXT)
                ->render(fn(Activity $activity) => $activity->log_name),

            TD::make('description', __('Description'))
                ->filter(TD::FILTER_TEXT)
                ->render(fn(Activity $activity) => $activity->description),

            TD::make('event', __('Event'))
                ->filter(TD::FILTER_TEXT)
                ->render(fn(Activity $activity) => ucfirst($activity->event)),

            TD::make('causer', __('Causer'))
                ->render(fn(Activity $activity) => optional($activity->causer)->name ?? '-'),

            TD::make('subject', __('Subject'))
                ->render(fn(Activity $activity) => sprintf('%s #%s', class_basename($activity->subject_type), $activity->subject_id)),

            TD::make('created_at', __('Date'))
                ->sort()
                ->render(fn(Activity $activity) => $activity->created_at->format('Y-m-d H:i:s')),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('80px')
                ->render(fn(Activity $activity) => Link::make(__('View'))
                    ->route('platform.activity.log', $activity->id)
                    ->icon('bs.eye')
                ),
        ];
    }
}