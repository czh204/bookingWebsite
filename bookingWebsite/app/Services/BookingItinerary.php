<?php

namespace App\Services;

use App\Models\Attraction;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\ItineraryEvent;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonImmutable;

/**
 * Turns a paid order into calendar entries.
 *
 * The planner calendar reads itinerary_events and nothing else, so this is
 * what makes a booking show up there: one event per purchased line, tagged
 * source = 'booking' and pointed back at its order.
 *
 * Dates come from the order line's own booking_date — the day the
 * customer picked at add-to-cart time. Only the time of day is looked up
 * from the underlying record (a flight's departure time, a hotel's
 * check-in, an attraction's slot), since that isn't the customer's to
 * choose. Rows written before the date picker existed were backfilled to
 * their order date, so booking_date is always present.
 */
class BookingItinerary
{
    /**
     * Rebuilds an order's calendar entries from scratch.
     *
     * Idempotent by deletion first, so re-running it (a backfill, a repeat
     * checkout callback) can't leave duplicate dots on the calendar.
     */
    public function syncOrder(Order $order): int
    {
        ItineraryEvent::where('order_id', $order->id)->delete();

        // A guest order from before checkout required signing in has no
        // owner, so there is no calendar to put it on.
        if ($order->user_id === null) {
            return 0;
        }

        $order->loadMissing('items');
        $created = 0;

        foreach ($order->items as $item) {
            $resolved = $this->resolveItem($item, $order);

            ItineraryEvent::create([
                'user_id' => $order->user_id,
                'trip_id' => null,
                'order_id' => $order->id,
                'source' => ItineraryEvent::SOURCE_BOOKING,
                'category' => $resolved['category'],
                'title' => $item->title,
                'location' => $item->meta,
                'event_date' => $resolved['date'],
                'start_time' => $resolved['time'],
                'notes' => "Booking {$order->reference}",
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * The date and time a purchased line belongs on.
     *
     * The date is the customer's chosen booking_date. Only the time of day
     * is looked up, since that belongs to the product rather than the
     * booking.
     *
     * @return array{date: string, time: ?string, category: string}
     */
    protected function resolveItem(OrderItem $item, Order $order): array
    {
        // Legacy rows predating the picker fall back to the order date,
        // which is what the calendar used to assume for them anyway.
        $date = $item->booking_date?->toDateString()
            ?? CarbonImmutable::parse($order->created_at)->toDateString();

        return match ($item->type) {
            'flight' => ['date' => $date, 'time' => $this->flightTime($item), 'category' => 'flight'],
            'hotel' => ['date' => $date, 'time' => $this->hotelTime($item), 'category' => 'hotel'],
            'attraction' => ['date' => $date, 'time' => $this->attractionTime($item), 'category' => 'activity'],
            default => ['date' => $date, 'time' => null, 'category' => 'activity'],
        };
    }

    protected function flightTime(OrderItem $item): ?string
    {
        // find(), not findOrFail(): a receipt outlives the record it points
        // at, so a deleted flight must not break the calendar.
        $flight = Flight::find($item->item_id);

        return $flight ? substr((string) $flight->departure_time, 0, 5) : null;
    }

    protected function hotelTime(OrderItem $item): ?string
    {
        $hotel = Hotel::find($item->item_id);

        return $hotel ? substr((string) $hotel->check_in_time, 0, 5) : null;
    }

    protected function attractionTime(OrderItem $item): ?string
    {
        $slot = Attraction::find($item->item_id)
            ?->timeSlots
            ->firstWhere('slot_key', $item->option_key);

        // Slots are stored for display ("8:00 AM"), so parse rather than
        // assume a 24-hour string.
        return $slot ? $this->parseSlotTime($slot->time_label) : null;
    }

    protected function parseSlotTime(?string $label): ?string
    {
        if (! $label) {
            return null;
        }

        try {
            return CarbonImmutable::parse($label)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }
}
