<?php

namespace App\Enum;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case EMPLOYEE = 'employee';

    public static function strings(): array
    {
        return [
            self::ADMIN->value,
            self::EMPLOYEE->value,
        ];
    }

    public static function translated(): array
    {
        return [
            self::ADMIN->value => 'Admin',
            self::EMPLOYEE->value => 'Zamestnanec',
        ];
    }
}
