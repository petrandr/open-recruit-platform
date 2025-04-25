<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Application;

use App\Models\JobApplication;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal as ModalLayout;
use Orchid\Screen\Sight;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationRejectedMail;
use Orchid\Screen\Fields\Input;
use App\Models\ApplicationComment;

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
        return [
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
            // Allow resetting status to Submitted
            Button::make(__('Submitted'))
                ->icon('bs.calendar2-check')
                ->method('changeStatus', ['id' => $this->application->id, 'status' => 'submitted'])
                ->confirm(__('Are you sure you want to reset status to Submitted?'))
                ->novalidate(),
            Button::make(__('Under Review'))
                ->icon('bs.hourglass-split')
                ->method('changeStatus', ['id' => $this->application->id, 'status' => 'under review'])
                ->confirm(__('Are you sure you want to mark this application as Under Review?'))
                ->novalidate(),
            Button::make(__('Accept'))
                ->icon('bs.check2-circle')
                ->method('changeStatus', ['id' => $this->application->id, 'status' => 'accepted'])
                ->confirm(__('Are you sure you want to accept this application?'))
                ->novalidate(),
            ModalToggle::make(__('Reject'))
                ->icon('bs.x-circle')
                ->modal('rejectModal')
                ->novalidate(),
        ];
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
                $color = match ($status) {
                    'submitted' => 'info',
                    'under review' => 'warning',
                    'accepted' => 'success',
                    'rejected' => 'danger',
                    default => 'secondary',
                };
                return "<span class='badge bg-{$color} status-badge'>" . ucfirst($status) . "</span>";
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
            ],
        ])
            ->ratio('70/30');
        // CV preview modal
        $layouts[] = Layout::view('partials.application-cv-modal');
        // Rejection modal: textarea and footer buttons
        $layouts[] = Layout::modal('rejectModal', [
            Layout::rows([
                Input::make('id')
                    ->type('hidden')
                    ->value($this->application->id),
                TextArea::make('rejection_message')
                    ->title(__('Rejection Message'))
                    ->rows(8)
                    ->value(
                        "Dear {$this->application->candidate->first_name} {$this->application->candidate->last_name},\n\n" .
                        "Thanks for your interest in the {$this->application->jobListing->title} position at " . config('platform.organization') . ". " .
                        "Unfortunately, we will not be moving forward with your application but we appreciate your time and interest in " . config('platform.organization') . ".\n\n" .
                        "Regards,\n" . config('platform.organization')
                    )
                    ->class('form-control no-resize mw-100'),
            ]),
            Layout::view('partials.reject-modal-buttons', [
                'application' => $this->application,
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
        $message = trim($request->get('rejection_message', ''));
        // Update status
        $application->update(['status' => 'rejected']);
        // Send rejection email if candidate email available and message provided
        if ($message !== '' && $application->candidate && $application->candidate->email) {
            Mail::to($application->candidate->email)
                ->send(new ApplicationRejectedMail($application, $message));
            // Mark email sent
            $application->update(['rejection_sent' => true]);
            Toast::info(__('Application rejected and email notification sent.'));
        } else {
            Toast::info(__('Application rejected.'));
        }
    }
}
