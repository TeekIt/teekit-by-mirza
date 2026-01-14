<?php

namespace App\Policies;

use App\Models\RequestedDelivery;
use App\Models\User;

class RequestedDeliveryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(int $userId): bool
    {
        return $userId === User::getAuthUser()->id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RequestedDelivery $requestedDelivery): bool
    {
        return $user->id === $requestedDelivery->creator_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RequestedDelivery $requestedDelivery): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RequestedDelivery $requestedDelivery): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RequestedDelivery $requestedDelivery): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RequestedDelivery $requestedDelivery): bool
    {
        //
    }
}
