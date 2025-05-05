<?php

declare(strict_types=1);

use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\MailLog\MailLogListScreen;
use App\Orchid\Screens\MailLog\MailLogViewScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use App\Orchid\Screens\Application\ApplicationListScreen;
use App\Orchid\Screens\Application\ApplicationViewScreen;
use App\Models\JobApplication;
use App\Orchid\Screens\JobListing\JobListingListScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;
use App\Orchid\Screens\JobListing\JobListingEditScreen;
use App\Orchid\Screens\JobListing\JobListingViewScreen;
use App\Models\JobListing;
use App\Orchid\Screens\Candidate\CandidateListScreen;
use App\Orchid\Screens\Candidate\CandidateViewScreen;
use App\Orchid\Screens\ActivityLog\ActivityLogListScreen;
use App\Orchid\Screens\ActivityLog\ActivityLogViewScreen;
use App\Orchid\Screens\NotificationLog\NotificationLogListScreen;
use App\Orchid\Screens\NotificationLog\NotificationLogViewScreen;
use App\Orchid\Screens\AppointmentCalendar\AppointmentCalendarListScreen;
use App\Orchid\Screens\AppointmentCalendar\AppointmentCalendarEditScreen;
use App\Orchid\Screens\NotificationTemplate\NotificationTemplateListScreen as NotificationTemplateListScreen;
use App\Orchid\Screens\NotificationTemplate\NotificationTemplateEditScreen as NotificationTemplateEditScreen;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn(Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn(Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));
// Platform > Recruitment > Jobs > Create
Route::screen('jobs/create', JobListingEditScreen::class)
    ->name('platform.jobs.create')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.jobs')
        ->push(__('Create'), route('platform.jobs.create')));
// Platform > Recruitment > Jobs > Edit
Route::screen('jobs/{job}/edit', JobListingEditScreen::class)
    ->name('platform.jobs.edit')
    ->breadcrumbs(fn(Trail $trail, JobListing $job) => $trail
        ->parent('platform.jobs')
        ->push($job->title, route('platform.jobs.edit', $job)));
// Platform > Recruitment > Jobs > View
Route::screen('jobs/{job}', JobListingViewScreen::class)
    ->name('platform.jobs.view')
    ->breadcrumbs(fn(Trail $trail, JobListing $job) => $trail
        ->parent('platform.jobs')
        ->push($job->title, route('platform.jobs.view', $job)));
// Platform > Recruitment > Jobs
Route::screen('jobs', JobListingListScreen::class)
    ->name('platform.jobs')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Jobs'), route('platform.jobs')));
// Platform > Recruitment > Candidates
Route::screen('candidates', CandidateListScreen::class)
    ->name('platform.candidates')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Candidates'), route('platform.candidates')));
// Platform > Recruitment > Candidate Details
Route::screen('candidates/{candidate}', CandidateViewScreen::class)
    ->name('platform.candidates.view')
    ->breadcrumbs(fn(Trail $trail, \App\Models\Candidate $candidate) => $trail
        ->parent('platform.candidates')
        ->push($candidate->full_name, route('platform.candidates.view', $candidate)));
// Platform > Recruitment > Applications
Route::screen('applications', ApplicationListScreen::class)
    ->name('platform.applications')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Applications'), route('platform.applications')));
// Platform > Recruitment > Application CV Preview
Route::get('applications/{application}/cv', [\App\Http\Controllers\ApplicationDetailController::class, 'cv'])
    ->name('platform.applications.cv');
// Platform > Recruitment > Screening Questions AJAX search
Route::get('screening-questions/search', [\App\Http\Controllers\ScreeningQuestionController::class, 'search'])
    ->name('platform.screening-questions.search');
// AJAX: fetch appointment calendars by user
Route::get('applications/calendars', function (Illuminate\Http\Request $request) {
    $userId = $request->get('user_id');
    $items = App\Models\AppointmentCalendar::where('user_id', $userId)
        ->get(['id', 'name', 'url']);
    return response()->json($items);
})->name('platform.applications.calendars');

// Platform > Recruitment > Application Full View
Route::screen('applications/{application}', ApplicationViewScreen::class)
    ->name('platform.applications.view')
    ->breadcrumbs(fn(Trail $trail, JobApplication $application) => $trail
        ->parent('platform.applications')
        ->push("#{$application->id} - {$application->candidate->full_name} - {$application->jobListing->title}", route('platform.applications.view', $application)));
// Platform > Recruitment > Interviews
Route::screen('interviews', App\Orchid\Screens\Interview\InterviewListScreen::class)
    ->name('platform.interviews')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Interviews'), route('platform.interviews')));
// Platform > Recruitment > Interviews > Edit
Route::screen('interviews/{interview}/edit', App\Orchid\Screens\Interview\InterviewEditScreen::class)
    ->name('platform.interviews.edit')
    ->breadcrumbs(fn (Trail $trail, App\Models\Interview $interview) => $trail
        ->parent('platform.interviews')
        ->push("#{$interview->id}", route('platform.interviews.edit', $interview)));

// Platform > Recruitment > Interviews > View
Route::screen('interviews/{interview}', App\Orchid\Screens\Interview\InterviewViewScreen::class)
    ->name('platform.interviews.view')
    ->breadcrumbs(fn (Trail $trail, App\Models\Interview $interview) => $trail
        ->parent('platform.interviews')
        ->push("#{$interview->id}", route('platform.interviews.view', $interview)));
// Platform > System > Activity Logs
Route::screen('activity-logs', ActivityLogListScreen::class)
    ->name('platform.activity.logs')
    ->breadcrumbs(fn(Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Activity Logs'), route('platform.activity.logs')));
// Platform > System > Activity Log Detail
Route::screen('activity-logs/{id}', ActivityLogViewScreen::class)
    ->name('platform.activity.log')
    ->breadcrumbs(fn(Trail $trail, $id) => $trail
        ->parent('platform.activity.logs')
        ->push("#{$id}", route('platform.activity.log', $id)));

// Platform > System > Notification Logs
Route::screen('notification-logs', NotificationLogListScreen::class)
    ->name('platform.notification.logs')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Notification Logs'), route('platform.notification.logs')));

// Platform > System > Notification Log Detail
Route::screen('notification-logs/{id}', NotificationLogViewScreen::class)
    ->name('platform.notification.log')
    ->breadcrumbs(fn (Trail $trail, $id) => $trail
        ->parent('platform.notification.logs')
        ->push("#{$id}", route('platform.notification.log', $id)));

//// Example...
//Route::screen('example', ExampleScreen::class)
//    ->name('platform.example')
//    ->breadcrumbs(fn (Trail $trail) => $trail
//        ->parent('platform.index')
//        ->push('Example Screen'));
//
//Route::screen('/examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
//Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
//Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
//Route::screen('/examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');
//
//Route::screen('/examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
//Route::screen('/examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
//Route::screen('/examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
//Route::screen('/examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');

// Route::screen('idea', Idea::class, 'platform.screens.idea');
// Platform > System > My Calendars
Route::screen('calendars', AppointmentCalendarListScreen::class)
    ->name('platform.calendars')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('My Calendars'), route('platform.calendars')));

// Platform > System > My Calendars > Create
Route::screen('calendars/create', AppointmentCalendarEditScreen::class)
    ->name('platform.calendars.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.calendars')
        ->push(__('Add'), route('platform.calendars.create')));

// Platform > System > My Calendars > Edit
Route::screen('calendars/{calendar}/edit', AppointmentCalendarEditScreen::class)
    ->name('platform.calendars.edit')
    ->breadcrumbs(fn (Trail $trail, $calendar) => $trail
        ->parent('platform.calendars')
        ->push($calendar->name, route('platform.calendars.edit', $calendar)));
// Platform > System > Notification Templates
Route::screen('notification-templates', NotificationTemplateListScreen::class)
    ->name('platform.notification.templates')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Notification Templates'), route('platform.notification.templates')));
// Platform > System > Notification Templates > Create
Route::screen('notification-templates/create', NotificationTemplateEditScreen::class)
    ->name('platform.notification.templates.create');
// Platform > System > Notification Templates > Edit
Route::screen('notification-templates/{template}/edit', NotificationTemplateEditScreen::class)
    ->name('platform.notification.templates.edit')
    ->breadcrumbs(fn (Trail $trail, $template) => $trail
        ->parent('platform.notification.templates')
        ->push(optional($template)->name ?: __('Edit'), route('platform.notification.templates.edit', $template)));
