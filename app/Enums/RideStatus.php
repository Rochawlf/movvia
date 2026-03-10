<?php

namespace App\Enums;

enum RideStatus: string
{
    // Estados principais da corrida
    case Pending = 'pending';
    case Accepted = 'accepted';
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Retorna o rótulo amigável para a interface do usuário (UX).
     */
    public function label(): string
    {
        return match($this) {
            self::Pending => 'Procurando motorista',
            self::Accepted => 'Motorista a caminho',
            self::InProgress => 'Viagem em andamento',
            self::Finished => 'Chegamos ao destino',
            self::Completed => 'Viagem finalizada',
            self::Cancelled => 'Corrida cancelada',
        };
    }

    /**
     * Retorna a cor predominante para badges ou ícones.
     */
    public function color(): string
    {
        return match($this) {
            self::Pending => 'blue',
            self::Accepted, self::InProgress => 'orange',
            self::Finished, self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }

    /**
     * Define se a corrida ainda está em um estado que permite cancelamento.
     */
    public function isCancellable(): bool
    {
        return in_array($this, [self::Pending, self::Accepted]);
    }

    /**
     * Define se a interface deve exibir o card de corrida ativa.
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::Pending, 
            self::Accepted, 
            self::InProgress, 
            self::Finished
        ]);
    }
}