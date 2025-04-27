<?php declare(strict_types=1);

namespace App\Orchid\Screens\AppointmentCalendar;

use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use App\Models\AppointmentCalendar;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class AppointmentCalendarEditScreen extends Screen
{
    /**
     * The calendar model instance.
     *
     * @var \App\Models\AppointmentCalendar
     */
    public $calendar;

    /**
     * Screen name
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->calendar->exists
            ? __('Edit Calendar')
            : __('Add Calendar');
    }

    /**
     * Permissions.
     *
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        return ['platform.calendars'];
    }

    /**
     * Query data.
     *
     * @param AppointmentCalendar $calendar
     * @return array<string, mixed>
     */
    public function query(AppointmentCalendar $calendar): iterable
    {
        return [
            'calendar' => $calendar,
        ];
    }

    /**
     * Action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            // Only Back button; Save is in block commands
            Link::make(__('Back'))
                ->icon('bs.arrow-left')
                ->route('platform.calendars'),
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
            Layout::block(
                Layout::rows([
                    Input::make('calendar.name')
                        ->title(__('Name'))
                        ->required(),

                    Input::make('calendar.url')
                        ->type('url')
                        ->title(__('URL'))
                        ->required(),
                ])
            )
            ->title($this->calendar->exists ? __('Edit Calendar') : __('Add Calendar'))
            ->description(__('Provide a descriptive name and the external URL for your calendar.'))
            ->commands([
                Button::make(__('Save'))
                    ->icon('bs.check2')
                    ->method('save'),
            ]),
        ];
    }

    /**
     * Save calendar.
     *
     * @param Request $request
     * @param AppointmentCalendar $calendar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request, AppointmentCalendar $calendar)
    {
        $request->validate([
            'calendar.name' => 'required|string|max:255',
            'calendar.url'  => 'required|url|max:255',
        ]);

        $calendar->fill($request->input('calendar'));
        if (! $calendar->exists) {
            $calendar->user()->associate($request->user());
        }
        $calendar->save();

        Toast::info(__('Calendar saved successfully'));

        return redirect()->route('platform.calendars');
    }
}