<?php

namespace App\Policies\Service;

use App\Models\Service\ServiceTypeCount;
use App\Models\User;
use App\Policies\CommonPolicy;

class ServiceTypeCountPolicy extends CommonPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceTypeCount $serviceTypeCount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceTypeCount $serviceTypeCount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceTypeCount $serviceTypeCount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServiceTypeCount $serviceTypeCount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServiceTypeCount $serviceTypeCount): bool
    {
        return true;
    }

    public function restoreAny(User $user): bool
    {
        return true;
    }

    public function deleteAny(User $user): bool
    {
        return true;
    }

    public function forceDeleteAny(User $user): bool
    {
        return true;
    }
}
