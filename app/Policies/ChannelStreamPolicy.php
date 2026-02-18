<?php

namespace App\Policies;

use App\Enum\RoleEnum;
use App\Models\ChannelStream;
use App\Models\User;
use App\User\Role;

class ChannelStreamPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === RoleEnum::EMPLOYEE) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ChannelStream $stream): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ChannelStream $stream): bool
    {
        return true;
    }

    public function delete(User $user, ChannelStream $stream): bool
    {
        return true;
    }

    public function restore(User $user, ChannelStream $stream): bool
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

    public function forceDelete(User $user, ChannelStream $stream): bool
    {
        return true;
    }
}
