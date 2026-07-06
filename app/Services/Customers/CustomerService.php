<?php

namespace App\Services\Customers;

use App\Models\Customer\Customer;
use App\Models\Customer\CustomerService as CustomerServiceModel;
use Illuminate\Database\Eloquent\Model;

class CustomerService
{
    public static function takenIpAddresses($ipStart, $ipEnd, $serverId, ?int $ignoreId = null): array
    {
        if (empty($serverId)) {
            return [];
        }

        $query = Customer::where('server_id', (int) $serverId);

        // Add range check if both start and end IPs are provided
        if (! empty($ipStart) && ! empty($ipEnd)) {
            $query->where(function ($q) use ($ipStart, $ipEnd) {
                foreach (range($ipStart, $ipEnd) as $ip) {
                    $q->orWhereJsonContains('ip_addresses', (int) $ip);
                }
            });
        } elseif (! empty($ipStart)) {
            // Check for single IP if only `ipStart` is provided
            $query->whereJsonContains('ip_addresses', (int) $ipStart);
        }

        // Exclude the record with the ignored ID if provided
        if (! empty($ignoreId)) {
            $query->where('id', '<>', $ignoreId);
        }

        // Get all matching records
        $existingRecords = $query->get();

        // Extract the conflicting IPs
        $takenIps = [];
        foreach ($existingRecords as $record) {
            $conflictingIps = array_intersect(
                $record->ip_addresses ?? [], // Existing IPs in the record
                ! empty($ipStart) && ! empty($ipEnd) ? range($ipStart, $ipEnd) : [$ipStart]
            );
            $takenIps = array_merge($takenIps, $conflictingIps);
        }

        // Return unique IPs
        return array_values(array_unique($takenIps));
    }

    public static function parseCombinedService(?string $combinedService): ?array
    {
        $separator = '-';
        if (empty($combinedService) || ! str_contains($combinedService, $separator)) {
            return null;
        }

        $arr = explode($separator, $combinedService);

        return [
            'service_type_id' => (int) $arr[0],
            'service_count_id' => (int) $arr[1],
        ];
    }

    public static function parseMultipleCombinedService(array $combinedServices = []): ?array
    {
        $return = [];

        foreach ($combinedServices as $combinedService) {
            $parsed = self::parseCombinedService($combinedService);
            if (! empty($parsed)) {
                $return[] = $parsed;
            }
        }

        return $return;
    }

    /**
     * Normalize a mixed list of service selector tokens into filter selectors.
     *
     * Accepts three token shapes: "{typeId}-{countId}" (concrete combo),
     * "type:{typeId}" (whole service type) and "count:{countValue}" (whole count).
     * A null column means "any".
     *
     * @param  array<int, string>|null  $values
     * @return array<int, array{service_type_id: int|null, service_count_id: int|null}>
     */
    public static function parseServiceSelectors(?array $values): array
    {
        $selectors = [];

        foreach ($values ?? [] as $value) {
            if (str_starts_with($value, 'type:')) {
                $selectors[] = ['service_type_id' => (int) substr($value, 5), 'service_count_id' => null];
            } elseif (str_starts_with($value, 'count:')) {
                $selectors[] = ['service_type_id' => null, 'service_count_id' => (int) substr($value, 6)];
            } elseif ($pair = self::parseCombinedService($value)) {
                $selectors[] = ['service_type_id' => $pair['service_type_id'], 'service_count_id' => $pair['service_count_id']];
            }
        }

        return $selectors;
    }

    public static function syncRepeaterServices(?Model $customer, array $data): void
    {
        if (empty($customer)) {
            return;
        }

        $submittedServiceIds = array_column($data['services'], 'id');

        if (! empty($services = $data['services'])) {
            foreach ($services as $serviceData) {
                if (! empty($id = $serviceData['id'])) {
                    if ($service = CustomerServiceModel::find($id)) {
                        $service->fill($serviceData);
                        $service->customer_id = $customer->id;
                        $service->save();
                    }
                } else {
                    $service = new CustomerServiceModel;
                    $service->fill($serviceData);
                    $service->customer_id = $customer->id;
                    $service->save();
                    $submittedServiceIds[] = $service->id;
                }
            }
        }

        $customer->services()->whereNotIn('id', $submittedServiceIds)->delete();
    }
}
