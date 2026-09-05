<?php

namespace Database\Seeders;

use App\Models\ItineraryEvent;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItinerarySeeder extends Seeder
{
    /**
     * Seeds a demo planner for the Test User: one confirmed trip with its
     * booking events, plus a set of AI-suggested entries that aren't tied
     * to any booking. Both sources are needed for the calendar dots and
     * the day panel's two groups to be visible.
     *
     * Dates are anchored to the month after "now" so the seeded data
     * always lands in the future rather than going stale.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        if (! $user) {
            $this->command?->warn('ItinerarySeeder skipped: test@example.com not found.');

            return;
        }

        ItineraryEvent::where('user_id', $user->id)->delete();
        Trip::where('user_id', $user->id)->delete();

        $base = now()->addMonth()->startOfMonth();

        $trips = [
            [
                'title' => 'Paris Getaway',
                'destination' => 'Paris, France',
                'start_day' => 15,
                'end_day' => 19,
                'booking_reference' => 'VYG-4821PAR',
                'total_price' => 1840,
                'events' => [
                    ['day' => 15, 'time' => '07:15', 'category' => 'flight', 'title' => 'Flight AF 011 to Paris', 'location' => 'JFK Terminal 4'],
                    ['day' => 15, 'time' => '20:00', 'category' => 'hotel', 'title' => 'Check-in: Hôtel Saint-Germain', 'location' => '6th Arrondissement'],
                    ['day' => 19, 'time' => '11:00', 'category' => 'hotel', 'title' => 'Check-out: Hôtel Saint-Germain', 'location' => '6th Arrondissement'],
                    ['day' => 19, 'time' => '15:40', 'category' => 'flight', 'title' => 'Flight AF 012 to New York', 'location' => 'Charles de Gaulle T2E'],
                ],
            ],
            [
                'title' => 'Tokyo Spring Break',
                'destination' => 'Tokyo, Japan',
                'start_day' => 24,
                'end_day' => 28,
                'booking_reference' => 'VYG-7734TYO',
                'total_price' => 2260,
                'events' => [
                    ['day' => 24, 'time' => '01:30', 'category' => 'flight', 'title' => 'Flight JL 005 to Tokyo', 'location' => 'JFK Terminal 1'],
                    ['day' => 25, 'time' => '10:00', 'category' => 'activity', 'title' => 'Teamlab Planets Booking', 'location' => 'Toyosu'],
                    ['day' => 28, 'time' => '17:25', 'category' => 'flight', 'title' => 'Flight JL 006 to New York', 'location' => 'Haneda T3'],
                ],
            ],
        ];

        foreach ($trips as $row) {
            $trip = Trip::create([
                'user_id' => $user->id,
                'title' => $row['title'],
                'destination' => $row['destination'],
                'start_date' => $base->copy()->addDays($row['start_day'] - 1)->toDateString(),
                'end_date' => $base->copy()->addDays($row['end_day'] - 1)->toDateString(),
                'booking_reference' => $row['booking_reference'],
                'status' => 'upcoming',
                'total_price' => $row['total_price'],
            ]);

            foreach ($row['events'] as $event) {
                ItineraryEvent::create([
                    'user_id' => $user->id,
                    'trip_id' => $trip->id,
                    'source' => ItineraryEvent::SOURCE_BOOKING,
                    'category' => $event['category'],
                    'title' => $event['title'],
                    'location' => $event['location'],
                    'event_date' => $base->copy()->addDays($event['day'] - 1)->toDateString(),
                    'start_time' => $event['time'],
                ]);
            }
        }

        // AI suggestions: no trip_id, because nothing here is booked yet.
        $suggestions = [
            ['day' => 15, 'time' => '09:00', 'category' => 'sightseeing', 'title' => 'Louvre Museum Visit', 'location' => 'Rue de Rivoli, Paris'],
            ['day' => 15, 'time' => '13:00', 'category' => 'dining', 'title' => 'Lunch at Café de Flore', 'location' => 'Saint-Germain-des-Prés'],
            ['day' => 15, 'time' => '15:30', 'category' => 'sightseeing', 'title' => 'Eiffel Tower Tour', 'location' => 'Champ de Mars, Paris'],
            ['day' => 15, 'time' => '19:00', 'category' => 'activity', 'title' => 'Seine River Dinner Cruise', 'location' => 'Pont de l\'Alma'],
            ['day' => 16, 'time' => '10:00', 'category' => 'sightseeing', 'title' => 'Musée d\'Orsay', 'location' => "Rue de la Légion d'Honneur"],
            ['day' => 16, 'time' => '14:00', 'category' => 'activity', 'title' => 'Montmartre Walking Tour', 'location' => 'Place du Tertre'],
            ['day' => 17, 'time' => '09:30', 'category' => 'transport', 'title' => 'Day Trip to Versailles', 'location' => 'RER C from Saint-Michel'],
            ['day' => 17, 'time' => '20:00', 'category' => 'dining', 'title' => 'Dinner in Le Marais', 'location' => '3rd Arrondissement'],
            ['day' => 25, 'time' => '08:00', 'category' => 'sightseeing', 'title' => 'Senso-ji Temple at Sunrise', 'location' => 'Asakusa, Tokyo'],
            ['day' => 26, 'time' => '12:30', 'category' => 'dining', 'title' => 'Tsukiji Outer Market Food Crawl', 'location' => 'Chuo City, Tokyo'],
            ['day' => 27, 'category' => 'activity', 'title' => 'Free day — shopping in Shibuya', 'location' => 'Shibuya, Tokyo'],
        ];

        foreach ($suggestions as $event) {
            ItineraryEvent::create([
                'user_id' => $user->id,
                'trip_id' => null,
                'source' => ItineraryEvent::SOURCE_AI,
                'category' => $event['category'],
                'title' => $event['title'],
                'location' => $event['location'],
                'event_date' => $base->copy()->addDays($event['day'] - 1)->toDateString(),
                // No time at all renders as "All day" in the day panel.
                'start_time' => $event['time'] ?? null,
            ]);
        }
    }
}
