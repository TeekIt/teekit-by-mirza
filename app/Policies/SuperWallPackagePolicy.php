<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\SuperWallPackage;
use App\Models\User;

class SuperWallPackagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SuperWallPackage $superWallPackage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role_id === UserRoleEnum::SUPERADMIN->value;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->role_id === UserRoleEnum::SUPERADMIN->value;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->role_id === UserRoleEnum::SUPERADMIN->value;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SuperWallPackage $superWallPackage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user): bool
    {
        return $user->role_id === UserRoleEnum::SUPERADMIN->value;
    }
}
