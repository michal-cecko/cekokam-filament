<?php

namespace Database\Seeders;

use App\Enum\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = bcrypt(env('ADMIN_SEED_PASSWORD', bin2hex(random_bytes(16))));

        $admins = [
            [
                'email' => 'cekokam@gmail.com',
                'name' => 'Peter Čečko',
                'password' => $password,
                'role' => RoleEnum::ADMIN,
            ],
            [
                'email' => 'ceckomichal@gmail.com',
                'name' => 'Michal Čečko',
                'password' => $password,
                'role' => RoleEnum::ADMIN,
            ],
        ];

        foreach ($admins as $admin) {
            if (! User::where('email', $admin['email'])->exists()) {
                User::create($admin);
            }
        }
    }
}
