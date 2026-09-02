<?php

namespace Database\Seeders;

use App\Models\Attraction;
use App\Models\AttractionTimeSlot;
use Illuminate\Database\Seeder;

class AttractionSeeder extends Seeder
{
    /**
     * Category-level included/not-included/bring-list/policy content.
     * Mirrors what AttractionController::categoryDetails() used to
     * generate on the fly — now it's real seeded data per attraction
     * instead of being computed at request time.
     */
    protected function categoryDetails(string $category): array
    {
        return match ($category) {
            'Museum' => [
                'included' => ['Skip-the-line entry', 'Expert local guide', 'Headset audio guide'],
                'not_included' => ['Hotel transfers (can be arranged +$15)', 'Food & drinks', 'Gratuities'],
                'what_to_bring' => ['Comfortable shoes', 'Valid photo ID', 'Camera'],
                'min_age_fitness' => 'Suitable for all ages. Minimal walking required.',
                'cancellation_policy' => 'Free cancellation up to 24 hours in advance.',
            ],
            'Food & Drink' => [
                'included' => ['All food tastings (12+ dishes)', 'Local guide', 'Cooking demonstration'],
                'not_included' => ['Hotel transfers (can be arranged +$15)', 'Alcoholic beverages', 'Gratuities'],
                'what_to_bring' => ['Modest clothing (for temple)', 'Comfortable shoes', 'Appetite!', 'Cash for extra snacks'],
                'min_age_fitness' => 'Suitable for ages 6 and up. Vegetarian options available.',
                'cancellation_policy' => 'Free cancellation up to 24 hours in advance.',
            ],
            'Adventure' => [
                'included' => ['Certified guide', 'Safety equipment', 'Bottled water & snacks'],
                'not_included' => ['Hotel transfers (can be arranged +$15)', 'Travel insurance', 'Gratuities'],
                'what_to_bring' => ['Comfortable athletic shoes', 'Sun protection', 'Reusable water bottle', 'Light jacket'],
                'min_age_fitness' => 'Suitable for ages 12 and up. Good physical fitness required.',
                'cancellation_policy' => 'Free cancellation up to 48 hours in advance. No refunds within 48 hours.',
            ],
            default => [
                'included' => ['Professional guide', 'All entrance fees', 'Bottled water'],
                'not_included' => ['Hotel transfers (can be arranged +$15)', 'Meals', 'Gratuities'],
                'what_to_bring' => ['Comfortable shoes', 'Sun protection', 'Camera'],
                'min_age_fitness' => 'Suitable for ages 6 and up. Moderate walking required.',
                'cancellation_policy' => 'Free cancellation up to 24 hours in advance.',
            ],
        };
    }

    protected function formatDuration(float $hours): string
    {
        if (floor($hours) == $hours) {
            return (int) $hours.' hour'.($hours > 1 ? 's' : '');
        }

        return rtrim(rtrim(number_format($hours, 1), '0'), '.').' hours';
    }

    public function run(): void
    {
        AttractionTimeSlot::query()->delete();
        Attraction::query()->delete();

        $rows = [
            ['title' => 'Louvre Museum Guided Tour', 'category' => 'Museum', 'city' => 'Paris', 'country' => 'France', 'location_label' => 'Paris 1st, Paris', 'duration_hours' => 3, 'capacity' => 20, 'price' => 45, 'description' => "Skip the lines and discover the Louvre's masterpieces — the Mona Lisa, Venus de Milo, and more — with an expert art historian guide.", 'meeting_point' => 'Louvre Pyramid entrance, Rue de Rivoli, Paris. Look for the Voyagr banner.', 'languages' => 'English · French · Spanish', 'slots' => ['morning', 'afternoon']],
            ['title' => 'Seine River Sunset Cruise', 'category' => 'Tour', 'city' => 'Paris', 'country' => 'France', 'location_label' => "Pont de l'Alma, Paris", 'duration_hours' => 2, 'capacity' => 50, 'price' => 65, 'description' => "Glide past Notre-Dame, the Eiffel Tower, and Musée d'Orsay as the city lights come on, with commentary on Paris's landmarks along the way.", 'meeting_point' => "Pont de l'Alma boat dock, Paris. Look for the Voyagr banner.", 'languages' => 'English · French', 'slots' => ['evening']],
            ['title' => 'Bali Food & Culture Walk', 'category' => 'Food & Drink', 'city' => 'Bali', 'country' => 'Indonesia', 'location_label' => 'Seminyak, Bali', 'duration_hours' => 4, 'capacity' => 15, 'price' => 80, 'description' => "Taste your way through Seminyak's vibrant street food scene with a local foodie guide. Sample nasi goreng, satay lilit, lawar, and fresh tropical fruit at markets and warungs most tourists never find. Includes cooking demo.", 'meeting_point' => 'Seminyak Square car park, Jl. Kayu Jati, Seminyak. Look for the Voyagr banner.', 'languages' => 'English · Indonesian', 'slots' => ['morning', 'evening']],
            ['title' => 'Mount Batur Sunrise Trek', 'category' => 'Adventure', 'city' => 'Bali', 'country' => 'Indonesia', 'location_label' => 'Kintamani, Bali', 'duration_hours' => 6, 'capacity' => 12, 'price' => 95, 'description' => 'Hike an active volcano in the dark and watch the sunrise from the summit, with a simple breakfast cooked over volcanic steam vents.', 'meeting_point' => 'Toya Bungkah village car park, Kintamani. Hotel pickup available on request.', 'languages' => 'English · Indonesian', 'slots' => ['morning']],
            ['title' => 'Tokyo Street Food Night Tour', 'category' => 'Food & Drink', 'city' => 'Tokyo', 'country' => 'Japan', 'location_label' => 'Shinjuku, Tokyo', 'duration_hours' => 3, 'capacity' => 18, 'price' => 70, 'description' => "Wander Shinjuku's neon-lit alleys with a local guide, sampling yakitori, takoyaki, and ramen at hole-in-the-wall spots you'd never find alone.", 'meeting_point' => 'Shinjuku Station East Exit, in front of the Studio Alta screen.', 'languages' => 'English · Japanese', 'slots' => ['evening']],
            ['title' => 'Santorini Caldera Sailing', 'category' => 'Tour', 'city' => 'Santorini', 'country' => 'Greece', 'location_label' => 'Fira, Santorini', 'duration_hours' => 5, 'capacity' => 30, 'price' => 120, 'description' => 'Sail the caldera on a traditional catamaran, stopping to swim at the hot springs and Red Beach before a sunset dinner on board.', 'meeting_point' => 'Ammoudi Bay marina, Oia, Santorini.', 'languages' => 'English · Greek', 'slots' => ['morning', 'afternoon']],
            ['title' => 'Vatican Museums & Sistine Chapel', 'category' => 'Museum', 'city' => 'Rome', 'country' => 'Italy', 'location_label' => 'Vatican City, Rome', 'duration_hours' => 3, 'capacity' => 25, 'price' => 55, 'description' => "Skip the notoriously long lines with priority entry and see Raphael's Rooms and Michelangelo's Sistine Chapel ceiling with an art historian.", 'meeting_point' => 'Piazza Risorgimento 2, outside the Vatican Museums entrance.', 'languages' => 'English · Italian · Spanish', 'slots' => ['morning', 'afternoon']],
            ['title' => 'Greenwich Village Food Tour', 'category' => 'Food & Drink', 'city' => 'New York', 'country' => 'USA', 'location_label' => 'Greenwich Village, New York', 'duration_hours' => 3, 'capacity' => 12, 'price' => 75, 'description' => "Sample pizza, bagels, and pastries across Greenwich Village while your guide shares the neighbourhood's bohemian, jazz-age history.", 'meeting_point' => 'Washington Square Arch, Greenwich Village, New York.', 'languages' => 'English', 'slots' => ['afternoon', 'evening']],
            ['title' => 'Dubai Desert Safari & BBQ', 'category' => 'Adventure', 'city' => 'Dubai', 'country' => 'UAE', 'location_label' => 'Al Marmoom Desert, Dubai', 'duration_hours' => 6, 'capacity' => 20, 'price' => 85, 'description' => 'Dune-bash across the Al Marmoom desert, ride a camel at sunset, and finish with a BBQ dinner and live entertainment under the stars.', 'meeting_point' => 'Hotel pickup included across central Dubai.', 'languages' => 'English · Arabic', 'slots' => ['afternoon']],
            ['title' => 'Sydney Harbour Bridge Climb', 'category' => 'Adventure', 'city' => 'Sydney', 'country' => 'Australia', 'location_label' => 'The Rocks, Sydney', 'duration_hours' => 3.5, 'capacity' => 14, 'price' => 250, 'description' => "Climb to the summit of Sydney's iconic bridge for 360° views over the Opera House and harbour — one of the city's essential experiences.", 'meeting_point' => '3 Cumberland St, The Rocks, Sydney — BridgeClimb base.', 'languages' => 'English', 'slots' => ['morning', 'afternoon', 'evening']],
            ['title' => 'Barcelona Tapas & Wine Evening', 'category' => 'Food & Drink', 'city' => 'Barcelona', 'country' => 'Spain', 'location_label' => 'El Born, Barcelona', 'duration_hours' => 4, 'capacity' => 15, 'price' => 90, 'description' => "Hop between El Born's best tapas bars with a local guide, pairing jamón, patatas bravas, and pintxos with regional Catalan wines.", 'meeting_point' => 'Passeig del Born 1, El Born, Barcelona.', 'languages' => 'English · Spanish · Catalan', 'slots' => ['evening']],
            ['title' => 'Table Mountain Cable Car & Hike', 'category' => 'Tour', 'city' => 'Cape Town', 'country' => 'South Africa', 'location_label' => 'Table Mountain, Cape Town', 'duration_hours' => 8, 'capacity' => 10, 'price' => 110, 'description' => 'Ride the rotating cable car to the summit for panoramic views over Cape Town, then take a guided walk along the plateau trails.', 'meeting_point' => 'Tafelberg Rd, Table Mountain Lower Cableway Station.', 'languages' => 'English', 'slots' => ['morning', 'afternoon']],
            ['title' => 'Grand Palace & Temples Tour', 'category' => 'Museum', 'city' => 'Bangkok', 'country' => 'Thailand', 'location_label' => 'Phra Nakhon, Bangkok', 'duration_hours' => 4, 'capacity' => 20, 'price' => 50, 'description' => "Explore Bangkok's dazzling Grand Palace and Wat Phra Kaew with a local guide who brings the temples' history and legends to life.", 'meeting_point' => 'Na Phra Lan Rd, in front of the Grand Palace main gate.', 'languages' => 'English · Thai', 'slots' => ['morning', 'afternoon']],
        ];

        $slotDefs = [
            'morning' => ['label' => '8:00 AM', 'sort' => 1],
            'afternoon' => ['label' => '12:00 PM', 'sort' => 2],
            'evening' => ['label' => '4:00 PM', 'sort' => 3],
        ];

        foreach ($rows as $row) {
            $slotKeys = $row['slots'];
            $categoryDetails = $this->categoryDetails($row['category']);

            $attraction = Attraction::create([
                'title' => $row['title'],
                'category' => $row['category'],
                'city' => $row['city'],
                'country' => $row['country'],
                'location_label' => $row['location_label'],
                'duration_hours' => $row['duration_hours'],
                'duration_label' => $this->formatDuration($row['duration_hours']),
                'capacity' => $row['capacity'],
                'price' => $row['price'],
                'description' => $row['description'],
                'meeting_point' => $row['meeting_point'],
                'included' => $categoryDetails['included'],
                'not_included' => $categoryDetails['not_included'],
                'what_to_bring' => $categoryDetails['what_to_bring'],
                'min_age_fitness' => $categoryDetails['min_age_fitness'],
                'languages' => $row['languages'],
                'cancellation_policy' => $categoryDetails['cancellation_policy'],
            ]);

            foreach ($slotKeys as $key) {
                AttractionTimeSlot::create([
                    'attraction_id' => $attraction->id,
                    'slot_key' => $key,
                    'time_label' => $slotDefs[$key]['label'],
                    'price' => $row['price'],
                    'sort_order' => $slotDefs[$key]['sort'],
                ]);
            }
        }
    }
}
