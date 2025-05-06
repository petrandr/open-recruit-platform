<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Interview;

use App\Models\Interview;
use App\Orchid\Layouts\Interview\InterviewFiltersLayout;
use App\Orchid\Layouts\Interview\InterviewListLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;

class MyInterviewsScreen extends Screen
{
    /**
     * Screen name
     */
    public function name(): ?string
    {
        return __('My Interviews');
    }

    /**
     * Screen description
     */
    public function description(): ?string
    {
        return __('List of interviews assigned to you.');
    }

    /**
     * Permissions
     */
    public function permission(): ?iterable
    {
        return ['platform.my_interviews'];
    }

    /**
     * Query data
     */
    public function query(Request $request): iterable
    {
        $query = Interview::with(['application.candidate', 'interviewer', 'application.jobListing'])
            ->filters(InterviewFiltersLayout::class)
            ->where('interviewer_id', Auth::id())
            ->orderByDesc('scheduled_at');

        $interviews = $query->paginate();

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
            InterviewFiltersLayout::class,
            InterviewListLayout::class,
        ];
    }
}
