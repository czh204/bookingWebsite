<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'trip_id',
    'order_id',
    'source',
    'category',
    'title',
    'location',
    'event_date',
    'start_time',
    'end_time',
    'notes',
])]
class ItineraryEvent extends Model
{
    /** An AI-suggested entry — planned, not booked. */
    public const SOURCE_AI = 'ai';

    /** An entry backed by a confirmed booking. */
    public const SOURCE_BOOKING = 'booking';

    protected $casts = [
        'event_date' => 'date',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /** Set when this entry came from a paid checkout rather than a seeded trip. */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isBooking(): bool
    {
        return $this->source === self::SOURCE_BOOKING;
    }

    /**
     * Times come back from MySQL as H:i:s; the day panel shows H:i.
     * All-day entries have no start_time at all.
     */
    public function getTimeLabelAttribute(): string
    {
        return $this->start_time ? substr((string) $this->start_time, 0, 5) : 'All day';
    }
}
