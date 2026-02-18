<?php

namespace App\Policies\Other;

use App\Models\Other\BankAccount;
use App\Models\User;
use App\Policies\CommonPolicy;

class BankAccountPolicy extends CommonPolicy
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
    public function view(User $user, BankAccount $bankAccount): bool
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
    public function update(User $user, BankAccount $bankAccount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BankAccount $bankAccount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BankAccount $bankAccount): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BankAccount $bankAccount): bool
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
