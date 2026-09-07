<?php

namespace App\Http\Controllers;

use App\Ai\Agents\SupportAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Attributes\Provider as ProviderAttribute;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Models\Conversation;
use ReflectionClass;
use Throwable;

class ChatController extends Controller
{
    /**
     * The conversation ID is kept in the session rather than round-tripped
     * through the browser: it survives page navigation for free, works the
     * same for guests as for signed-in users, and can't be swapped by a
     * client for someone else's conversation UUID.
     */
    protected const SESSION_KEY = 'support_agent_conversation_id';

    /**
     * How many past messages to replay into the widget on page load.
     */
    protected const HISTORY_LIMIT = 50;

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
        ]);

        // The widget is available to guests, so the participant is attached
        // only when signed in; the conversation itself is tracked in session
        // either way (see resolveConversationId).
        $participant = $request->user();

        if (! $this->activeProviderIsConfigured()) {
            return response()->json([
                'reply' => $this->fallbackReply($validated['message']),
                'configured' => false,
            ]);
        }

        try {
            $conversationId = $this->resolveConversationId($request, $validated['message'], $participant);

            $response = (new SupportAgent)
                ->continue($conversationId, as: $participant)
                ->prompt($validated['message']);

            return response()->json([
                'reply' => (string) $response,
                'configured' => true,
            ]);
        } catch (Throwable $e) {
            Log::error('Support assistant failed', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json([
                'reply' => $this->fallbackReply($validated['message']),
                'configured' => true,
            ], 500);
        }
    }

    /**
     * Replays the current conversation so the widget can restore its
     * transcript after a page navigation.
     */
    public function history(Request $request): JsonResponse
    {
        $conversationId = $request->session()->get(self::SESSION_KEY);

        if (! $conversationId || ! $this->conversationExists($conversationId)) {
            return response()->json(['messages' => []]);
        }

        $messages = resolve(ConversationStore::class)
            ->getLatestConversationMessages($conversationId, self::HISTORY_LIMIT)
            ->map(fn ($message) => [
                'role' => $message->role instanceof \BackedEnum ? $message->role->value : (string) $message->role,
                'content' => (string) ($message->content ?? ''),
            ])
            // Tool-call turns come back with empty content and would render
            // as blank bubbles; only user/assistant text belongs in the UI.
            ->filter(fn (array $message) => $message['content'] !== ''
                && in_array($message['role'], ['user', 'assistant'], true))
            ->values();

        return response()->json(['messages' => $messages]);
    }

    /**
     * Returns the session's conversation ID, creating the conversation row
     * first if there isn't a usable one yet.
     *
     * The row is created explicitly rather than letting the SDK do it on
     * first prompt, because Laravel\Ai\Middleware\RememberConversation only
     * persists a turn when there is a participant OR an existing
     * conversation — so a guest would otherwise never get either, and their
     * history would silently never save. Creating it up front also skips
     * the SDK's extra title-generation API call, which nothing here shows.
     */
    protected function resolveConversationId(Request $request, string $message, ?object $participant): string
    {
        $existing = $request->session()->get(self::SESSION_KEY);

        if ($existing && $this->conversationExists($existing)) {
            return $existing;
        }

        $conversationId = resolve(ConversationStore::class)->storeConversation(
            $participant ? Conversation::participantType($participant) : null,
            $participant ? Conversation::participantKey($participant) : null,
            Str::limit($message, 50, preserveWords: true),
        );

        $request->session()->put(self::SESSION_KEY, $conversationId);

        return $conversationId;
    }

    /**
     * Guards against a stale session pointing at a conversation that no
     * longer exists — after a migrate:fresh, for example — which would
     * otherwise orphan every new message under a dead conversation ID.
     */
    protected function conversationExists(string $conversationId): bool
    {
        return DB::connection(config('ai.conversations.connection'))
            ->table(config('ai.conversations.tables.conversations', 'agent_conversations'))
            ->where('id', $conversationId)
            ->exists();
    }

    /**
     * Whether an API key exists for whichever provider SupportAgent will
     * actually use. Mirrors Laravel\Ai\Promptable::getProvidersAndModels()'s
     * own resolution order, in its order of precedence:
     *
     *   1. a provider() method on the agent  (what we use — AI_PROVIDER)
     *   2. a #[Provider(...)] class attribute
     *   3. config('ai.default')
     *
     * Checking any one of those alone silently disagrees with the SDK the
     * moment they stop pointing at the same provider, which shows up as
     * the fallback answer firing when a key is present, or a live request
     * being attempted when it isn't.
     */
    protected function activeProviderIsConfigured(): bool
    {
        $agent = app(SupportAgent::class);

        if (method_exists($agent, 'provider')) {
            $provider = $agent->provider();
        } else {
            $attributes = (new ReflectionClass(SupportAgent::class))->getAttributes(ProviderAttribute::class);
            $provider = $attributes === [] ? config('ai.default') : $attributes[0]->newInstance()->value;
        }

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
        // Mirrors the booking policies in App\Ai\Agents\SupportAgent — if
        // one changes, this has to change with it, or the offline answer
        // contradicts the live one.
        return "I'm having trouble reaching the assistant right now, but here's what I can tell you: "
            .'you can view and cancel bookings from the My Bookings page — open the booking and '
            .'select "Cancel Reservation". Whether a cancellation is allowed and how much is refunded '
            .'is set by the airline or hotel, not by Voyagr; approved refunds are processed within 48 hours. '
            .'Modifications can\'t be made through the website after purchase — contact the phone number '
            .'under the booking tab, as this is subject to the provider\'s own policy. '
            .'We accept major credit/debit cards and popular e-wallets including TnG and Boost.';
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
