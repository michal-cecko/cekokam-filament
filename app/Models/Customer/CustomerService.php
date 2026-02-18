<?php

namespace App\Models\Customer;

use App\Models\Service\AccountSubscription;
use App\Models\Service\ServiceType;
use App\Models\Service\ServiceTypeCount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerService extends Model
{
    protected $fillable = [
        'customer_id',
        'service_count_id',
        'service_type_id',
        'archive_account_id',
        'price',
        'subscription_start',
        'subscription_end',
        'combined_service',
        'continue_next_period',
    ];

    protected $casts = [
        'subscription_start' => 'date:F Y',
        'subscription_end' => 'date:F Y',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            if ($service = \App\Services\Customers\CustomerService::parseCombinedService($model->combined_service)) {
                $model->service_type_id = $service['service_type_id'];
                $model->service_count_id = $service['service_count_id'];
            }

            $subscriptionStart = Carbon::parse($model->subscription_start);
            $model->subscription_start = $subscriptionStart->startOfMonth();

            if (! empty($model->subscription_end)) {
                $subscriptionEnd = Carbon::parse($model->subscription_end);
                $model->subscription_end = $subscriptionEnd->endOfMonth();
            }

            if ($model->service_type_id != config('service_types.archive_id')) {
                $model->archive_account_id = null;
            }

            if (empty($model->archive_account_id)) {
                $model->archive_account_id = null;
            }

            unset($model->combined_service);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceCount(): BelongsTo
    {
        return $this->belongsTo(ServiceTypeCount::class, 'service_count_id');
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function archiveAccount(): BelongsTo
    {
        return $this->belongsTo(AccountSubscription::class, 'archive_account_id');
    }

    public function getFullServiceNameAttribute(): string
    {
        return "{$this->serviceType->name} {$this->service_count_id} TV";
    }

    public function getTotalPriceAttribute(): float
    {
        return $this->price * $this->months_count;
    }

    public function getMonthsCountAttribute(): int
    {
        $start = Carbon::parse($this->subscription_start);
        $end = Carbon::parse($this->subscription_end);

        // First get the months difference
        $months = $start->diffInMonths($end);

        // If we're at the end of a month, Carbon might undercount
        // Add 1 if the end date is the last day of its month
        if ($end->isLastOfMonth() && ! $start->isSameDay($end)) {
            $months++;
        }

        return $months;
    }

    public function getDefaultCombinedService(): string
    {
        return "{$this->service_type_id}-{$this->service_count_id}";
    }
}
