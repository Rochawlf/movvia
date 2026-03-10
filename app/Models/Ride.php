<?php

namespace App\Models;

use App\Enums\RideStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ride extends Model
{
    /**
     * Campos que podem ser preenchidos em massa.
     * Incluindo 'payment_method' para suportar a finalização da corrida.
     */
    protected $fillable = [
        'passenger_id',
        'driver_id',
        'origin_address',
        'destination_address',
        'origin_lat',
        'origin_lng',
        'destination_lat',
        'destination_lng',
        'category',
        'status',
        'distance',
        'fare',
        'payment_method',
    ];

    /**
     * Casts para garantir tipos de dados corretos e integração com Enums.
     */
    protected $casts = [
        'status' => RideStatus::class,
        'origin_lat' => 'float',
        'origin_lng' => 'float',
        'destination_lat' => 'float',
        'destination_lng' => 'float',
        'distance' => 'float',
        'fare' => 'float',
    ];

    /**
     * Relacionamento com o passageiro (usuário que solicitou).
     */
    public function passenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    /**
     * Relacionamento com o motorista (usuário que aceitou).
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Relacionamento com a avaliação da corrida.
     */
    public function rating(): HasOne
    {
        return $this->hasOne(RideRating::class, 'ride_id');
    }

    /**
     * Helper para verificar se a corrida ainda pode ser cancelada.
     * Útil para travar ações na interface e no backend.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            RideStatus::Pending,
            RideStatus::Accepted
        ]);
    }
}