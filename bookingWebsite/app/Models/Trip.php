<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'title',
    'destination',
    'start_date',
    'end_date',
    'booking_reference',
    'status',
    'total_price',
])]
class Trip extends Model
{
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ItineraryEvent::class)->orderBy('event_date')->orderBy('start_time');
    }

    /**
     * "12 – 16 Apr 2026", collapsing the month when both ends share one.
     */
    public function getDateRangeLabelAttribute(): string
    {
        if ($this->start_date->isSameMonth($this->end_date)) {
            return $this->start_date->format('j').' – '.$this->end_date->format('j M Y');
        }

        return $this->start_date->format('j M').' – '.$this->end_date->format('j M Y');
    }

    public function getNightsAttribute(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date);
    }
}
