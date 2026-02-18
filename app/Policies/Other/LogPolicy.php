<?php

namespace App\Policies\Other;

use App\Models\Other\Log;
use App\Models\User;
use App\Policies\CommonPolicy;

class LogPolicy extends CommonPolicy
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
    public function view(User $user, Log $log): bool
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
    public function update(User $user, Log $log): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Log $log): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Log $log): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Log $log): bool
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
