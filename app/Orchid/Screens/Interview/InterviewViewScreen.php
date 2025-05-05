<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Interview;

use App\Models\Interview;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;

class InterviewViewScreen extends Screen
{
    /**
     * The interview instance.
     *
     * @var Interview
     */
    public $interview;

    /**
     * Query data for the screen.
     *
     * @param Interview $interview
     * @return iterable
     */
    public function query(Interview $interview): iterable
    {
        $interview->load(['application.candidate', 'application.jobListing', 'interviewer']);

        return [
            'interview' => $interview,
        ];
    }

    /**
     * Screen name shown in header.
     */
    public function name(): ?string
    {
        return __('Interview Details');
    }

    /**
     * Screen description shown under header.
     */
    public function description(): ?string
    {
        return __('View interview details');
    }

    /**
     * Permissions required to access this screen.
     */
    public function permission(): ?iterable
    {
        return ['platform.interviews'];
    }

    /**
     * Action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Back to Interviews'))
                ->icon('bs.arrow-left')
                ->route('platform.interviews'),

            Link::make(__('Edit Interview'))
                ->icon('bs.pencil')
                ->route('platform.interviews.edit', $this->interview->id),
        ];
    }

    /**
     * Screen layout.
     */
    public function layout(): iterable
    {
        return [
            Layout::legend('interview', [
                Sight::make('id', __('Interview #'))->render(fn() => '#'.$this->interview->id),
                Sight::make('application', __('Application'))->render(function () {
                    $app = $this->interview->application;
                    $label = '#'.$app->id.' - '.$app->candidate->first_name.' '.$app->candidate->last_name;

                    return Link::make($label)
                        ->route('platform.applications.view', $app->id);
                }),
                Sight::make('position', __('Position'))->render(fn() => $this->interview->application->jobListing?->title ?? '-'),
                Sight::make('interviewer', __('Interviewer'))->render(fn() => $this->interview->interviewer?->name ?? '-'),
                Sight::make('scheduled_at', __('Scheduled At'))->render(fn() => $this->interview->scheduled_at?->format('Y-m-d H:i:s') ?? '-'),
                Sight::make('status', __('Status'))->render(function () {
                    $item = \App\Support\Interview::statuses()[$this->interview->status];
                    return "<span class=\"badge bg-{$item['color']} status-badge\">{$item['label']}</span>";
                }),
                Sight::make('round', __('Round'))->render(function () {
                    $item = \App\Support\Interview::rounds()[$this->interview->round];
                    return "<span class=\"badge bg-{$item['color']} status-badge\">{$item['label']}</span>";
                }),
                Sight::make('mode', __('Mode'))->render(function () {
                    $item = \App\Support\Interview::modes()[$this->interview->mode];
                    return "<span class=\"badge bg-{$item['color']} status-badge\">{$item['label']}</span>";
                }),
                Sight::make('location', __('Location'))->render(fn() => $this->interview->location ?? '-'),
                Sight::make('duration_minutes', __('Duration (min)'))->render(fn() => $this->interview->duration_minutes !== null ? $this->interview->duration_minutes : '-'),
                Sight::make('comments', __('Comments'))->render(fn() => $this->interview->comments ? nl2br(($this->interview->comments)) : '-'),
            ])->title(__('Interview Details')),
        ];
    }
}
