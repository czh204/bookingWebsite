<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FlightController extends Controller
{
    protected const PER_PAGE = 10;

    public function index(Request $request)
    {
        $flights = $this->mockFlights();

        $flights = $this->applyFilters($flights, $request);
        $flights = $this->applySort($flights, $request->string('sort', 'price_asc')->toString());

        $airlines = $this->mockFlights()->pluck('airline_name')->unique()->sort()->values();

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
     * Stand-in for Flight::query()->...->paginate() until the `flights`
     * table (see database/migrations/*_create_flights_table.php) is
     * migrated and seeded. Field names match the Flight model's columns
     * so this method can be swapped for a real query later.
     */
    protected function mockFlights(): Collection
    {
        $rows = [
            ['airline_name' => 'British Airways', 'airline_code' => 'GB', 'flight_number' => 'BA 178', 'aircraft' => 'Boeing 777-300ER', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'LHR', 'destination_city' => 'London', 'departure_time' => '10:00', 'arrival_time' => '22:30', 'duration_minutes' => 450, 'stops' => 0, 'price' => 450],
            ['airline_name' => 'Air France', 'airline_code' => 'FR', 'flight_number' => 'AF 011', 'aircraft' => 'Airbus A350-900', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'CDG', 'destination_city' => 'Paris', 'departure_time' => '18:45', 'arrival_time' => '08:15', 'duration_minutes' => 450, 'stops' => 0, 'price' => 380],
            ['airline_name' => 'Emirates', 'airline_code' => 'AE', 'flight_number' => 'EK 202', 'aircraft' => 'Airbus A380-800', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'DXB', 'destination_city' => 'Dubai', 'departure_time' => '23:59', 'arrival_time' => '21:30', 'duration_minutes' => 811, 'stops' => 1, 'price' => 520],
            ['airline_name' => 'Singapore Airlines', 'airline_code' => 'SG', 'flight_number' => 'SQ 25', 'aircraft' => 'Airbus A350-900ULR', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'SIN', 'destination_city' => 'Singapore', 'departure_time' => '09:30', 'arrival_time' => '07:05', 'duration_minutes' => 1115, 'stops' => 0, 'price' => 780],
            ['airline_name' => 'British Airways', 'airline_code' => 'GB', 'flight_number' => 'BA 112', 'aircraft' => 'Boeing 787-9', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'LHR', 'destination_city' => 'London', 'departure_time' => '19:15', 'arrival_time' => '07:05', 'duration_minutes' => 410, 'stops' => 0, 'price' => 470],
            ['airline_name' => 'Air France', 'airline_code' => 'FR', 'flight_number' => 'AF 023', 'aircraft' => 'Boeing 777-200ER', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'CDG', 'destination_city' => 'Paris', 'departure_time' => '06:20', 'arrival_time' => '19:55', 'duration_minutes' => 575, 'stops' => 1, 'price' => 410],
            ['airline_name' => 'Emirates', 'airline_code' => 'AE', 'flight_number' => 'EK 204', 'aircraft' => 'Boeing 777-300ER', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'DXB', 'destination_city' => 'Dubai', 'departure_time' => '11:05', 'arrival_time' => '08:40', 'duration_minutes' => 815, 'stops' => 0, 'price' => 610],
            ['airline_name' => 'Singapore Airlines', 'airline_code' => 'SG', 'flight_number' => 'SQ 21', 'aircraft' => 'Airbus A350-900', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'SIN', 'destination_city' => 'Singapore', 'departure_time' => '21:40', 'arrival_time' => '06:15', 'duration_minutes' => 1055, 'stops' => 1, 'price' => 705],
            ['airline_name' => 'Lufthansa', 'airline_code' => 'LH', 'flight_number' => 'LH 400', 'aircraft' => 'Airbus A340-600', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'FRA', 'destination_city' => 'Frankfurt', 'departure_time' => '17:30', 'arrival_time' => '07:00', 'duration_minutes' => 450, 'stops' => 0, 'price' => 495],
            ['airline_name' => 'Qatar Airways', 'airline_code' => 'QR', 'flight_number' => 'QR 701', 'aircraft' => 'Boeing 777-300ER', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'DOH', 'destination_city' => 'Doha', 'departure_time' => '22:10', 'arrival_time' => '18:45', 'duration_minutes' => 755, 'stops' => 1, 'price' => 560],
            ['airline_name' => 'Lufthansa', 'airline_code' => 'LH', 'flight_number' => 'LH 402', 'aircraft' => 'Boeing 747-8', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'FRA', 'destination_city' => 'Frankfurt', 'departure_time' => '08:05', 'arrival_time' => '21:35', 'duration_minutes' => 450, 'stops' => 0, 'price' => 515],
            ['airline_name' => 'Qatar Airways', 'airline_code' => 'QR', 'flight_number' => 'QR 703', 'aircraft' => 'Airbus A350-1000', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'DOH', 'destination_city' => 'Doha', 'departure_time' => '13:20', 'arrival_time' => '10:00', 'duration_minutes' => 760, 'stops' => 2, 'price' => 505],
            ['airline_name' => 'British Airways', 'airline_code' => 'GB', 'flight_number' => 'BA 184', 'aircraft' => 'Airbus A380-800', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'LHR', 'destination_city' => 'London', 'departure_time' => '00:30', 'arrival_time' => '12:15', 'duration_minutes' => 465, 'stops' => 0, 'price' => 440],
            ['airline_name' => 'Air France', 'airline_code' => 'FR', 'flight_number' => 'AF 007', 'aircraft' => 'Airbus A220-300', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'CDG', 'destination_city' => 'Paris', 'departure_time' => '02:45', 'arrival_time' => '16:20', 'duration_minutes' => 455, 'stops' => 2, 'price' => 360],
        ];

        $airportNames = [
            'JFK' => 'John F. Kennedy International Airport',
            'LHR' => 'London Heathrow Airport',
            'CDG' => 'Paris Charles de Gaulle Airport',
            'DXB' => 'Dubai International Airport',
            'SIN' => 'Singapore Changi Airport',
            'FRA' => 'Frankfurt Airport',
            'DOH' => 'Hamad International Airport',
        ];

        // Which fare classes each flight offers. Flights not listed here
        // default to Economy only. Once `flight_fares` (see the
        // create_flight_fares_table migration) is seeded per flight, this
        // map — and buildFares() below — can be replaced by the flight's
        // real fares() relation.
        $fareTiersByFlightNumber = [
            'BA 178' => ['economy', 'premium_economy', 'business'],
            'BA 112' => ['economy', 'premium_economy'],
            'BA 184' => ['economy'],
            'AF 011' => ['economy', 'premium_economy', 'business'],
            'AF 023' => ['economy', 'premium_economy'],
            'AF 007' => ['economy'],
            'EK 202' => ['economy', 'premium_economy', 'business'],
            'EK 204' => ['economy', 'business'],
            'SQ 25' => ['economy', 'premium_economy', 'business'],
            'SQ 21' => ['economy', 'business'],
            'LH 400' => ['economy', 'premium_economy', 'business'],
            'LH 402' => ['economy', 'premium_economy'],
            'QR 701' => ['economy', 'premium_economy', 'business'],
            'QR 703' => ['economy', 'premium_economy'],
        ];

        return collect($rows)
            ->map(function (array $row, int $i) use ($airportNames, $fareTiersByFlightNumber) {
                $availableTiers = $fareTiersByFlightNumber[$row['flight_number']] ?? ['economy'];

                return (object) array_merge($row, [
                    'id' => $i + 1,
                    'departure_date' => now()->toDateString(),
                    'origin_airport_name' => $airportNames[$row['origin_code']] ?? $row['origin_city'].' Airport',
                    'destination_airport_name' => $airportNames[$row['destination_code']] ?? $row['destination_city'].' Airport',
                    'stop_label' => match ($row['stops']) {
                        0 => 'Non-stop',
                        1 => '1 Stop',
                        default => $row['stops'].' Stops',
                    },
                    'highlight' => $this->buildHighlight($row, $availableTiers),
                    'fares' => $this->buildFares($row['price'], $availableTiers),
                ]);
            });
    }

    /**
     * Fare class pricing/perks. In the real schema this becomes
     * FlightFare rows (fare_class, badge, price, perks, ...) belonging to
     * a Flight; the multipliers here just derive a plausible price per
     * tier from the flight's base (economy) fare.
     */
    protected function buildFares(float $basePrice, array $availableTiers): array
    {
        $tiers = [
            'economy' => [
                'name' => 'Economy',
                'badge' => null,
                'multiplier' => 1,
                'checked_bag' => '23 kg checked bag',
                'carry_on' => '7 kg carry-on',
                'perks' => ['Standard seat (pitch 31–32")', 'Meal & beverages', 'Personal IFE screen', 'USB charging port'],
                'policy' => 'Non-refundable · Changes from $75',
            ],
            'premium_economy' => [
                'name' => 'Premium Economy',
                'badge' => 'Popular',
                'multiplier' => 1.9,
                'checked_bag' => '32 kg checked bag',
                'carry_on' => '10 kg carry-on',
                'perks' => ['Wider seat (pitch 38")', 'Premium meals & wine list', 'Larger IFE screen', 'Priority boarding', 'Extra legroom'],
                'policy' => 'Refundable · Free date changes',
            ],
            'business' => [
                'name' => 'Business',
                'badge' => 'Best Value',
                'multiplier' => 3.8,
                'checked_bag' => '40 kg checked bag',
                'carry_on' => '2× carry-on bags',
                'perks' => ['Lie-flat bed (seat pitch 72")', 'Fine dining à la carte', 'Noise-cancelling headphones', 'Priority check-in & lounge', 'Limousine transfer (select routes)'],
                'policy' => 'Fully refundable · Free changes',
            ],
        ];

        return collect($tiers)->map(function (array $tier, string $key) use ($availableTiers, $basePrice) {
            $available = in_array($key, $availableTiers, true);

            return [
                'key' => $key,
                'name' => $tier['name'],
                'badge' => $tier['badge'],
                'available' => $available,
                'price' => $available ? (int) round($basePrice * $tier['multiplier']) : null,
                'checked_bag' => $tier['checked_bag'],
                'carry_on' => $tier['carry_on'],
                'perks' => $tier['perks'],
                'policy' => $tier['policy'],
            ];
        })->values()->all();
    }

    protected function buildHighlight(array $row, array $availableTiers): string
    {
        $service = match ($row['stops']) {
            0 => 'Direct overnight service',
            1 => 'One-stop service',
            default => 'Multi-stop service',
        };

        $tierNames = collect($availableTiers)
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

        return "{$service} to {$row['destination_city']}. Enjoy {$cabins} cabins with modern amenities.";
    }

    protected function applyFilters(Collection $flights, Request $request): Collection
    {
        $from = trim((string) $request->query('from'));
        $to = trim((string) $request->query('to'));
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $stops = (array) $request->query('stops', []);
        $airlines = (array) $request->query('airlines', []);
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
            ->when(count($airlines) > 0, fn ($c) => $c->filter(fn ($f) => in_array($f->airline_name, $airlines, true)))
            ->when(count($departureWindows) > 0, fn ($c) => $c->filter(function ($f) use ($departureWindows) {
                $hour = (int) explode(':', $f->departure_time)[0];
                $window = match (true) {
                    $hour >= 6 && $hour < 12 => 'morning',
                    $hour >= 12 && $hour < 18 => 'afternoon',
                    default => 'evening',
                };

                return in_array($window, $departureWindows, true);
            }))
            ->values();
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
