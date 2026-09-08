<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Turns "plan me three days in Paris" into calendar entries.
 *
 * Two mechanisms are doing different jobs here, and it's worth being clear
 * about which does what:
 *
 *   schema()       guarantees the SHAPE. The provider enforces it, so the
 *                  response is always an object with an events array whose
 *                  items have these keys and these types. No parsing of
 *                  free text, no "the model forgot a comma" failures.
 *
 *   instructions() teaches the CONTENT conventions that a schema cannot
 *                  express: that a flight belongs at the start of day one,
 *                  that a hotel gets a check-in and a check-out entry, that
 *                  meals sit between sights rather than stacked at 9am.
 *                  Those are shown as few-shot examples because they're a
 *                  pattern to imitate, not a rule to reason about — the
 *                  opposite of SupportAgent, which is zero-shot because its
 *                  content is policy facts.
 *
 * Nothing here books anything. Every entry is written with source = 'ai',
 * which the calendar renders as a blue "Planned event" dot, distinct from
 * the gold dots that come from a paid order.
 */
#[MaxTokens(4096)]
class ItineraryPlanner implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Matches ItineraryController::CATEGORY_COLORS, so every category the
     * planner can emit already has a colour on the day panel. Covers the
     * three booking types the site sells plus the connective tissue of a
     * real day.
     */
    public const CATEGORIES = ['flight', 'hotel', 'sightseeing', 'dining', 'activity', 'transport'];

    /**
     * A plan longer than this is almost certainly the model running away.
     *
     * Enforced in ItineraryPlanWriter, not in schema() — Gemini's response
     * schema is a restricted subset of JSON Schema and rejects the whole
     * request with "Request contains an invalid argument" if an array
     * carries maxItems. Capping on write is the stronger place anyway: it
     * holds regardless of which provider produced the JSON.
     */
    public const MAX_EVENTS = 40;

    public function provider(): string
    {
        return (string) config('ai.default');
    }

    public function model(): ?string
    {
        return config('ai.agent_model') ?: null;
    }

    /**
     * The structured output contract. The provider is handed this schema
     * and returns JSON conforming to it.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()
                ->description('Short name for the trip, e.g. "3 days in Paris".'),

            'destination' => $schema->string()
                ->description('City and country, e.g. "Paris, France".'),

            'summary' => $schema->string()
                ->description('One or two sentences describing the plan, shown in the chat panel.'),

            'events' => $schema->array()->items(
                $schema->object([
                    'date' => $schema->string()
                        ->description('Calendar date as YYYY-MM-DD. Must be today or later.'),
                    'time' => $schema->string()
                        ->description('Start time as HH:MM in 24-hour form. Empty string for an all-day entry.'),
                    'category' => $schema->string()->enum(self::CATEGORIES)
                        ->description('flight and hotel for travel and stays; sightseeing, dining or activity for things to do; transport for getting between them.'),
                    'title' => $schema->string()
                        ->description('What the entry is, e.g. "Louvre Museum Visit".'),
                    'location' => $schema->string()
                        ->description('Where it happens, e.g. "Rue de Rivoli, Paris". Empty string if not applicable.'),
                ])
            )->description('Every entry in the plan, in chronological order.'),
        ];
    }

    public function instructions(): Stringable|string
    {
        // Injected rather than hardcoded: the model has no reliable idea
        // what day it is, and every date it emits is validated against
        // today before being written.
        $today = now()->toDateString();
        $categories = implode(', ', self::CATEGORIES);

        return <<<PROMPT
        You are the Voyagr itinerary planner. You turn a travel request into a
        day-by-day plan that gets written onto the user's calendar.

        Today is {$today}. Resolve relative dates ("next month", "the first
        week of March") against it. Never place an entry before today.

        // What a plan must contain

        Cover all three things Voyagr sells, when the request implies them:

        - flight    getting there and getting back
        - hotel     one check-in entry and one check-out entry
        - things to do   sightseeing, dining or activity entries

        Give each day a realistic shape: a morning sight, lunch, an afternoon
        activity, dinner. Three to five entries per day is right. Do not stack
        everything in the morning, and do not invent prices, airlines, flight
        numbers or confirmation numbers - name the place, not the booking.

        Use only these categories: {$categories}

        // Examples

        Request: "Plan 2 days in Paris starting 2026-04-15, flying from New York"

        {
          "title": "2 days in Paris",
          "destination": "Paris, France",
          "summary": "Two days built around the Louvre and the Eiffel Tower, with an evening on the Seine.",
          "events": [
            {"date": "2026-04-15", "time": "07:15", "category": "flight", "title": "Flight to Paris", "location": "New York (JFK) to Paris (CDG)"},
            {"date": "2026-04-15", "time": "15:00", "category": "hotel", "title": "Hotel check-in", "location": "6th Arrondissement, Paris"},
            {"date": "2026-04-15", "time": "17:30", "category": "sightseeing", "title": "Walk the Champs-Elysees", "location": "8th Arrondissement, Paris"},
            {"date": "2026-04-15", "time": "20:00", "category": "dining", "title": "Dinner in Le Marais", "location": "3rd Arrondissement, Paris"},
            {"date": "2026-04-16", "time": "09:00", "category": "sightseeing", "title": "Louvre Museum", "location": "Rue de Rivoli, Paris"},
            {"date": "2026-04-16", "time": "13:00", "category": "dining", "title": "Lunch at Cafe de Flore", "location": "Saint-Germain-des-Pres, Paris"},
            {"date": "2026-04-16", "time": "15:30", "category": "sightseeing", "title": "Eiffel Tower", "location": "Champ de Mars, Paris"},
            {"date": "2026-04-16", "time": "19:00", "category": "activity", "title": "Seine river dinner cruise", "location": "Pont de l'Alma, Paris"},
            {"date": "2026-04-17", "time": "11:00", "category": "hotel", "title": "Hotel check-out", "location": "6th Arrondissement, Paris"},
            {"date": "2026-04-17", "time": "15:40", "category": "flight", "title": "Flight home", "location": "Paris (CDG) to New York (JFK)"}
          ]
        }

        Request: "I want a relaxed weekend in Bali next month, no flights needed"

        {
          "title": "Relaxed weekend in Bali",
          "destination": "Bali, Indonesia",
          "summary": "A slow two days in Ubud - rice terraces, a temple, and long meals.",
          "events": [
            {"date": "2026-05-09", "time": "14:00", "category": "hotel", "title": "Resort check-in", "location": "Ubud, Bali"},
            {"date": "2026-05-09", "time": "17:00", "category": "sightseeing", "title": "Tegallalang rice terraces at sunset", "location": "Tegallalang, Bali"},
            {"date": "2026-05-09", "time": "19:30", "category": "dining", "title": "Dinner in central Ubud", "location": "Ubud, Bali"},
            {"date": "2026-05-10", "time": "", "category": "activity", "title": "Free morning - spa and pool", "location": "Ubud, Bali"},
            {"date": "2026-05-10", "time": "16:00", "category": "sightseeing", "title": "Tirta Empul water temple", "location": "Tampaksiring, Bali"},
            {"date": "2026-05-11", "time": "11:00", "category": "hotel", "title": "Resort check-out", "location": "Ubud, Bali"}
          ]
        }

        Note in the second example: no flights were asked for, so none were
        added, and an all-day entry uses an empty time. Follow the request -
        do not add legs the user did not ask for.

        // Continuing a conversation

        The prompt may include the conversation so far. Everything the user
        already said still counts: if they named the destination two
        messages ago and the date in the newest one, you now have both -
        build the plan. Never ask again for something already given.

        A short newest message is usually a CHANGE to the plan you just
        made, not a new request. "9th september", "make it 5 days", "add
        more food" all mean: rebuild the same trip with that one change,
        keeping the destination from earlier in the conversation. Return
        the full revised plan, not just the changed day.

        // Plan first, ask almost never

        The destination is the ONLY thing you need from the user. Fill in
        anything else yourself rather than asking for it:

        - no dates given      -> start one week from today
        - no length given     -> plan three days
        - no origin city      -> leave flights out entirely

        "Plan a 3-day itinerary in Paris" is a complete request. So is
        "Tokyo". Build the plan.

        // The events array is the answer

        A summary is a caption for the plan, never a replacement for it.
        If your summary describes a trip - days, sights, meals - then
        events MUST contain those entries. Writing "here is your 3-day
        Tokyo plan" while leaving events empty is a broken response; the
        user sees nothing on their calendar.

        Return empty events in exactly ONE case: you cannot tell which
        destination is wanted. Then, and only then, use summary to ask for
        the destination alone.
        PROMPT;
    }
}
