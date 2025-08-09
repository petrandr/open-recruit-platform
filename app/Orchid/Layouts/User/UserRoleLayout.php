<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Platform\Models\Role;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class UserRoleLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->query->get('user');
        $selectedRoles = $user->roles->pluck('id')->toArray();

        return [
            // Use a flat field name to avoid Collection/value binding issues in Orchid
            Select::make('user_roles')
                ->name('user_roles')
                ->options($this->getAllowedRolesOptions())
                ->multiple()
                ->value($selectedRoles)
                ->title(__('Name role'))
                ->help(__('Specify which groups this account should belong to')),
        ];
    }
    /**
     * Get allowed role options based on current user's role hierarchy.
     *
     * @return array<int,string>
     */
    protected function getAllowedRolesOptions(): array
    {
        $mapping = [
            'regular'    => 0,
            'admin'      => 1,
            'superadmin' => 2,
        ];
        $currentRank = auth()->user()->getHighestRoleTypeRank();

        return Role::all()
            ->filter(fn (Role $role) => $currentRank >= ($mapping[$role->role_type] ?? 0))
            ->pluck('name', 'id')
            ->toArray();
    }
}
