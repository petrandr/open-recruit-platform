<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\JobApplication;

class NewApplicationNotification extends Notification
{
    use Queueable;

    /** @var JobApplication */
    protected $application;

    /**
     * Create a new notification instance.
     *
     * @param JobApplication $application
     */
    public function __construct(JobApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $job = $this->application->jobListing;
        $fullName = $this->application->candidate->first_name . ' ' . $this->application->candidate->last_name;

        return (new MailMessage)
            ->subject('New Application for ' . $job->title . ': ' . $fullName)
            ->greeting('Hello,')
            ->line('A new application has been submitted for the job: ' . $job->title)
            ->line('Applicant: ' . $fullName)
            ->action('View Application', route('platform.applications.view', $this->application->id))
            ->line('Thank you for using our ATS system.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
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
    /**
     * Retrieve the associated JobApplication.
     *
     * @return \App\Models\JobApplication
     */
    public function getApplication(): \App\Models\JobApplication
    {
        return $this->application;
    }
}
