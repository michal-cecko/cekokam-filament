<?php

namespace App\Models\Other;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name',
        'postal_code',
    ];

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
