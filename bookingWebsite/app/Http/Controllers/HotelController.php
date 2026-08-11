<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HotelController extends Controller
{
    protected const PER_PAGE = 10;

    public function index(Request $request)
    {
        $hotels = $this->mockHotels();

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
     * Stand-in for Hotel::query()->...->paginate() until the `hotels`
     * table (see database/migrations/*_create_hotels_table.php) is
     * migrated and seeded. Field names match the Hotel model's columns
     * so this method can be swapped for a real query later.
     */
    protected function mockHotels(): Collection
    {
        $rows = [
            ['name' => 'The Peninsula Paris', 'city' => 'Paris', 'country' => 'France', 'address' => '19 Av. Kléber, 75116 Paris, France', 'star_rating' => 5, 'rating' => 4.9, 'review_count' => 2847, 'badge' => 'Best Value', 'price_per_night' => 480, 'contact_phone' => '+33 1 58 12 28 88', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Gym', 'Spa'], 'description' => 'A landmark of Parisian luxury, The Peninsula Paris is housed in a magnificent Haussmann building steps from the Arc de Triomphe. Expect impeccable service, sumptuous interiors, and rooftop dining with iconic city views.'],
            ['name' => 'Four Seasons Bali at Sayan', 'city' => 'Ubud', 'country' => 'Indonesia', 'address' => 'Sayan, Ubud, Gianyar, Bali, Indonesia', 'star_rating' => 5, 'rating' => 4.8, 'review_count' => 1923, 'badge' => 'Staff Pick', 'price_per_night' => 320, 'contact_phone' => '+62 361 977577', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Spa'], 'description' => 'Set deep in the Ayung River valley, this jungle sanctuary blends Balinese architecture with river views, a suspended lotus-pond restaurant, and one of Asia\'s most celebrated spas.'],
            ['name' => 'Aman Tokyo', 'city' => 'Otemachi', 'country' => 'Japan', 'address' => '1-5-6 Otemachi, Chiyoda-ku, Tokyo, Japan', 'star_rating' => 5, 'rating' => 4.9, 'review_count' => 1104, 'badge' => 'Luxury', 'price_per_night' => 620, 'contact_phone' => '+81 3 5224 3333', 'amenities' => ['WiFi', 'Pool', 'Gym', 'Spa', 'Restaurant'], 'description' => 'Occupying the top six floors of the Otemachi Tower, Aman Tokyo pairs minimalist washi-paper interiors with sweeping skyline views and an urban onsen.'],
            ['name' => 'Grace Santorini', 'city' => 'Imerovigli', 'country' => 'Greece', 'address' => 'Imerovigli, Santorini 847 00, Greece', 'star_rating' => 5, 'rating' => 4.7, 'review_count' => 986, 'badge' => 'Romantic', 'price_per_night' => 410, 'contact_phone' => '+30 22860 29300', 'amenities' => ['WiFi', 'Pool', 'Breakfast'], 'description' => 'Cliffside infinity pools and caldera-facing suites make Grace Santorini a favourite for sunset views over the Aegean.'],
            ['name' => 'The Ritz-Carlton New York', 'city' => 'New York', 'country' => 'USA', 'address' => '50 Central Park South, New York, NY 10019', 'star_rating' => 5, 'rating' => 4.6, 'review_count' => 3201, 'badge' => 'City Break', 'price_per_night' => 395, 'contact_phone' => '+1 212-308-9100', 'amenities' => ['WiFi', 'Gym', 'Spa', 'Restaurant'], 'description' => 'Overlooking Central Park, this Manhattan institution pairs classic Ritz-Carlton service with unbeatable proximity to Fifth Avenue.'],
            ['name' => 'Soneva Fushi', 'city' => 'Kunfunadhoo Island', 'country' => 'Maldives', 'address' => 'Baa Atoll, Maldives', 'star_rating' => 5, 'rating' => 5.0, 'review_count' => 742, 'badge' => 'Luxury', 'price_per_night' => 890, 'contact_phone' => '+960 660 0304', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Spa'], 'description' => 'Barefoot luxury on a private Maldivian island — overwater villas, a castaway observatory, and a "no news, no shoes" philosophy.'],
            ['name' => 'The Savoy London', 'city' => 'London', 'country' => 'United Kingdom', 'address' => 'Strand, London WC2R 0EZ, United Kingdom', 'star_rating' => 5, 'rating' => 4.7, 'review_count' => 2654, 'badge' => null, 'price_per_night' => 460, 'contact_phone' => '+44 20 7836 4343', 'amenities' => ['WiFi', 'Gym', 'Spa', 'Restaurant'], 'description' => 'An Edwardian icon on the Thames, The Savoy pairs Art Deco glamour with the storied American Bar and afternoon tea in the Thames Foyer.'],
            ['name' => 'Rosewood Bangkok', 'city' => 'Bangkok', 'country' => 'Thailand', 'address' => '1041/16 Ploenchit Road, Bangkok 10330, Thailand', 'star_rating' => 5, 'rating' => 4.8, 'review_count' => 1567, 'badge' => 'Staff Pick', 'price_per_night' => 260, 'contact_phone' => '+66 2 080 8888', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Gym', 'Spa'], 'description' => 'A sculptural tower in the heart of Bangkok, Rosewood pairs contemporary Thai design with sky-high suites and a rooftop bar.'],
            ['name' => 'Burj Al Arab Jumeirah', 'city' => 'Dubai', 'country' => 'UAE', 'address' => 'Jumeirah St, Dubai, UAE', 'star_rating' => 5, 'rating' => 4.9, 'review_count' => 4012, 'badge' => 'Luxury', 'price_per_night' => 1050, 'contact_phone' => '+971 4 301 7777', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Spa', 'Restaurant'], 'description' => 'The sail-shaped icon of Dubai\'s skyline, offering duplex suites, a private beach, and chauffeured Rolls-Royce transfers.'],
            ['name' => 'Park Hyatt Sydney', 'city' => 'Sydney', 'country' => 'Australia', 'address' => '7 Hickson Rd, The Rocks NSW 2000, Australia', 'star_rating' => 5, 'rating' => 4.6, 'review_count' => 1389, 'badge' => 'City Break', 'price_per_night' => 340, 'contact_phone' => '+61 2 9256 1234', 'amenities' => ['WiFi', 'Pool', 'Gym', 'Restaurant'], 'description' => 'Harbourside luxury with uninterrupted views of the Sydney Opera House and Harbour Bridge from a private rooftop pool.'],
            ['name' => 'Hotel Arts Barcelona', 'city' => 'Barcelona', 'country' => 'Spain', 'address' => "Carrer de la Marina, 19-21, 08005 Barcelona, Spain", 'star_rating' => 5, 'rating' => 4.5, 'review_count' => 2210, 'badge' => null, 'price_per_night' => 290, 'contact_phone' => '+34 932 21 10 00', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Gym'], 'description' => 'A beachfront tower with panoramic Mediterranean views, art-filled interiors, and a Michelin-starred restaurant.'],
            ['name' => 'Ellerman House', 'city' => 'Cape Town', 'country' => 'South Africa', 'address' => '180 Kloof Rd, Bantry Bay, Cape Town, South Africa', 'star_rating' => 5, 'rating' => 4.8, 'review_count' => 587, 'badge' => 'Romantic', 'price_per_night' => 375, 'contact_phone' => '+27 21 430 3200', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Spa'], 'description' => 'A cliffside villa hotel above the Atlantic, with a renowned wine gallery, private art collection, and whale-watching suites.'],
            ['name' => 'Ace Hotel Downtown LA', 'city' => 'Los Angeles', 'country' => 'USA', 'address' => '929 S Broadway, Los Angeles, CA 90015', 'star_rating' => 4, 'rating' => 4.2, 'review_count' => 1876, 'badge' => 'Best Value', 'price_per_night' => 175, 'contact_phone' => '+1 213-623-3233', 'amenities' => ['WiFi', 'Gym', 'Restaurant'], 'description' => 'A restored 1920s theatre building turned design-forward hotel, with a rooftop pool overlooking the DTLA skyline.'],
        ];

        // Which room tiers each hotel offers. Hotels not listed here
        // default to Classic Room only. Once `hotel_rooms` (see the
        // create_hotel_rooms_table migration) is seeded per hotel, this
        // map — and buildRooms() below — can be replaced by the hotel's
        // real rooms() relation.
        $roomTiersByHotel = [
            'The Peninsula Paris' => ['classic', 'deluxe', 'suite'],
            'Four Seasons Bali at Sayan' => ['classic', 'deluxe', 'suite'],
            'Aman Tokyo' => ['classic', 'deluxe', 'suite'],
            'Grace Santorini' => ['classic', 'deluxe'],
            'The Ritz-Carlton New York' => ['classic', 'deluxe', 'suite'],
            'Soneva Fushi' => ['deluxe', 'suite'],
            'The Savoy London' => ['classic', 'deluxe'],
            'Rosewood Bangkok' => ['classic', 'deluxe', 'suite'],
            'Burj Al Arab Jumeirah' => ['suite'],
            'Park Hyatt Sydney' => ['classic', 'deluxe'],
            'Hotel Arts Barcelona' => ['classic'],
            'Ellerman House' => ['classic', 'deluxe', 'suite'],
            'Ace Hotel Downtown LA' => ['classic'],
        ];

        return collect($rows)->map(function (array $row, int $i) use ($roomTiersByHotel) {
            $availableTiers = $roomTiersByHotel[$row['name']] ?? ['classic'];

            return (object) array_merge($row, [
                'id' => $i + 1,
                'check_in_time' => '15:00',
                'check_out_time' => '12:00',
                'policies' => [
                    ['title' => 'Cancellation', 'description' => 'Free cancellation up to 48 hours before check-in. Late cancellations charged one night.'],
                    ['title' => 'Pets', 'description' => 'Pets are welcome. A non-refundable fee of $50/night applies.'],
                ],
                'rooms' => $this->buildRooms($row['price_per_night'], $availableTiers),
            ]);
        });
    }

    /**
     * Room tier pricing/perks. In the real schema this becomes HotelRoom
     * rows (room_class, price_per_night, perks, ...) belonging to a
     * Hotel; the multipliers here just derive a plausible price per
     * tier from the hotel's base (Classic) rate.
     */
    protected function buildRooms(float $basePrice, array $availableTiers): array
    {
        $tiers = [
            'classic' => [
                'name' => 'Classic Room',
                'multiplier' => 1,
                'size_sqm' => 48,
                'bed_info' => '1 King or 2 Twin',
                'perks' => ['City view', 'Nespresso machine', 'Marble bathroom', 'Free WiFi'],
            ],
            'deluxe' => [
                'name' => 'Deluxe Room',
                'multiplier' => 1.35,
                'size_sqm' => 62,
                'bed_info' => '1 King',
                'perks' => ['Premium view', 'Butler service', 'Soaking tub', 'Free minibar'],
            ],
            'suite' => [
                'name' => 'Suite',
                'multiplier' => 2.9,
                'size_sqm' => 130,
                'bed_info' => '1 King',
                'perks' => ['Panoramic views', 'Separate living room', '24-hr butler', 'VIP airport transfer'],
            ],
        ];

        return collect($tiers)->map(function (array $tier, string $key) use ($availableTiers, $basePrice) {
            $available = in_array($key, $availableTiers, true);

            return [
                'key' => $key,
                'name' => $tier['name'],
                'available' => $available,
                'price' => $available ? (int) round($basePrice * $tier['multiplier'] / 10) * 10 : null,
                'size_sqm' => $tier['size_sqm'],
                'bed_info' => $tier['bed_info'],
                'perks' => $tier['perks'],
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
            'rating' => $hotels->sortByDesc('rating')->values(),
            default => $hotels->sortByDesc('review_count')->sortByDesc('rating')->values(),
        };
    }
}
