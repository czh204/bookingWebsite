<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AttractionController extends Controller
{
    protected const PER_PAGE = 10;

    protected const CATEGORIES = ['Museum', 'Tour', 'Food & Drink', 'Adventure'];

    public function index(Request $request)
    {
        $attractions = $this->mockAttractions();

        $attractions = $this->applyFilters($attractions, $request);
        $attractions = $this->applySort($attractions, $request->string('sort', 'recommended')->toString());

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paged = new LengthAwarePaginator(
            $attractions->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values(),
            $attractions->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('attractions.index', [
            'attractions' => $paged,
            'categories' => self::CATEGORIES,
        ]);
    }

    /**
     * Stand-in for Attraction::query()->...->paginate() until the
     * `attractions` table (see database/migrations/*_create_attractions_table.php)
     * is migrated and seeded. Field names match the Attraction model's
     * columns so this method can be swapped for a real query later.
     */
    protected function mockAttractions(): Collection
    {
        $rows = [
            ['title' => 'Louvre Museum Guided Tour', 'category' => 'Museum', 'city' => 'Paris', 'country' => 'France', 'location_label' => 'Paris 1st, Paris', 'rating' => 4.8, 'review_count' => 3241, 'duration_hours' => 3, 'capacity' => 20, 'price' => 45],
            ['title' => 'Seine River Sunset Cruise', 'category' => 'Tour', 'city' => 'Paris', 'country' => 'France', 'location_label' => "Pont de l'Alma, Paris", 'rating' => 4.7, 'review_count' => 1872, 'duration_hours' => 2, 'capacity' => 50, 'price' => 65],
            ['title' => 'Bali Food & Culture Walk', 'category' => 'Food & Drink', 'city' => 'Bali', 'country' => 'Indonesia', 'location_label' => 'Seminyak, Bali', 'rating' => 4.9, 'review_count' => 987, 'duration_hours' => 4, 'capacity' => 15, 'price' => 80],
            ['title' => 'Mount Batur Sunrise Trek', 'category' => 'Adventure', 'city' => 'Bali', 'country' => 'Indonesia', 'location_label' => 'Kintamani, Bali', 'rating' => 4.6, 'review_count' => 2109, 'duration_hours' => 6, 'capacity' => 12, 'price' => 95],
            ['title' => 'Tokyo Street Food Night Tour', 'category' => 'Food & Drink', 'city' => 'Tokyo', 'country' => 'Japan', 'location_label' => 'Shinjuku, Tokyo', 'rating' => 4.8, 'review_count' => 1654, 'duration_hours' => 3, 'capacity' => 18, 'price' => 70],
            ['title' => 'Santorini Caldera Sailing', 'category' => 'Tour', 'city' => 'Santorini', 'country' => 'Greece', 'location_label' => 'Fira, Santorini', 'rating' => 4.9, 'review_count' => 3890, 'duration_hours' => 5, 'capacity' => 30, 'price' => 120],
            ['title' => 'Vatican Museums & Sistine Chapel', 'category' => 'Museum', 'city' => 'Rome', 'country' => 'Italy', 'location_label' => 'Vatican City, Rome', 'rating' => 4.9, 'review_count' => 5210, 'duration_hours' => 3, 'capacity' => 25, 'price' => 55],
            ['title' => 'Greenwich Village Food Tour', 'category' => 'Food & Drink', 'city' => 'New York', 'country' => 'USA', 'location_label' => 'Greenwich Village, New York', 'rating' => 4.7, 'review_count' => 1420, 'duration_hours' => 3, 'capacity' => 12, 'price' => 75],
            ['title' => 'Dubai Desert Safari & BBQ', 'category' => 'Adventure', 'city' => 'Dubai', 'country' => 'UAE', 'location_label' => 'Al Marmoom Desert, Dubai', 'rating' => 4.8, 'review_count' => 4310, 'duration_hours' => 6, 'capacity' => 20, 'price' => 85],
            ['title' => 'Sydney Harbour Bridge Climb', 'category' => 'Adventure', 'city' => 'Sydney', 'country' => 'Australia', 'location_label' => 'The Rocks, Sydney', 'rating' => 4.9, 'review_count' => 2765, 'duration_hours' => 3.5, 'capacity' => 14, 'price' => 250],
            ['title' => 'Barcelona Tapas & Wine Evening', 'category' => 'Food & Drink', 'city' => 'Barcelona', 'country' => 'Spain', 'location_label' => 'El Born, Barcelona', 'rating' => 4.8, 'review_count' => 998, 'duration_hours' => 4, 'capacity' => 15, 'price' => 90],
            ['title' => 'Table Mountain Cable Car & Hike', 'category' => 'Tour', 'city' => 'Cape Town', 'country' => 'South Africa', 'location_label' => 'Table Mountain, Cape Town', 'rating' => 4.6, 'review_count' => 1345, 'duration_hours' => 8, 'capacity' => 10, 'price' => 110],
            ['title' => 'Grand Palace & Temples Tour', 'category' => 'Museum', 'city' => 'Bangkok', 'country' => 'Thailand', 'location_label' => 'Phra Nakhon, Bangkok', 'rating' => 4.7, 'review_count' => 1890, 'duration_hours' => 4, 'capacity' => 20, 'price' => 50],
        ];

        $descriptions = [
            'Louvre Museum Guided Tour' => "Skip the lines and discover the Louvre's masterpieces — the Mona Lisa, Venus de Milo, and more — with an expert art historian guide.",
            'Seine River Sunset Cruise' => 'Glide past Notre-Dame, the Eiffel Tower, and Musée d\'Orsay as the city lights come on, with commentary on Paris\'s landmarks along the way.',
            'Bali Food & Culture Walk' => "Taste your way through Seminyak's vibrant street food scene with a local foodie guide. Sample nasi goreng, satay lilit, lawar, and fresh tropical fruit at markets and warungs most tourists never find. Includes cooking demo.",
            'Mount Batur Sunrise Trek' => 'Hike an active volcano in the dark and watch the sunrise from the summit, with a simple breakfast cooked over volcanic steam vents.',
            'Tokyo Street Food Night Tour' => "Wander Shinjuku's neon-lit alleys with a local guide, sampling yakitori, takoyaki, and ramen at hole-in-the-wall spots you'd never find alone.",
            'Santorini Caldera Sailing' => 'Sail the caldera on a traditional catamaran, stopping to swim at the hot springs and Red Beach before a sunset dinner on board.',
            'Vatican Museums & Sistine Chapel' => "Skip the notoriously long lines with priority entry and see Raphael's Rooms and Michelangelo's Sistine Chapel ceiling with an art historian.",
            'Greenwich Village Food Tour' => 'Sample pizza, bagels, and pastries across Greenwich Village while your guide shares the neighbourhood\'s bohemian, jazz-age history.',
            'Dubai Desert Safari & BBQ' => 'Dune-bash across the Al Marmoom desert, ride a camel at sunset, and finish with a BBQ dinner and live entertainment under the stars.',
            'Sydney Harbour Bridge Climb' => "Climb to the summit of Sydney's iconic bridge for 360° views over the Opera House and harbour — one of the city's essential experiences.",
            'Barcelona Tapas & Wine Evening' => "Hop between El Born's best tapas bars with a local guide, pairing jamón, patatas bravas, and pintxos with regional Catalan wines.",
            'Table Mountain Cable Car & Hike' => 'Ride the rotating cable car to the summit for panoramic views over Cape Town, then take a guided walk along the plateau trails.',
            'Grand Palace & Temples Tour' => "Explore Bangkok's dazzling Grand Palace and Wat Phra Kaew with a local guide who brings the temples' history and legends to life.",
        ];

        $meetingPoints = [
            'Louvre Museum Guided Tour' => 'Louvre Pyramid entrance, Rue de Rivoli, Paris. Look for the Voyagr banner.',
            'Seine River Sunset Cruise' => "Pont de l'Alma boat dock, Paris. Look for the Voyagr banner.",
            'Bali Food & Culture Walk' => 'Seminyak Square car park, Jl. Kayu Jati, Seminyak. Look for the Voyagr banner.',
            'Mount Batur Sunrise Trek' => 'Toya Bungkah village car park, Kintamani. Hotel pickup available on request.',
            'Tokyo Street Food Night Tour' => 'Shinjuku Station East Exit, in front of the Studio Alta screen.',
            'Santorini Caldera Sailing' => 'Ammoudi Bay marina, Oia, Santorini.',
            'Vatican Museums & Sistine Chapel' => 'Piazza Risorgimento 2, outside the Vatican Museums entrance.',
            'Greenwich Village Food Tour' => 'Washington Square Arch, Greenwich Village, New York.',
            'Dubai Desert Safari & BBQ' => 'Hotel pickup included across central Dubai.',
            'Sydney Harbour Bridge Climb' => '3 Cumberland St, The Rocks, Sydney — BridgeClimb base.',
            'Barcelona Tapas & Wine Evening' => 'Passeig del Born 1, El Born, Barcelona.',
            'Table Mountain Cable Car & Hike' => 'Tafelberg Rd, Table Mountain Lower Cableway Station.',
            'Grand Palace & Temples Tour' => 'Na Phra Lan Rd, in front of the Grand Palace main gate.',
        ];

        $languages = [
            'Louvre Museum Guided Tour' => 'English · French · Spanish',
            'Seine River Sunset Cruise' => 'English · French',
            'Bali Food & Culture Walk' => 'English · Indonesian',
            'Mount Batur Sunrise Trek' => 'English · Indonesian',
            'Tokyo Street Food Night Tour' => 'English · Japanese',
            'Santorini Caldera Sailing' => 'English · Greek',
            'Vatican Museums & Sistine Chapel' => 'English · Italian · Spanish',
            'Greenwich Village Food Tour' => 'English',
            'Dubai Desert Safari & BBQ' => 'English · Arabic',
            'Sydney Harbour Bridge Climb' => 'English',
            'Barcelona Tapas & Wine Evening' => 'English · Spanish · Catalan',
            'Table Mountain Cable Car & Hike' => 'English',
            'Grand Palace & Temples Tour' => 'English · Thai',
        ];

        // Which time slots each experience offers. Attractions not listed
        // here default to the morning slot only. Once `attraction_time_slots`
        // (see the create_attraction_time_slots_table migration) is
        // seeded per attraction, this map can be replaced by the
        // attraction's real timeSlots() relation.
        $slotsByTitle = [
            'Louvre Museum Guided Tour' => ['morning', 'afternoon'],
            'Seine River Sunset Cruise' => ['evening'],
            'Bali Food & Culture Walk' => ['morning', 'evening'],
            'Mount Batur Sunrise Trek' => ['morning'],
            'Tokyo Street Food Night Tour' => ['evening'],
            'Santorini Caldera Sailing' => ['morning', 'afternoon'],
            'Vatican Museums & Sistine Chapel' => ['morning', 'afternoon'],
            'Greenwich Village Food Tour' => ['afternoon', 'evening'],
            'Dubai Desert Safari & BBQ' => ['afternoon'],
            'Sydney Harbour Bridge Climb' => ['morning', 'afternoon', 'evening'],
            'Barcelona Tapas & Wine Evening' => ['evening'],
            'Table Mountain Cable Car & Hike' => ['morning', 'afternoon'],
            'Grand Palace & Temples Tour' => ['morning', 'afternoon'],
        ];

        return collect($rows)->map(function (array $row, int $i) use ($descriptions, $meetingPoints, $languages, $slotsByTitle) {
            $categoryDetails = $this->categoryDetails($row['category']);
            $availableSlots = $slotsByTitle[$row['title']] ?? ['morning'];

            return (object) array_merge($row, [
                'id' => $i + 1,
                'duration_label' => $this->formatDuration($row['duration_hours']),
                'description' => $descriptions[$row['title']] ?? '',
                'meeting_point' => $meetingPoints[$row['title']] ?? $row['location_label'],
                'languages' => $languages[$row['title']] ?? 'English',
                'included' => $categoryDetails['included'],
                'not_included' => $categoryDetails['not_included'],
                'what_to_bring' => $categoryDetails['what_to_bring'],
                'min_age_fitness' => $categoryDetails['min_age_fitness'],
                'cancellation_policy' => $categoryDetails['cancellation_policy'],
                'time_slots' => $this->buildTimeSlots($row['price'], $availableSlots),
            ]);
        });
    }

    protected function formatDuration(float $hours): string
    {
        if ($hours < 1) {
            return round($hours * 60).' minutes';
        }

        if (floor($hours) == $hours) {
            return (int) $hours.' hour'.($hours > 1 ? 's' : '');
        }

        return rtrim(rtrim(number_format($hours, 1), '0'), '.').' hours';
    }

    /**
     * Included/excluded/bring-list/policy content shared by every
     * experience in a category. In the real schema these become
     * per-attraction columns; this template just keeps the mock data
     * from needing 13 fully bespoke write-ups.
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

    protected function buildTimeSlots(float $price, array $availableSlots): array
    {
        $slots = [
            'morning' => ['label' => '8:00 AM', 'sort' => 1],
            'afternoon' => ['label' => '12:00 PM', 'sort' => 2],
            'evening' => ['label' => '4:00 PM', 'sort' => 3],
        ];

        return collect($slots)->map(fn (array $slot, string $key) => [
            'key' => $key,
            'label' => $slot['label'],
            'available' => in_array($key, $availableSlots, true),
            'price' => (int) $price,
        ])->sortBy('sort')->values()->all();
    }

    protected function applyFilters(Collection $attractions, Request $request): Collection
    {
        $location = trim((string) $request->query('location'));
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $categories = (array) $request->query('categories', self::CATEGORIES);
        $durations = (array) $request->query('duration', []);

        return $attractions
            ->when($location !== '', fn ($c) => $c->filter(fn ($a) => str_contains(strtolower($a->city), strtolower($location))
                || str_contains(strtolower($a->country), strtolower($location))
                || str_contains(strtolower($a->title), strtolower($location))))
            ->when(is_numeric($minPrice), fn ($c) => $c->filter(fn ($a) => $a->price >= (float) $minPrice))
            ->when(is_numeric($maxPrice), fn ($c) => $c->filter(fn ($a) => $a->price <= (float) $maxPrice))
            ->when(count($categories) > 0, fn ($c) => $c->filter(fn ($a) => in_array($a->category, $categories, true)))
            ->when(count($durations) > 0, fn ($c) => $c->filter(function ($a) use ($durations) {
                $bucket = match (true) {
                    $a->duration_hours < 2 => 'under-2',
                    $a->duration_hours <= 4 => '2-4',
                    $a->duration_hours <= 8 => '4-8',
                    default => 'full-day',
                };

                return in_array($bucket, $durations, true);
            }))
            ->values();
    }

    protected function applySort(Collection $attractions, string $sort): Collection
    {
        return match ($sort) {
            'price_asc' => $attractions->sortBy('price')->values(),
            'price_desc' => $attractions->sortByDesc('price')->values(),
            'rating' => $attractions->sortByDesc('rating')->values(),
            default => $attractions->sortByDesc('review_count')->sortByDesc('rating')->values(),
        };
    }
}
