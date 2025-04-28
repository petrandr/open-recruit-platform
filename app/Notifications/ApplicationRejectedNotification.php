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

    /** @var string Mail subject */
    protected $templateSubject;

    /** @var string Mail and DB body */
    protected $templateBody;

    /**
     * Create a new notification instance.
     *
     * @param JobApplication $application
     * @param string $messageText
     */
    /**
     * @param JobApplication $application
     * @param string $templateSubject
     * @param string $templateBody
     */
    public function __construct(JobApplication $application, string $templateSubject, string $templateBody)
    {
        $this->application = $application;
        $this->templateSubject = $templateSubject;
        $this->templateBody = $templateBody;
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
        return (new MailMessage)
            ->subject($this->templateSubject)
            ->view('emails.generic', ['body' => $this->templateBody]);
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
            'application_id'   => $this->application->id,
            'template_subject' => $this->templateSubject,
            'template_body'    => $this->templateBody,
        ];
    }
}
