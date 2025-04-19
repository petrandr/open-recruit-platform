<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Application;

use App\Models\JobApplication;
use App\Orchid\Layouts\Application\ApplicationFiltersLayout;
use App\Orchid\Layouts\Application\ApplicationListLayout;
use App\Orchid\Layouts\Application\ApplicationOffcanvasLayout;
use Illuminate\Http\Request;
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
        // Apply filters via Orchid Filterable
        $applications = JobApplication::with('jobListing', 'candidate')
            ->filters(ApplicationFiltersLayout::class)
            ->defaultSort('id', 'desc')
            ->paginate();
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
            // Offcanvas layout showing application details
            \App\Orchid\Layouts\Application\ApplicationOffcanvasLayout::class,
        ];
    }

    /**
     * Remove an application.
     */
    public function removeApplication(Request $request): void
    {
        $application = JobApplication::findOrFail($request->get('id'));
        $application->delete();
        Toast::info('Application was removed.');
    }
}
