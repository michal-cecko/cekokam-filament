<?php

namespace App\Models\Service;

use App\Models\Customer\CustomerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceType extends Model
{
    protected $fillable = [
        'name',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function ($model) {
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
