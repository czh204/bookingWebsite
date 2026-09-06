@extends('layouts.homeApp')

@section('title', 'My Bookings')

@push('styles')
<style>
    /* ---------- Planner hero ---------- */
    .planner-hero {
        background: linear-gradient(135deg, #2a3a5c 0%, var(--navy) 55%, var(--navy-dark) 100%);
        color: #fff;
        padding: 2.25rem 1.5rem 5.5rem;
    }

    .planner-hero h1 { font-size: 2rem; margin-bottom: .25rem; }
    .planner-hero p { opacity: .85; margin: 0; font-size: .95rem; }

    .btn-toggle-panel {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.35);
        border-radius: .6rem;
        color: #fff;
        font-weight: 500;
        font-size: .9rem;
        padding: .5rem 1.1rem;
        white-space: nowrap;
    }

    .btn-toggle-panel:hover { background: rgba(255,255,255,.2); color: #fff; }

    /* ---------- Layout ---------- */
    .planner-body {
        max-width: 1240px;
        margin: -4.25rem auto 0;
        padding: 0 1.5rem 4rem;
        position: relative;
        z-index: 2;
    }

    /* Hidden panel: the calendar column takes the full width instead. */
    .planner-body.panel-hidden .planner-panel-col { display: none; }
    .planner-body.panel-hidden .planner-calendar-col { flex: 0 0 100%; max-width: 100%; }

    .planner-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .9rem;
        overflow: hidden;
    }

    /* ---------- Side panel ---------- */
    .planner-panel {
        display: flex;
        flex-direction: column;
        height: 640px;
        position: sticky;
        top: 1.5rem;
    }

    .planner-panel-header {
        border-bottom: 1px solid var(--border-soft);
        background: var(--cream);
        padding: .85rem 1rem;
        font-size: .92rem;
        font-weight: 700;
        color: var(--navy-dark);
        display: flex;
        align-items: center;
        gap: .45rem;
        flex-shrink: 0;
    }

    .planner-panel-header i { color: var(--navy); }

    .planner-tab-pane { flex: 1; display: none; flex-direction: column; min-height: 0; }
    .planner-tab-pane.active { display: flex; }

    /* ---------- View switch (bookings ↔ calendar) ---------- */
    .view-switch {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .9rem;
        padding: .4rem;
        margin-bottom: 1.25rem;
    }

    .view-switch-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: .6rem;
        padding: .6rem 1.1rem;
        font-size: .9rem;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
    }

    .view-switch-btn:hover { background: var(--cream); color: var(--navy-dark); }

    .view-switch-btn.active {
        background: var(--navy);
        color: #fff;
    }

    .view-switch-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: #e7eefb;
        color: #2563eb;
        font-size: .7rem;
        padding: .1rem .4rem;
    }

    .view-switch-btn.active .view-switch-badge { background: rgba(255,255,255,.2); color: #fff; }

    /* Marks the AI planner as living inside this page. */
    .hero-ai-hint {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.3);
        border-radius: 999px;
        padding: .05rem .6rem;
        font-weight: 600;
        white-space: nowrap;
    }

    /* ---------- AI planner chat (display only for now) ---------- */
    .planner-quick {
        padding: 1rem;
        border-bottom: 1px solid var(--border-soft);
        flex-shrink: 0;
    }

    .planner-quick-label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: .6rem;
    }

    .planner-quick-btn {
        display: block;
        width: 100%;
        text-align: left;
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .55rem;
        padding: .55rem .85rem;
        font-size: .84rem;
        color: var(--navy-dark);
        margin-bottom: .5rem;
    }

    .planner-quick-btn:last-child { margin-bottom: 0; }
    .planner-quick-btn:first-child { background: #eef2f9; border-color: #d5deed; }

    .planner-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    .planner-message { display: flex; align-items: flex-start; gap: .55rem; }

    .planner-message-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--navy);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        flex-shrink: 0;
    }

    .planner-bubble {
        background: var(--cream);
        border-radius: .8rem;
        padding: .65rem .9rem;
        font-size: .85rem;
        line-height: 1.45;
        color: var(--navy-dark);
        max-width: 85%;
    }

    .planner-input-row {
        display: flex;
        align-items: center;
        gap: .55rem;
        padding: .85rem 1rem;
        border-top: 1px solid var(--border-soft);
        flex-shrink: 0;
    }

    .planner-input {
        flex: 1;
        border: 1px solid var(--border-soft);
        background: var(--cream);
        border-radius: 999px;
        padding: .6rem 1rem;
        font-size: .85rem;
    }

    .planner-send-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--navy);
        color: #fff;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .planner-input:disabled, .planner-send-btn:disabled, .planner-quick-btn:disabled {
        opacity: .65;
        cursor: not-allowed;
    }

    .planner-soon-note {
        font-size: .75rem;
        color: var(--text-muted);
        text-align: center;
        padding: .5rem 1rem 0;
    }

    /* ---------- Calendar ---------- */
    .calendar-header {
        background: var(--navy);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
    }

    .calendar-title { font-weight: 700; font-size: 1.15rem; }

    .calendar-nav-btn {
        background: none;
        border: none;
        color: rgba(255,255,255,.85);
        font-size: 1.2rem;
        line-height: 1;
        padding: .2rem .5rem;
        text-decoration: none;
        border-radius: .4rem;
    }

    .calendar-nav-btn:hover { color: #fff; background: rgba(255,255,255,.12); }

    .calendar-grid { padding: 1rem 1.25rem 0; }

    .calendar-weekdays, .calendar-week {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: .35rem;
    }

    .calendar-weekday {
        text-align: center;
        font-size: .78rem;
        font-weight: 600;
        color: var(--text-muted);
        padding: .5rem 0;
    }

    .calendar-day {
        aspect-ratio: 1 / 1;
        /* Square cells, but capped so hiding the panel doesn't stretch
           the grid into a full-width wall of very tall rows. */
        max-height: 90px;
        border: none;
        background: none;
        border-radius: .7rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        font-size: .95rem;
        color: var(--navy-dark);
        text-decoration: none;
        position: relative;
    }

    .calendar-day:hover { background: var(--cream); color: var(--navy-dark); }
    .calendar-day.is-blank { pointer-events: none; }

    .calendar-day.is-today { font-weight: 700; box-shadow: inset 0 0 0 1px var(--border-soft); }

    .calendar-day.is-selected,
    .calendar-day.is-selected:hover {
        background: var(--navy);
        color: #fff;
        font-weight: 700;
    }

    .calendar-dots { display: flex; gap: .2rem; height: 6px; }

    .calendar-dot { width: 6px; height: 6px; border-radius: 50%; }
    .dot-ai { background: #2563eb; }
    .dot-booking { background: var(--gold); }
    /* On the navy selected cell the blue dot would disappear. */
    .calendar-day.is-selected .dot-ai { background: #7dabff; }

    .calendar-legend {
        display: flex;
        gap: 1.25rem;
        align-items: center;
        padding: 1rem 1.25rem;
        margin-top: 1rem;
        border-top: 1px solid var(--border-soft);
        font-size: .82rem;
        color: var(--text-muted);
    }

    .calendar-legend span { display: inline-flex; align-items: center; gap: .4rem; }

    /* ---------- Day detail ---------- */
    .day-detail { margin-top: 1.25rem; padding: 1.25rem; }

    .day-detail-date { font-weight: 700; font-size: 1.2rem; color: var(--navy-dark); }
    .day-detail-count { font-size: .82rem; color: var(--text-muted); }

    .btn-add-activity {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        font-size: .88rem;
        padding: .6rem 1.1rem;
        white-space: nowrap;
    }

    .btn-add-activity:hover { background: var(--navy-dark); color: #fff; }
    .btn-add-activity:disabled { opacity: .55; cursor: not-allowed; }

    .day-group-label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: .4rem;
        margin: 1.25rem 0 .65rem;
    }

    .day-event {
        display: flex;
        align-items: center;
        gap: 1rem;
        border: 1px solid var(--border-soft);
        border-radius: .7rem;
        padding: .8rem 1rem;
        margin-bottom: .6rem;
    }

    .day-event:last-child { margin-bottom: 0; }

    .day-event-time {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: .82rem;
        color: var(--navy-dark);
        width: 58px;
        flex-shrink: 0;
    }

    .day-event-accent { width: 3px; align-self: stretch; border-radius: 2px; flex-shrink: 0; }
    .day-event-title { font-weight: 600; color: var(--navy-dark); font-size: .92rem; }
    .day-event-location { font-size: .8rem; color: var(--text-muted); }

    .day-event-ref {
        margin-left: auto;
        font-size: .72rem;
        color: var(--text-muted);
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        white-space: nowrap;
    }

    /* ---------- My Bookings ---------- */
    .bookings-card { margin-top: 1.25rem; padding: 1.25rem; }
    .bookings-title { font-size: 1.2rem; color: var(--navy-dark); margin: 0; }
    .bookings-count { font-size: .82rem; color: var(--text-muted); }
    .bookings-subtitle { font-size: .85rem; color: var(--text-muted); margin: 0 0 1rem; }

    .booking-row {
        border: 1px solid var(--border-soft);
        border-radius: .7rem;
        padding: .95rem 1rem;
    }

    .booking-row + .booking-row { margin-top: .75rem; }

    .booking-row-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .booking-ref {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 700;
        color: var(--navy-dark);
        margin-right: .5rem;
    }

    .booking-status {
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 700;
        padding: .12rem .6rem;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .status-confirmed { background: #dcf3e6; color: #16a34a; }
    .status-cancelled { background: #fde3e3; color: #b91c1c; }
    .status-refunded { background: #ecebe6; color: #4b5563; }

    .booking-total { font-weight: 700; font-size: 1.1rem; color: var(--navy-dark); }

    .booking-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .25rem .9rem;
        font-size: .8rem;
        color: var(--text-muted);
        margin: .4rem 0 .75rem;
    }

    .booking-item {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .5rem 0;
        border-top: 1px solid var(--border-soft);
    }

    .booking-item-icon {
        width: 30px;
        height: 30px;
        border-radius: .5rem;
        background: #e7eefb;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        flex-shrink: 0;
    }

    .booking-item-title { font-weight: 600; color: var(--navy-dark); font-size: .88rem; }
    .booking-item-meta { font-size: .78rem; color: var(--text-muted); }
    .booking-item-price { font-weight: 600; color: var(--navy-dark); font-size: .88rem; white-space: nowrap; }

    .booking-jump {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        margin-top: .75rem;
        font-size: .82rem;
        font-weight: 600;
        color: var(--navy);
        text-decoration: none;
    }

    .booking-jump:hover { text-decoration: underline; }

    /* ---------- Empty states ---------- */
    .planner-empty {
        text-align: center;
        color: var(--text-muted);
        padding: 2.5rem 1.25rem;
        font-size: .88rem;
    }

    .planner-empty i { font-size: 1.6rem; display: block; margin-bottom: .5rem; opacity: .6; }

    @media (max-width: 991.98px) {
        .planner-panel { height: auto; max-height: none; position: static; }
        .planner-messages { min-height: 180px; }
    }
</style>
@endpush

@php
    // Calendar links carry view=calendar so following one never bounces
    // back to the bookings list. Month nav drops ?date=, since the
    // selected day never belongs to a different month.
    $navQuery = fn (string $ym) => ['view' => 'calendar', 'month' => $ym];
@endphp

@section('content')

{{-- ================= HERO ================= --}}
<section class="planner-hero">
    <div class="mx-auto d-flex justify-content-between align-items-start gap-3 flex-wrap" style="max-width: 1240px;">
        <div>
            <h1 class="font-serif fw-bold">My Bookings</h1>
        </div>
        @if ($view === 'calendar')
            <button type="button" class="btn btn-toggle-panel" id="togglePanelBtn">
                <i class="bi bi-x-lg me-1" id="togglePanelIcon"></i><span id="togglePanelLabel">Hide Planner</span>
            </button>
        @endif
    </div>
</section>

{{-- ================= BODY ================= --}}
<div class="planner-body" id="plannerBody">

    {{-- View switch: bookings is where you land, the calendar (and the AI
         planner that lives beside it) is opened on purpose. --}}
    <div class="view-switch">
        <a href="{{ route('itinerary.index') }}"
           class="view-switch-btn {{ $view === 'bookings' ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> My Bookings
        </a>
        <a href="{{ route('itinerary.index', ['view' => 'calendar']) }}"
           class="view-switch-btn {{ $view === 'calendar' ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i> Calendar &amp; AI Planner
            <span class="view-switch-badge"><i class="bi bi-stars"></i></span>
        </a>
    </div>

    <div class="row g-4 {{ $view === 'calendar' ? '' : 'd-none' }}">

        {{-- ---------- Left panel ---------- --}}
        <div class="col-lg-4 planner-panel-col">
            <div class="planner-card planner-panel">

                <div class="planner-panel-header">
                    <i class="bi bi-stars"></i> AI Planner
                </div>

                {{-- UI only — no assistant wired up yet. --}}
                <div class="planner-tab-pane active" id="pane-planner">
                    <div class="planner-quick">
                        <div class="planner-quick-label">Quick Questions</div>
                        @foreach ($quickQuestions as $question)
                            <button type="button" class="planner-quick-btn" disabled>{{ $question }}</button>
                        @endforeach
                    </div>

                    <div class="planner-messages">
                        <div class="planner-message">
                            <span class="planner-message-avatar"><i class="bi bi-stars"></i></span>
                            <div class="planner-bubble">
                                Hi! I'm your AI travel assistant. Tell me your destination and travel dates
                                and I'll build a personalised itinerary for you.
                            </div>
                        </div>
                    </div>

                    <div class="planner-soon-note">
                        <i class="bi bi-info-circle me-1"></i>The planner assistant isn't connected yet.
                    </div>

                    <div class="planner-input-row">
                        <input type="text" class="planner-input" placeholder="Ask about your trip..." disabled>
                        <button type="button" class="planner-send-btn" disabled aria-label="Send">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- ---------- Calendar ---------- --}}
        <div class="col-lg-8 planner-calendar-col">
            <div class="planner-card">
                <div class="calendar-header">
                    <a class="calendar-nav-btn"
                       href="{{ route('itinerary.index', $navQuery($month->subMonth()->format('Y-m'))) }}"
                       aria-label="Previous month"><i class="bi bi-chevron-left"></i></a>
                    <div class="calendar-title">{{ $month->format('F Y') }}</div>
                    <a class="calendar-nav-btn"
                       href="{{ route('itinerary.index', $navQuery($month->addMonth()->format('Y-m'))) }}"
                       aria-label="Next month"><i class="bi bi-chevron-right"></i></a>
                </div>

                <div class="calendar-grid">
                    <div class="calendar-weekdays">
                        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                            <div class="calendar-weekday">{{ $weekday }}</div>
                        @endforeach
                    </div>

                    @foreach ($weeks as $week)
                        <div class="calendar-week">
                            @foreach ($week as $day)
                                @if (! $day->in_month)
                                    {{-- Padding cell: shown blank so weeks stay aligned. --}}
                                    <span class="calendar-day is-blank"></span>
                                @else
                                    <a class="calendar-day {{ $day->is_selected ? 'is-selected' : '' }} {{ $day->is_today && ! $day->is_selected ? 'is-today' : '' }}"
                                       href="{{ route('itinerary.index', $navQuery($month->format('Y-m')) + ['date' => $day->date->toDateString()]) }}">
                                        {{ $day->day }}
                                        <span class="calendar-dots">
                                            @if ($day->has_ai)<span class="calendar-dot dot-ai"></span>@endif
                                            @if ($day->has_booking)<span class="calendar-dot dot-booking"></span>@endif
                                        </span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <div class="calendar-legend">
                    <span><i class="calendar-dot dot-ai"></i> Planned event</span>
                    <span><i class="calendar-dot dot-booking"></i> My booking</span>
                </div>
            </div>

            {{-- ---------- Selected day ---------- --}}
            <div class="planner-card day-detail">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div>
                        <div class="day-detail-date">{{ $selected->format('F j, Y') }}</div>
                        <div class="day-detail-count">
                            {{ $plannedEvents->count() }} planned event{{ $plannedEvents->count() === 1 ? '' : 's' }}
                            · {{ $bookingEvents->count() }} booking{{ $bookingEvents->count() === 1 ? '' : 's' }}
                        </div>
                    </div>
                    <button type="button" class="btn btn-add-activity" disabled
                            title="Adding activities isn't available yet">
                        <i class="bi bi-plus-lg me-1"></i> Add Activity
                    </button>
                </div>

                @if ($bookingEvents->isNotEmpty())
                    <div class="day-group-label"><i class="bi bi-calendar-check"></i> My Bookings</div>
                    @foreach ($bookingEvents as $event)
                        <div class="day-event">
                            <div class="day-event-time">{{ $event->time_label }}</div>
                            <div class="day-event-accent" style="background: {{ $categoryColors[$event->category] ?? 'var(--gold)' }};"></div>
                            <div>
                                <div class="day-event-title">{{ $event->title }}</div>
                                @if ($event->location)
                                    <div class="day-event-location">{{ $event->location }}</div>
                                @endif
                            </div>
                            @if ($event->trip)
                                <div class="day-event-ref">{{ $event->trip->booking_reference }}</div>
                            @endif
                        </div>
                    @endforeach
                @endif

                @if ($plannedEvents->isNotEmpty())
                    <div class="day-group-label"><i class="bi bi-stars"></i> Planned Events</div>
                    @foreach ($plannedEvents as $event)
                        <div class="day-event">
                            <div class="day-event-time">{{ $event->time_label }}</div>
                            <div class="day-event-accent" style="background: {{ $categoryColors[$event->category] ?? '#2563eb' }};"></div>
                            <div>
                                <div class="day-event-title">{{ $event->title }}</div>
                                @if ($event->location)
                                    <div class="day-event-location">{{ $event->location }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif

                @if ($plannedEvents->isEmpty() && $bookingEvents->isEmpty())
                    <div class="planner-empty">
                        <i class="bi bi-calendar-x"></i>
                        @guest
                            <a href="{{ route('login') }}">Sign in</a> to see your planned and booked days.
                        @else
                            Nothing planned for this day.
                        @endguest
                    </div>
                @endif
            </div>

        </div>

    </div>

    {{-- ================= MY BOOKINGS (landing view) ================= --}}
    <div class="planner-card bookings-card {{ $view === 'bookings' ? '' : 'd-none' }}" id="myBookings">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
            @auth
                <span class="bookings-count">
                    {{ $bookings->count() }} booking{{ $bookings->count() === 1 ? '' : 's' }}
                </span>
            @endauth
        </div>
                @forelse ($bookings as $booking)
                    <div class="booking-row">
                        <div class="booking-row-head">
                            <div>
                                <span class="booking-ref">{{ $booking->reference }}</span>
                                <span class="booking-status status-{{ $booking->status }}">{{ $booking->status }}</span>
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

                        @if ($booking->calendar_date)
                            {{-- Opens the calendar view on the month and day
                                 this booking's first entry sits on. --}}
                            <a class="booking-jump"
                               href="{{ route('itinerary.index', ['view' => 'calendar', 'month' => $booking->calendar_date->format('Y-m'), 'date' => $booking->calendar_date->toDateString()]) }}">
                                <i class="bi bi-calendar-check"></i>
                                Show on calendar — {{ $booking->calendar_date->format('j M Y') }}
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="planner-empty">
                        <i class="bi bi-receipt"></i>
                        @guest
                            <a href="{{ route('login') }}">Sign in</a> to see your bookings here.
                        @else
                            No bookings yet.
                            <a href="{{ route('flights.index') }}">Find something to book</a>
                            and it'll appear here and on the calendar.
                        @endguest
                    </div>
                @endforelse
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    // ----- Hide / show the AI planner panel -----
    // Only rendered in the calendar view, so bail out on the bookings
    // view rather than throwing on a missing button.
    var body = document.getElementById('plannerBody');
    var btn = document.getElementById('togglePanelBtn');

    if (!btn) return;

    var icon = document.getElementById('togglePanelIcon');
    var label = document.getElementById('togglePanelLabel');

    btn.addEventListener('click', function () {
        var hidden = body.classList.toggle('panel-hidden');

        icon.className = hidden ? 'bi bi-stars me-1' : 'bi bi-x-lg me-1';
        label.textContent = hidden ? 'Show Planner' : 'Hide Planner';
    });
})();
</script>
@endpush
