<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HotelController extends Controller
{
    protected const PER_PAGE = 5;

    public function index(Request $request)
    {
        $hotels = $this->loadHotels();

        $hotels = $this->applyFilters($hotels, $request);
        $hotels = $this->applySort($hotels, $request->string('sort', 'recommended')->toString());

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paged = new LengthAwarePaginator(
            $hotels->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values(),
            $hotels->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('hotels.index', [
            'hotels' => $paged,
        ]);
    }

    /**
     * Loads every hotel from the database, eager-loading whichever room
     * tiers (see the create_hotel_rooms_table migration) exist for each
     * one, seeded by database/seeders/HotelSeeder.php.
     */
    protected function loadHotels(): Collection
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

    protected function applyFilters(Collection $hotels, Request $request): Collection
    {
        $destination = trim((string) $request->query('destination'));
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $starRatings = (array) $request->query('star_rating', []);
        $amenities = (array) $request->query('amenities', []);

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
