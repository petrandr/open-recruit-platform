<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

//        $dashboard->registerResource('scripts', asset('js/app.js'));
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        $menus = [
            Menu::make('Dashboard')
                ->icon('bs.speedometer')
                ->route(config('platform.index')),

            Menu::make(__('Jobs'))
                ->icon('bs.briefcase')
                ->route('platform.jobs')
                ->permission('platform.jobs')
                ->set('group', 'Recruitment'),

            Menu::make(__('Candidates'))
                ->icon('bs.person-lines-fill')
                ->route('platform.candidates')
                ->permission('platform.candidates')
                ->set('group', 'Recruitment'),

            Menu::make(__('Applications'))
                ->icon('bs.file-earmark-text')
                ->route('platform.applications')
                ->permission('platform.applications')
                ->set('group', 'Recruitment'),
            // Interviews management
            Menu::make(__('Interviews'))
                ->icon('bs.calendar-event')
                ->route('platform.interviews')
                ->permission(['platform.interviews','platform.my_interviews'])
                ->set('group', 'Recruitment'),
            Menu::make(__('My Calendars'))
                ->icon('bs.calendar')
                ->route('platform.calendars')
                ->permission('platform.calendars')
                ->set('group', 'Recruitment'),
            // Industries management
            Menu::make(__('Industries'))
                ->icon('bs.building')
                ->route('platform.industries')
                ->permission('platform.industries')
                ->set('group', 'Recruitment'),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->set('group', 'Access Controls'),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->set('group', 'Access Controls')
                ->divider(),

            Menu::make(__('Activity Logs'))
                ->icon('bs.clock-history')
                ->route('platform.activity.logs')
                ->permission('platform.activity-logs')
                ->set('group', 'System'),

            // Platform > System > Notification Logs
            Menu::make(__('Notification Logs'))
                ->icon('bs.bell')
                ->route('platform.notification.logs')
                // Visible to users with activity logs permission
                ->permission('platform.activity-logs')
                ->set('group', 'System'),

            Menu::make(__('Notification Templates'))
                ->icon('bs.card-list')
                ->route('platform.notification.templates')
                ->permission('platform.notification.templates')
                ->set('group', 'System')
                ->divider(),

//
//            Menu::make('Get Started')
//                ->icon('bs.book')
//                ->title('Navigation')
//                ->route(config('platform.index')),
//
//            Menu::make('Sample Screen')
//                ->icon('bs.collection')
//                ->route('platform.example')
//                ->badge(fn () => 6),
//
//            Menu::make('Form Elements')
//                ->icon('bs.card-list')
//                ->route('platform.example.fields')
//                ->active('*/examples/form/*'),
//
//            Menu::make('Layouts Overview')
//                ->icon('bs.window-sidebar')
//                ->route('platform.example.layouts'),
//
//            Menu::make('Grid System')
//                ->icon('bs.columns-gap')
//                ->route('platform.example.grid'),
//
//            Menu::make('Charts')
//                ->icon('bs.bar-chart')
//                ->route('platform.example.charts'),
//
//            Menu::make('Cards')
//                ->icon('bs.card-text')
//                ->route('platform.example.cards')
//                ->divider(),
        ];

        // Display category titles on the first visible item of each group
        foreach (['Recruitment', 'Access Controls', 'System'] as $groupTitle) {
            foreach ($menus as $menu) {
                if ($menu->get('group') === $groupTitle && $menu->isSee()) {
                    $menu->title(__($groupTitle));
                    break;
                }
            }
        }

        return $menus;

    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users'))
                ->addPermission('platform.activity-logs', __('Activity Logs'))
                ->addPermission('platform.notification-logs', __('Notification Logs'))
                ->addPermission('platform.notification.templates', __('Notification Templates'))
                ->addPermission('platform.calendars', __('Calendars'))
                ->addPermission('platform.pending-jobs', __('View Pending Queue Jobs')),
            ItemPermission::group(__('Recruitment'))
                ->addPermission('platform.jobs', __('View Jobs'))
                ->addPermission('platform.jobs.create', __('Create Jobs'))
                ->addPermission('platform.jobs.edit', __('Edit Jobs'))
                ->addPermission('platform.jobs.delete', __('Delete Jobs'))
                ->addPermission('platform.candidates', __('Candidates'))
                ->addPermission('platform.applications', __('Applications'))
                ->addPermission('platform.interviews', __('Interviews (Wide Access)'))
                ->addPermission('platform.my_interviews', __('My Interviews'))
                ->addPermission('platform.industries', __('Industries')),
        ];
    }

}
