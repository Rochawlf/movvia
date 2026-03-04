<?php

namespace App\Enums;

enum RideStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Aguardando Motorista',
            self::Accepted => 'Motorista a Caminho',
            self::InProgress => 'Em Corrida',
            self::Finished => 'Corrida Finalizada',
            self::Completed => 'Finalizada',
            self::Cancelled => 'Cancelada',
        };
    }
}