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
        return User::getAuthUser()->role_id === UserRoleEnum::SUPERADMIN || User::getAuthUser()->role_id === UserRoleEnum::COMPANY;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Van $van): bool
    {
        return User::getAuthUser()->id === $van->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return User::getAuthUser()->role_id === UserRoleEnum::SUPERADMIN || User::getAuthUser()->role_id === UserRoleEnum::COMPANY;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Van $van): bool
    {
        return User::getAuthUser()->id === $van->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Van $van): bool
    {
        return User::getAuthUser()->id === $van->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Van $van): bool
    {
        return User::getAuthUser()->id === $van->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Van $van): bool
    {
        return User::getAuthUser()->id === $van->company_id;
    }
}
