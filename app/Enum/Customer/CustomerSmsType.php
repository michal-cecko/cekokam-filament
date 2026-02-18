<?php

namespace App\Enum\Customer;

enum CustomerSmsType: string
{
    case CUSTOM = 'CUSTOM';
    case PAYMENT_REQUEST = 'PAYMENT_REQUEST';

    public static function strings(): array
    {
        return [
            self::PAYMENT_REQUEST->value,
            self::CUSTOM->value,
        ];
    }

    public static function translated(): array
    {
        return [
            self::CUSTOM->value => 'Vlastná',
            self::PAYMENT_REQUEST->value => 'Výzva k zaplateniu',
        ];
    }

    public static function colors(): array
    {
        return [
            self::CUSTOM->value => 'primary',
            self::PAYMENT_REQUEST->value => 'primary',
        ];
    }

    public static function icons(): array
    {
        return [
            self::CUSTOM->value => 'heroicon-s-pencil',
            self::PAYMENT_REQUEST->value => 'heroicon-s-currency-euro',
        ];
    }
}
