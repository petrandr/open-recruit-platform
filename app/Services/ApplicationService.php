<?php
// app/Services/ApplicationService.php
declare(strict_types=1);

namespace App\Services;

use App\Models\JobApplication;
use App\Models\NotificationTemplate;
use App\Notifications\ApplicationRejectedNotification;
use Carbon\Carbon;

class ApplicationService
{
    /**
     * Reject an application and optionally send a rejection email.
     *
     * @param JobApplication $application
     * @param int $templateId
     * @param string|null $subjectOverride
     * @param string|null $bodyOverride
     * @param Carbon|null $sendAt
     * @return string Toast message describing the result.
     */
    public function rejectWithEmail(
        JobApplication $application,
        int $templateId,
        ?string $subjectOverride = null,
        ?string $bodyOverride = null,
        ?Carbon $sendAt = null
    ): string {
        // Update status to rejected
        $application->update(['status' => 'rejected']);

        // Fetch the notification template
        $template = NotificationTemplate::findOrFail($templateId);

        // Determine subject and body, allow overrides
        $subject = $subjectOverride ?: $this->parseTemplate($template->subject, $application);
        $body    = $bodyOverride    ?: $this->parseTemplate($template->body, $application);

        // Send notification if candidate email is present
        if ($application->candidate && $application->candidate->email) {
            $sendAt = $sendAt ?: Carbon::now()->addHour();
            $notification = (new ApplicationRejectedNotification($application, $subject, $body))
                ->delay($sendAt);
            $application->candidate->notify($notification);
            // Mark as sent
            $application->update(['rejection_sent' => true]);
            return __('Application rejected and notification scheduled.');
        }

        // No email sent
        return __('Application rejected.');
    }

    /**
     * Replace placeholders in a template string with application data.
     *
     * @param string $template
     * @param JobApplication $application
     * @return string
     */
    public function parseTemplate(string $template, JobApplication $application): string
    {
        $candidate = $application->candidate;
        $replacements = [
            '{{application_id}}'          => $application->id,
            '{{job_title}}'               => $application->jobListing->title,
            '{{job_type}}'                => $application->jobListing->job_type ?? '',
            '{{job_location}}'            => $application->jobListing->location ?? '',
            '{{candidate_first_name}}'    => $candidate->first_name ?? '',
            '{{candidate_last_name}}'     => $candidate->last_name ?? '',
            '{{candidate_full_name}}'     => trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? '')),
            '{{candidate_email}}'         => $candidate->email ?? '',
            '{{candidate_mobile_number}}' => $candidate->mobile_number ?? '',
            '{{company}}'                 => config('platform.organization'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
