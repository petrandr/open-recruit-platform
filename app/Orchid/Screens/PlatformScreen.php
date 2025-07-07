<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlatformScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $jobs = DB::table('jobs')
            ->orderBy('available_at', 'asc')
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $commandName = data_get($payload, 'data.commandName', data_get($payload, 'displayName', ''));
                $displayName = data_get($payload, 'displayName', '');
                return (object)[
                    'id'           => $job->id,
                    'display_name' => $displayName,
                    'command'      => $commandName,
                    'scheduled_at' => Carbon::createFromTimestamp($job->available_at, config('app.timezone')),
                    'attempts'     => $job->attempts,
                ];
            });

        return [
            'jobs' => $jobs,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Dashboard';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Welcome to your ' . config('app.name') . '.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        $layouts = [
            Layout::view('platform::partials.update-assets'),
        ];
        // Add pending jobs table only to users with permission
        if (auth()->user()->hasAccess('platform.pending-jobs')) {
            $layouts[] = Layout::block(
                Layout::table('jobs', [
                    TD::make('id', 'ID')->render(fn($job) => $job->id)->width('50px'),
                    TD::make('display_name', 'Job')->render(fn($job) => $job->display_name),
                    TD::make('command', 'Job')->render(fn($job) => $job->command),
                    TD::make('scheduled_at', 'Scheduled At')->render(fn($job) => $job->scheduled_at->toDateTimeString()),
                    TD::make('attempts', 'Attempts')->render(fn($job) => $job->attempts)->align(TD::ALIGN_CENTER),
                ])
            )
            ->title('Pending Queue Jobs')
            ->description('List of pending Laravel queue jobs with scheduled time and attempt count');
        }
        return $layouts;
    }
}
