<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Models\VanInventoryOrder;

class VanInventoryOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role_id, [
            UserRoleEnum::SUPERADMIN->value,
            UserRoleEnum::COMPANY->value,
        ]);
    }

    /**
     * View single van product
     */
    public function view(): bool
    {
       return true; // safe default
    }

    /**
     * Create van product
     */
    public function create(): bool
    {
        return in_array(User::getAuthUser()->role_id, [
            UserRoleEnum::SUPERADMIN->value,
            UserRoleEnum::COMPANY->value,
        ]);
    }

    /**
     * Update product
     */
    public function update(User $user, VanInventoryOrder $vanInventoryOrder): bool
    {
        return $user->id === $vanInventoryOrder->company_id;
    }

    /**
     * Delete product
     */
    public function delete(): bool
    {
        return true; // safe default
    }

    /**
     * Restore product
     */
    public function restore(): bool
    {
        return true; // safe default
    }

    /**
     * Force delete
     */
    public function forceDelete(): bool
    {
        return true; // safe default
    }
}
