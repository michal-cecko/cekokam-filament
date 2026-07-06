<?php

namespace App\Models\Service;

use App\Models\Customer\CustomerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class ServiceType extends Model
{
    protected $fillable = [
        'name',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saved(fn () => Cache::forget('service_types_all'));

        static::deleted(function ($model) {
            Cache::forget('service_types_all');
            $model->customerServices()->delete();
        });
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ServiceTypePrice::class);
    }


    public function customerServices(): HasMany
    {
        return $this->hasMany(CustomerService::class, 'service_type_id');
    }
}
