<?php

namespace App\Enum\AccountSubscription;

enum AccountSubscriptionExpiryStatus: string
{
    case SOON = 'SOON';
    case EXPIRED = 'EXPIRED';
    case OK = 'OK';

    public static function translated(): array
    {
        return [
            self::SOON->value => 'Čoskoro expiruje',
            self::EXPIRED->value => 'Expirovaný',
            self::OK->value => 'OK',
        ];
    }

    public static function colors(): array
    {
        return [
            self::SOON->value => 'warning',
            self::EXPIRED->value => 'danger',
            self::OK->value => 'success',
        ];
    }
}
