<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use App\Support\Airports;
use Carbon\CarbonImmutable;
use DateTimeZone;
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
    'timezone',
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

    /**
     * The airport a flight entry's time is local to, e.g. "JFK".
     *
     * A departure time is local to the origin, so a JFK departure shown on
     * a Doha itinerary reads as a Doha time unless it says otherwise. This
     * is what lets the day panel label it instead of quietly misleading.
     *
     * Null for everything else: a dinner in Kyoto needs no label, since it
     * happens where the day says it does.
     */
    public function getTimeZoneHintAttribute(): ?string
    {
        if ($this->category !== 'flight' || $this->start_time === null) {
            return null;
        }

        return Airports::codeIn($this->location) ?? Airports::codeIn($this->title);
    }

    /**
     * The same moment in the destination's local time, e.g. "20:20 DOH".
     *
     * This is the number a traveller actually wants on a trip calendar: a
     * departure printed only in the origin's clock is a time they will
     * never experience. Shown alongside the origin time rather than
     * replacing it, because the origin time is the one on the boarding
     * pass.
     *
     * Null unless both ends are known and genuinely differ - two airports
     * in one zone would otherwise print the same number twice.
     */
    public function getArrivalZoneTimeAttribute(): ?string
    {
        $from = $this->timezone ?? Airports::zone($this->time_zone_hint);
        $toCode = $this->destinationCode();
        $to = Airports::zone($toCode);

        if ($from === null || $to === null || $from === $to || $this->start_time === null) {
            return null;
        }

        $local = CarbonImmutable::parse(
            $this->event_date->toDateString().' '.substr((string) $this->start_time, 0, 5),
            new DateTimeZone($from),
        );

        return $local->setTimezone(new DateTimeZone($to))->format('H:i').' '.$toCode;
    }

    /**
     * The second known airport code in the route text - the arrival end of
     * "New York (JFK) to Doha (DOH)".
     */
    protected function destinationCode(): ?string
    {
        $text = $this->location ?? $this->title;

        if (! preg_match_all('/\(([A-Z]{3})\)/', (string) $text, $matches)) {
            return null;
        }

        $known = array_values(array_filter(
            $matches[1],
            fn (string $code) => Airports::zone($code) !== null,
        ));

        return $known[1] ?? null;
    }
}
