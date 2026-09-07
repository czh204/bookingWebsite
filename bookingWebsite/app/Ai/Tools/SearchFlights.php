<?php

namespace App\Ai\Tools;

use App\Services\FlightSearch;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Lets the support assistant search real flight inventory.
 *
 * The model's job is extracting parameters from natural language; the
 * actual filtering runs through App\Services\FlightSearch — the same
 * service the /flights page uses — so chat answers can never disagree
 * with what the user sees when they click through.
 */
class SearchFlights implements Tool
{
    public function __construct(protected FlightSearch $search = new FlightSearch) {}

    /**
     * Without this, Laravel\Ai\Tools\ToolNameResolver falls back to
     * class_basename() and exposes the tool as "SearchFlights".
     */
    public function name(): string
    {
        return 'search_flights';
    }

    public function description(): Stringable|string
    {
        return 'Search Voyagr\'s flight inventory. Call this whenever the user describes a route, '
            .'a budget, an airline, how many stops they will accept, or what time of day they want '
            .'to leave — even loosely (e.g. "cheap non-stop from New York to London"). Returns '
            .'matching flights with airline, route, times, duration, stops and fare classes, plus '
            .'a link to the full results page. Do not invent flights, times, or prices; only '
            .'report what this tool returns.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'from' => $schema->string()
                ->description('Departure city or airport code, e.g. "New York" or "JFK". Omit to search all origins.'),
            'to' => $schema->string()
                ->description('Arrival city or airport code, e.g. "London" or "LHR". Omit to search all destinations.'),
            'min_price' => $schema->integer()->min(0)
                ->description('Lowest acceptable fare in MYR.'),
            'max_price' => $schema->integer()->min(0)
                ->description('Highest acceptable fare in MYR.'),
            'stops' => $schema->array()->items($schema->string()->enum(FlightSearch::STOP_BUCKETS))
                ->description('Acceptable stop counts. Use ["non-stop"] when the user asks for direct flights. '
                    .'Valid values: '.implode(', ', FlightSearch::STOP_BUCKETS)),
            'airline' => $schema->string()
                ->description('Airline name, airline code, or flight number to match, e.g. "Emirates" or "BA 178".'),
            'departure_time' => $schema->array()->items($schema->string()->enum(FlightSearch::DEPARTURE_WINDOWS))
                ->description('Preferred departure windows. night = 12am-6am, morning = 6am-12pm, '
                    .'afternoon = 12pm-6pm, evening = 6pm-12am.'),
            'sort' => $schema->string()->enum(FlightSearch::SORTS)
                ->description('Result ordering. Use price_asc for the cheapest, duration for the fastest.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $filters = array_filter([
            'from' => $request['from'] ?? null,
            'to' => $request['to'] ?? null,
            'min_price' => $request['min_price'] ?? null,
            'max_price' => $request['max_price'] ?? null,
            'stops' => $request['stops'] ?? null,
            'airline' => $request['airline'] ?? null,
            'departure_time' => $request['departure_time'] ?? null,
            'sort' => $request['sort'] ?? null,
        ], fn ($value) => $value !== null && $value !== []);

        $flights = $this->search->search($filters);

        if ($flights->isEmpty()) {
            return 'No flights matched those criteria. Suggest relaxing the budget, allowing stops, '
                ."or widening the departure time. Browse all flights: {$this->search->deepLink()}";
        }

        $lines = $flights->take(5)->map(function ($flight) {
            // Only fare classes this flight actually sells are listed, so
            // the assistant can't offer a cabin that has no row.
            $fares = collect($flight->fares)
                ->filter(fn ($fare) => $fare['available'])
                ->map(fn ($fare) => "{$fare['name']} \${$fare['price']}")
                ->implode(', ');

            return sprintf(
                '- %s %s: %s (%s) → %s (%s), %s–%s, %dh %dm, %s. From $%d. Fares: %s',
                $flight->airline_name,
                $flight->flight_number,
                $flight->origin_city,
                $flight->origin_code,
                $flight->destination_city,
                $flight->destination_code,
                $flight->departure_time,
                $flight->arrival_time,
                intdiv($flight->duration_minutes, 60),
                $flight->duration_minutes % 60,
                $flight->stop_label,
                (int) round((float) $flight->price),
                $fares !== '' ? $fares : 'none listed',
            );
        })->implode("\n");

        $total = $flights->count();
        $shown = min(5, $total);

        return "Found {$total} matching flight(s); showing {$shown}:\n{$lines}\n\n"
            ."Full results with filters applied: {$this->search->deepLink($filters)}";
    }
}
