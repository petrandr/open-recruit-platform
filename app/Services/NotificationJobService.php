<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\JobApplication;

/**
 * Service to retrieve pending notification queue jobs.
 */
class NotificationJobService
{
    /**
     * Get all pending notification jobs.
     *
     * @return Collection<\stdClass>
     */
    public function getAll(): Collection
    {
        return DB::table('jobs')
            ->orderBy('available_at', 'asc')
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true) ?: [];
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
                    'id'             => $job->id,
                    'notification'   => get_class($notification),
                    'channels'       => $queued->channels ?? [],
                    'scheduled_at'   => Carbon::createFromTimestamp($job->available_at, config('app.timezone')),
                    'attempts'       => $job->attempts,
                    'application_id' => $app->id,
                    'candidate_name' => $candidate ? ($candidate->first_name . ' ' . $candidate->last_name) : null,
                    'candidate_id'   => $candidate ? $candidate->id : null,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Get pending notification jobs for a specific application.
     *
     * @param JobApplication $application
     * @return Collection<\stdClass>
     */
    public function getForApplication(JobApplication $application): Collection
    {
        return $this->getAll()
            ->filter(fn($job) => isset($job->application_id) && $job->application_id === $application->id)
            ->values();
    }
}