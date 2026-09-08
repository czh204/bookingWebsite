<?php

namespace App\Services;

use App\Models\Flight;
use Illuminate\Support\Collection;

/**
 * Single source of truth for flight filtering.
 *
 * Both FlightController (the /flights page) and the AI SearchFlights tool
 * go through here, so the chatbot can never disagree with what the page
 * shows. Mirrors HotelSearch, which does the same job for /hotels.
 */
class FlightSearch
{
    public const STOP_BUCKETS = ['non-stop', '1-stop', '2-plus'];

    public const DEPARTURE_WINDOWS = ['night', 'morning', 'afternoon', 'evening'];

    public const SORTS = ['price_asc', 'price_desc', 'duration', 'departure'];

    /**
     * Full airport names for the modal's route bar. Not a flights column —
     * this is a static lookup rather than data worth storing per row.
     */
    public const AIRPORT_NAMES = [
        'JFK' => 'John F. Kennedy International Airport',
        'LHR' => 'London Heathrow Airport',
        'CDG' => 'Paris Charles de Gaulle Airport',
        'DXB' => 'Dubai International Airport',
        'SIN' => 'Singapore Changi Airport',
        'FRA' => 'Frankfurt Airport',
        'DOH' => 'Hamad International Airport',
    ];

    /**
     * Display labels for the three fare classes. Every flight renders all
     * three, so these have to exist even for a class the flight doesn't
     * sell — the flight_fares table only stores the ones it does.
     */
    public const FARE_CLASS_NAMES = [
        'economy' => 'Economy',
        'premium_economy' => 'Premium Economy',
        'business' => 'Business',
    ];

    /**
     * Filter and sort every flight against the given criteria.
     *
     * Recognised keys: from, to, min_price, max_price, stops[], airline,
     * departure_time[], sort. Any key omitted simply doesn't filter.
     */
    public function search(array $filters = []): Collection
    {
        $flights = $this->applyFilters($this->loadFlights(), $filters);

        return $this->applySort($flights, (string) ($filters['sort'] ?? 'price_asc'));
    }

    /**
     * Builds a link into the real /flights page carrying the same filters,
     * so a chat answer can hand the user off to the browsable UI.
     */
    public function deepLink(array $filters = []): string
    {
        $query = array_filter([
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
            'min_price' => $filters['min_price'] ?? null,
            'max_price' => $filters['max_price'] ?? null,
            'stops' => $filters['stops'] ?? null,
            'airline' => $filters['airline'] ?? null,
            'departure_time' => $filters['departure_time'] ?? null,
        ], fn ($value) => $value !== null && $value !== '' && $value !== []);

        return route('flights.index', $query, absolute: false);
    }

    /**
     * Loads every flight from the database, eager-loading whichever fare
     * classes (see the create_flight_fares_table migration) exist for each
     * one, seeded by database/seeders/FlightSeeder.php.
     */
    public function loadFlights(): Collection
    {
        return Flight::with('fares')
            ->get()
            ->map(fn (Flight $flight) => $this->mapFlight($flight));
    }

    /**
     * Flattens a Flight model (plus its loaded fares relation) into the
     * plain object shape the view/JS expect. Built from
     * attributesToArray() rather than the model itself so the JSON we
     * embed in data-flight="" isn't clobbered by Eloquent's
     * auto-serialization of the loaded `fares` relation.
     */
    protected function mapFlight(Flight $flight): object
    {
        $data = $flight->attributesToArray();

        // Times come back from MySQL as H:i:s; the cards show H:i.
        $data['departure_time'] = substr((string) $flight->departure_time, 0, 5);
        $data['arrival_time'] = substr((string) $flight->arrival_time, 0, 5);
        $data['stops'] = (int) $flight->stops;

        $data['origin_airport_name'] = self::AIRPORT_NAMES[$flight->origin_code] ?? $flight->origin_city.' Airport';
        $data['destination_airport_name'] = self::AIRPORT_NAMES[$flight->destination_code] ?? $flight->destination_city.' Airport';
        $data['stop_label'] = $this->stopLabel((int) $flight->stops);
        $data['fares'] = $this->buildFares($flight);
        $data['highlight'] = $this->buildHighlight($flight);

        return (object) $data;
    }

    public function stopLabel(int $stops): string
    {
        return match ($stops) {
            0 => 'Non-stop',
            1 => '1 Stop',
            default => $stops.' Stops',
        };
    }

    /**
     * All three fare classes always render; one with no matching
     * flight_fares row for this flight shows as unavailable rather than
     * being fabricated.
     */
    protected function buildFares(Flight $flight): array
    {
        $existing = $flight->fares->keyBy('fare_class');

        return collect(self::FARE_CLASS_NAMES)->map(function (string $name, string $key) use ($existing) {
            $fare = $existing->get($key);

            if (! $fare) {
                return [
                    'key' => $key,
                    'name' => $name,
                    'badge' => null,
                    'available' => false,
                    'price' => null,
                    'checked_bag' => null,
                    'carry_on' => null,
                    'perks' => [],
                    'policy' => null,
                ];
            }

            return [
                'key' => $key,
                'name' => $name,
                'badge' => $fare->badge,
                'available' => true,
                'price' => (int) round((float) $fare->price),
                'checked_bag' => $fare->checked_bag_kg ? "{$fare->checked_bag_kg} kg checked bag" : null,
                'carry_on' => $fare->carry_on,
                // seat_info leads the perk list, matching how the card reads.
                'perks' => array_values(array_filter(array_merge([$fare->seat_info], $fare->perks ?? []))),
                'policy' => $this->buildPolicy($fare),
            ];
        })->values()->all();
    }

    /**
     * Turns the fare's refundable / change_fee_from columns into the
     * one-line policy the fare card shows.
     */
    protected function buildPolicy(\App\Models\FlightFare $fare): string
    {
        $fee = $fare->change_fee_from === null ? null : (int) round((float) $fare->change_fee_from);
        $changes = $fee ? "Changes from \${$fee}" : 'Free changes';

        return ($fare->refundable ? 'Fully refundable' : 'Non-refundable')." · {$changes}";
    }

    protected function buildHighlight(Flight $flight): string
    {
        $service = match ((int) $flight->stops) {
            0 => 'Direct overnight service',
            1 => 'One-stop service',
            default => 'Multi-stop service',
        };

        $tierNames = $flight->fares
            ->pluck('fare_class')
            ->reverse()
            ->map(fn ($tier) => match ($tier) {
                'business' => 'business',
                'premium_economy' => 'premium economy',
                default => 'economy',
            })
            ->values();

        $cabins = $tierNames->count() > 1
            ? $tierNames->slice(0, -1)->implode(', ').', and '.$tierNames->last()
            : $tierNames->first();

        return "{$service} to {$flight->destination_city}. Enjoy {$cabins} cabins with modern amenities.";
    }

    protected function applyFilters(Collection $flights, array $filters): Collection
    {
        $from = trim((string) ($filters['from'] ?? ''));
        $to = trim((string) ($filters['to'] ?? ''));
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;
        $stops = (array) ($filters['stops'] ?? []);
        $airline = trim((string) ($filters['airline'] ?? ''));
        $departureWindows = (array) ($filters['departure_time'] ?? []);

        return $flights
            ->when($from !== '', fn ($c) => $c->filter(fn ($f) => str_contains(strtolower($f->origin_city), strtolower($from))
                || str_contains(strtolower($f->origin_code), strtolower($from))))
            ->when($to !== '', fn ($c) => $c->filter(fn ($f) => str_contains(strtolower($f->destination_city), strtolower($to))
                || str_contains(strtolower($f->destination_code), strtolower($to))))
            ->when(is_numeric($minPrice), fn ($c) => $c->filter(fn ($f) => $f->price >= (float) $minPrice))
            ->when(is_numeric($maxPrice), fn ($c) => $c->filter(fn ($f) => $f->price <= (float) $maxPrice))
            ->when(count($stops) > 0, fn ($c) => $c->filter(fn ($f) => in_array($this->stopBucket((int) $f->stops), $stops, true)))
            ->when($airline !== '', fn ($c) => $c->filter(fn ($f) => str_contains(strtolower($f->airline_name), strtolower($airline))
                || str_contains(strtolower($f->airline_code), strtolower($airline))
                || str_contains(strtolower($f->flight_number), strtolower($airline))))
            ->when(count($departureWindows) > 0, fn ($c) => $c->filter(
                fn ($f) => in_array($this->departureWindow($f->departure_time), $departureWindows, true)
            ))
            ->values();
    }

    public function stopBucket(int $stops): string
    {
        return match (true) {
            $stops === 0 => 'non-stop',
            $stops === 1 => '1-stop',
            default => '2-plus',
        };
    }

    /**
     * Buckets a departure time into one of the four filter windows.
     *
     * Every range is stated explicitly: an earlier version let hours 0-5
     * fall through a `default` branch into "evening", so a 02:45 departure
     * was filed under "Evening (6pm-12am)" and there was no way to filter
     * for early-morning flights at all.
     */
    public function departureWindow(string $departureTime): string
    {
        $hour = (int) explode(':', $departureTime)[0];

        return match (true) {
            $hour >= 0 && $hour < 6 => 'night',
            $hour >= 6 && $hour < 12 => 'morning',
            $hour >= 12 && $hour < 18 => 'afternoon',
            default => 'evening',
        };
    }

    protected function applySort(Collection $flights, string $sort): Collection
    {
        return match ($sort) {
            'price_desc' => $flights->sortByDesc('price')->values(),
            'duration' => $flights->sortBy('duration_minutes')->values(),
            'departure' => $flights->sortBy('departure_time')->values(),
            default => $flights->sortBy('price')->values(),
        };
    }
}
