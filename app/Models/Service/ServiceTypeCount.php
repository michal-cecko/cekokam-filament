<?php

namespace App\Models\Service;

use App\Models\Customer\CustomerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceTypeCount extends Model
{
    protected $primaryKey = 'count_value';

    public $incrementing = false;

    protected $fillable = [
        'count_value',
        'color',
    ];

    protected static function boot(): void
    {
        parent::boot();

        parent::deleted(function ($model) {
            $model->prices()->delete();
            $model->customerServices()->delete();
        });
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ServiceTypePrice::class, 'service_count_id', 'count_value');
    }

    public function customerServices(): HasMany
    {
        return $this->hasMany(CustomerService::class, 'service_count_id');
    }
}
