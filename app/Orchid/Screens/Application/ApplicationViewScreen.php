<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Application;

use App\Models\JobApplication;
use App\Orchid\Fields\Ckeditor;
use App\Support\ApplicationStatus;
use App\Models\Interview;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Layouts\Modal as ModalLayout;
use Orchid\Screen\Sight;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Auth;

use App\Notifications\ApplicationRejectedNotification;
use App\Notifications\ApplicationInterviewInvitationNotification;
use Orchid\Screen\Fields\Select;
use App\Models\NotificationTemplate;
use App\Models\AppointmentCalendar;
use Orchid\Screen\Fields\Input;

class ApplicationViewScreen extends Screen
{
    /**
     * The application model instance.
     *
     * @var JobApplication
     */
    public $application;

    /**
     * Query data for the screen.
     *
     * @param JobApplication $application
     * @return array<string, mixed>
     */
    public function query(JobApplication $application): iterable
    {
        $application->load([
            'jobListing',
            'candidate',
            'answers.question',
            'jobListing.screeningQuestions',
            'tracking',
            'comments.user',
            'statusLogs.user',
        ]);
        // Fetch other applications submitted by this candidate (exclude current)
        $otherApplications = $application->candidate
            ->applications()
            ->where('id', '!=', $application->id)
            ->with('jobListing')
            ->get();
        return [
            'application' => $application,
            'otherApplications' => $otherApplications,
            'statusLogs' => $application->statusLogs,
        ];
    }

    /**
     * Screen name shown in header.
     */
    public function name(): ?string
    {
        return __('Application Details');
    }

    /**
     * Screen description shown under header.
     */
    public function description(): ?string
    {
        return __('Full-page view of the application information.');
    }

    /**
     * Permissions required to access this screen.
     */
    public function permission(): ?iterable
    {
        return ['platform.applications'];
    }

    /**
     * Action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        $commands = [
            // Back link (preserve filters)
            Link::make(__('Back to Applications'))
                ->icon('bs.arrow-left')
                ->route('platform.applications', request()->query()),
            // View CV modal trigger
            Link::make(__('View CV'))
                ->icon('bs.file-earmark-text')
                ->class('application-cv-trigger btn btn-outline-primary text-primary')
                ->set('data-bs-toggle', 'modal')
                ->set('data-bs-target', '#applicationCvModal')
                ->set('data-application-id', $this->application->id),
        ];

        if (in_array($this->application->status, ApplicationStatus::allowedForInterview())) {
            $commands[] = ModalToggle::make(__('Schedule Interview'))
                ->icon('bs.calendar-event')
                ->modal('scheduleModal')
                ->novalidate();
        }

        $commands[] = DropDown::make(__('Change Status'))
            ->icon('bs.sliders')
            ->list(
                collect(ApplicationStatus::all())
                    ->filter(function ($meta, $key) {
                        return $key !== $this->application->status;
                    })
                    ->map(function ($meta, $key) {
                        if ($key == $this->application->status) {
                            return;
                        }
                        // Use modal for rejected status
                        if ($key === 'rejected') {
                            return ModalToggle::make($meta['label'])
                                ->icon($meta['icon'])
                                ->modal('rejectModal')
                                ->novalidate();
                        }
                        return Button::make($meta['label'])
                            ->icon($meta['icon'])
                            ->method('changeStatus', ['id' => $this->application->id, 'status' => $key])
                            ->confirm(__("Change status to :status?", ['status' => $meta['label']]))
                            ->novalidate();
                    })
                    ->toArray()
            );

        return $commands;
    }

    /**
     * Add a comment to this application.
     *
     * @param Request $request
     */
    public function addComment(Request $request): void
    {
        $app = JobApplication::findOrFail($request->get('id'));
        $text = trim($request->get('comment_text', ''));
        if ($text !== '') {
            $app->comments()->create([
                'comment_text' => $text,
                'source' => 'panel',
                'user_id' => Auth::id(),
            ]);
            Toast::info(__('Comment added.'));
        }
    }

    /**
     * Screen layout.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        // Basic application details as read-only cards
        $basic = [
            Sight::make('jobListing.title', __('Job Title')),
            Sight::make('id', __('Application #')),
            Sight::make('candidate.first_name', __('First Name')),
            Sight::make('candidate.last_name', __('Last Name')),
            Sight::make('candidate.email', __('Email')),
            Sight::make('candidate.mobile_number', __('Mobile Number')),
            Sight::make('jobListing.job_type', __('Job Type')),
            Sight::make('jobListing.location', __('Location')),
            Sight::make('status', __('Status'))->render(function () {
                $status = $this->application->status;
                $statuses = ApplicationStatus::all();
                $meta = $statuses[$status] ?? null;
                $label = $meta['label'] ?? ucfirst($status);
                $color = $meta['color'] ?? 'secondary';
                return "<span class='badge bg-{$color} status-badge'>{$label}</span>";
            }),
            // Fit status based on screening questions
            Sight::make('fit', __('Fit'))->render(function () {
                return "<span class='badge bg-{$this->application->fitClass} status-badge'>{$this->application->fit}</span>";
            }),
            Sight::make('notice_period', __('Notice Period')),
            Sight::make('desired_salary', __('Desired Salary'))->render(function () {
                $value = $this->application->desired_salary;
                if ($value === null) {
                    return '-';
                }
                $amount = (float)$value;
                $formatted = number_format($amount, 2, ',', '.');
                $code = strtoupper($this->application->salary_currency ?? '');
                $symbol = match ($code) {
                    'EUR' => '€',
                    'USD' => '$',
                    'GBP' => '£',
                    default => $code,
                };
                return $symbol ? $symbol . $formatted : $formatted;
            }),
            Sight::make('linkedin_profile', __('LinkedIn Profile'))->render(fn() => $this->application->linkedin_profile
                ? Link::make($this->application->linkedin_profile)->href($this->application->linkedin_profile)
                : '-'
            ),
            Sight::make('github_profile', __('GitHub Profile'))->render(fn() => $this->application->github_profile
                ? Link::make($this->application->github_profile)->href($this->application->github_profile)
                : '-'
            ),
            Sight::make('how_heard', __('How Did They Hear About Us?')),
            Sight::make('submitted_at', __('Submitted At'))->render(fn() => $this->application->submitted_at->format('Y-m-d H:i:s')
            ),
        ];

        // Screening questions
        $questions = [];
        foreach ($this->application->jobListing->screeningQuestions as $question) {
            $questions[] = Sight::make('question_' . $question->id, $question->question_text ?? $question->question)
                ->render(fn() => optional(
                    $this->application->answers->firstWhere('question_id', $question->id)
                )->answer_text ?? '-'
                );
        }

        // Assemble layouts: include sticky header CSS first
        // Assemble layouts
        $application_details = Layout::legend('application', $basic)
            ->title(__('Application Details'));

        // Table of other applications by this candidate
        $otherApplicationsTable = Layout::table('otherApplications', [
            TD::make('jobListing.title', __('Job Title'))
//                ->class('text-nowrap')
                ->render(fn(JobApplication $app) => $app->jobListing->title),
            TD::make('submitted_at', __('Submitted At'))
                ->render(fn(JobApplication $app) => $app->submitted_at
                    ? $app->submitted_at->format('M d, Y')
                    : '-'
                ),
            TD::make('status', __('Status'))
                ->render(function (JobApplication $app) {
                    $status = $app->status;
                    $color = match ($status) {
                        'submitted' => 'info',
                        'under review' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    };
                    return "<span class='badge bg-{$color}'>" . ucfirst($status) . "</span>";
                }),
            TD::make(__('View'))
                ->render(fn(JobApplication $app) => Link::make(__('View'))
                    ->route('platform.applications.view', $app->id)
                ),
        ])->title(__('Other Applications'));
        // Build layouts array
        $layouts = [];
        // Show job status alert if job listing is disabled or inactive
        $jobStatus = $this->application->jobListing->status;
        if (in_array($jobStatus, ['disable', 'inactive'], true)) {
            $layouts[] = Layout::view('partials.job-status-alert', [
                'status' => $jobStatus,
            ]);
        }
        // Show other applications only if there are any
        if ($this->application->candidate->applications()
            ->where('id', '!=', $this->application->id)
            ->exists()) {
            $layouts[] = $otherApplicationsTable;
        }
        // Show a warning banner if rejection email has been sent
        if ($this->application->rejection_sent) {
            $layouts[] = Layout::view('partials.application-rejected-warning');
        }
        // Two-column layout: details and comments
        $layouts[] = Layout::split([
            // Left: details and screening
            array_filter([
                $application_details,
                !empty($questions)
                    ? Layout::view('partials.screening-questions')
                    : null,
            ]),
            // Right: comments
            [
                Layout::block([
                    Layout::view('partials.application-comments'),
                    Layout::rows([
                        TextArea::make('comment_text')
                            ->title(__('New Comment'))
                            ->required()
                            ->rows(3)
                            ->class('form-control comment-textarea'),
                        Button::make(__('Add Comment'))
                            ->icon('bs.chat-dots')
                            ->method('addComment', ['id' => $this->application->id])
                            ->class('btn btn-link icon-link comment-submit'),
                    ]),
                ])
                    ->title(__('Comments'))
                    ->vertical(),
                Layout::block([
                    // Application progress (status history)
                    Layout::view('partials.application-status-progress', [
                        'created_at' => $this->application->created_at,
                        'statusLogs' => $this->application->statusLogs,
                    ]),
                ])
                    ->title(__('Application Progress'))
                    ->vertical(),
            ],

        ])
            ->ratio('70/30');
        // CV preview modal
        $layouts[] = Layout::view('partials.application-cv-modal');
        // Schedule Interview modal: select template, preview and modify before sending
        // Fetch interview invitation templates and pre-render placeholders
        $rawInterviewTemplates = NotificationTemplate::where('type', 'interview_invitation')
            ->get(['id', 'subject', 'body']);
        $interviewTemplates = [];
        foreach ($rawInterviewTemplates as $tmpl) {
            $interviewTemplates[] = [
                'id' => $tmpl->id,
                'subject' => $this->parseTemplate($tmpl->subject, $this->application),
                'body' => $this->parseTemplate($tmpl->body, $this->application),
            ];
        }

        if (in_array($this->application->status, ApplicationStatus::allowedForInterview())) {
            $layouts[] = Layout::modal('scheduleModal', [
                // First section: ID, template, subject
                Layout::rows([
                    Input::make('id')
                        ->type('hidden')
                        ->value($this->application->id),
                    Select::make('template_id')
                        ->id('schedule-template-select')
                        ->title(__('Invitation Template'))
                        ->options(NotificationTemplate::where('type', 'interview_invitation')->pluck('name', 'id'))
                        ->empty(__('Select invitation template'), '')
                        ->value('')
                        ->required()
                        ->set('data-templates', json_encode($interviewTemplates)),
                    Input::make('subject')
                        ->id('schedule-subject')
                        ->title(__('Notification Subject'))
                        ->required(),
                ]),
                // Calendar picker (user & calendar) appears here
                Layout::view('partials.schedule-modal-calendar-picker'),
                // Body message editor
                Layout::rows([
                    Ckeditor::make('body')
                        ->id('schedule-body')
                        ->title(__('Message'))
                        ->rows(10)
                        ->required(),
                ]),
                // Template-editor script (populates subject/body on template change)
                Layout::view('partials.schedule-modal-template-editor', [
                    'templates' => $interviewTemplates,
                ]),
                // Action buttons
                Layout::view('partials.schedule-modal-buttons', [
                    'application' => $this->application,
                ]),
            ])
                ->title(__('Schedule Interview'))
                ->withoutApplyButton()
                ->withoutCloseButton()
                ->staticBackdrop()
                ->size(ModalLayout::SIZE_LG);
        }
        // Rejection modal: select template, preview and modify before sending
        // Fetch rejection templates and pre-render placeholders for preview
        $rawTemplates = NotificationTemplate::where('type', 'rejection')->get(['id', 'subject', 'body']);
        $rejectTemplates = [];
        foreach ($rawTemplates as $tmpl) {
            $rejectTemplates[] = [
                'id' => $tmpl->id,
                'subject' => $this->parseTemplate($tmpl->subject, $this->application),
                'body' => $this->parseTemplate($tmpl->body, $this->application),
            ];
        }
        $layouts[] = Layout::modal('rejectModal', [
            Layout::rows([
                Input::make('id')
                    ->type('hidden')
                    ->value($this->application->id),
                Select::make('template_id')
                    ->id('reject-template-select')
                    ->title(__('Template'))
                    ->options(NotificationTemplate::where('type', 'rejection')->pluck('name', 'id'))
                    ->empty(__('Select a template'), '')
                    ->value('')
                    ->required(),
                Input::make('subject')
                    ->id('reject-subject')
                    ->title(__('Subject'))
                    ->required(),
                Ckeditor::make('body')
                    ->id('reject-body')
                    ->title(__('Message'))
                    ->rows(10)
            ]),
            Layout::view('partials.reject-modal-buttons', [
                'application' => $this->application,
            ]),
            Layout::view('partials.reject-modal-template-editor', [
                'templates' => $rejectTemplates,
            ]),
        ])
            ->title(__('Reject Application'))
            ->withoutApplyButton()
            ->withoutCloseButton()
            ->staticBackdrop()
            ->size(ModalLayout::SIZE_LG);
        return $layouts;
    }

    /**
     * Change application status.
     *
     * @param Request $request
     */
    public function changeStatus(Request $request): void
    {
        $application = JobApplication::findOrFail($request->get('id'));
        $status = $request->get('status');
        $application->update(['status' => $status]);
        Toast::info(__('Application status changed to :status', ['status' => ucfirst($status)]));
    }

    /**
     * Reject application and send rejection email with optional message.
     *
     * @param Request $request
     */
    public function rejectWithEmail(Request $request): void
    {
        $application = JobApplication::with('candidate')->findOrFail($request->get('id'));
        // Update status
        $application->update(['status' => 'rejected']);
        // Select template and notify
        $templateId = $request->get('template_id');
        $template = NotificationTemplate::findOrFail($templateId);
        // Determine subject and body, allowing user overrides
        $subject = $request->filled('subject')
            ? $request->get('subject')
            : $this->parseTemplate($template->subject, $application);
        $body = $request->filled('body')
            ? $request->get('body')
            : $this->parseTemplate($template->body, $application);
        if ($application->candidate && $application->candidate->email) {
            $application->candidate->notify(
                new ApplicationRejectedNotification($application, $subject, $body)
            );
            $application->update(['rejection_sent' => true]);
            Toast::info(__('Application rejected and notification sent.'));
        } else {
            Toast::info(__('Application rejected.'));
        }
    }

    /**
     * Schedule interview and optionally send invitation email.
     *
     * @param Request $request
     */
    public function scheduleInterviewWithEmail(Request $request): void
    {
        $application = JobApplication::with('candidate')->findOrFail($request->get('id'));
        // Create interview record (scheduled_at can be null until confirmed)
        Interview::create([
            'application_id' => $application->id,
            'interviewer_id' => $request->get('user_id'),
        ]);
        // Update status to interview scheduled
        $application->update(['status' => 'interview_scheduled']);
        // Select template and notify
        $templateId = $request->get('template_id');
        $template = NotificationTemplate::findOrFail($templateId);
        $subject = $request->filled('subject')
            ? $request->get('subject')
            : $this->parseTemplate($template->subject, $application);
        $body = $request->filled('body')
            ? $request->get('body')
            : $this->parseTemplate($template->body, $application);
        // Replace appointment_calendar placeholder with selected calendar URL
        if (str_contains($body, '{{appointment_calendar}}')) {
            $calendarId = $request->get('calendar_id');
            $calendar = AppointmentCalendar::findOrFail($calendarId);
            $body = str_replace('{{appointment_calendar}}', $calendar->url, $body);
        }
        if ($application->candidate && $application->candidate->email) {
            $application->candidate->notify(
                new ApplicationInterviewInvitationNotification($application, $subject, $body)
            );
            Toast::info(__('Interview scheduled and invitation sent.'));
        } else {
            Toast::info(__('Interview scheduled.'));
        }
    }

    /**
     * Replace placeholders in template text.
     *
     * @param string $template
     * @param JobApplication $application
     * @return string
     */
    protected function parseTemplate(string $template, JobApplication $application): string
    {
        $candidate = $application->candidate;
        $replacements = [
            '{{application_id}}' => $application->id,
            '{{job_title}}' => $application->jobListing->title,
            '{{job_type}}' => $application->jobListing->job_type ?? '',
            '{{job_location}}' => $application->jobListing->location ?? '',
            '{{candidate_first_name}}' => $candidate->first_name ?? '',
            '{{candidate_last_name}}' => $candidate->last_name ?? '',
            '{{candidate_full_name}}' => trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? '')),
            '{{candidate_email}}' => $candidate->email ?? '',
            '{{candidate_mobile_number}}' => $candidate->mobile_number ?? '',
            '{{company}}' => config('platform.organization'),
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
