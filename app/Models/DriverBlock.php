<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideRating extends Model
{
    public $timestamps = false; // Resolve o erro da imagem c903e5
    protected $fillable = ['ride_id', 'driver_id', 'stars', 'complaints'];
    protected $casts = ['complaints' => 'array'];
}
