<?php

namespace Database\Seeders;

use App\Models\Flight;
use App\Models\FlightFare;
use Illuminate\Database\Seeder;

class FlightSeeder extends Seeder
{
    /**
     * Fare tier pricing and inclusions shared by every flight. Mirrors what
     * FlightController::buildFares() used to generate on the fly — now it's
     * real flight_fares rows instead of being computed at request time.
     *
     * The multiplier derives each tier's price from the flight's base
     * (economy) fare.
     */
    protected function fareTiers(): array
    {
        return [
            'economy' => [
                'badge' => null,
                'multiplier' => 1,
                'checked_bag_kg' => 23,
                'carry_on' => '7 kg carry-on',
                'seat_info' => 'Standard seat (pitch 31–32")',
                'perks' => ['Meal & beverages', 'Personal IFE screen', 'USB charging port'],
                'refundable' => false,
                'change_fee_from' => 75,
                'sort_order' => 1,
            ],
            'premium_economy' => [
                'badge' => 'Popular',
                'multiplier' => 1.9,
                'checked_bag_kg' => 32,
                'carry_on' => '10 kg carry-on',
                'seat_info' => 'Wider seat (pitch 38")',
                'perks' => ['Premium meals & wine list', 'Larger IFE screen', 'Priority boarding', 'Extra legroom'],
                'refundable' => true,
                'change_fee_from' => null,
                'sort_order' => 2,
            ],
            'business' => [
                'badge' => 'Best Value',
                'multiplier' => 3.8,
                'checked_bag_kg' => 40,
                'carry_on' => '2× carry-on bags',
                'seat_info' => 'Lie-flat bed (seat pitch 72")',
                'perks' => ['Fine dining à la carte', 'Noise-cancelling headphones', 'Priority check-in & lounge', 'Limousine transfer (select routes)'],
                'refundable' => true,
                'change_fee_from' => null,
                'sort_order' => 3,
            ],
        ];
    }

    public function run(): void
    {
        FlightFare::query()->delete();
        Flight::query()->delete();

        $rows = [
            ['airline_name' => 'British Airways', 'airline_code' => 'GB', 'flight_number' => 'BA 178', 'aircraft' => 'Boeing 777-300ER', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'LHR', 'destination_city' => 'London', 'departure_time' => '10:00', 'arrival_time' => '22:30', 'duration_minutes' => 450, 'stops' => 0, 'price' => 450, 'fare_classes' => ['economy', 'premium_economy', 'business']],
            ['airline_name' => 'Air France', 'airline_code' => 'FR', 'flight_number' => 'AF 011', 'aircraft' => 'Airbus A350-900', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'CDG', 'destination_city' => 'Paris', 'departure_time' => '18:45', 'arrival_time' => '08:15', 'duration_minutes' => 450, 'stops' => 0, 'price' => 380, 'fare_classes' => ['economy', 'premium_economy', 'business']],
            ['airline_name' => 'Emirates', 'airline_code' => 'AE', 'flight_number' => 'EK 202', 'aircraft' => 'Airbus A380-800', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'DXB', 'destination_city' => 'Dubai', 'departure_time' => '23:59', 'arrival_time' => '21:30', 'duration_minutes' => 811, 'stops' => 1, 'price' => 520, 'fare_classes' => ['economy', 'premium_economy', 'business']],
            ['airline_name' => 'Singapore Airlines', 'airline_code' => 'SG', 'flight_number' => 'SQ 25', 'aircraft' => 'Airbus A350-900ULR', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'SIN', 'destination_city' => 'Singapore', 'departure_time' => '09:30', 'arrival_time' => '07:05', 'duration_minutes' => 1115, 'stops' => 0, 'price' => 780, 'fare_classes' => ['economy', 'premium_economy', 'business']],
            ['airline_name' => 'British Airways', 'airline_code' => 'GB', 'flight_number' => 'BA 112', 'aircraft' => 'Boeing 787-9', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'LHR', 'destination_city' => 'London', 'departure_time' => '19:15', 'arrival_time' => '07:05', 'duration_minutes' => 410, 'stops' => 0, 'price' => 470, 'fare_classes' => ['economy', 'premium_economy']],
            ['airline_name' => 'Air France', 'airline_code' => 'FR', 'flight_number' => 'AF 023', 'aircraft' => 'Boeing 777-200ER', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'CDG', 'destination_city' => 'Paris', 'departure_time' => '06:20', 'arrival_time' => '19:55', 'duration_minutes' => 575, 'stops' => 1, 'price' => 410, 'fare_classes' => ['economy', 'premium_economy']],
            ['airline_name' => 'Emirates', 'airline_code' => 'AE', 'flight_number' => 'EK 204', 'aircraft' => 'Boeing 777-300ER', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'DXB', 'destination_city' => 'Dubai', 'departure_time' => '11:05', 'arrival_time' => '08:40', 'duration_minutes' => 815, 'stops' => 0, 'price' => 610, 'fare_classes' => ['economy', 'business']],
            ['airline_name' => 'Singapore Airlines', 'airline_code' => 'SG', 'flight_number' => 'SQ 21', 'aircraft' => 'Airbus A350-900', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'SIN', 'destination_city' => 'Singapore', 'departure_time' => '21:40', 'arrival_time' => '06:15', 'duration_minutes' => 1055, 'stops' => 1, 'price' => 705, 'fare_classes' => ['economy', 'business']],
            ['airline_name' => 'Lufthansa', 'airline_code' => 'LH', 'flight_number' => 'LH 400', 'aircraft' => 'Airbus A340-600', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'FRA', 'destination_city' => 'Frankfurt', 'departure_time' => '17:30', 'arrival_time' => '07:00', 'duration_minutes' => 450, 'stops' => 0, 'price' => 495, 'fare_classes' => ['economy', 'premium_economy', 'business']],
            ['airline_name' => 'Qatar Airways', 'airline_code' => 'QR', 'flight_number' => 'QR 701', 'aircraft' => 'Boeing 777-300ER', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'DOH', 'destination_city' => 'Doha', 'departure_time' => '22:10', 'arrival_time' => '18:45', 'duration_minutes' => 755, 'stops' => 1, 'price' => 560, 'fare_classes' => ['economy', 'premium_economy', 'business']],
            ['airline_name' => 'Lufthansa', 'airline_code' => 'LH', 'flight_number' => 'LH 402', 'aircraft' => 'Boeing 747-8', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'FRA', 'destination_city' => 'Frankfurt', 'departure_time' => '08:05', 'arrival_time' => '21:35', 'duration_minutes' => 450, 'stops' => 0, 'price' => 515, 'fare_classes' => ['economy', 'premium_economy']],
            ['airline_name' => 'Qatar Airways', 'airline_code' => 'QR', 'flight_number' => 'QR 703', 'aircraft' => 'Airbus A350-1000', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'DOH', 'destination_city' => 'Doha', 'departure_time' => '13:20', 'arrival_time' => '10:00', 'duration_minutes' => 760, 'stops' => 2, 'price' => 505, 'fare_classes' => ['economy', 'premium_economy']],
            ['airline_name' => 'British Airways', 'airline_code' => 'GB', 'flight_number' => 'BA 184', 'aircraft' => 'Airbus A380-800', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'LHR', 'destination_city' => 'London', 'departure_time' => '00:30', 'arrival_time' => '12:15', 'duration_minutes' => 465, 'stops' => 0, 'price' => 440, 'fare_classes' => ['economy']],
            ['airline_name' => 'Air France', 'airline_code' => 'FR', 'flight_number' => 'AF 007', 'aircraft' => 'Airbus A220-300', 'origin_code' => 'JFK', 'origin_city' => 'New York', 'destination_code' => 'CDG', 'destination_city' => 'Paris', 'departure_time' => '02:45', 'arrival_time' => '16:20', 'duration_minutes' => 455, 'stops' => 2, 'price' => 360, 'fare_classes' => ['economy']],
        ];

        $tiers = $this->fareTiers();

        foreach ($rows as $row) {
            $flight = Flight::create([
                'airline_name' => $row['airline_name'],
                'airline_code' => $row['airline_code'],
                'flight_number' => $row['flight_number'],
                'aircraft' => $row['aircraft'],
                'origin_code' => $row['origin_code'],
                'origin_city' => $row['origin_city'],
                'destination_code' => $row['destination_code'],
                'destination_city' => $row['destination_city'],
                'departure_date' => now()->toDateString(),
                'departure_time' => $row['departure_time'],
                'arrival_time' => $row['arrival_time'],
                'duration_minutes' => $row['duration_minutes'],
                'stops' => $row['stops'],
                'price' => $row['price'],
            ]);

            // Only the classes this flight actually sells get a row; a
            // missing row is what makes the modal show that tier as
            // unavailable instead of inventing a price for it.
            foreach ($row['fare_classes'] as $fareClass) {
                $tier = $tiers[$fareClass];

                FlightFare::create([
                    'flight_id' => $flight->id,
                    'fare_class' => $fareClass,
                    'badge' => $tier['badge'],
                    'price' => (int) round($row['price'] * $tier['multiplier']),
                    'checked_bag_kg' => $tier['checked_bag_kg'],
                    'carry_on' => $tier['carry_on'],
                    'seat_info' => $tier['seat_info'],
                    'perks' => $tier['perks'],
                    'refundable' => $tier['refundable'],
                    'change_fee_from' => $tier['change_fee_from'],
                    'sort_order' => $tier['sort_order'],
                ]);
            }
        }
    }
}
