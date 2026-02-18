<?php

namespace App\Models\Customer\Payment;

use App\Enum\Customer\CustomerPaymentStatus;
use App\Models\Customer\Customer;
use App\Models\Other\BankAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPayment extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_id',
        'status',
        'customer_deleted_at',
        'amount_paid',
        'amount_expected',
        'note',
        'received_at',
        'iban',
    ];

    protected $casts = [
        'status' => CustomerPaymentStatus::class,
    ];

    public static function getGloballySearchableAttributes(): array
    {
        return ['customer_name'];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            if (! empty($model->iban)) {
                $model->iban = str_replace(' ', '', $model->iban);
            }
            $model->setDefaultCustomerName();
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CustomerPaymentStatus::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'iban', 'iban');
    }

    public function getTitleAttribute(): string
    {
        return "$this->customer_name - {$this->amount_paid}€";
    }

    public function setDefaultCustomerName(): void
    {
        if (empty($this->customer_name)) {
            $this->customer_name = $this->customer?->name;
        }
    }
}
