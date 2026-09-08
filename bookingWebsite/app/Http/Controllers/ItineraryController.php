<?php

namespace App\Http\Controllers;

use App\Models\ItineraryEvent;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ItineraryController extends Controller
{
    /**
     * Quick prompts shown above the planner chat. The assistant itself is
     * not wired up yet, so these are display-only for now — see the
     * "coming soon" note in the view.
     */
    protected const QUICK_QUESTIONS = [
        'Plan a 3-day itinerary in Paris',
        'Best restaurants in Bali for dinner',
        'Family-friendly activities in Tokyo',
        'Hidden gems in Santorini',
    ];

    /**
     * Left accent colour for a day-panel event, by category. Presentation
     * only, so it lives here rather than in the events table.
     */
    protected const CATEGORY_COLORS = [
        'flight' => '#2563eb',
        'hotel' => '#7c3aed',
        'dining' => '#d97706',
        'sightseeing' => '#16a34a',
        'activity' => '#0891b2',
        'transport' => '#64748b',
    ];

    public function index(Request $request)
    {
        $month = $this->resolveMonth($request->query('month'));
        $selected = $this->resolveSelectedDate($request->query('date'), $month);

        $events = $this->loadEventsForMonth($month);
        $eventsByDate = $events->groupBy(fn (ItineraryEvent $e) => $e->event_date->toDateString());

        $selectedEvents = $eventsByDate->get($selected->toDateString(), collect());

        // Split once here rather than twice in the view: isPast() walks the
        // order's items and events, so it shouldn't be called per row.
        [$pastBookings, $bookings] = $this->loadBookings()
            ->partition(fn (Order $order) => $order->isPast());

        return view('itinerary.index', [
            'month' => $month,
            'selected' => $selected,
            'weeks' => $this->buildWeeks($month, $selected, $eventsByDate),
            'plannedEvents' => $selectedEvents->where('source', ItineraryEvent::SOURCE_AI)->values(),
            'bookingEvents' => $selectedEvents->where('source', ItineraryEvent::SOURCE_BOOKING)->values(),
            'bookings' => $bookings->values(),
            // Most recently travelled first, so the trip you just got back
            // from is at the top rather than buried under older ones.
            'pastBookings' => $pastBookings
                ->sortByDesc(fn (Order $order) => $order->travelDate())
                ->values(),
            'refundHours' => Order::REFUND_PROCESSING_HOURS,
            'quickQuestions' => self::QUICK_QUESTIONS,
            'categoryColors' => self::CATEGORY_COLORS,
            'view' => $this->resolveView($request),
        ]);
    }

    /**
     * Which half of the page to show. Bookings is the landing view — the
     * calendar and its planner are opened deliberately.
     *
     * A ?month= or ?date= in the URL implies the calendar even without
     * ?view=, so a hand-typed or older link still lands somewhere sensible
     * rather than on a bookings list that ignores the date it was given.
     */
    protected function resolveView(Request $request): string
    {
        if ($request->query('view') === 'calendar'
            || $request->filled('month')
            || $request->filled('date')) {
            return 'calendar';
        }

        return 'bookings';
    }

    /**
     * Refund a booking.
     *
     * Approval is automatic — this is a mock payment system, so there is
     * no acquirer to ask and nothing to actually credit back. What the
     * customer is told is deliberately honest about that: Voyagr has
     * approved it, the money moves within 48 hours, and the final timing
     * belongs to whoever holds it (see the FAQ).
     */
    public function refund(Request $request, Order $order)
    {
        // Ownership, not just authentication: without this, any signed-in
        // user could refund someone else's booking by guessing its id.
        abort_unless($order->user_id === auth()->id(), 404);

        // Re-checked server side. The button is hidden on a booking that
        // can't be refunded, but a hidden button is a UI convenience, not
        // a control — a replayed POST has to be turned away here.
        if (! $order->isRefundable()) {
            return redirect()
                ->route('itinerary.index')
                ->with('refund_error', "Booking {$order->reference} can no longer be refunded.");
        }

        $order->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        // The calendar entries go with it: a refunded booking is not a
        // trip you're taking, so leaving it on the calendar would have the
        // page contradicting the status shown right next to it.
        $order->events()->delete();

        return redirect()
            ->route('itinerary.index')
            ->with('refund_success', $order->reference);
    }

    /**
     * ?month=YYYY-MM, falling back to the current month when absent or
     * unparseable rather than erroring on a hand-edited URL.
     */
    protected function resolveMonth(?string $month): CarbonImmutable
    {
        // 01-12 only: without the month bound, "2026-13" would silently
        // overflow into January 2027 rather than being rejected.
        if (is_string($month) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            try {
                return CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
            } catch (\Throwable) {
                // Fall through to the current month.
            }
        }

        return CarbonImmutable::now()->startOfMonth();
    }

    /**
     * The day whose events the panel below the grid shows. A date outside
     * the month being viewed is ignored, so ?month= and ?date= can never
     * disagree; with no usable date we highlight today when it falls in
     * this month, and the 1st otherwise.
     */
    protected function resolveSelectedDate(?string $date, CarbonImmutable $month): CarbonImmutable
    {
        if (is_string($date) && preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $date)) {
            try {
                $parsed = CarbonImmutable::createFromFormat('Y-m-d', $date)->startOfDay();

                // Round-trip check rejects a day that doesn't exist —
                // "2026-02-31" parses, but as March 3rd.
                if ($parsed->format('Y-m-d') === $date && $parsed->isSameMonth($month)) {
                    return $parsed;
                }
            } catch (\Throwable) {
                // Fall through to the default below.
            }
        }

        $today = CarbonImmutable::today();

        return $today->isSameMonth($month) ? $today : $month;
    }

    /**
     * Only the visible month is queried — the grid never shows a day
     * outside it as anything but a blank spacer.
     */
    protected function loadEventsForMonth(CarbonImmutable $month): Collection
    {
        if (! auth()->check()) {
            return collect();
        }

        return ItineraryEvent::query()
            ->where('user_id', auth()->id())
            ->whereBetween('event_date', [$month->toDateString(), $month->endOfMonth()->toDateString()])
            ->orderBy('event_date')
            ->orderByRaw('start_time IS NULL, start_time')
            ->get();
    }

    /**
     * Every confirmed order this user has placed, newest first — the "My
     * Bookings" list under the calendar.
     *
     * Unlike the calendar above it, this is not scoped to the visible
     * month: it's the full booking history, and each row links across to
     * whichever month its first calendar entry sits in.
     */
    protected function loadBookings(): Collection
    {
        if (! auth()->check()) {
            return collect();
        }

        return Order::query()
            ->where('user_id', auth()->id())
            ->with(['items', 'events'])
            ->latest('created_at')
            ->get()
            ->map(function (Order $order) {
                // The date the calendar would jump to for this booking.
                $firstEvent = $order->events->first();
                $order->setAttribute('calendar_date', $firstEvent?->event_date);

                return $order;
            });
    }

    /**
     * Builds the six-ish week rows the grid renders, padded out to whole
     * Sunday–Saturday weeks so every row has exactly seven cells.
     */
    protected function buildWeeks(CarbonImmutable $month, CarbonImmutable $selected, Collection $eventsByDate): array
    {
        $cursor = $month->startOfWeek(CarbonImmutable::SUNDAY);
        $end = $month->endOfMonth()->endOfWeek(CarbonImmutable::SATURDAY);
        $today = CarbonImmutable::today();

        $weeks = [];
        $week = [];

        while ($cursor <= $end) {
            $key = $cursor->toDateString();
            $dayEvents = $eventsByDate->get($key, collect());
            $inMonth = $cursor->isSameMonth($month);

            $week[] = (object) [
                'date' => $cursor,
                'day' => $cursor->day,
                'in_month' => $inMonth,
                'is_today' => $cursor->isSameDay($today),
                'is_selected' => $inMonth && $cursor->isSameDay($selected),
                // Dots are per-source, not per-event: a day with four AI
                // entries still shows one blue dot.
                'has_ai' => $dayEvents->contains(fn (ItineraryEvent $e) => $e->source === ItineraryEvent::SOURCE_AI),
                'has_booking' => $dayEvents->contains(fn (ItineraryEvent $e) => $e->source === ItineraryEvent::SOURCE_BOOKING),
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }

            $cursor = $cursor->addDay();
        }

        return $weeks;
    }
}
