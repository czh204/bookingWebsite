<?php

namespace App\Services;

use App\Ai\Agents\ItineraryPlanner;
use App\Models\ItineraryEvent;
use Carbon\CarbonImmutable;

/**
 * Validates a planner response and writes it onto the calendar.
 *
 * The schema guarantees the JSON's shape, not that its contents make
 * sense: a schema can say "date is a string" but not "that string is a
 * real date, in this century, and not last Tuesday". Everything the model
 * produces is re-checked here before it becomes a row, and a bad entry is
 * dropped rather than failing the whole plan — one nonsense date
 * shouldn't lose the other nine good days.
 */
class ItineraryPlanWriter
{
    /**
     * Validates a whole plan and returns only the entries that could
     * actually be written.
     *
     * Split from store() so the preview shown to the user is built from
     * the same rows that will be saved — previewing the model's raw output
     * would promise entries that validation later drops.
     *
     * @param  array<string, mixed>  $plan  the structured planner response
     * @return list<array{date: string, time: ?string, category: string, title: string, location: ?string}>
     */
    public function validateAll(array $plan): array
    {
        $events = is_array($plan['events'] ?? null) ? $plan['events'] : [];
        $valid = [];

        foreach (array_slice($events, 0, ItineraryPlanner::MAX_EVENTS) as $event) {
            $row = $this->validate(is_array($event) ? $event : []);

            if ($row !== null) {
                $valid[] = $row;
            }
        }

        return $valid;
    }

    /**
     * Writes already-validated entries to the calendar.
     *
     * @param  list<array<string, mixed>>  $events  from validateAll()
     * @return array{created: int, first_date: ?CarbonImmutable}
     */
    public function store(array $events, string $planTitle, int $userId): array
    {
        $title = $this->text($planTitle, 120);
        $created = 0;
        $firstDate = null;

        foreach ($events as $row) {
            ItineraryEvent::create([
                'user_id' => $userId,
                'trip_id' => null,
                'order_id' => null,
                'source' => ItineraryEvent::SOURCE_AI,
                'category' => $row['category'],
                'title' => $row['title'],
                'location' => $row['location'],
                'event_date' => $row['date'],
                'start_time' => $row['time'],
                // Ties the entry back to the plan it came from, so a day
                // panel showing several plans stays readable.
                'notes' => $title !== '' ? "Planned: {$title}" : null,
            ]);

            $date = CarbonImmutable::parse($row['date']);
            if ($firstDate === null || $date->lt($firstDate)) {
                $firstDate = $date;
            }

            $created++;
        }

        return ['created' => $created, 'first_date' => $firstDate];
    }

    /**
     * @return array{date: string, time: ?string, category: string, title: string, location: ?string}|null
     */
    protected function validate(array $event): ?array
    {
        $title = $this->text($event['title'] ?? '', 191);
        $date = $this->date($event['date'] ?? null);

        // A entry with no title or no usable date can't be shown at all.
        if ($title === '' || $date === null) {
            return null;
        }

        $category = (string) ($event['category'] ?? '');

        return [
            'date' => $date,
            'time' => $this->time($event['time'] ?? null),
            // Falls back rather than rejecting: an unexpected category is
            // a colour problem, not a reason to lose the entry.
            'category' => in_array($category, ItineraryPlanner::CATEGORIES, true) ? $category : 'activity',
            'title' => $title,
            'location' => $this->text($event['location'] ?? '', 191) ?: null,
        ];
    }

    /**
     * Y-m-d, a real day, and not in the past — the same rule the booking
     * date picker enforces, so plans and bookings can't disagree about
     * what counts as a valid date.
     */
    protected function date(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $value)) {
            return null;
        }

        try {
            $parsed = CarbonImmutable::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        // Round-trip rejects a day that doesn't exist: "2026-02-31" parses,
        // but as March 3rd.
        if ($parsed->format('Y-m-d') !== $value || $parsed->lt(CarbonImmutable::today())) {
            return null;
        }

        return $value;
    }

    /** H:i, or null for an all-day entry. */
    protected function time(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', trim($value))) {
            return null;
        }

        return trim($value);
    }

    protected function text(mixed $value, int $max): string
    {
        return is_string($value) ? mb_substr(trim($value), 0, $max) : '';
    }
}
