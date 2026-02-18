<?php

namespace App\Models\Customer;

use App\Enum\Customer\CustomerStatus;
use App\Models\Customer\Payment\CustomerPayment;
use App\Models\Other\BankAccount;
use App\Models\Other\City;
use App\Models\Other\Server;
use App\Services\Customers\BillingPeriodService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'year_added',
        'server_id',
        'ip_addresses',
        'has_different_prices',
        'status',
        'city_id',
        'iban',
        'note',
        'ip_start',
        'ip_end',
        'is_paid',
        'highest_ip',
        'can_bulk_change_period',
    ];

    protected $casts = [
        'phone' => 'array',
        'ip_addresses' => 'array',
        'status' => CustomerStatus::class,
    ];

    protected $appends = [
        'phone_string',
    ];

    protected $with = [
        'services',
    ];

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Customer $model) {
            if (! empty($model->iban)) {
                $model->iban = str_replace(' ', '', $model->iban);
            }
            $model->createBankAccountIfNotExists();
            $model->parseIsPaidStatus();
            if (! empty($model->ip_start) || ! empty($model->ip_end)) {
                $model->parseIpAddressRange();
            }
            $model->sortIpAddresses();
        });

        static::deleted(function (Customer $model) {
            $model->updateRelatedPayments();
        });
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(CustomerService::class)->orderBy('service_type_id', 'ASC');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function currentPeriodPayments(): HasMany
    {
        $period = BillingPeriodService::getCurrentPeriod();

        return $this->payments()->whereBetween('received_at', [$period['start'], $period['end']]);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'iban', 'iban');
    }

    public function getTitleAttribute(): string
    {
        return $this->name;
    }

    public function getPhoneStringAttribute(): string
    {
        return implode('<br>', $this->phone);
    }

    public function getTotalPriceAttribute(): float
    {
        $amount = $this->services->sum('total_price') - $this->currentPeriodPayments->sum('amount_paid');

        return max($amount, 0);
    }

    public function getTotalMonthlyPriceAttribute(): float
    {
        return $this->services->sum('price');
    }

    public function getSubscriptionStartAttribute(): ?Carbon
    {
        return Carbon::parse($this->services->min('subscription_start'));
    }

    public function getSubscriptionEndAttribute(): ?Carbon
    {
        return Carbon::parse($this->services->max('subscription_end'));
    }

    public function getSubscriptionDateStringAttribute(): ?string
    {
        if ($this->subscription_start->format('FY') === $this->subscription_end->format('FY')) {
            $string = "{$this->subscription_start->translatedFormat('F')} - {$this->subscription_end->translatedFormat('F Y')}";
        } else {
            $string = "{$this->subscription_start->translatedFormat('F Y')} - {$this->subscription_end->translatedFormat('F Y')}";
        }

        return Str::ascii($string);
    }

    public function getServerLinkAttribute(): ?string
    {
        $serverPlaceholderLink = $this->server?->ip_link;
        if (empty($serverPlaceholderLink)) {
            return null;
        }

        // Replace the {IP} placeholder with the actual IP
        $url = str_replace('{IP}', $this->highest_ip, $serverPlaceholderLink);

        // Include the credentials in the URL
        $credentials = 'root:cekokam790310';
        $urlWithCredentials = str_replace('http://', "http://$credentials@", $url);

        return $urlWithCredentials;
    }

    public function getOscamLinkAttribute(): ?string
    {
        $serverLink = $this->server_link;
        if (empty($serverLink)) {
            return null;
        }

        return "{$serverLink}:8888";
    }

    public function getWebLinkAttribute(): ?string
    {
        $serverLink = $this->server_link;
        if (empty($serverLink)) {
            return null;
        }

        return "{$serverLink}:88";
    }

    public function updateRelatedPayments(): void
    {
        $this->payments()->update([
            'customer_deleted_at' => now(),
        ]);
    }

    public static function years(): array
    {
        $endYear = now()->year + 1;
        $startYear = 2016;

        return array_combine(range($startYear, $endYear), range($startYear, $endYear));
    }

    public static function defaultYear(): int
    {
        return intval(date('Y'));
    }

    public function getLowestIpAttribute(): int
    {
        return ! empty($this->ip_addresses) ? min($this->ip_addresses) : 0;
    }

    public function parseIpAddressRange(): void
    {
        if (! empty($this->ip_start) && ! empty($this->ip_end)) {
            $this->ip_addresses = range($this->ip_start, $this->ip_end);
        } elseif (! empty($this->ip_start)) {
            $this->ip_addresses = [$this->ip_start];
        } elseif (! empty($this->ip_end)) {
            $this->ip_addresses = [$this->ip_end];
        } else {
            $this->ip_addresses = [];
        }

        unset($this->ip_start, $this->ip_end);
    }

    public function parseIsPaidStatus(): void
    {
        if (! isset($this->is_paid)) {
            return;
        }

        $this->status = $this->is_paid ? CustomerStatus::PAID : CustomerStatus::UNPAID;
        unset($this->is_paid);
    }

    public function sortIpAddresses(): void
    {
        if (empty($this->ip_addresses)) {
            return;
        }

        $this->ip_addresses = collect($this->ip_addresses)->map(fn ($ip) => (int) $ip)->sortDesc()->values();
        $this->highest_ip = max($this->ip_addresses);
    }

    private function createBankAccountIfNotExists(): void
    {
        if (! BankAccount::where('iban', $this->iban)->exists()) {
            BankAccount::create([
                'iban' => $this->iban,
            ]);
        }
    }
}
