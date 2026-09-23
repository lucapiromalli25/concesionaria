<?php

namespace App\Enum;

enum VehicleStatus: string
{
    case EnStock         = 'En Stock';
    case Reservado       = 'Reservado';
    case Vendido         = 'Vendido';
    case EnMantenimiento = 'En Mantenimiento';

    public static function choices(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_column(self::cases(), 'value')
        );
    }
}
