<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'airline_name',
    'airline_code',
    'flight_number',
    'aircraft',
    'origin_code',
    'origin_city',
    'origin_timezone',
    'destination_code',
    'destination_city',
    'destination_timezone',
    'departure_date',
    'departure_time',
    'arrival_time',
    'duration_minutes',
    'stops',
    'price',
])]
class Flight extends Model
{
    protected $casts = [
        'departure_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function fares(): HasMany
    {
        return $this->hasMany(FlightFare::class)->orderBy('sort_order');
    }
}
