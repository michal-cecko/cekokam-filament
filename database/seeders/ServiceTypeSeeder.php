<?php

namespace Database\Seeders;

use App\Models\Service\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Skylink',
            ],
            [
                'name' => 'Antik',
            ],
            [
                'name' => 'Archív',
            ],
            [
                'name' => 'Digi',
            ],
        ];

        foreach ($services as $service) {
            if (! ServiceType::where('name', $service['name'])->exists()) {
                ServiceType::create($service);
            }
        }
    }
}
