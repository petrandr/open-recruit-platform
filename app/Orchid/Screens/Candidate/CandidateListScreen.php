<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Candidate;

use App\Models\Candidate;
use App\Orchid\Layouts\Candidate\CandidateListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class CandidateListScreen extends Screen
{
    /**
     * Screen name displayed in header.
     */
    public function name(): ?string
    {
        return 'Candidates';
    }

    /**
     * Screen description displayed under the header.
     */
    public function description(): ?string
    {
        return 'List of all candidates registered in the system.';
    }

    /**
     * Query data for the screen.
     *
     * @return array<string, mixed>
     */
    public function query(): iterable
    {
        return [
            'candidates' => Candidate::defaultSort('id', 'desc')->paginate(),
        ];
    }

    /**
     * Permission required to view this screen.
     *
     * @return array<string>
     */
    public function permission(): ?iterable
    {
        return ['platform.candidates'];
    }

    /**
     * Action buttons for the screen.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Layout elements for the screen.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            CandidateListLayout::class,
        ];
    }

    /**
     * Remove a candidate.
     */
    public function removeCandidate(Request $request): void
    {
        Candidate::findOrFail($request->get('id'))->delete();
        Toast::info('Candidate was removed.');
    }
}