<?php declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\JobApplication;

class ApplicationRejectedNotification extends Notification
{
    use Queueable;

    /** @var JobApplication */
    protected $application;

    /** @var string */
    protected $messageText;

    /**
     * Create a new notification instance.
     *
     * @param JobApplication $application
     * @param string $messageText
     */
    public function __construct(JobApplication $application, string $messageText)
    {
        $this->application = $application;
        $this->messageText = $messageText;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $job = $this->application->jobListing;
        $appId = $this->application->id;
        return (new MailMessage)
            ->subject(__('Application Rejected'))
            ->greeting(__('Hello,'))
            ->line(__('We regret to inform you that your application #:id for :title has been rejected.', [
                'id' => $appId,
                'title' => $job->title,
            ]))
            ->line($this->messageText)
            ->line(__('Thank you for your interest in our positions.'));
    }

    /**
     * Get the array representation of the notification for storage.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'job_title'      => $this->application->jobListing->title,
            'message'        => $this->messageText,
        ];
    }
}