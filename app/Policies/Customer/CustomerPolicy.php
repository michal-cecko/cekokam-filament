<?php

namespace App\Policies\Customer;

use App\Enum\RoleEnum;
use App\Models\Customer\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === RoleEnum::EMPLOYEE && in_array($ability, ['create', 'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny', 'restore', 'restoreAny'])) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Customer $customer): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Customer $customer): bool
    {
        return true;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return true;
    }

    public function restore(User $user, Customer $customer): bool
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

    public function forceDelete(User $user, Customer $customer): bool
    {
        return true;
    }
}
