<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Application;

use App\Models\JobApplication;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Sight;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

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
        ]);
        return [
            'application' => $application,
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
            Link::make(__('Back to Applications'))
                ->icon('bs.arrow-left')
                ->route('platform.applications'),
            // Allow resetting status to Submitted
            Button::make(__('Submitted'))
                ->icon('bs.calendar2-check')
                ->method('changeStatus', ['id' => $this->application->id, 'status' => 'submitted']),
            Button::make(__('Under Review'))
                ->icon('bs.hourglass-split')
                ->method('changeStatus', ['id' => $this->application->id, 'status' => 'under review']),
            Button::make(__('Accept'))
                ->icon('bs.check2-circle')
                ->method('changeStatus', ['id' => $this->application->id, 'status' => 'accepted']),
            Button::make(__('Reject'))
                ->icon('bs.x-circle')
                ->method('changeStatus', ['id' => $this->application->id, 'status' => 'rejected']),
        ];
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
                    'submitted'    => 'info',
                    'under review' => 'warning',
                    'accepted'     => 'success',
                    'rejected'     => 'danger',
                    default        => 'secondary',
                };
                return "<span class='badge bg-{$color} status-badge'>" . ucfirst($status) . "</span>";
            }),
            Sight::make('notice_period', __('Notice Period')),
            Sight::make('desired_salary', __('Desired Salary'))->render(fn () =>
                $this->application->desired_salary
                    ? number_format((float)$this->application->desired_salary, 2, ',', ' ') . ' ' . $this->application->salary_currency
                    : '-'
            ),
            Sight::make('linkedin_profile', __('LinkedIn Profile'))->render(fn () =>
                $this->application->linkedin_profile
                    ? Link::make($this->application->linkedin_profile)->href($this->application->linkedin_profile)
                    : '-'
            ),
            Sight::make('github_profile', __('GitHub Profile'))->render(fn () =>
                $this->application->github_profile
                    ? Link::make($this->application->github_profile)->href($this->application->github_profile)
                    : '-'
            ),
            Sight::make('how_heard', __('How Did They Hear About Us?')),
            Sight::make('submitted_at', __('Submitted At'))->render(fn () =>
                $this->application->submitted_at->format('Y-m-d H:i:s')
            ),
        ];

        // Screening questions
        $questions = [];
        foreach ($this->application->jobListing->screeningQuestions as $question) {
            $questions[] = Sight::make('question_' . $question->id, $question->question_text ?? $question->question)
                ->render(fn () =>
                    optional(
                        $this->application->answers->firstWhere('question_id', $question->id)
                    )->answer_text ?? '-'
                );
        }

        // Assemble layouts: include sticky header CSS first
        // Assemble layouts
        $layouts = [
            Layout::legend('application', $basic)
                ->title(__('Application Details')),
        ];

        if (!empty($questions)) {
            $layouts[] = Layout::legend('application', $questions)
                ->title(__('Screening Questions'));
        }

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
}