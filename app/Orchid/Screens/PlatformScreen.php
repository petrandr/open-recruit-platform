<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use App\Orchid\Screens\Concerns\CancelsPendingJobs;

class PlatformScreen extends Screen
{
    use CancelsPendingJobs;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        // Retrieve all pending notification jobs
        $jobs = app(\App\Services\NotificationJobService::class)->getAll();

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
}
