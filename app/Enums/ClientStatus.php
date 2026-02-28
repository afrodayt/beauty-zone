<?php

namespace App\Enums;

enum ClientStatus: string
{
    case NEW = 'NEW';
    case ACTIVE = 'ACTIVE';
    case LOST = 'LOST';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
