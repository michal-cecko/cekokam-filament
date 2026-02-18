<?php

namespace App\Services\Customers;

use Carbon\Carbon;

class BillingPeriodService
{
    public static function getCurrentPeriod(): array
    {
        $currentMonth = now()->month;

        // If current month is July (7) or later, current period is July to December
        if ($currentMonth >= 7) {
            return [
                'start' => Carbon::create(now()->year, 7, 1)->startOfMonth(),
                'end' => Carbon::create(now()->year, 12, 1)->endOfMonth(),
            ];
        }

        // Otherwise, current period is January to June
        return [
            'start' => Carbon::create(now()->year, 1, 1)->startOfMonth(),
            'end' => Carbon::create(now()->year, 6, 1)->endOfMonth(),
        ];
    }
}
