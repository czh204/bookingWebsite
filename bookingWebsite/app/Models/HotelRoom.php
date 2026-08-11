<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hotel_id',
    'room_class',
    'name',
    'size_sqm',
    'bed_info',
    'price_per_night',
    'perks',
    'sort_order',
])]
class HotelRoom extends Model
{
    protected $casts = [
        'perks' => 'array',
        'price_per_night' => 'decimal:2',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
