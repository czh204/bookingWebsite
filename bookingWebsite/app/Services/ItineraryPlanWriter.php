<?php

namespace App\Services;

use App\Ai\Agents\ItineraryPlanner;
use App\Models\ItineraryEvent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

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
     * The longest trip a single plan is assumed to describe. Used only to
     * spot a mis-dated entry, not to cap the plan itself.
     */
    public const MAX_TRIP_DAYS = 60;

    public function __construct(protected ItinerarySchedule $schedule = new ItinerarySchedule) {}

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

        return $this->repairYearSlips($valid);
    }

    /**
     * Pulls back entries the model dated a year late.
     *
     * A trip that crosses new year's - or, far more often, one that simply
     * crosses from December into January or September into October - comes
     * back with the later days carrying the wrong year: "30 Sept 2026"
     * followed by "1 Oct 2027". Each date is individually valid, so
     * validate() has no reason to reject them, and the trip silently
     * scatters across two years with most of it a year out of view.
     *
     * The repair is deliberately narrow. An entry is only moved when
     * shifting its year back lands it inside a plausible trip window from
     * the earliest date, which means a genuine "book me next October" stays
     * exactly where the user asked for it.
     *
     * @param  list<array{date: string, ...}>  $events
     * @return list<array{date: string, ...}>
     */
    protected function repairYearSlips(array $events): array
    {
        if ($events === []) {
            return $events;
        }

        $start = CarbonImmutable::parse(min(array_column($events, 'date')));

        foreach ($events as $index => $row) {
            $date = CarbonImmutable::parse($row['date']);

            // abs(): Carbon 3's diffInDays is signed, so a date a year
            // ahead reads as -366 and would pass an unsigned check.
            if (abs($date->diffInDays($start)) <= self::MAX_TRIP_DAYS) {
                continue;
            }

            // Try the same day and month in the trip's own year, and the
            // year after it, so a trip that really does cross new year
            // still resolves forwards rather than backwards.
            foreach ([$start->year, $start->year + 1] as $year) {
                $shifted = $date->setYear($year);

                if ($shifted->gte($start) && abs($shifted->diffInDays($start)) <= self::MAX_TRIP_DAYS) {
                    $events[$index]['date'] = $shifted->format('Y-m-d');
                    break;
                }
            }
        }

        // Re-sorted because a repaired entry can land before ones already
        // placed, and store() reports the first date of what it wrote.
        usort($events, fn (array $a, array $b) => [$a['date'], $a['time'] ?? ''] <=> [$b['date'], $b['time'] ?? '']);

        return $events;
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
        $dates = array_values(array_unique(array_column($events, 'date')));

        return DB::transaction(function () use ($events, $title, $userId, $dates) {
            // Replanning a day replaces it. Appending produced a day
            // holding two plans at once - the discarded one and the new
            // one interleaved by time - which reads as one incoherent
            // itinerary rather than a revision.
            //
            // Scoped to source = 'ai': booking entries are backed by a paid
            // order, so the planner has no business deleting them.
            // whereDate(), not whereIn(): the column holds a full datetime
            // ("2026-09-12 00:00:00"), so matching it against a bare
            // "2026-09-12" deletes nothing and the day silently appends
            // instead of being replaced.
            $replaced = ItineraryEvent::query()
                ->where('user_id', $userId)
                ->where('source', ItineraryEvent::SOURCE_AI)
                ->where(function ($query) use ($dates) {
                    foreach ($dates as $date) {
                        $query->orWhereDate('event_date', $date);
                    }
                })
                ->delete();

            $created = 0;
            $skipped = 0;
            $firstDate = null;
            $written = [];

            // Days that already carry a booked flight. The prompt tells the
            // model not to invent a second one, and it does anyway - so the
            // rule is enforced here as well. A duplicated flight is the
            // worst kind of wrong entry: two departure times for the same
            // journey, one of them fictional.
            $bookedFlightDates = ItineraryEvent::query()
                ->where('user_id', $userId)
                ->where('source', ItineraryEvent::SOURCE_BOOKING)
                ->where('category', 'flight')
                ->where(function ($query) use ($dates) {
                    foreach ($dates as $date) {
                        $query->orWhereDate('event_date', $date);
                    }
                })
                ->pluck('event_date')
                ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString())
                ->all();

            foreach ($events as $row) {
                if ($row['category'] === 'flight' && in_array($row['date'], $bookedFlightDates, true)) {
                    $skipped++;

                    continue;
                }

                // Checked after the day is cleared, so a replan only has to
                // avoid the bookings that survived and the entries this
                // same plan has already placed.
                $clash = $this->schedule->conflict(
                    $userId, $row['date'], $row['time'], $row['end'] ?? null,
                    alsoBooked: $written,
                );

                if ($clash !== null) {
                    $skipped++;

                    continue;
                }

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
                    'end_time' => $row['end'] ?? null,
                    // Ties the entry back to the plan it came from, so a day
                    // panel showing several plans stays readable.
                    'notes' => $title !== '' ? "Planned: {$title}" : null,
                ]);

                $written[] = [
                    'date' => $row['date'],
                    'time' => $row['time'],
                    'end' => $row['end'] ?? null,
                    'title' => $row['title'],
                ];

                $date = CarbonImmutable::parse($row['date']);
                if ($firstDate === null || $date->lt($firstDate)) {
                    $firstDate = $date;
                }

                $created++;
            }

            return [
                'created' => $created,
                'skipped' => $skipped,
                'replaced' => $replaced,
                'first_date' => $firstDate,
            ];
        });
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
            'end' => $this->time($event['end_time'] ?? $event['end'] ?? null),
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
