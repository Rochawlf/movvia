<?php

namespace App\Models;

use App\Enums\RideStatus;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
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
    ];

    protected $casts = [
        'status' => RideStatus::class,
        'origin_lat' => 'float',
        'origin_lng' => 'float',
        'destination_lat' => 'float',
        'destination_lng' => 'float',
        'distance' => 'float',
        'fare' => 'float',
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