<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Application;

use App\Models\JobApplication;
use App\Orchid\Fields\Ckeditor;
use App\Orchid\Layouts\Application\ApplicationFiltersLayout;
use App\Orchid\Layouts\Application\ApplicationListLayout;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;
use App\Models\NotificationTemplate;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Layouts\Modal as ModalLayout;
use Carbon\Carbon;
use App\Services\ApplicationService;
use App\Notifications\ApplicationRejectedNotification;

class ApplicationListScreen extends Screen
{
    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return 'Applications';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'List of all job applications submitted through the system.';
    }

    /**
     * Query data for the screen.
     *
     * @return array<string, mixed>
     */
    public function query(Request $request): iterable
    {
        // Base query with Orchid filters and sorting
        $query = JobApplication::with('jobListing', 'candidate')
            ->filters(ApplicationFiltersLayout::class)
            ->defaultSort('id', 'desc');
        // Restrict to applications for accessible jobs OR shared with the user
        $userId = auth()->id();
        $roleIds = auth()->user()->roles()->pluck('id')->toArray();
        $query->where(function ($q) use ($roleIds, $userId) {
            $q->whereHas('jobListing.roles', function ($q2) use ($roleIds) {
                $q2->whereIn('roles.id', $roleIds);
            })
            ->orWhereHas('sharedWith', function ($q3) use ($userId) {
                $q3->where('user_id', $userId);
            });
        });

        if ($name = $request->get('candidate')) {
            $query->whereHas('candidate', function ($q) use ($name) {
                $q->where('first_name', 'like', "%{$name}%")
                    ->orWhere('last_name', 'like', "%{$name}%");
            });
        }

        // Apply free-text filter for job title if provided
        if ($jobTitle = $request->input('filter.job_title')) {
            $query->whereHas('jobListing', function ($q) use ($jobTitle) {
                $q->where('title', 'ilike', "%{$jobTitle}%");
            });
        }
        // Apply column filter for fit category if provided
        if ($fitCategory = $request->input('filter.fit_ratio')) {
            match ($fitCategory) {
                'good'  => $query->where('fit_ratio', '>=', 0.8),
                'maybe' => $query->where('fit_ratio', '>=', 0.5)->where('fit_ratio', '<', 0.8),
                'not'   => $query->where('fit_ratio', '<', 0.5),
                default => null,
            };
        }

//        dd($query->toSql());

        $applications = $query->paginate();

        return [
            'applications' => $applications,
        ];
    }

    /**
     * Permission for viewing this screen.
     *
     * @return array<string>
     */
    public function permission(): ?iterable
    {
        return ['platform.applications'];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            ApplicationFiltersLayout::class,
            ApplicationListLayout::class,
            // Modal for CV preview
            Layout::view('partials.application-cv-modal'),
            // Rejection modal (async)
            Layout::modal('rejectModal', [
                Layout::rows([
                    Input::make('id')
                        ->type('hidden'),
                    Select::make('template_id')
                        ->id('reject-template-select')
                        ->title(__('Template'))
                        ->options(NotificationTemplate::where('type', 'rejection')->pluck('name', 'id'))
                        ->empty(__('Select a template'), '')
                        ->required(),
                    Input::make('subject')
                        ->id('reject-subject')
                        ->title(__('Subject'))
                        ->required(),
                    Ckeditor::make('body')
                        ->id('reject-body')
                        ->title(__('Message')),
                    DateTimer::make('send_at')
                        ->enableTime()
                        ->title(__('Send At'))
                        ->help(__('Optional: schedule when rejection email is sent. Defaults to one hour from now.')),
                ]),
                Layout::view('partials.reject-modal-buttons'),
                Layout::view('partials.reject-modal-template-editor'),
            ])
                ->async('asyncRejectModal')
                ->title(__('Reject Application'))
                ->withoutApplyButton()
                ->withoutCloseButton()
                ->staticBackdrop()
                ->size(ModalLayout::SIZE_LG),
        ];
    }

    /**
     * Remove an application.
     */
    /**
     * Anonymize an application (remove personal information).
     */
    public function anonymizeApplication(Request $request): void
    {
        $application = JobApplication::with('candidate')->findOrFail($request->get('id'));
        // Anonymize candidate personal info
        if ($application->candidate) {
            $application->candidate->update([
                'first_name' => 'Anonymous',
                'last_name' => 'Applicant',
                'email' => sprintf('anon+%d@example.com', $application->id),
                'mobile_number' => '0000000000',
            ]);
        }
        // Clear application-specific personal fields
        $application->update([
            'linkedin_profile' => '',
            'github_profile' => '',
            'how_heard' => '',
        ]);
        Toast::info('Application personal data was anonymized.');
    }

    /**
     * Load modal data for rejecting an application.
     *
     * @param JobApplication $application
     * @return array<string, mixed>
     */
    public function asyncRejectModal(JobApplication $application): array
    {
        $application->load(['jobListing', 'candidate']);
        $raw = NotificationTemplate::where('type', 'rejection')->get(['id', 'subject', 'body']);
        $templates = [];
        foreach ($raw as $tmpl) {
            $templates[] = [
                'id' => $tmpl->id,
                'subject' => $this->parseTemplate($tmpl->subject, $application),
                'body' => $this->parseTemplate($tmpl->body, $application),
            ];
        }
        return [
            'application' => $application,
            'templates' => $templates,
        ];
    }

    /**
     * Parse template placeholders into actual values.
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

    /**
     * Change application status (used for rejecting only).
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
}
