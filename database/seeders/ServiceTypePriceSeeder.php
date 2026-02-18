<?php

namespace Database\Seeders;

use App\Models\Service\ServiceType;
use App\Models\Service\ServiceTypeCount;
use App\Models\Service\ServiceTypePrice;
use Illuminate\Database\Seeder;

class ServiceTypePriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceCounts = ServiceTypeCount::all();
        $serviceTypes = ServiceType::all();

        foreach ($serviceTypes as $serviceType) {
            foreach ($serviceCounts as $count) {
                ServiceTypePrice::create([
                    'service_type_id' => $serviceType->id,
                    'service_count_id' => $count->count_value,
                    'price' => rand(1, 20),
                ]);
            }
        }
    }
}
