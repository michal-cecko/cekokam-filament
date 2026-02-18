<?php

namespace Database\Seeders;

use App\Models\Service\ServiceTypeCount;
use Illuminate\Database\Seeder;

class ServiceCountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceCounts = [
            [
                'count_value' => 1,
                'color' => '#66e335',
            ],
            [
                'count_value' => 2,
                'color' => '#0160fe',
            ],
            [
                'count_value' => 3,
                'color' => '#f1f500',
            ],
            [
                'count_value' => 4,
                'color' => '#fe7a02',
            ],
            [
                'count_value' => 5,
                'color' => '#ff0000',
            ],
            [
                'count_value' => 6,
                'color' => '#d887f7',
            ],
            [
                'count_value' => 7,
                'color' => '#7e018f',
            ],
            [
                'count_value' => 8,
                'color' => '#7e591b',
            ],
            [
                'count_value' => 9,
                'color' => '#7a7a7a',
            ],
            [
                'count_value' => 10,
                'color' => '#000000',
            ],
        ];

        foreach ($serviceCounts as $serviceCount) {
            if (! ServiceTypeCount::where('count_value', $serviceCount['count_value'])->exists()) {
                ServiceTypeCount::create($serviceCount);
            }
        }
    }
}
