<?php

namespace App\Models;

use App\Enums\RideStatus;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    // Campos que permitimos preencher via Ride::create()
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
        'fare',
    ];

    // Transforma o texto do banco no nosso objeto Enum automaticamente
    protected $casts = [
        'status' => RideStatus::class,
    ];

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function rating()
    {
        return $this->hasOne(RideRating::class, 'ride_id');
    }
}
