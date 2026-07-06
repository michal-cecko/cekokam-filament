<?php

namespace App\Services\ServiceType;

use App\Models\Service\ServiceType;
use App\Models\Service\ServiceTypeCount;
use Illuminate\Support\Collection;

class ServiceTypeService
{
    /**
     * Grouped select options representing whole service types and whole counts.
     *
     * Values use the "type:{id}" and "count:{count_value}" prefixes so they never
     * collide with the concrete "{type_id}-{count_value}" combo values.
     *
     * @return array<string, array<string, string>>
     */
    public static function getServiceGroupsOptions(): array
    {
        $types = ServiceType::query()->orderBy('name')->get();
        $counts = ServiceTypeCount::query()->orderBy('count_value')->get();

        return [
            'Typy (celé)' => $types
                ->mapWithKeys(fn (ServiceType $type): array => ["type:{$type->id}" => "{$type->name} (všetky počty)"])
                ->all(),
            'Počty (celé)' => $counts
                ->mapWithKeys(fn (ServiceTypeCount $count): array => ["count:{$count->count_value}" => "{$count->count_value} TV (všetky typy)"])
                ->all(),
        ];
    }

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
