<?php

namespace App\Services;

use App\Models\Attraction;
use App\Models\Flight;
use App\Models\Hotel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Session-backed shopping cart.
 *
 * Nothing here trusts a price sent by the browser. A request only names
 * *what* is being added (type + record id + option key); the price, title
 * and description are all re-read from the database. That way a tampered
 * form can't buy a $780 business fare for $1.
 */
class Cart
{
    protected const SESSION_KEY = 'cart.lines';

    protected const PROMO_KEY = 'cart.promo';

    /** Flat booking fee, charged once per order rather than per item. */
    public const SERVICE_FEE = 25.0;

    public const TAX_RATE = 0.10;

    /**
     * Promo codes and what they take off the subtotal. 'percent' is a
     * fraction of the subtotal, 'amount' is a flat dollar figure.
     */
    protected const PROMOS = [
        'TRAVEL10' => ['type' => 'percent', 'value' => 0.10, 'label' => '10% off'],
        'SAVE50' => ['type' => 'amount', 'value' => 50.0, 'label' => '$50 off'],
    ];

    /** @return Collection<int, object> */
    public function lines(): Collection
    {
        return collect(session(self::SESSION_KEY, []))
            ->map(fn (array $line) => (object) $line)
            ->values();
    }

    public function count(): int
    {
        return (int) $this->lines()->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->lines()->isEmpty();
    }

    /**
     * Adds an item, resolving its real details from the database.
     *
     * Returns null when the requested option doesn't exist — an
     * unavailable fare class, a room type the hotel doesn't offer — or
     * when the booking date is missing, malformed or in the past, so the
     * caller can reject the request rather than adding a phantom line.
     */
    public function add(string $type, int $itemId, string $optionKey, ?string $bookingDate, int $quantity = 1): ?object
    {
        // Re-checked here, not just in the picker: `min` on a date input
        // is a hint to the browser, not a rule a POST has to respect.
        $date = $this->normaliseBookingDate($bookingDate);

        if ($date === null) {
            return null;
        }

        $resolved = match ($type) {
            'flight' => $this->resolveFlight($itemId, $optionKey),
            'hotel' => $this->resolveHotel($itemId, $optionKey),
            'attraction' => $this->resolveAttraction($itemId, $optionKey),
            default => null,
        };

        if ($resolved === null) {
            return null;
        }

        $resolved['booking_date'] = $date;

        $lines = session(self::SESSION_KEY, []);
        // Same item + same option + same date is one line with a bumped
        // quantity. The date is part of the key, so the same flight on two
        // different days is correctly two separate lines.
        $lineId = $this->lineId($type, $itemId, $optionKey, $date);

        if (isset($lines[$lineId])) {
            $lines[$lineId]['quantity'] += $quantity;
        } else {
            $lines[$lineId] = $resolved + ['line_id' => $lineId, 'quantity' => $quantity];
        }

        session([self::SESSION_KEY => $lines]);

        return (object) $lines[$lineId];
    }

    /**
     * Accepts a Y-m-d string on or after today, returning it normalised.
     * Null means "unusable" — absent, wrong shape, a date that doesn't
     * exist (2026-02-31), or in the past.
     */
    protected function normaliseBookingDate(?string $date): ?string
    {
        if (! is_string($date) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $date)) {
            return null;
        }

        try {
            $parsed = CarbonImmutable::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        // Round-trip check rejects a day that doesn't exist: "2026-02-31"
        // parses happily, but as March 3rd.
        if ($parsed->format('Y-m-d') !== $date) {
            return null;
        }

        // No backdating. Today itself is allowed — a same-day booking is
        // a normal thing to want.
        if ($parsed->lt(CarbonImmutable::today())) {
            return null;
        }

        return $date;
    }

    public function remove(string $lineId): bool
    {
        $lines = session(self::SESSION_KEY, []);

        if (! isset($lines[$lineId])) {
            return false;
        }

        unset($lines[$lineId]);
        session([self::SESSION_KEY => $lines]);

        // An empty cart shouldn't keep holding a discount for the next item.
        if ($lines === []) {
            $this->clearPromo();
        }

        return true;
    }

    public function clear(): void
    {
        session()->forget([self::SESSION_KEY, self::PROMO_KEY]);
    }

    public function applyPromo(string $code): bool
    {
        $code = strtoupper(trim($code));

        if (! isset(self::PROMOS[$code])) {
            return false;
        }

        session([self::PROMO_KEY => $code]);

        return true;
    }

    public function clearPromo(): void
    {
        session()->forget(self::PROMO_KEY);
    }

    public function promoCode(): ?string
    {
        return session(self::PROMO_KEY);
    }

    public function promoLabel(): ?string
    {
        $code = $this->promoCode();

        return $code ? self::PROMOS[$code]['label'] : null;
    }

    /**
     * The order summary figures, in the order the panel shows them.
     * Rounded to cents at each step so the displayed lines actually add
     * up to the displayed total.
     */
    public function totals(): object
    {
        $lines = $this->lines();
        $subtotal = round($lines->sum(fn ($line) => $line->unit_price * $line->quantity), 2);
        $discount = round($this->discountFor($subtotal), 2);
        $taxable = max(0, $subtotal - $discount);

        // No items means no service fee — an empty cart totals zero, not $25.
        $serviceFee = $lines->isEmpty() ? 0.0 : self::SERVICE_FEE;
        $tax = round($taxable * self::TAX_RATE, 2);

        return (object) [
            'item_count' => (int) $lines->sum('quantity'),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'service_fee' => $serviceFee,
            'tax' => $tax,
            'total' => round($taxable + $serviceFee + $tax, 2),
        ];
    }

    protected function discountFor(float $subtotal): float
    {
        $code = $this->promoCode();

        if (! $code) {
            return 0.0;
        }

        $promo = self::PROMOS[$code];

        // Never discount below zero, however large a flat code is.
        return $promo['type'] === 'percent'
            ? $subtotal * $promo['value']
            : min($promo['value'], $subtotal);
    }

    protected function lineId(string $type, int $itemId, string $optionKey, string $bookingDate): string
    {
        return substr(md5("{$type}:{$itemId}:{$optionKey}:{$bookingDate}"), 0, 16);
    }

    /**
     * The three resolvers below are the only place a price enters the
     * cart, and each reads it from the option's own row — a fare class
     * the flight doesn't sell has no row, so it returns null.
     */
    protected function resolveFlight(int $id, string $fareClass): ?array
    {
        $flight = Flight::with('fares')->find($id);
        $fare = $flight?->fares->firstWhere('fare_class', $fareClass);

        if (! $fare) {
            return null;
        }

        $names = ['economy' => 'Economy', 'premium_economy' => 'Premium Economy', 'business' => 'Business'];
        $stopLabel = match ((int) $flight->stops) {
            0 => 'Non-stop',
            1 => '1 Stop',
            default => $flight->stops.' Stops',
        };

        return [
            'type' => 'flight',
            'item_id' => $flight->id,
            'option_key' => $fareClass,
            'icon' => 'bi-airplane',
            'title' => "{$flight->origin_code} → {$flight->destination_code} (".($names[$fareClass] ?? $fareClass).')',
            'subtitle' => "Stops: {$stopLabel}",
            'meta' => "{$flight->airline_name} · ".substr((string) $flight->departure_time, 0, 5).' → '.substr((string) $flight->arrival_time, 0, 5),
            'unit_price' => (float) $fare->price,
        ];
    }

    protected function resolveHotel(int $id, string $roomClass): ?array
    {
        $hotel = Hotel::with('rooms')->find($id);
        $room = $hotel?->rooms->firstWhere('room_class', $roomClass);

        if (! $room) {
            return null;
        }

        return [
            'type' => 'hotel',
            'item_id' => $hotel->id,
            'option_key' => $roomClass,
            'icon' => 'bi-building',
            'title' => "{$hotel->name} ({$room->name})",
            'subtitle' => $room->bed_info ? "Bed: {$room->bed_info}" : 'Room booking',
            // MySQL hands TIME back as H:i:s; the cart line shows H:i.
            'meta' => "{$hotel->city} · Check-in ".substr((string) $hotel->check_in_time, 0, 5),
            'unit_price' => (float) $room->price_per_night,
        ];
    }

    protected function resolveAttraction(int $id, string $slotKey): ?array
    {
        $attraction = Attraction::with('timeSlots')->find($id);
        $slot = $attraction?->timeSlots->firstWhere('slot_key', $slotKey);

        if (! $slot) {
            return null;
        }

        return [
            'type' => 'attraction',
            'item_id' => $attraction->id,
            'option_key' => $slotKey,
            'icon' => 'bi-ticket-perforated',
            'title' => "{$attraction->title} ({$slot->time_label})",
            'subtitle' => "Duration: {$attraction->duration_label}",
            'meta' => "{$attraction->location_label} · {$attraction->category}",
            'unit_price' => (float) $slot->price,
        ];
    }
}
