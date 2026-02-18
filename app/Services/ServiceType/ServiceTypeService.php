<?php

namespace App\Services\ServiceType;

use App\Models\Service\ServiceType;
use Illuminate\Support\Collection;

class ServiceTypeService
{
    // TYPES: flatten_with_prices, options
    public static function getGrouppedServiceTypes(): Collection
    {
        $serviceTypes = ServiceType::with('prices.serviceTypeCount')->get();
        $return = [];

        foreach ($serviceTypes as $serviceType) {
            $return['options'][$serviceType->name] = [];

            foreach ($serviceType->prices as $price) {
                $name = "{$serviceType->name} {$price->serviceTypeCount->count_value} TV";

                $return['options'][$serviceType->name]["{$serviceType->id}-{$price->serviceTypeCount->count_value}"] = $name;
                $return['prices']["{$serviceType->id}-{$price->serviceTypeCount->count_value}"] = [
                    'value' => $name,
                    'price' => $price->price,
                ];
            }
        }

        return collect($return);
    }
}
