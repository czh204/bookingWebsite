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
 * A note on dates. The cart records *what* was bought, never *when* it is
 * for — there is no date picker feeding it. So only flights have a real
 * service date to use (flights.departure_date); a hotel stay or an
 * attraction visit falls back to the day the booking was made, which is
 * the only date those rows actually carry. Capturing travel dates at
 * add-to-cart time is what would fix that properly.
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
     * The date and time a purchased line belongs on, looked up from the
     * underlying record rather than stored on the order.
     *
     * @return array{date: string, time: ?string, category: string}
     */
    protected function resolveItem(OrderItem $item, Order $order): array
    {
        $bookedOn = CarbonImmutable::parse($order->created_at)->toDateString();

        return match ($item->type) {
            'flight' => $this->resolveFlight($item, $bookedOn),
            'hotel' => $this->resolveHotel($item, $bookedOn),
            'attraction' => $this->resolveAttraction($item, $bookedOn),
            default => ['date' => $bookedOn, 'time' => null, 'category' => 'activity'],
        };
    }

    protected function resolveFlight(OrderItem $item, string $bookedOn): array
    {
        // find(), not findOrFail(): a receipt outlives the record it points
        // at, so a deleted flight must not break the calendar.
        $flight = Flight::find($item->item_id);

        return [
            'date' => $flight?->departure_date?->toDateString() ?? $bookedOn,
            'time' => $flight ? substr((string) $flight->departure_time, 0, 5) : null,
            'category' => 'flight',
        ];
    }

    protected function resolveHotel(OrderItem $item, string $bookedOn): array
    {
        $hotel = Hotel::find($item->item_id);

        return [
            // Hotels store no stay date — only a daily check-in time.
            'date' => $bookedOn,
            'time' => $hotel ? substr((string) $hotel->check_in_time, 0, 5) : null,
            'category' => 'hotel',
        ];
    }

    protected function resolveAttraction(OrderItem $item, string $bookedOn): array
    {
        $slot = Attraction::find($item->item_id)
            ?->timeSlots
            ->firstWhere('slot_key', $item->option_key);

        return [
            'date' => $bookedOn,
            // Slots are stored for display ("8:00 AM"), so parse rather
            // than assume a 24-hour string.
            'time' => $slot ? $this->parseSlotTime($slot->time_label) : null,
            'category' => 'activity',
        ];
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
