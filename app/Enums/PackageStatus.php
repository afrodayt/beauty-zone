<?php

namespace App\Enums;

enum PackageStatus: string
{
    case ACTIVE = 'ACTIVE';
    case EXPIRED = 'EXPIRED';
    case EXHAUSTED = 'EXHAUSTED';
    case CANCELED = 'CANCELED';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
