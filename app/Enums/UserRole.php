<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case CASHIER = 'cashier';

    /**
     * Get all roles
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
