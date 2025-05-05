<?php
declare(strict_types=1);

namespace App\Orchid\Layouts\Interview;

use App\Models\Interview;
use App\Models\JobApplication;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class InterviewListLayout extends Table
{
    public $target = 'interviews';

    /**
     * Get form fields for editing an interview.
     *
     * @return array<\Orchid\Screen\Field>
     */
    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))->render(function(Interview $interview) {
                return Link::make((string) $interview->id)
                    ->route('platform.interviews.view', $interview);
            }),
            TD::make('application', __('Application'))->render(function (Interview $interview) {
                $app = $interview->application;
                $label = '#' . $app->id . ' - ' . ($app->candidate->first_name ?? '') . ' ' . ($app->candidate->last_name ?? '');
                return Link::make($label)
                    ->route('platform.applications.view', $app->id);
            }),
            TD::make('position', __('Position'))->render(fn(Interview $i) => $i->application->jobListing?->title ?? '-'),
            TD::make('interviewer', __('Interviewer'))->render(fn(Interview $i) => $i->interviewer?->name ?? '-'),
            TD::make('scheduled_at', __('Scheduled At'))->sort(),
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
            TD::make('location', __('Location')),
            TD::make('duration_minutes', __('Duration (min)')),
            TD::make(__('Actions'))
                ->alignRight()
                ->render(function (Interview $interview) {
                    // Base dropdown
                    $dropdown = DropDown::make()->icon('bs.three-dots-vertical');
                    return $dropdown->list([
                        Link::make(__('Edit'))
                            ->icon('bs.pencil')
                            ->route('platform.interviews.edit', $interview->id),
                    ]);
                }),];
    }
}
