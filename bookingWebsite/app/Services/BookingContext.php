<?php

namespace App\Services;

use App\Models\Attraction;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Airports;
use Carbon\CarbonImmutable;

/**
 * Describes what a user has already paid for, as text the planner can read.
 *
 * Without this the planner is planning blind: it doesn't know a flight
 * lands at 16:30, so it puts a temple visit at 15:00 on arrival day and a
 * dinner in the wrong city. The bookings are the fixed points of a trip -
 * everything the model invents has to fit around them.
 *
 * Flights carry the detail that matters most. A booking line on its own is
 * just a title and a date; the Flight record behind it knows the route, the
 * duration and the arrival time, which is what lets the model reason about
 * how much of a day is actually spent travelling.
 */
class BookingContext
{
    /**
     * How far ahead to describe. A planner request is about an upcoming
     * trip, so a booking a year out is noise in the prompt.
     */
    public const HORIZON_DAYS = 400;

    /**
     * The user's upcoming bookings as prompt text, or an empty string when
     * there are none.
     *
     * Empty rather than "no bookings" on purpose: a sentence saying there
     * is nothing invites the model to comment on it, and the caller can
     * simply omit the section instead.
     */
    public function forPrompt(?int $userId): string
    {
        if ($userId === null) {
            return '';
        }

        $lines = $this->lines($userId);

        if ($lines === []) {
            return '';
        }

        return "The traveller has already booked and paid for the following. These are "
            ."fixed - plan around them, never duplicate them, and never place an entry "
            ."that overlaps one:\n"
            .implode("\n", $lines)."\n\n"
            ."Times are local to the place they happen in: a departure time is local to "
            ."the origin, an arrival time is local to the destination. Do not convert "
            ."them, and do not add the flight duration to the departure time yourself - "
            ."the arrival time given is already the local landing time.";
    }

    /**
     * One line per booked item, oldest first.
     *
     * @return list<string>
     */
    protected function lines(int $userId): array
    {
        $today = CarbonImmutable::today();

        $orders = Order::query()
            ->where('user_id', $userId)
            ->where('status', 'confirmed')
            // A refunded order is no longer a fixed point of the trip.
            ->whereNull('refunded_at')
            ->with('items')
            ->get();

        $items = $orders
            ->flatMap(fn (Order $order) => $order->items)
            ->filter(function (OrderItem $item) use ($today) {
                $date = $item->booking_date;

                return $date !== null
                    && $date->gte($today)
                    && $date->lte($today->addDays(self::HORIZON_DAYS));
            })
            ->sortBy(fn (OrderItem $item) => [$item->booking_date->toDateString(), $item->type]);

        return $items->map(fn (OrderItem $item) => $this->describe($item))->values()->all();
    }

    protected function describe(OrderItem $item): string
    {
        $date = $item->booking_date->format('Y-m-d');

        return match ($item->type) {
            'flight' => $this->describeFlight($item, $date),
            'hotel' => $this->describeHotel($item, $date),
            'attraction' => $this->describeAttraction($item, $date),
            default => "- {$date} {$item->title}",
        };
    }

    /**
     * The one line worth getting right.
     *
     * Route, both local times and the duration, so the model can see how
     * much of the day the flight eats and what is left of it on arrival.
     */
    protected function describeFlight(OrderItem $item, string $date): string
    {
        // find(), not findOrFail(): a receipt outlives the record it points
        // at, and a deleted flight must not break planning.
        $flight = Flight::find($item->item_id);

        if ($flight === null) {
            return "- {$date} FLIGHT: {$item->title}";
        }

        $depart = substr((string) $flight->departure_time, 0, 5);
        $arrive = substr((string) $flight->arrival_time, 0, 5);
        $duration = $this->duration((int) $flight->duration_minutes);

        // Real offsets, resolved for the travel date so summer time is
        // right, rather than leaving the model to recall which zone a city
        // is in - the part it is least reliable at.
        $from = Airports::offset($flight->origin_code, $date);
        $to = Airports::offset($flight->destination_code, $date);
        $zones = $from && $to ? " Origin is {$from}, destination is {$to}." : '';

        return "- {$date} FLIGHT {$flight->origin_city} ({$flight->origin_code}) to "
            ."{$flight->destination_city} ({$flight->destination_code}): departs {$depart} "
            ."local, arrives {$arrive} local, {$duration} in the air.".$zones;
    }

    protected function describeHotel(OrderItem $item, string $date): string
    {
        $hotel = Hotel::find($item->item_id);
        $where = $hotel ? "{$hotel->name}, {$hotel->city}" : $item->title;
        $checkIn = $hotel ? substr((string) $hotel->check_in_time, 0, 5) : null;

        return "- {$date} HOTEL: {$where}"
            .($checkIn ? " (check-in from {$checkIn} local)" : '');
    }

    protected function describeAttraction(OrderItem $item, string $date): string
    {
        $attraction = Attraction::find($item->item_id);
        $where = $attraction ? "{$attraction->name}, {$attraction->city}" : $item->title;

        return "- {$date} BOOKED ACTIVITY: {$where}"
            .($item->meta ? " ({$item->meta})" : '');
    }

    /** "8h 30m", or "45m" for anything under the hour. */
    protected function duration(int $minutes): string
    {
        if ($minutes <= 0) {
            return 'duration unknown';
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $hours === 0 ? "{$rest}m" : trim("{$hours}h ".($rest > 0 ? "{$rest}m" : ''));
    }
}
