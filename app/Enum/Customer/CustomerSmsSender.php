<?php

namespace App\Enum\Customer;

enum CustomerSmsSender: string
{
    case ANDROID_CONNECTOR = 'ANDROID_CONNECTOR';

    case ANONYMOUS = 'AN0NYMOUS';

    public static function strings(): array
    {
        return [
            self::ANDROID_CONNECTOR->value,
            self::ANONYMOUS->value,
        ];
    }

    public static function colors(): array
    {
        return [
            self::ANDROID_CONNECTOR->value => 'info',
            self::ANONYMOUS->value => 'info',
        ];
    }

    public static function translated(): array
    {
        return [
            self::ANDROID_CONNECTOR->value => 'Android telefón',
            self::ANONYMOUS->value => 'Anonymný odosielateľ',
        ];
    }
}
