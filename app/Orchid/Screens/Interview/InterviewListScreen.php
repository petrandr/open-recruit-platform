<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Interview;

use App\Models\Interview;
use App\Orchid\Layouts\Interview\InterviewListLayout;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class InterviewListScreen extends Screen
{
    /**
     * Screen name
     */
    public function name(): ?string
    {
        return __('Interviews');
    }

    /**
     * Screen description
     */
    public function description(): ?string
    {
        return __('List of scheduled interviews');
    }

    /**
     * Permissions
     */
    public function permission(): ?iterable
    {
        return ['platform.interviews'];
    }

    /**
     * Query data
     */
    public function query(): iterable
    {
        $interviews = Interview::with(['application.candidate', 'interviewer', 'application.jobListing'])
            ->orderByDesc('scheduled_at')
            ->paginate();
        return [
            'interviews' => $interviews,
        ];
    }

    /**
     * Action buttons
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Screen layout
     */
    public function layout(): iterable
    {
        return [
            InterviewListLayout::class
        ];
    }
}
