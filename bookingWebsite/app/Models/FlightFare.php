<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'flight_id',
    'fare_class',
    'badge',
    'price',
    'checked_bag_kg',
    'carry_on',
    'seat_info',
    'perks',
    'refundable',
    'change_fee_from',
    'sort_order',
])]
class FlightFare extends Model
{
    protected $casts = [
        'perks' => 'array',
        'refundable' => 'boolean',
        'price' => 'decimal:2',
        'change_fee_from' => 'decimal:2',
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }
}
