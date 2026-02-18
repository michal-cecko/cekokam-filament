<?php

namespace App\Services\Other;

use App\Enum\RoleEnum;
use App\Models\User;
use App\User\Role;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class HelpService
{
    public static function getModelFiles($directory): array
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    public static function notifyAdmins(...$notifications): void
    {
        $admins = User::query()
            ->where('role', RoleEnum::ADMIN)
            ->when(app()->isProduction(), fn ($q) => $q->where('email', '!=', 'ceckomichal@gmail.com')
            )
            ->get();

        foreach ($admins as $user) {
            foreach ($notifications as $notification) {
                $user->notify($notification);
            }
        }
    }
}
