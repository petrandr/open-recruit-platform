<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Application;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use App\Models\JobApplication;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;

class ApplicationListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    public $target = 'applications';

    /**
     * Table columns.
     *
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))
                ->sort()
                ->render(fn(JobApplication $application) => $application->id),

            TD::make('candidate', __('Candidate'))
                ->filter(Input::make())
                ->render(function (JobApplication $application) {
                    $name = $application->candidate->first_name . ' ' . $application->candidate->last_name;
                    // Trigger offcanvas summary when clicking on the candidate name
                    return sprintf(
                        '<span class="application-offcanvas-trigger" data-id="%d" style="cursor:pointer;">%s</span>',
                        $application->id,
                        e($name)
                    );
                }),

            TD::make('job', __('Job'))
                ->filter(Input::make())
                ->render(fn(JobApplication $application) => $application->jobListing?->title ?? '-'),

            // Desired Salary with currency
            TD::make('desired_salary', __('Desired Salary'))
                ->sort()
                ->render(function (JobApplication $application) {
                    if ($application->desired_salary === null) {
                        return '-';
                    }
                    $amount = (float)$application->desired_salary;
                    $formatted = number_format($amount, 2, ',', ' ');
                    $currency = $application->salary_currency ?? '';
                    return $currency
                        ? sprintf('%s %s', $formatted, $currency)
                        : $formatted;
                }),

            TD::make('status', __('Status'))
                ->filter(Input::make())
                ->sort()
                ->render(function (JobApplication $application) {
                    $status = $application->status;
                    $label = ucfirst($status);
                    // Map statuses to Bootstrap badge colors
                    $color = match ($status) {
                        'under review' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    };
                    return "<span class=\"badge bg-{$color} status-badge\">{$label}</span>";
                }),

            TD::make('location', __('Location'))
                ->sort()
                ->render(fn(JobApplication $application) => ucfirst($application->location)),

            TD::make('submitted_at', __('Submitted'))
                ->usingComponent(DateTimeSplit::class)
                ->align(TD::ALIGN_RIGHT)
                ->sort(),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn(JobApplication $application) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('View'))
                            ->icon('bs.eye')
                            ->route('platform.applications.view', $application->id),
                        Button::make(__('Anonymize'))
                            ->icon('bs.eye-slash')
                            ->confirm(__('Are you sure you want to anonymize this application? This will remove personal information.'))
                            ->method('anonymizeApplication', ['id' => $application->id]),
                    ])
                ),
        ];
    }
}
