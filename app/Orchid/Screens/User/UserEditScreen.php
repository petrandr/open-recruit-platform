<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Orchid\Layouts\Role\RolePermissionLayout;
use App\Orchid\Layouts\User\UserEditLayout;
use App\Orchid\Layouts\User\UserPasswordLayout;
use App\Orchid\Layouts\User\UserRoleLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Orchid\Access\Impersonation;
use App\Models\User;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class UserEditScreen extends Screen
{
    /**
     * @var User
     */
    public $user;

    /**
     * Fetch data to be displayed on the screen.
     *
     */
    public function query(User $user)
    {
        $user->load(['roles']);

        return [
            'user' => $user,
            'permission' => $user->getStatusPermission(),
        ];
    }

    public function checkAccess(Request $request): bool
    {
        if (!parent::checkAccess($request)) {
            return false;
        }

        $userParam = $request->route('user');
        $user = $userParam instanceof User ? $userParam : User::find($userParam);

        // Restrict access based on role hierarchy
        if ($user && $user->exists && !auth()->user()->canModifyUser($user)) {
            Toast::warning(__('You do not have permission to edit that user.'));
            throw new HttpResponseException(
                redirect()->route('platform.systems.users')
            );
        }


        return true;
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->user->exists ? 'Edit User' : 'Create User';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'User profile and privileges, including their associated role.';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.systems.users',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('Impersonate user'))
                ->icon('bg.box-arrow-in-right')
                ->confirm(__('You can revert to your original state by logging out.'))
                ->method('loginAs')
                ->canSee(
                    $this->user->exists
                    && $this->user->id !== request()->user()->id
                    && auth()->user()->canModifyUser($this->user)
                ),

            Button::make(__('Remove'))
                ->icon('bs.trash3')
                ->confirm(__('Once the account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.'))
                ->method('remove')
                ->canSee(
                    $this->user->exists
                    && auth()->user()->canModifyUser($this->user)
                ),

            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [

            Layout::block(UserEditLayout::class)
                ->title(__('Profile Information'))
                ->description(__('Update your account\'s profile information and email address.'))
                ->commands(
                    Button::make(__('Save'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

            Layout::block(UserPasswordLayout::class)
                ->title(__('Password'))
                ->description(__('Ensure your account is using a long, random password to stay secure.'))
                ->commands(
                    Button::make(__('Save'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

            Layout::block(UserRoleLayout::class)
                ->title(__('Roles'))
                ->description(__('A Role defines a set of tasks a user assigned the role is allowed to perform.'))
                ->commands(
                    Button::make(__('Save'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

        ];
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(User $user, Request $request)
    {
        // Restrict saving based on role hierarchy
        if ($user->exists && !auth()->user()->canModifyUser($user)) {
            Toast::warning(__('You do not have permission to edit that user.'));
            throw new HttpResponseException(
                redirect()->route('platform.systems.users')
            );
        }
        $request->validate([
            'user.email' => [
                'required',
                Rule::unique(User::class, 'email')->ignore($user),
            ],
        ]);

        $permissions = collect($request->get('permissions'))
            ->map(fn($value, $key) => [base64_decode($key) => $value])
            ->collapse()
            ->toArray();

        // Handle password assignment: if SAML SSO is enabled, auto-generate on create; otherwise use provided password
        $samlEnabled = config('platform.saml_auth', false);
        if ($samlEnabled && !$user->exists) {
            // Generate a random password for SAML users
            $user->password = Hash::make(Str::random(40));
        } elseif ($request->filled('user.password')) {
            $user->password = Hash::make($request->input('user.password'));
        }

        $user
            ->fill($request->collect('user')->except(['password', 'permissions', 'roles'])->toArray())
            ->forceFill(['permissions' => $permissions])
            ->save();

        // Sync roles from the flat user_roles[] input field
        $user->replaceRoles($request->input('user_roles', []));

        Toast::info(__('User was saved.'));

        return redirect()->route('platform.systems.users');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     *
     */
    public function remove(User $user)
    {
        // Restrict deletion based on role hierarchy
        if (!auth()->user()->canModifyUser($user)) {
            Toast::warning(__('You do not have permission to delete that user.'));
            return redirect()->route('platform.systems.users');
        }
        $user->delete();

        Toast::info(__('User was removed'));

        return redirect()->route('platform.systems.users');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginAs(User $user)
    {
        // Restrict impersonation based on role hierarchy
        if (!auth()->user()->canModifyUser($user)) {
            Toast::warning(__('You do not have permission to impersonate that user.'));
            return redirect()->route('platform.systems.users');
        }
        Impersonation::loginAs($user);

        Toast::info(__('You are now impersonating this user'));

        return redirect()->route(config('platform.index'));
    }
}
