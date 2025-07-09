<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\JobApplication;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Orchid\Screen\Actions\Link;
use Illuminate\Notifications\SendQueuedNotifications;
use Orchid\Screen\Actions\Button;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

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
                $payload = json_decode($job->payload, true) ?? [];
                // Only include notification jobs
                if (data_get($payload, 'data.commandName') !== SendQueuedNotifications::class) {
                    return null;
                }
                $serialized = data_get($payload, 'data.command');
                try {
                    $queued = unserialize($serialized);
                } catch (\Throwable $e) {
                    return null;
                }
                if (! $queued instanceof SendQueuedNotifications) {
                    return null;
                }
                $notification = $queued->notification;
                if (! method_exists($notification, 'getApplication')) {
                    return null;
                }
                $app = $notification->getApplication();
                if (! $app instanceof JobApplication) {
                    return null;
                }
                $candidate = $app->candidate;

                return (object) [
                    'id'              => $job->id,
                    'notification'    => get_class($notification),
                    // Channels this notification will be sent through
                    'channels'        => $queued->channels ?? [],
                    'scheduled_at'    => Carbon::createFromTimestamp($job->available_at, config('app.timezone')),
                    'attempts'        => $job->attempts,
                    'application_id'  => $app->id,
                    'candidate_name'  => $candidate ? ($candidate->first_name . ' ' . $candidate->last_name) : null,
                    'candidate_id'    => $candidate ? $candidate->id : null,
                ];
            })
            ->filter()
            ->values();

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
                    TD::make('id', 'ID')
                        ->render(fn($job) => $job->id)
                        ->width('50px'),

                    TD::make('notification', 'Notification')
                        ->render(fn($job) => class_basename($job->notification)),
                    TD::make('channels', 'Channels')
                        ->render(fn($job) => is_array($job->channels) && count($job->channels)
                            ? implode(', ', $job->channels)
                            : '-'
                        ),

                    TD::make('scheduled_at', 'Scheduled At')
                        ->render(fn($job) => $job->scheduled_at->toDateTimeString()),

                    TD::make('candidate_name', 'Candidate')
                        ->render(fn($job) => $job->candidate_name
                            ? Link::make($job->candidate_name)
                                ->route('platform.candidates.view', ['candidate' => $job->candidate_id])
                            : '-'
                        ),

                    TD::make('application_id', 'Application')
                        ->render(fn($job) => $job->application_id
                            ? Link::make('#'.$job->application_id)
                                ->route('platform.applications.view', ['application' => $job->application_id])
                            : '-'
                        )
                        ->align(TD::ALIGN_CENTER),

                    TD::make('attempts', 'Attempts')
                        ->render(fn($job) => $job->attempts)
                        ->align(TD::ALIGN_CENTER),
                    TD::make('actions', __('Actions'))
                        ->align(TD::ALIGN_CENTER)
                        ->width('100px')
                        ->render(fn($job) => Button::make(__('Cancel'))
                            ->icon('bs.x-circle')
                            ->confirm(__('Are you sure you want to cancel this job?'))
                            ->method('cancelJob', ['id' => $job->id])
                        ),
                ])
            )
            ->title('Pending Notification Jobs')
            ->description('List of pending notification jobs with scheduled time and related candidate/application');
        }
        return $layouts;
    }
    /**
     * Cancel a queued notification job.
     *
     * @param Request $request
     */
    public function cancelJob(Request $request): void
    {
        $id = $request->get('id');
        DB::table('jobs')->where('id', $id)->delete();
        Toast::info(__('Job :id cancelled.', ['id'=>$id]));
    }
}
