<?php

namespace App\Enum\Customer;

enum CustomerStatus: string
{
    case PAID = 'PAID';
    case FREE = 'FREE';
    case TURNED_OFF = 'TURNED_OFF';
    case UNPAID = 'UNPAID';

    public static function strings(): array
    {
        return [
            self::PAID->value,
            self::UNPAID->value,
            self::TURNED_OFF->value,
            self::FREE->value,
        ];
    }

    public static function translated(): array
    {
        return [
            self::PAID->value => 'Zaplatené',
            self::UNPAID->value => 'Nezaplatené',
            self::TURNED_OFF->value => 'Vypnutý',
            self::FREE->value => 'Free',
        ];
    }

    public static function icons(): array
    {
        return [
            self::PAID->value => 'heroicon-o-check',
            self::UNPAID->value => 'heroicon-o-x-mark',
            self::TURNED_OFF->value => 'heroicon-c-power',
            self::FREE->value => 'heroicon-o-hand-thumb-up',
        ];
    }

    public static function colors(): array
    {
        return [
            self::PAID->value => 'success',
            self::UNPAID->value => 'danger',
            self::TURNED_OFF->value => 'warning',
            self::FREE->value => 'info',
        ];
    }
}
