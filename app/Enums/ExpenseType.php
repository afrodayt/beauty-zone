<?php

namespace App\Enums;

enum ExpenseType: string
{
    case RENT = 'RENT';
    case SALARY = 'SALARY';
    case SUPPLIES = 'SUPPLIES';
    case MARKETING = 'MARKETING';
    case OTHER = 'OTHER';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
