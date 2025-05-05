<?php

namespace App\Support;

class Interview
{
    public static function rounds(): array
    {
        return [
            'screening' => [
                'label' => __('Screening'),
                'color' => 'info',
                'icon'  => 'bs.calendar2-check',
            ],
            'skill_assessment' => [
                'label' => __('Skill Assessment'),
                'color' => 'warning',
                'icon'  => 'bs.hourglass-split',
            ],
            'technical' => [
                'label' => __('Technical'),
                'color' => 'primary',
                'icon'  => 'bs.list-check',
            ],
            'behavioral' => [
                'label' => __('Behavioral'),
                'color' => 'info',
                'icon'  => 'bs.calendar-event',
            ],
            'managerial' => [
                'label' => __('Managerial'),
                'color' => 'secondary',
                'icon'  => 'bs.person-check',
            ],
            'hr' => [
                'label' => __('HR'),
                'color' => 'success',
                'icon'  => 'bs.envelope-at',
            ],
        ];
    }

    public static function statuses(): array
    {
        return [
            'scheduled' => [
                'label' => __('Scheduled'),
                'color' => 'info',
                'icon'  => 'bs.calendar-event',
            ],
            'completed' => [
                'label' => __('Completed'),
                'color' => 'success',
                'icon'  => 'bs.calendar2-check',
            ],
            'cancelled' => [
                'label' => __('Cancelled'),
                'color' => 'danger',
                'icon'  => 'bs.x-circle',
            ],
            'no-show' => [
                'label' => __('No-Show'),
                'color' => 'warning',
                'icon'  => 'bs.person-x',
            ],
        ];
    }

    public static function modes(): array
    {
        return [
            'online' => [
                'label' => __('Online'),
                'color' => 'success',
                'icon'  => 'bs.globe2',
            ],
            'in-person' => [
                'label' => __('In-Person'),
                'color' => 'primary',
                'icon'  => 'bs.house-door',
            ],
            'phone' => [
                'label' => __('Phone'),
                'color' => 'info',
                'icon'  => 'bs.phone',
            ],
        ];
    }

}
