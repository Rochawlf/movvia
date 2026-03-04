<?php

namespace App\Enums;

enum UserRole: string
{
    case Passenger = 'passenger';
    case Driver = 'driver';
    case Admin = 'admin';

    // Helper para exibir um nome bonito na interface, se precisar
    public function label(): string
    {
        return match($this) {
            self::Passenger => 'Passageiro',
            self::Driver => 'Motorista',
            self::Admin => 'Administrador',
        };
    }
}