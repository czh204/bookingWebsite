<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FlightController extends Controller
{
    protected const PER_PAGE = 10;

    public function index(Request $request)
    {
        $flights = $this->loadFlights();

        $airlines = $flights->pluck('airline_name')->unique()->sort()->values();

        $flights = $this->applyFilters($flights, $request);
        $flights = $this->applySort($flights, $request->string('sort', 'price_asc')->toString());

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paged = new LengthAwarePaginator(
            $flights->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values(),
            $flights->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('flights.index', [
            'flights' => $paged,
            'airlines' => $airlines,
        ]);
    }

    /**
     * Full airport names for the modal's route bar. Not a flights column —
     * this is a static lookup rather than data worth storing per row.
     */
    protected const AIRPORT_NAMES = [
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
    protected const FARE_CLASS_NAMES = [
        'economy' => 'Economy',
        'premium_economy' => 'Premium Economy',
        'business' => 'Business',
    ];

    /**
     * Loads every flight from the database, eager-loading whichever fare
     * classes (see the create_flight_fares_table migration) exist for each
     * one, seeded by database/seeders/FlightSeeder.php.
     */
    protected function loadFlights(): Collection
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
        $data['stop_label'] = match ((int) $flight->stops) {
            0 => 'Non-stop',
            1 => '1 Stop',
            default => $flight->stops.' Stops',
        };
        $data['fares'] = $this->buildFares($flight);
        $data['highlight'] = $this->buildHighlight($flight);

        return (object) $data;
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

    protected function applyFilters(Collection $flights, Request $request): Collection
    {
        $from = trim((string) $request->query('from'));
        $to = trim((string) $request->query('to'));
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $stops = (array) $request->query('stops', []);
        $airline = trim((string) $request->query('airline'));
        $departureWindows = (array) $request->query('departure_time', []);

        return $flights
            ->when($from !== '', fn ($c) => $c->filter(fn ($f) => str_contains(strtolower($f->origin_city), strtolower($from))
                || str_contains(strtolower($f->origin_code), strtolower($from))))
            ->when($to !== '', fn ($c) => $c->filter(fn ($f) => str_contains(strtolower($f->destination_city), strtolower($to))
                || str_contains(strtolower($f->destination_code), strtolower($to))))
            ->when(is_numeric($minPrice), fn ($c) => $c->filter(fn ($f) => $f->price >= (float) $minPrice))
            ->when(is_numeric($maxPrice), fn ($c) => $c->filter(fn ($f) => $f->price <= (float) $maxPrice))
            ->when(count($stops) > 0, fn ($c) => $c->filter(function ($f) use ($stops) {
                $bucket = $f->stops === 0 ? 'non-stop' : ($f->stops === 1 ? '1-stop' : '2-plus');

                return in_array($bucket, $stops, true);
            }))
            ->when($airline !== '', fn ($c) => $c->filter(fn ($f) => str_contains(strtolower($f->airline_name), strtolower($airline))
                || str_contains(strtolower($f->airline_code), strtolower($airline))
                || str_contains(strtolower($f->flight_number), strtolower($airline))))
            ->when(count($departureWindows) > 0, fn ($c) => $c->filter(function ($f) use ($departureWindows) {
                return in_array($this->departureWindow($f->departure_time), $departureWindows, true);
            }))
            ->values();
    }

    /**
     * Buckets a departure time into one of the four filter windows.
     *
     * Every range is stated explicitly: an earlier version let hours 0-5
     * fall through a `default` branch into "evening", so a 02:45 departure
     * was filed under "Evening (6pm-12am)" and there was no way to filter
     * for early-morning flights at all.
     */
    protected function departureWindow(string $departureTime): string
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
