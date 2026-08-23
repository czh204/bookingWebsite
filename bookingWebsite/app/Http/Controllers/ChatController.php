<?php

namespace App\Http\Controllers;

use App\Ai\Agents\SupportAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Attributes\Provider as ProviderAttribute;
use Laravel\Ai\Enums\Lab;
use ReflectionClass;
use Throwable;

class ChatController extends Controller
{
    /**
     * Keyword => fallback category. Checked in order, so action words
     * ("cancel", "refund") win over a merely-mentioned "hotel" — someone
     * asking to cancel a hotel booking wants the booking-process answer,
     * not hotel-search tips.
     */
    protected const BOOKING_KEYWORDS = [
        'cancel', 'refund', 'modify', 'reservation', 'booking', 'book',
        'payment', 'pay', 'confirm', 'itinerary', 'receipt', 'change my',
    ];

    protected const HOTEL_KEYWORDS = [
        'hotel', 'room', 'stay', 'resort', 'accommodation', 'amenit',
        'check-in', 'checkin', 'check-out', 'checkout', 'star rating',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'conversation_id' => ['nullable', 'string', 'max:36'],
        ]);

        // The widget is available to guests, so conversations are keyed to the
        // signed-in user when there is one and to the session otherwise.
        $participant = $request->user();

        if (! $this->activeProviderIsConfigured()) {
            return response()->json([
                'reply' => $this->fallbackReply($validated['message']),
                'conversation_id' => null,
                'configured' => false,
            ]);
        }

        try {
            $agent = new SupportAgent;

            $agent = $validated['conversation_id'] ?? false
                ? $agent->continue($validated['conversation_id'], as: $participant)
                : ($participant ? $agent->forUser($participant) : $agent);

            $response = $agent->prompt($validated['message']);

            return response()->json([
                'reply' => (string) $response,
                'conversation_id' => $response->conversationId ?? null,
                'configured' => true,
            ]);
        } catch (Throwable $e) {
            Log::error('Support assistant failed', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json([
                'reply' => $this->fallbackReply($validated['message']),
                'conversation_id' => $validated['conversation_id'] ?? null,
                'configured' => true,
            ], 500);
        }
    }

    /**
     * Whether an API key exists for whichever provider SupportAgent will
     * actually use. Mirrors Laravel\Ai\Promptable::getProvidersAndModels()'s
     * own resolution order — the agent's #[Provider(...)] class attribute
     * wins if present, otherwise it falls back to config('ai.default').
     * A naive check against one hardcoded provider name (or even against
     * config('ai.default') alone) silently disagrees with this the moment
     * the attribute and the config default point at different providers.
     */
    protected function activeProviderIsConfigured(): bool
    {
        $attributes = (new ReflectionClass(SupportAgent::class))->getAttributes(ProviderAttribute::class);
        $provider = $attributes === [] ? config('ai.default') : $attributes[0]->newInstance()->value;

        // ->value may be a Lab enum, a plain string, or (for failover) an
        // array of providers — in the array case, being configured for
        // just one of them is enough to attempt a request.
        $names = collect(is_array($provider) ? $provider : [$provider])
            ->map(fn ($p) => $p instanceof Lab ? $p->value : $p);

        return $names->contains(fn ($name) => filled(config("ai.providers.{$name}.key")));
    }

    /**
     * Static, self-service answer used whenever the AI can't respond —
     * no API key configured, or the request threw. Picks a category by
     * keyword so the fallback is at least relevant to what was asked,
     * rather than one generic "something went wrong" for everything.
     */
    protected function fallbackReply(string $message): string
    {
        $message = strtolower($message);

        foreach (self::BOOKING_KEYWORDS as $keyword) {
            if (str_contains($message, $keyword)) {
                return $this->bookingFallback();
            }
        }

        foreach (self::HOTEL_KEYWORDS as $keyword) {
            if (str_contains($message, $keyword)) {
                return $this->hotelFallback();
            }
        }

        return $this->generalFallback();
    }

    protected function bookingFallback(): string
    {
        return "I'm having trouble reaching the assistant right now, but here's what I can tell you: "
            .'For cancelling or modify a booking from My Trips - select "Cancel Reservation" or "Modify Reservation". '
            .'Refunds are full within 24 hours of purchase and partial after, based on fare rules. '
            .'We accept major credit/debit cards and popular e-wallets inclduing TnG and Boost. ';
    }

    protected function hotelFallback(): string
    {
        return "I'm having trouble reaching the assistant right now, so I can't search on your behalf. "
            .'You can browse and filter hotels directly on our Hotels page — search by destination, '
            .'price range, star rating, and amenities like WiFi, Pool, or Spa. '
            .'Please try asking me again shortly.';
    }

    protected function generalFallback(): string
    {
        return 'Sorry — I hit an error handling that. Please try again, '
            .'or contact a human agent if it keeps happening.';
    }
}
