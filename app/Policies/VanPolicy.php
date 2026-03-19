<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Models\Van;

class VanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return User::getAuthUser()->role_id === UserRoleEnum::SUPERADMIN->value ||
            User::getAuthUser()->role_id === UserRoleEnum::COMPANY->value;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Van $van): bool
    {
        if ($user) {
            return $user->id === $van->company_id;
        }

        return auth('van')->user()->id === $van->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return User::getAuthUser()->role_id === UserRoleEnum::SUPERADMIN->value ||
            User::getAuthUser()->role_id === UserRoleEnum::COMPANY->value;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Van $van): bool
    {
        return $user->id === $van->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Van $van): bool
    {
        return $user->id === $van->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Van $van): bool
    {
        return $user->id === $van->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Van $van): bool
    {
        return $user->id === $van->company_id;
    }
}
