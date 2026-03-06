<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideRating extends Model
{
    protected $fillable = [
        'ride_id',
        'passenger_id',
        'driver_id',
        'stars',
        'complaints',
    ];

    protected $casts = [
        'complaints' => 'array',
        'stars' => 'integer',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class, 'ride_id');
    }

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}