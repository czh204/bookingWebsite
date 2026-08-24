<?php

namespace App\Ai\Tools;

use App\Services\HotelSearch;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Lets the support assistant search real hotel inventory.
 *
 * The model's job is extracting parameters from natural language; the
 * actual filtering runs through App\Services\HotelSearch — the same
 * service the /hotels page uses — so chat answers can never disagree
 * with what the user sees when they click through.
 */
class SearchHotels implements Tool
{
    public function __construct(protected HotelSearch $search = new HotelSearch) {}

    /**
     * Without this, Laravel\Ai\Tools\ToolNameResolver falls back to
     * class_basename() and exposes the tool as "SearchHotels".
     */
    public function name(): string
    {
        return 'search_hotels';
    }

    public function description(): Stringable|string
    {
        return 'Search Voyagr\'s hotel inventory. Call this whenever the user describes a place '
            .'they want to stay, a budget, a star rating, or amenities they need — even loosely '
            .'(e.g. "somewhere in Paris under $500 with a pool"). Returns matching hotels with '
            .'their nightly rate, star rating, city and amenities, plus a link to the full '
            .'results page. Do not invent hotels or prices; only report what this tool returns.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'destination' => $schema->string()
                ->description('City, country, or hotel name to match. Omit to search everywhere.'),
            'min_price' => $schema->integer()->min(0)
                ->description('Lowest acceptable nightly price in MYR.'),
            'max_price' => $schema->integer()->min(0)
                ->description('Highest acceptable nightly price in MYR.'),
            'star_rating' => $schema->array()->items($schema->integer()->min(1)->max(5))
                ->description('Acceptable star ratings, e.g. [5] or [4,5].'),
            'amenities' => $schema->array()->items($schema->string()->enum(HotelSearch::AMENITIES))
                ->description('Amenities the hotel must have ALL of. Valid values: '.implode(', ', HotelSearch::AMENITIES)),
            'sort' => $schema->string()->enum(HotelSearch::SORTS)
                ->description('Result ordering. Use price_asc when the user asks for the cheapest.'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $filters = array_filter([
            'destination' => $request['destination'] ?? null,
            'min_price' => $request['min_price'] ?? null,
            'max_price' => $request['max_price'] ?? null,
            'star_rating' => $request['star_rating'] ?? null,
            'amenities' => $request['amenities'] ?? null,
            'sort' => $request['sort'] ?? null,
        ], fn ($value) => $value !== null && $value !== []);

        $hotels = $this->search->search($filters);

        if ($hotels->isEmpty()) {
            return "No hotels matched those criteria. Suggest relaxing the budget, star rating, "
                ."or amenity list. Browse all hotels: {$this->search->deepLink()}";
        }

        $lines = $hotels->take(5)->map(function ($hotel) {
            $rooms = collect($hotel->rooms)
                ->filter(fn ($room) => $room['available'])
                ->map(fn ($room) => "{$room['name']} \${$room['price']}")
                ->implode(', ');

            return sprintf(
                '- %s (%s, %s) — $%d/night, %d-star. Amenities: %s. Rooms: %s',
                $hotel->name,
                $hotel->city,
                $hotel->country,
                (int) round((float) $hotel->price_per_night),
                $hotel->star_rating,
                implode(', ', $hotel->amenities),
                $rooms !== '' ? $rooms : 'none listed',
            );
        })->implode("\n");

        $total = $hotels->count();
        $shown = min(5, $total);

        return "Found {$total} matching hotel(s); showing {$shown}:\n{$lines}\n\n"
            ."Full results with filters applied: {$this->search->deepLink($filters)}";
    }
}
