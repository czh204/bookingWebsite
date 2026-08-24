<?php

namespace App\Services;

use App\Models\Hotel;
use Illuminate\Support\Collection;

/**
 * Single source of truth for hotel filtering.
 *
 * Both HotelController (the /hotels page) and the AI SearchHotels tool go
 * through here, so the chatbot can never disagree with what the page shows.
 */
class HotelSearch
{
    public const AMENITIES = ['WiFi', 'Pool', 'Gym', 'Spa', 'Parking', 'Restaurant', 'Breakfast'];

    public const SORTS = ['recommended', 'price_asc', 'price_desc'];

    /**
     * Filter and sort every hotel against the given criteria.
     *
     * Recognised keys: destination, min_price, max_price, star_rating[],
     * amenities[], sort. Any key omitted simply doesn't filter.
     */
    public function search(array $filters = []): Collection
    {
        $hotels = $this->applyFilters($this->loadHotels(), $filters);

        return $this->applySort($hotels, (string) ($filters['sort'] ?? 'recommended'));
    }

    /**
     * Builds a link into the real /hotels page carrying the same filters,
     * so a chat answer can hand the user off to the browsable UI.
     */
    public function deepLink(array $filters = []): string
    {
        $query = array_filter([
            'destination' => $filters['destination'] ?? null,
            'min_price' => $filters['min_price'] ?? null,
            'max_price' => $filters['max_price'] ?? null,
            'star_rating' => $filters['star_rating'] ?? null,
            'amenities' => $filters['amenities'] ?? null,
        ], fn ($value) => $value !== null && $value !== '' && $value !== []);

        return route('hotels.index', $query);
    }

    /**
     * Loads every hotel from the database, eager-loading whichever room
     * tiers (see the create_hotel_rooms_table migration) exist for each
     * one, seeded by database/seeders/HotelSeeder.php.
     */
    public function loadHotels(): Collection
    {
        return Hotel::with('rooms')
            ->get()
            ->map(fn (Hotel $hotel) => $this->mapHotel($hotel));
    }

    /**
     * Flattens a Hotel model (plus its loaded rooms relation) into the
     * plain object shape the view/JS expect. Built from
     * attributesToArray() rather than the model itself so the JSON we
     * embed in data-hotel="" isn't clobbered by Eloquent's
     * auto-serialization of the loaded `rooms` relation.
     */
    protected function mapHotel(Hotel $hotel): object
    {
        $data = $hotel->attributesToArray();
        $data['rooms'] = $this->buildRooms($hotel);

        return (object) $data;
    }

    /**
     * The three room tiers always render; one with no matching
     * hotel_rooms row for this hotel shows as unavailable rather than
     * being fabricated.
     */
    protected function buildRooms(Hotel $hotel): array
    {
        $labels = [
            'classic' => ['name' => 'Classic Room', 'size_sqm' => 48, 'bed_info' => '1 King or 2 Twin'],
            'deluxe' => ['name' => 'Deluxe Room', 'size_sqm' => 62, 'bed_info' => '1 King'],
            'suite' => ['name' => 'Suite', 'size_sqm' => 130, 'bed_info' => '1 King'],
        ];

        $existing = $hotel->rooms->keyBy('room_class');

        return collect($labels)->map(function (array $meta, string $key) use ($existing) {
            $room = $existing->get($key);

            return [
                'key' => $key,
                'name' => $meta['name'],
                'available' => (bool) $room,
                'price' => $room ? (int) round((float) $room->price_per_night) : null,
                'size_sqm' => $room->size_sqm ?? $meta['size_sqm'],
                'bed_info' => $room->bed_info ?? $meta['bed_info'],
                'perks' => $room ? $room->perks : [],
            ];
        })->values()->all();
    }

    protected function applyFilters(Collection $hotels, array $filters): Collection
    {
        $destination = trim((string) ($filters['destination'] ?? ''));
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;
        $starRatings = array_map('strval', (array) ($filters['star_rating'] ?? []));
        $amenities = (array) ($filters['amenities'] ?? []);

        return $hotels
            ->when($destination !== '', fn ($c) => $c->filter(fn ($h) => str_contains(strtolower($h->name), strtolower($destination))
                || str_contains(strtolower($h->city), strtolower($destination))
                || str_contains(strtolower($h->country), strtolower($destination))))
            ->when(is_numeric($minPrice), fn ($c) => $c->filter(fn ($h) => $h->price_per_night >= (float) $minPrice))
            ->when(is_numeric($maxPrice), fn ($c) => $c->filter(fn ($h) => $h->price_per_night <= (float) $maxPrice))
            ->when(count($starRatings) > 0, fn ($c) => $c->filter(fn ($h) => in_array((string) $h->star_rating, $starRatings, true)))
            ->when(count($amenities) > 0, fn ($c) => $c->filter(fn ($h) => count(array_intersect($amenities, $h->amenities)) === count($amenities)))
            ->values();
    }

    protected function applySort(Collection $hotels, string $sort): Collection
    {
        return match ($sort) {
            'price_asc' => $hotels->sortBy('price_per_night')->values(),
            'price_desc' => $hotels->sortByDesc('price_per_night')->values(),
            default => $hotels->sortBy('id')->values(),
        };
    }
}
