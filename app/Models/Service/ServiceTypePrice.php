<?php

namespace App\Models\Service;

use App\Enum\Customer\CustomerStatus;
use App\Models\Customer\CustomerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTypePrice extends Model
{
    protected $fillable = [
        'service_type_id',
        'service_count_id',
        'price',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::updated(function ($model) {

            $customerServices = CustomerService::query()
                ->whereHas('customer', fn ($q) => $q->where('has_different_prices', false)->whereNotIn('status', [CustomerStatus::FREE, CustomerStatus::TURNED_OFF]))
                ->where('service_type_id', $model->service_type_id)
                ->where('service_count_id', $model->service_count_id)
                ->get();

            foreach ($customerServices as $customerService) {
                $customerService->price = $model->price;
                $customerService->save();
            }

        });
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function serviceTypeCount(): BelongsTo
    {
        return $this->belongsTo(ServiceTypeCount::class, 'service_count_id', 'count_value');
    }
}
