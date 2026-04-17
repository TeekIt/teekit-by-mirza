<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VanProduct;
use App\Enums\UserRoleEnum;

class VanProductPolicy
{
    /**
     * View list (index page)
     */
    public function viewAny(?User $user): bool
    {
        if (!$user) return false;

        return in_array($user->role_id, [
            UserRoleEnum::SUPERADMIN->value,
            UserRoleEnum::COMPANY->value,
            UserRoleEnum::VAN->value,
        ]);
    }

    /**
     * View single van product
     */
    public function view(?User $user, VanProduct $vanProduct): bool
    {
        // Web users (Admin / Company)
        if ($user) {
            return $user->role_id === UserRoleEnum::SUPERADMIN->value
                || $user->id === $vanProduct->seller_id;
        }

        // Van API user
        return auth('van')->user()->id === $vanProduct->van_id;
    }

    /**
     * Create van product
     */
    public function create(): bool
    {
        return User::getAuthUser()->role_id === UserRoleEnum::SUPERADMIN->value
            || User::getAuthUser()->role_id === UserRoleEnum::COMPANY->value;
    }

    /**
     * Update product
     */
    public function update(?User $user, VanProduct $vanProduct): bool
    {
        if ($user) {
            return $user->role_id === UserRoleEnum::SUPERADMIN->value
                || $user->id === $vanProduct->seller_id;
        }

        return auth('van')->user()->id === $vanProduct->van_id;
    }

    /**
     * Delete product
     */
    public function delete(?User $user, VanProduct $vanProduct): bool
    {
        if ($user) {
            return $user->role_id === UserRoleEnum::SUPERADMIN->value
                || $user->id === $vanProduct->seller_id;
        }

        return auth('van')->user()->id === $vanProduct->van_id;
    }

    /**
     * Restore product
     */
    public function restore(?User $user, VanProduct $vanProduct): bool
    {
        return false; // safe default
    }

    /**
     * Force delete
     */
    public function forceDelete(?User $user, VanProduct $vanProduct): bool
    {
        return $user
            ? $user->role_id === UserRoleEnum::SUPERADMIN->value
            : false;
    }
}