<?php

namespace App\Enum\Customer;

enum CustomerPaymentStatus: string
{
    case NOT_SUFFICIENT = 'NOT_SUFFICIENT';
    case TOO_MUCH = 'TOO_MUCH';
    case OK = 'OK';
    case REDUNDANT = 'REDUNDANT';

    public static function strings(): array
    {
        return [
            self::NOT_SUFFICIENT->value,
            self::TOO_MUCH->value,
            self::OK->value,
            self::REDUNDANT->value,
        ];
    }

    public static function translated(): array
    {
        return [
            self::NOT_SUFFICIENT->value => 'Nedostatočná',
            self::TOO_MUCH->value => 'Viac ako treba',
            self::OK->value => 'OK',
            self::REDUNDANT->value => 'Nepotrebná',
        ];
    }

    public static function colors(): array
    {
        return [
            self::NOT_SUFFICIENT->value => 'danger',
            self::TOO_MUCH->value => 'warning',
            self::OK->value => 'success',
            self::REDUNDANT->value => 'info',
        ];
    }
}
