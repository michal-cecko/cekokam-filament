<?php

namespace App\Models\Other;

use App\Models\Customer\Customer;
use App\Models\Customer\Payment\CustomerPayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankAccount extends Model
{
    use HasFactory;

    public $primaryKey = 'iban';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'iban',
        'note',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (! empty($model->iban)) {
                $model->iban = str_replace(' ', '', $model->iban);
            }
        });
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['iban'];
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'iban', 'iban');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class, 'iban', 'iban');
    }
}
