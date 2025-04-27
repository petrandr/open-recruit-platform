<?php declare(strict_types=1);

namespace App\Orchid\Layouts\AppointmentCalendar;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use App\Models\AppointmentCalendar;
use Orchid\Screen\Actions\Link;

class AppointmentCalendarListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    public $target = 'calendars';

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
                ->render(fn(AppointmentCalendar $cal) => $cal->id),

            TD::make('name', __('Name'))
                ->sort()
                ->render(fn(AppointmentCalendar $cal) => Link::make($cal->name)
                    ->route('platform.calendars.edit', $cal->id)
                ),

            TD::make('url', __('URL'))
                ->render(fn(AppointmentCalendar $cal) => Link::make($cal->url)
                    ->href($cal->url)
                    ->target('_blank')
                ),

            TD::make('created_at', __('Created At'))
                ->sort()
                ->render(fn(AppointmentCalendar $cal) => $cal->created_at->format('Y-m-d H:i:s')),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->render(fn(AppointmentCalendar $cal) => Link::make(__('Edit'))
                    ->route('platform.calendars.edit', $cal->id)
                    ->icon('bs.pencil')
                ),
        ];
    }
}