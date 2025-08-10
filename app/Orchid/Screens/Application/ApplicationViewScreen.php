<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Application;

use App\Models\JobApplication;
use App\Notifications\ApplicationSharedNotification;
use App\Orchid\Fields\Ckeditor;
use App\Support\ApplicationStatus;
use App\Models\Interview;
use Carbon\Carbon;
use App\Services\ApplicationService;
use App\Services\NotificationJobService;
use Illuminate\Validation\Rule;
use Orchid\Screen\Fields\DateTimer;
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
use App\Orchid\Screens\Concerns\CancelsPendingJobs;

use App\Notifications\ApplicationInterviewInvitationNotification;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Input;
use App\Models\NotificationTemplate;
use App\Models\AppointmentCalendar;

class ApplicationViewScreen extends Screen
{
    use CancelsPendingJobs;
    /**
     * Pending notification jobs for this application.
     *
     * @var \Illuminate\Support\Collection
     */
    public $pendingNotifications;
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
        // Load relations including job roles and shared users for access control
        $application->load([
            'jobListing.roles',
            'sharedWith',
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
        // Fetch interviews for this application
        $interviews = $application->interviews()->with('interviewer')->get();
        // Fetch pending notification jobs for this application
        $pendingNotifications = app(NotificationJobService::class)
            ->getForApplication($application);
        // Store for use in layout
        $this->pendingNotifications = $pendingNotifications;
        return [
            'application'          => $application,
            'otherApplications'    => $otherApplications,
            'statusLogs'           => $application->statusLogs,
            'interviews'           => $interviews,
            'pendingNotifications' => $pendingNotifications,
        ];
    }

    public function checkAccess(Request $request): bool
    {
        if (!parent::checkAccess($request)) {
            return false;
        }

        if (auth()->user()->hasAdminPrivileges()) {
            return true;
        }

        $applicationParam = $request->route('application');
        $application = $applicationParam instanceof JobApplication ? $applicationParam : JobApplication::find($applicationParam);

        if (!$application) {
            return false;
        }

        // Access control: allow if user has a role for this job OR the application was shared with them
        $user = Auth::user();
        $userRoleIds = $user->roles()->pluck('id')->toArray();
        $jobRoleIds  = $application->jobListing->roles->pluck('id')->toArray();
        $shared = $application->sharedWith->contains('id', $user->id);
        if (empty(array_intersect($jobRoleIds, $userRoleIds)) && ! $shared) {
            return false;
        }

        return true;
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

        // Share Application button (opens shareModal) - only for users with job-level role
        $canShare = $this->application->jobListing
            ->roles()
            ->whereIn('roles.id', auth()->user()->roles()->pluck('id')->toArray())
            ->exists();
        $commands[] = ModalToggle::make(__('Share Application'))
            ->icon('bs.share')
            ->modal('shareModal')
            ->novalidate()
            ->canSee($canShare);
        return $commands;
    }
    /**
     * Share this application with other users.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function shareApplication(\Illuminate\Http\Request $request): void
    {
        $application = JobApplication::findOrFail($request->get('id'));
        // Authorization: only allow users with job-level roles to share
        $userRoleIds = auth()->user()->roles()->pluck('id')->toArray();
        $jobRoleIds = $application->jobListing->roles()->pluck('id')->toArray();
        if (empty(array_intersect($userRoleIds, $jobRoleIds))) {
            abort(403);
        }
        $userIds = $request->get('share_user_ids', []);
        if (!is_array($userIds)) {
            $userIds = [];
        }
        // Attach without detaching existing shares
        $application->sharedWith()->syncWithoutDetaching($userIds);
        // Notify selected users immediately (email & database)
        $users = \App\Models\User::whereIn('id', $userIds)->get();
        if ($users->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::sendNow(
                $users,
                new \App\Notifications\ApplicationSharedNotification(
                    $application,
                    auth()->user()
                )
            );
        }
        Toast::info(__('Application has been shared.'));
    }
    /**
     * Remove a shared user from this application.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function removeShare(\Illuminate\Http\Request $request): void
    {
        $application = JobApplication::findOrFail($request->get('id'));
        // Authorization: only allow users with job-level roles to remove share
        $userRoleIds = auth()->user()->roles()->pluck('id')->toArray();
        $jobRoleIds = $application->jobListing->roles()->pluck('id')->toArray();
        if (empty(array_intersect($userRoleIds, $jobRoleIds))) {
            abort(403);
        }
        $userId = $request->get('user_id');
        $application->sharedWith()->detach($userId);
        Toast::info(__('User removed from share list.'));
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
            Sight::make('country', __('Candidate Location'))->render(function () {
                return $this->application->country .' / '.$this->application->city;
            }),
            Sight::make('jobListing.location', __('Job Location')),
            Sight::make('jobListing.job_type', __('Job Type')),
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
        $application_details = Layout::legend('application', $basic);

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
        // Tabbed layout: Details and Interviews (main content)
        $detailsItems = [];
        $jobStatus = $this->application->jobListing->status;
        if (in_array($jobStatus, ['disable', 'inactive'], true)) {
            $detailsItems[] = Layout::view('partials.job-status-alert', [
                'status' => $jobStatus,
            ]);
        }
        if ($this->application->candidate->applications()
            ->where('id', '!=', $this->application->id)
            ->exists()) {
            $detailsItems[] = $otherApplicationsTable;
        }

        // Application details and screening questions
        $detailsItems[] = $application_details;
        if (!empty($questions)) {
            $detailsItems[] = Layout::view('partials.screening-questions');
        }
        // Interviews table
        $interviewTable = Layout::table('interviews', [
            TD::make('id', __('ID'))
                ->render(function (Interview $interview) {
                    return Link::make((string) $interview->id)
                        ->route('platform.interviews.view', $interview);
                }),
            TD::make('scheduled_at', __('Scheduled At'))
                ->render(fn(Interview $i) => $i->scheduled_at
                    ? $i->scheduled_at->format('Y-m-d H:i')
                    : '-'),
            TD::make('interviewer', __('Interviewer'))
                ->render(fn(Interview $i) => $i->interviewer?->name ?? '-'),
            TD::make('status', __('Status'))
                ->render(function (Interview $interview) {
                    $item = \App\Support\Interview::statuses()[$interview->status];
                    return "<span class=\"badge bg-{$item['color']} status-badge\">{$item['label']}</span>";
                }),
            TD::make('round', __('Round'))
                ->render(function (Interview $interview) {
                    return  \App\Support\Interview::rounds()[$interview->round]['label'];
                }),
            TD::make('mode', __('Mode'))
                ->render(function (Interview $interview) {
                    return  \App\Support\Interview::modes()[$interview->mode]['label'];
                }),
            TD::make(__('Actions'))
                ->alignRight()
                ->render(fn(Interview $i) => Link::make(__('Edit'))
                    ->route('platform.interviews.edit', $i->id)),
        ]);

        $tabs = Layout::tabs([
            __('Application Details') => $detailsItems,
            __('Interviews') => [$interviewTable],
        ]);
        // Sidebar: comments and application progress always visible
        $sidebar = [
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
            ])->title(__('Comments'))->vertical(),
            Layout::block([
                Layout::view('partials.application-status-progress', [
                    'created_at' => $this->application->created_at,
                    'statusLogs' => $this->application->statusLogs,
                ]),
            ])->title(__('Application Progress'))->vertical(),
            // Shared with list
            Layout::block([
                Layout::view('partials.application-shared-with-list', [
                    'shared' => $this->application->sharedWith,
                ]),
            ])->title(__('Shared With'))->vertical(),
        ];
        // Combine tabs and sidebar into two-column layout
        $layouts = [];
        // Show pending notification jobs for this application, if any
        if ($this->pendingNotifications->isNotEmpty()) {
            $layouts[] =
                Layout::table('pendingNotifications', [
                    TD::make('id', __('Job ID'))
                        ->render(fn($n) => $n->id)
                        ->width('50px'),
                    TD::make('notification', __('Notification'))
                        ->render(fn($n) => $n->notification),
                    TD::make('channels', __('Channels'))
                        ->render(fn($n) => is_array($n->channels) && count($n->channels)
                            ? implode(', ', $n->channels)
                            : '-'
                        ),
                    TD::make('scheduled_at', __('Scheduled At'))
                        ->render(fn($n) => $n->scheduled_at->toDateTimeString()),
                    TD::make('actions', __('Actions'))
                        ->align(TD::ALIGN_CENTER)
                        ->width('100px')
                        ->render(fn($n) => Button::make(__('Cancel'))
                            ->icon('bs.x-circle')
                            ->confirm(__('Are you sure you want to cancel this notification?'))
                            ->novalidate()
                            ->method('cancelJob', ['id' => $n->id])
                            ->canSee(auth()->user()->hasAccess('platform.pending-jobs'))
                        ),
                ])->title('Pending Notifications');

        }
        if ($this->application->rejection_sent) {
            $layouts[] = Layout::view('partials.application-rejected-warning');
        }
        $layouts[] = $tabs;
        $columns = Layout::split([
            $layouts,
            $sidebar,
        ])->ratio('70/30');

        $layouts = [
            $columns,
        ];
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
                    Select::make('interview_round')
                        ->title(__('Interview Round'))
                        ->empty(__('Select a round'), '')
                        ->options(collect(\App\Support\Interview::rounds())
                            ->mapWithKeys(fn($meta, $key) => [$key => $meta['label']])
                            ->toArray())
                        ->required(),
                    Select::make('interview_mode')
                        ->title(__('Interview Mode'))
                        ->empty(__('Select a mode'), '')
                        ->options(collect(\App\Support\Interview::modes())
                            ->mapWithKeys(fn($meta, $key) => [$key => $meta['label']])
                            ->toArray())
                        ->required(),
                    // Scheduled datetime
                    DateTimer::make('interview_scheduled_at')
                        ->title(__('Scheduled At'))
                        ->enableTime(),
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
        // Prepare shared and available users for sharing
        $sharedUsers = $this->application->sharedWith;
        $sharedIds = $sharedUsers->pluck('id')->toArray();
        $availableUsers = \App\Models\User::query()
            ->where('id', '<>', Auth::id())
            ->whereNotIn('id', $sharedIds)
            ->pluck('name', 'id')
            ->toArray();
        // Share Application modal
        $layouts[] = Layout::modal('shareModal', [
            // List of users already shared
            Layout::view('partials.share-modal-shared-list', [
                'shared'      => $sharedUsers,
                'application' => $this->application,
            ]),
            Layout::rows([
                // Hidden field to pass application ID
                Input::make('id')
                    ->type('hidden')
                    ->value($this->application->id),
                Select::make('share_user_ids')
                    ->options($availableUsers)
                    ->multiple()
                    ->title(__('Share With'))
                    ->help(__('Select users to share this application with.')),
            ]),
            // Share button
            Layout::view('partials.share-modal-buttons'),
        ])
            ->title(__('Share Application'))
            ->withoutApplyButton()
            ->withoutCloseButton()
            ->staticBackdrop()
            ->size(ModalLayout::SIZE_LG);

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
                    ->rows(10),
                DateTimer::make('send_at')
                    ->enableTime()
                    ->value(now()->addHour())
                    ->title(__('Send At'))
                    ->help(__('Optional: schedule when rejection email is sent. Defaults to one hour from now.')),
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
     * Reject application and optionally send rejection email.
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     */
    public function rejectWithEmail(Request $request, ApplicationService $applicationService): void
    {
        $application = JobApplication::with('candidate')->findOrFail($request->get('id'));
        $templateId = $request->get('template_id');
        $subjectOverride = $request->filled('subject') ? $request->get('subject') : null;
        $bodyOverride = $request->filled('body') ? $request->get('body') : null;
        $sendAt = $request->filled('send_at') ? Carbon::parse($request->get('send_at')) : null;
        $message = $applicationService->rejectWithEmail(
            $application,
            (int) $templateId,
            $subjectOverride,
            $bodyOverride,
            $sendAt
        );
        Toast::info($message);
    }

    /**
     * Schedule interview and optionally send invitation email.
     *
     * @param Request $request
     */
    public function scheduleInterviewWithEmail(Request $request): void
    {
        $request->validate([
            'id' => 'required|exists:job_applications,id',
            'interview_round' => ['required', Rule::in(array_keys(\App\Support\Interview::rounds()))],
            'interview_mode' => ['required', Rule::in(array_keys(\App\Support\Interview::modes()))],
            'interview_scheduled_at' => 'nullable|date',
            'template_id' => 'required|exists:notification_templates,id',
            'user_id' => 'required|exists:users,id',
            'calendar_id' => 'nullable|exists:appointment_calendars,id',
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
        ]);

        $application = JobApplication::with('candidate')->findOrFail($request->get('id'));
        // Create interview record (scheduled_at can be null until confirmed)
        Interview::create([
            'application_id' => $application->id,
            'interviewer_id' => $request->get('user_id'),
            'mode' => $request->get('interview_mode'),
            'round' => $request->get('interview_round'),
            'scheduled_at' => $request->get('interview_scheduled_at'),
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
