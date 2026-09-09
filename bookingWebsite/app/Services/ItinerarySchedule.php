<?php

namespace App\Services;

use App\Models\ItineraryEvent;

/**
 * Answers one question for the calendar: does this entry collide with
 * something already on that day?
 *
 * A day's entries are a timeline, not a list. Two things at the same hour
 * is not a full itinerary, it's a contradiction — you can't be at a museum
 * in Paris at 15:00 and boarding in Shanghai at 15:00. The planner has no
 * concept of travel time, so nothing upstream stops it from producing
 * that; this is where it gets caught.
 *
 * Bookings count as occupied time even though they can't be edited here:
 * a paid flight is the least movable thing on the calendar, so an activity
 * that lands on top of one is the activity that's wrong.
 */
class ItinerarySchedule
{
    /**
     * How long an entry occupies when nothing says otherwise.
     *
     * The planner only ever emits a start time, so without a default every
     * entry would be an instant and only exact-minute collisions would be
     * caught — 14:00 and 14:30 in different cities would both be accepted.
     */
    public const DEFAULT_DURATION_MINUTES = 60;

    /**
     * The first existing entry that overlaps the given window, or null if
     * the slot is free.
     *
     * All-day entries (no start time) never conflict: they make no claim
     * about a particular hour, so there is nothing to overlap.
     *
     * @param  string  $date  Y-m-d
     * @param  ?string  $start  H:i, or null for an all-day entry
     * @param  ?string  $end  H:i; defaults to start + DEFAULT_DURATION_MINUTES
     * @param  ?int  $ignoreId  an event to exclude — itself, when editing
     * @param  list<array{date: string, time: ?string, end: ?string}>  $alsoBooked
     *         pending entries not yet in the database, so a plan can be
     *         checked against itself before any of it is written
     */
    public function conflict(
        int $userId,
        string $date,
        ?string $start,
        ?string $end = null,
        ?int $ignoreId = null,
        array $alsoBooked = [],
    ): ?string {
        if ($start === null) {
            return null;
        }

        [$from, $to] = $this->window($start, $end);

        foreach ($alsoBooked as $pending) {
            if (($pending['date'] ?? null) !== $date || ($pending['time'] ?? null) === null) {
                continue;
            }

            [$pendingFrom, $pendingTo] = $this->window($pending['time'], $pending['end'] ?? null);

            if ($this->overlaps($from, $to, $pendingFrom, $pendingTo)) {
                return (string) ($pending['title'] ?? 'another entry in this plan');
            }
        }

        $existing = ItineraryEvent::query()
            ->where('user_id', $userId)
            ->whereDate('event_date', $date)
            ->whereNotNull('start_time')
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->get(['id', 'title', 'start_time', 'end_time']);

        foreach ($existing as $event) {
            [$eventFrom, $eventTo] = $this->window(
                substr((string) $event->start_time, 0, 5),
                $event->end_time ? substr((string) $event->end_time, 0, 5) : null,
            );

            if ($this->overlaps($from, $to, $eventFrom, $eventTo)) {
                return $event->title;
            }
        }

        return null;
    }

    /**
     * Start and end as minutes past midnight.
     *
     * An end at or before the start is treated as unset rather than
     * rejected — it usually means an entry that runs past midnight, and
     * clamping it to the default duration keeps the check on this day
     * instead of inventing a negative window that overlaps nothing.
     *
     * @return array{int, int}
     */
    protected function window(string $start, ?string $end): array
    {
        $from = $this->minutes($start);
        $to = $end !== null ? $this->minutes($end) : null;

        if ($to === null || $to <= $from) {
            $to = $from + self::DEFAULT_DURATION_MINUTES;
        }

        return [$from, $to];
    }

    /** Half-open intervals, so 14:00-15:00 and 15:00-16:00 sit side by side. */
    protected function overlaps(int $from, int $to, int $otherFrom, int $otherTo): bool
    {
        return $from < $otherTo && $otherFrom < $to;
    }

    protected function minutes(string $time): int
    {
        [$hours, $minutes] = array_pad(explode(':', $time), 2, '0');

        return ((int) $hours * 60) + (int) $minutes;
    }
}
