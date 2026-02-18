<?php

namespace App\Enum\AccountSubscription;

enum AccountSubscriptionType: string
{
    case ARCHIVE = 'ARCHIVE';

    public static function translated(): array
    {
        return [
            self::ARCHIVE->value => 'Archív',
        ];
    }
}
