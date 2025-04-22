<?php
declare(strict_types=1);

namespace App\Orchid\Screens\Candidate;

use App\Models\Candidate;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;

/**
 * Candidate detail dashboard screen.
 */
class CandidateViewScreen extends Screen
{
    /**
     * Candidate instance.
     *
     * @var Candidate
     */
    public $candidate;

    /**
     * Query data for the screen.
     *
     * @param Candidate $candidate
     * @return iterable<string, mixed>
     */
    public function query(Candidate $candidate): iterable
    {
        // Load related applications with job and tracking info
        $candidate->load(['applications.jobListing', 'applications.tracking']);
        return [
            'candidate'    => $candidate,
            'applications' => $candidate->applications,
        ];
    }

    /**
     * Screen name shown in header.
     */
    public function name(): ?string
    {
        return __('Candidate Details');
    }

    /**
     * Screen description shown under header.
     */
    public function description(): ?string
    {
        return __('Overview of candidate profile, applications, and tracking data.');
    }

    /**
     * Permissions required to access this screen.
     */
    public function permission(): ?iterable
    {
        return ['platform.candidates'];
    }

    /**
     * Action buttons.
     *
     * @return array<int, \Orchid\Screen\Action>
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Back to Candidates'))
                ->icon('bs.arrow-left')
                ->route('platform.candidates'),
        ];
    }

    /**
     * Screen layout.
     *
     * @return array<int, \Orchid\Screen\Layout>
     */
    public function layout(): iterable
    {
        return [
            Layout::tabs([
                __('Profile') => Layout::legend('candidate', [
                    Sight::make('first_name', __('First Name')),
                    Sight::make('last_name', __('Last Name')),
                    Sight::make('email', __('Email')),
                    Sight::make('mobile_number', __('Mobile Number')),
                    Sight::make('created_at', __('Registered At'))->render(fn () =>
                        $this->candidate->created_at->format('Y-m-d H:i:s')
                    ),
                    Sight::make('updated_at', __('Last Updated'))->render(fn () =>
                        $this->candidate->updated_at->format('Y-m-d H:i:s')
                    ),
                ]),

                __('Applications') => Layout::table('applications', [
                    TD::make('id', __('Application #'))
                        ->render(fn ($app) => Link::make('#' . $app->id)
                            ->route('platform.applications.view', $app)
                        ),
                    TD::make('jobListing.title', __('Job Title'))
                        ->render(fn ($app) => $app->jobListing->title),
                    TD::make('status', __('Status'))
                        ->render(fn ($app) =>
                            "<span class='badge bg-" . match ($app->status) {
                                'submitted'    => 'info',
                                'under review' => 'warning',
                                'accepted'     => 'success',
                                'rejected'     => 'danger',
                                default        => 'secondary',
                            } . "'>" . ucfirst($app->status) . "</span>"
                        )
                        ->align(TD::ALIGN_CENTER),
                    TD::make('submitted_at', __('Submitted At'))
                        ->render(fn ($app) =>
                            $app->submitted_at?->format('Y-m-d H:i:s') ?? '-'
                        ),
                ]),

                __('Tracking') => Layout::table('applications', [
                    TD::make('id', __('Application #'))
                        ->render(fn ($app) => Link::make('#' . $app->id)
                            ->route('platform.applications.view', $app)
                        ),
                    TD::make('utm_source', __('UTM Source'))
                        ->render(fn ($app) => optional($app->tracking)->utm_source ?? '-'),
                    TD::make('utm_medium', __('UTM Medium'))
                        ->render(fn ($app) => optional($app->tracking)->utm_medium ?? '-'),
                    TD::make('utm_campaign', __('UTM Campaign'))
                        ->render(fn ($app) => optional($app->tracking)->utm_campaign ?? '-'),
                    TD::make('gclid', __('GCLID'))
                        ->render(fn ($app) => optional($app->tracking)->gclid ?? '-'),
                    TD::make('fbclid', __('FBCLID'))
                        ->render(fn ($app) => optional($app->tracking)->fbclid ?? '-'),
                ]),
            ]),
        ];
    }
}