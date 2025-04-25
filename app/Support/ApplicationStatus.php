<?php

namespace App\Support;

class ApplicationStatus
{
    public static function all()
    {
        return [
            // status_key => [label, color, icon]
            'submitted' => [
                'label' => __('Submitted'),
                'color' => 'info',
                'icon' => 'bs.calendar2-check',
            ],
            'under_review' => [
                'label' => __('Under Review'),
                'color' => 'warning',
                'icon' => 'bs.hourglass-split',
            ],
            'shortlisted' => [
                'label' => __('Shortlisted'),
                'color' => 'primary',
                'icon' => 'bs.list-check',
            ],
            'interview_scheduled' => [
                'label' => __('Interview Scheduled'),
                'color' => 'info',
                'icon' => 'bs.calendar-event',
            ],
            'interviewed' => [
                'label' => __('Interviewed'),
                'color' => 'secondary',
                'icon' => 'bs.person-check',
            ],
            'offer_sent' => [
                'label' => __('Offer Sent'),
                'color' => 'success',
                'icon' => 'bs.envelope-at',
            ],
            'hired' => [
                'label' => __('Hired'),
                'color' => 'success',
                'icon' => 'bs.person-badge-fill',
            ],
            'rejected' => [
                'label' => __('Rejected'),
                'color' => 'danger',
                'icon' => 'bs.x-circle',
            ],
            'withdrawn' => [
                'label' => __('Withdrawn'),
                'color' => 'dark',
                'icon' => 'bs.person-dash',
            ],
        ];
    }
}
