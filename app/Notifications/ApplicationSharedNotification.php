<?php
declare(strict_types=1);

namespace App\Notifications;

use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent when an application is shared with a user.
 */
class ApplicationSharedNotification extends Notification
{

    /** @var JobApplication */
    public $application;
    /** @var User */
    public $sharer;

    /**
     * Create a new notification instance.
     *
     * @param JobApplication $application
     * @param User $sharer
     */
    public function __construct(JobApplication $application, User $sharer)
    {
        $this->application = $application;
        $this->sharer = $sharer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<string>
     */
    public function via($notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $app = $this->application;
        $candidate = $app->candidate;
        $job = $app->jobListing;
        $url = route('platform.applications.view', $app->id);

        return (new MailMessage)
            ->subject("Application #{$app->id} Shared with You")
            ->greeting("Hello {$notifiable->name},")
            ->line("An application has been shared with you by {$this->sharer->name}.")
            ->line("Job Title: {$job->title}")
            ->line("Candidate: {$candidate->first_name} {$candidate->last_name}")
            ->action('View Application', $url)
            ->line('Thank you for using our recruitment platform!');
    }

    /**
     * Get the array representation of the notification for storage.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        $mailMessage = $this->toMail($notifiable);

        $fullMessage = implode("\n", array_merge(
            $mailMessage->introLines,
            [$mailMessage->actionText . ' (' . $mailMessage->actionUrl . ')'],
            $mailMessage->outroLines
        ));

        return [
            'template_body' => $fullMessage,
        ];
    }
}
