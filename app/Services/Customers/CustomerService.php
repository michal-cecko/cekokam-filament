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
