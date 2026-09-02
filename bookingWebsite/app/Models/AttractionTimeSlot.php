<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attraction_id',
    'slot_key',
    'time_label',
    'price',
    'sort_order',
])]
class AttractionTimeSlot extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function attraction(): BelongsTo
    {
        return $this->belongsTo(Attraction::class);
    }
}
