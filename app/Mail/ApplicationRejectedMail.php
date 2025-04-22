<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\JobApplication;

class ApplicationRejectedMail extends Mailable
{
    use SerializesModels;

    /** @var JobApplication */
    public $application;

    /** @var string */
    public $message_text;

    /**
     * Create a new message instance.
     *
     * @param JobApplication $application
     * @param string $message_text
     */
    public function __construct(JobApplication $application, string $message_text)
    {
        $this->application = $application;
        $this->message_text = $message_text;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(__('Application Rejection'))
            ->view('emails.application_rejected')
            ->with([
                'application' => $this->application,
                'message_text' => $this->message_text,
            ]);
    }
}