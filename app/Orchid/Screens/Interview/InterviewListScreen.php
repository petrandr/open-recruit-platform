<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Interview;

use App\Models\Interview;
use App\Orchid\Layouts\Interview\InterviewFiltersLayout;
use App\Orchid\Layouts\Interview\InterviewListLayout;
use Illuminate\Http\Request;
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
        return ['platform.interviews','platform.my_interviews'];
    }

    /**
     * Indicates is the auth user has wide interview access
     */
    public function hasWideAccess(): bool
    {
        return auth()->user()->hasAccess('platform.interviews');
    }

    /**
     * Query data
     */
    public function query(Request $request): iterable
    {
        $query = Interview::with(['application.candidate', 'interviewer', 'application.jobListing'])
            ->filters(InterviewFiltersLayout::class)
            ->defaultSort('id', 'desc');

        // If user have no wide access, restrict wide view
        if (!$this->hasWideAccess()) {
            $query->where('interviewer_id', auth()->id());
        }

        // Restrict to interviews for accessible jobs
        $roleIds = auth()->user()->roles()->pluck('id')->toArray();
        $query->where(function ($q) use ($roleIds) {
              $q->WhereHas('application.jobListing.roles', function ($q2) use ($roleIds) {
                  $q2->whereIn('roles.id', $roleIds);
              });
        });

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
