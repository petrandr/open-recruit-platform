<?php declare(strict_types=1);

namespace App\Orchid\Screens\AppointmentCalendar;

use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use App\Models\AppointmentCalendar;
use App\Orchid\Layouts\AppointmentCalendar\AppointmentCalendarListLayout;
use Orchid\Screen\Actions\Link;
use Illuminate\Support\Facades\Toast;

class AppointmentCalendarListScreen extends Screen
{
    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return __('My Calendars');
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return __('Manage your appointment scheduling calendars');
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
     * Fetch data to show.
     *
     * @return array<string, mixed>
     */
    public function query(): iterable
    {
        $user = auth()->user();
        return [
            'calendars' => AppointmentCalendar::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(),
        ];
    }

    /**
     * Command bar buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add Calendar'))
                ->icon('bs.plus')
                ->route('platform.calendars.create'),
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
            AppointmentCalendarListLayout::class,
        ];
    }
}