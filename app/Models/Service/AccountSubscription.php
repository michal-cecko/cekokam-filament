<?php

namespace App\Models\Service;

use App\Enum\AccountSubscription\AccountSubscriptionExpiryStatus;
use App\Enum\AccountSubscription\AccountSubscriptionType;
use App\Models\Customer\CustomerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountSubscription extends Model
{
    protected $fillable = [
        'login',
        'expires_at',
        'type',
        'note',
        'expiration_days_to_add',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'type' => AccountSubscriptionType::class,
    ];

    public static function getGloballySearchableAttributes(): array
    {
        return ['login'];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $model) {
            if (isset($model->expiration_days_to_add)) {
                $model->expires_at = now()->addDays((int) $model->expiration_days_to_add);
                unset($model->expiration_days_to_add);
            }
        });
    }

    public function customerServices(): HasMany
    {
        return $this->hasMany(CustomerService::class, 'archive_account_id');
    }

    public function getExpiryDaysAttribute(): int
    {
        return -1 * (floor($this->expires_at->diffInDays()));
    }

    public function getExpiryStatusAttribute(): AccountSubscriptionExpiryStatus
    {
        $now = now();
        $expiryDate = $this->expires_at;

        if ($now->gt($expiryDate)) {
            return AccountSubscriptionExpiryStatus::EXPIRED;
        } elseif ($now->addDays(3)->gte($expiryDate)) {
            return AccountSubscriptionExpiryStatus::SOON;
        }

        return AccountSubscriptionExpiryStatus::OK;
    }
}
