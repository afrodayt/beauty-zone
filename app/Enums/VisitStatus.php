<?php

namespace App\Enums;

enum VisitStatus: string
{
    case SCHEDULED = 'SCHEDULED';
    case ARRIVED = 'ARRIVED';
    case CANCELED = 'CANCELED';
    case NO_SHOW = 'NO_SHOW';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
