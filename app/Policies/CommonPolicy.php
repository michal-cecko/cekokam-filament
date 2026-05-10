<?php

namespace App\Policies;

use App\Enum\RoleEnum;
use App\Models\User;

class CommonPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === RoleEnum::EMPLOYEE && in_array($ability, ['create', 'update', 'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny', 'restore', 'restoreAny'])) {
            return false;
        }

        return null;
    }
}
