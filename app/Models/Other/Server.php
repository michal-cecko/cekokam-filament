<?php

namespace App\Models\Other;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    protected $fillable = [
        'name',
        'color',
        'server_link',
        'ip_link',
        'iban',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            if (str_ends_with($model->ip_link, '/')) {
                $model->ip_link = substr($model->ip_link, 0, -1);
            }
            if (str_ends_with($model->server_link, '/')) {
                $model->server_link = substr($model->server_link, 0, -1);
            }
            if (! empty($model->iban)) {
                $model->iban = str_replace(' ', '', $model->iban);
            }
        });
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'iban', 'iban');
    }

    public static function defaultServer(): int
    {
        return 1;
    }
}
