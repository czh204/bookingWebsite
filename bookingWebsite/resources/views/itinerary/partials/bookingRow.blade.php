{{--
    One booking row, shared by the upcoming and past lists so the two can
    never drift apart.

    $booking — the Order
    $past    — true in the "Past Bookings" list, which drops the refund
               button and the calendar link (there is nothing left to
               refund, and nothing upcoming to jump to)
--}}
@php($past = $past ?? false)

<div class="booking-row">
    <div class="booking-row-head">
        <div>
            <span class="booking-ref">{{ $booking->reference }}</span>
            <span class="booking-status status-{{ $booking->status }}">{{ $booking->status }}</span>
            @if ($past)
                <span class="past-badge">Completed</span>
            @endif
        </div>
        <div class="booking-total">${{ number_format($booking->total, 2) }}</div>
    </div>

    <div class="booking-meta">
        <span><i class="bi bi-calendar3"></i> Booked {{ $booking->created_at->format('j M Y') }}</span>
        <span><i class="bi bi-credit-card"></i> {{ $booking->payment_label }}</span>
        <span><i class="bi bi-bag"></i> {{ $booking->items->count() }} item{{ $booking->items->count() === 1 ? '' : 's' }}</span>
    </div>

    @foreach ($booking->items as $item)
        <div class="booking-item">
            <span class="booking-item-icon">
                <i class="bi {{ ['flight' => 'bi-airplane', 'hotel' => 'bi-building', 'attraction' => 'bi-ticket-perforated'][$item->type] ?? 'bi-dot' }}"></i>
            </span>
            <div class="flex-grow-1">
                <div class="booking-item-title">{{ $item->title }}</div>
                <div class="booking-item-meta">
                    @if ($item->booking_date)
                        <strong>{{ $item->booking_date->format('j M Y') }}</strong> ·
                    @endif
                    {{ $item->meta }}@if ($item->quantity > 1) · ×{{ $item->quantity }}@endif
                </div>
            </div>
            <div class="booking-item-price">${{ number_format($item->line_total, 2) }}</div>
        </div>
    @endforeach

    {{-- A booking refunded earlier keeps saying so, so the customer isn't
         left wondering whether the request actually went through. --}}
    @if ($booking->refunded_at)
        <div class="booking-refunded-note">
            <i class="bi bi-check-circle-fill"></i>
            Refund approved {{ $booking->refunded_at->format('j M Y, g:ia') }} —
            processed within {{ \App\Models\Order::REFUND_PROCESSING_HOURS }} hours, subject to the
            provider's own timings.
        </div>
    @endif

    @if (! $past)
        <div class="booking-actions">
            @if ($booking->calendar_date)
                {{-- Opens the calendar view on the month and day this
                     booking's first entry sits on. --}}
                <a class="booking-jump"
                   href="{{ route('itinerary.index', ['view' => 'calendar', 'month' => $booking->calendar_date->format('Y-m'), 'date' => $booking->calendar_date->toDateString()]) }}">
                    <i class="bi bi-calendar-check"></i>
                    Show on calendar — {{ $booking->calendar_date->format('j M Y') }}
                </a>
            @else
                <span></span>
            @endif

            @if ($booking->isRefundable())
                {{-- Opens the shared confirmation dialog; the data-*
                     attributes are what it shows back to the customer. --}}
                <button type="button"
                        class="btn btn-refund js-refund-btn"
                        data-reference="{{ $booking->reference }}"
                        data-total="${{ number_format($booking->total, 2) }}"
                        data-action="{{ route('itinerary.refund', $booking) }}">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Request Refund
                </button>
            @endif
        </div>
    @endif
</div>
