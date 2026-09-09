<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeZone;

/**
 * Where the airports Voyagr sells flights to actually are, in time.
 *
 * The flights table stores departure_time and arrival_time as bare clock
 * times with no zone, which is fine for a schedule ("departs 10:00") and
 * useless for a calendar ("10:00 where?"). This is the missing half: the
 * IANA zone each code sits in, so a time can be labelled honestly and a
 * real offset can be handed to the planner.
 *
 * A hardcoded map rather than a table because the inventory is seeded and
 * small. If flights ever become real inventory this belongs in a column on
 * an airports table - the shape of the lookup would not change.
 */
class Airports
{
    /** IANA zone by IATA code, so DST is handled by the zone, not by us. */
    public const TIMEZONES = [
        'CDG' => 'Europe/Paris',
        'DOH' => 'Asia/Qatar',
        'DXB' => 'Asia/Dubai',
        'FRA' => 'Europe/Berlin',
        'HND' => 'Asia/Tokyo',
        'JFK' => 'America/New_York',
        'KIX' => 'Asia/Tokyo',
        'KUL' => 'Asia/Kuala_Lumpur',
        'LHR' => 'Europe/London',
        'NRT' => 'Asia/Tokyo',
        'SIN' => 'Asia/Singapore',
    ];

    public static function zone(?string $code): ?string
    {
        return self::TIMEZONES[strtoupper((string) $code)] ?? null;
    }

    /**
     * The UTC offset at a given date, e.g. "UTC+3".
     *
     * Date-dependent on purpose: New York is UTC-4 in September and UTC-5
     * in December, and a plan written for the wrong one is an hour out.
     */
    public static function offset(?string $code, ?string $date = null): ?string
    {
        $zone = self::zone($code);

        if ($zone === null) {
            return null;
        }

        $at = $date !== null
            ? CarbonImmutable::parse($date, new DateTimeZone($zone))
            : CarbonImmutable::now(new DateTimeZone($zone));

        $minutes = $at->utcOffset();
        $sign = $minutes < 0 ? '-' : '+';
        $minutes = abs($minutes);
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return 'UTC'.$sign.$hours.($rest > 0 ? ':'.str_pad((string) $rest, 2, '0', STR_PAD_LEFT) : '');
    }

    /**
     * The first IATA code in a free-text location, e.g. "New York (JFK) to
     * Doha (DOH)" gives JFK.
     *
     * Calendar entries carry the route as text rather than as ids, so this
     * is how a row's time gets labelled with the airport it belongs to.
     * Only codes we actually know are returned, so a stray "(VIP)" in a
     * title can't be mistaken for an airport.
     */
    public static function codeIn(?string $text): ?string
    {
        if (! is_string($text) || $text === '') {
            return null;
        }

        if (! preg_match_all('/\(([A-Z]{3})\)/', $text, $matches)) {
            return null;
        }

        foreach ($matches[1] as $code) {
            if (isset(self::TIMEZONES[$code])) {
                return $code;
            }
        }

        return null;
    }
}
