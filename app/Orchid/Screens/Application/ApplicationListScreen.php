<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Application;

use App\Models\JobApplication;
use App\Orchid\Layouts\Application\ApplicationFiltersLayout;
use App\Orchid\Layouts\Application\ApplicationListLayout;
use App\Orchid\Layouts\Application\ApplicationOffcanvasLayout;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

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
            // Offcanvas summary pane
            ApplicationOffcanvasLayout::class,
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
            // Replace personal info with placeholders
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
}
