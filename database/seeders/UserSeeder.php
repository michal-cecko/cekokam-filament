<?php

namespace Database\Seeders;

use App\Enum\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'email' => 'cekokam@gmail.com',
                'name' => 'Peter Čečko',
                'password' => bcrypt('cekokam123admin'),
                'role' => RoleEnum::ADMIN,
            ],
            [
                'email' => 'ceckomichal@gmail.com',
                'name' => 'Michal Čečko',
                'password' => bcrypt('cekokam123admin'),
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
