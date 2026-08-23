<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\HotelRoom;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    /**
     * Room tier pricing/perks shared by every hotel. Mirrors what
     * HotelController::buildRooms() used to generate on the fly — now
     * it's real seeded data per hotel instead of being computed at
     * request time.
     */
    protected function roomTiers(): array
    {
        return [
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
    }

    public function run(): void
    {
        HotelRoom::query()->delete();
        Hotel::query()->delete();

        $rows = [
            ['name' => 'The Peninsula Paris', 'city' => 'Paris', 'country' => 'France', 'address' => '19 Av. Kléber, 75116 Paris, France', 'star_rating' => 5, 'badge' => 'Best Value', 'price_per_night' => 480, 'contact_phone' => '+33 1 58 12 28 88', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Gym', 'Spa'], 'description' => 'A landmark of Parisian luxury, The Peninsula Paris is housed in a magnificent Haussmann building steps from the Arc de Triomphe. Expect impeccable service, sumptuous interiors, and rooftop dining with iconic city views.', 'room_tiers' => ['classic', 'deluxe', 'suite']],
            ['name' => 'Four Seasons Bali at Sayan', 'city' => 'Ubud', 'country' => 'Indonesia', 'address' => 'Sayan, Ubud, Gianyar, Bali, Indonesia', 'star_rating' => 5, 'badge' => 'Staff Pick', 'price_per_night' => 320, 'contact_phone' => '+62 361 977577', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Spa'], 'description' => "Set deep in the Ayung River valley, this jungle sanctuary blends Balinese architecture with river views, a suspended lotus-pond restaurant, and one of Asia's most celebrated spas.", 'room_tiers' => ['classic', 'deluxe', 'suite']],
            ['name' => 'Aman Tokyo', 'city' => 'Otemachi', 'country' => 'Japan', 'address' => '1-5-6 Otemachi, Chiyoda-ku, Tokyo, Japan', 'star_rating' => 5, 'badge' => 'Luxury', 'price_per_night' => 620, 'contact_phone' => '+81 3 5224 3333', 'amenities' => ['WiFi', 'Pool', 'Gym', 'Spa', 'Restaurant'], 'description' => 'Occupying the top six floors of the Otemachi Tower, Aman Tokyo pairs minimalist washi-paper interiors with sweeping skyline views and an urban onsen.', 'room_tiers' => ['classic', 'deluxe', 'suite']],
            ['name' => 'Grace Santorini', 'city' => 'Imerovigli', 'country' => 'Greece', 'address' => 'Imerovigli, Santorini 847 00, Greece', 'star_rating' => 5, 'badge' => 'Romantic', 'price_per_night' => 410, 'contact_phone' => '+30 22860 29300', 'amenities' => ['WiFi', 'Pool', 'Breakfast'], 'description' => 'Cliffside infinity pools and caldera-facing suites make Grace Santorini a favourite for sunset views over the Aegean.', 'room_tiers' => ['classic', 'deluxe']],
            ['name' => 'The Ritz-Carlton New York', 'city' => 'New York', 'country' => 'USA', 'address' => '50 Central Park South, New York, NY 10019', 'star_rating' => 5, 'badge' => 'City Break', 'price_per_night' => 395, 'contact_phone' => '+1 212-308-9100', 'amenities' => ['WiFi', 'Gym', 'Spa', 'Restaurant'], 'description' => 'Overlooking Central Park, this Manhattan institution pairs classic Ritz-Carlton service with unbeatable proximity to Fifth Avenue.', 'room_tiers' => ['classic', 'deluxe', 'suite']],
            ['name' => 'Soneva Fushi', 'city' => 'Kunfunadhoo Island', 'country' => 'Maldives', 'address' => 'Baa Atoll, Maldives', 'star_rating' => 5, 'badge' => 'Luxury', 'price_per_night' => 890, 'contact_phone' => '+960 660 0304', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Spa'], 'description' => 'Barefoot luxury on a private Maldivian island — overwater villas, a castaway observatory, and a "no news, no shoes" philosophy.', 'room_tiers' => ['deluxe', 'suite']],
            ['name' => 'The Savoy London', 'city' => 'London', 'country' => 'United Kingdom', 'address' => 'Strand, London WC2R 0EZ, United Kingdom', 'star_rating' => 5, 'badge' => null, 'price_per_night' => 460, 'contact_phone' => '+44 20 7836 4343', 'amenities' => ['WiFi', 'Gym', 'Spa', 'Restaurant'], 'description' => 'An Edwardian icon on the Thames, The Savoy pairs Art Deco glamour with the storied American Bar and afternoon tea in the Thames Foyer.', 'room_tiers' => ['classic', 'deluxe']],
            ['name' => 'Rosewood Bangkok', 'city' => 'Bangkok', 'country' => 'Thailand', 'address' => '1041/16 Ploenchit Road, Bangkok 10330, Thailand', 'star_rating' => 5, 'badge' => 'Staff Pick', 'price_per_night' => 260, 'contact_phone' => '+66 2 080 8888', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Gym', 'Spa'], 'description' => 'A sculptural tower in the heart of Bangkok, Rosewood pairs contemporary Thai design with sky-high suites and a rooftop bar.', 'room_tiers' => ['classic', 'deluxe', 'suite']],
            ['name' => 'Burj Al Arab Jumeirah', 'city' => 'Dubai', 'country' => 'UAE', 'address' => 'Jumeirah St, Dubai, UAE', 'star_rating' => 5, 'badge' => 'Luxury', 'price_per_night' => 1050, 'contact_phone' => '+971 4 301 7777', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Spa', 'Restaurant'], 'description' => "The sail-shaped icon of Dubai's skyline, offering duplex suites, a private beach, and chauffeured Rolls-Royce transfers.", 'room_tiers' => ['suite']],
            ['name' => 'Park Hyatt Sydney', 'city' => 'Sydney', 'country' => 'Australia', 'address' => '7 Hickson Rd, The Rocks NSW 2000, Australia', 'star_rating' => 5, 'badge' => 'City Break', 'price_per_night' => 340, 'contact_phone' => '+61 2 9256 1234', 'amenities' => ['WiFi', 'Pool', 'Gym', 'Restaurant'], 'description' => 'Harbourside luxury with uninterrupted views of the Sydney Opera House and Harbour Bridge from a private rooftop pool.', 'room_tiers' => ['classic', 'deluxe']],
            ['name' => 'Hotel Arts Barcelona', 'city' => 'Barcelona', 'country' => 'Spain', 'address' => 'Carrer de la Marina, 19-21, 08005 Barcelona, Spain', 'star_rating' => 5, 'badge' => null, 'price_per_night' => 290, 'contact_phone' => '+34 932 21 10 00', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Gym'], 'description' => 'A beachfront tower with panoramic Mediterranean views, art-filled interiors, and a Michelin-starred restaurant.', 'room_tiers' => ['classic']],
            ['name' => 'Ellerman House', 'city' => 'Cape Town', 'country' => 'South Africa', 'address' => '180 Kloof Rd, Bantry Bay, Cape Town, South Africa', 'star_rating' => 5, 'badge' => 'Romantic', 'price_per_night' => 375, 'contact_phone' => '+27 21 430 3200', 'amenities' => ['WiFi', 'Pool', 'Breakfast', 'Spa'], 'description' => 'A cliffside villa hotel above the Atlantic, with a renowned wine gallery, private art collection, and whale-watching suites.', 'room_tiers' => ['classic', 'deluxe', 'suite']],
            ['name' => 'Ace Hotel Downtown LA', 'city' => 'Los Angeles', 'country' => 'USA', 'address' => '929 S Broadway, Los Angeles, CA 90015', 'star_rating' => 4, 'badge' => 'Best Value', 'price_per_night' => 175, 'contact_phone' => '+1 213-623-3233', 'amenities' => ['WiFi', 'Gym', 'Restaurant'], 'description' => 'A restored 1920s theatre building turned design-forward hotel, with a rooftop pool overlooking the DTLA skyline.', 'room_tiers' => ['classic']],
        ];

        $tiers = $this->roomTiers();

        foreach ($rows as $row) {
            $hotel = Hotel::create([
                'name' => $row['name'],
                'city' => $row['city'],
                'country' => $row['country'],
                'address' => $row['address'],
                'star_rating' => $row['star_rating'],
                'badge' => $row['badge'],
                'price_per_night' => $row['price_per_night'],
                'description' => $row['description'],
                'check_in_time' => '15:00',
                'check_out_time' => '12:00',
                'contact_phone' => $row['contact_phone'],
                'amenities' => $row['amenities'],
                'policies' => [
                    ['title' => 'Cancellation', 'description' => 'Free cancellation up to 48 hours before check-in. Late cancellations charged one night.'],
                    ['title' => 'Pets', 'description' => 'Pets are welcome. A non-refundable fee of $50/night applies.'],
                ],
            ]);

            foreach ($row['room_tiers'] as $sortOrder => $tierKey) {
                $tier = $tiers[$tierKey];

                HotelRoom::create([
                    'hotel_id' => $hotel->id,
                    'room_class' => $tierKey,
                    'name' => $tier['name'],
                    'size_sqm' => $tier['size_sqm'],
                    'bed_info' => $tier['bed_info'],
                    'price_per_night' => (int) round($row['price_per_night'] * $tier['multiplier'] / 10) * 10,
                    'perks' => $tier['perks'],
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }
}
