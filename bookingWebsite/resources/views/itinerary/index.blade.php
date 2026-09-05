@extends('layouts.homeApp')

@section('title', 'Itinerary Planner')

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

    .planner-tabs {
        display: flex;
        border-bottom: 1px solid var(--border-soft);
        flex-shrink: 0;
    }

    .planner-tab {
        flex: 1;
        background: var(--cream);
        border: none;
        border-bottom: 2px solid transparent;
        padding: .85rem .5rem;
        font-size: .88rem;
        font-weight: 600;
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
    }

    .planner-tab:hover { color: var(--navy-dark); }

    .planner-tab.active {
        background: #fff;
        color: var(--navy-dark);
        border-bottom-color: var(--navy);
    }

    .planner-tab-pane { flex: 1; display: none; flex-direction: column; min-height: 0; }
    .planner-tab-pane.active { display: flex; }

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

    /* ---------- My Trips ---------- */
    .trips-list { flex: 1; overflow-y: auto; padding: 1rem; }

    .trip-card {
        border: 1px solid var(--border-soft);
        border-radius: .7rem;
        padding: .9rem 1rem;
        margin-bottom: .75rem;
        background: #fff;
    }

    .trip-card:last-child { margin-bottom: 0; }
    .trip-card .trip-title { font-weight: 700; color: var(--navy-dark); font-size: .95rem; }

    .trip-card .trip-meta {
        font-size: .8rem;
        color: var(--text-muted);
        display: flex;
        flex-wrap: wrap;
        gap: .25rem .75rem;
        margin-top: .35rem;
    }

    .trip-status {
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
        padding: .12rem .6rem;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .status-upcoming { background: #dbe8fd; color: #2563eb; }
    .status-completed { background: #e6e6e6; color: #4b5563; }
    .status-cancelled { background: #fde3e3; color: #b91c1c; }

    .trip-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: .7rem;
        padding-top: .7rem;
        border-top: 1px solid var(--border-soft);
    }

    .trip-ref { font-size: .75rem; color: var(--text-muted); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    .trip-total { font-weight: 700; color: var(--navy-dark); }

    .btn-view-trip-days {
        background: none;
        border: none;
        color: var(--navy);
        font-size: .8rem;
        font-weight: 600;
        text-decoration: none;
        padding: 0;
    }

    .btn-view-trip-days:hover { text-decoration: underline; }

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
    // Month nav keeps the tab you're on but drops ?date=, since the
    // selected day never belongs to a different month.
    $navQuery = fn (string $ym) => array_filter(['month' => $ym, 'tab' => $tab === 'trips' ? 'trips' : null]);
@endphp

@section('content')

{{-- ================= HERO ================= --}}
<section class="planner-hero">
    <div class="mx-auto d-flex justify-content-between align-items-start gap-3" style="max-width: 1240px;">
        <div>
            <h1 class="font-serif fw-bold">Itinerary Planner</h1>
            <p>AI-planned trips and your booked experiences in one calendar</p>
        </div>
        <button type="button" class="btn btn-toggle-panel" id="togglePanelBtn">
            <i class="bi bi-x-lg me-1" id="togglePanelIcon"></i><span id="togglePanelLabel">Hide Panel</span>
        </button>
    </div>
</section>

{{-- ================= BODY ================= --}}
<div class="planner-body" id="plannerBody">
    <div class="row g-4">

        {{-- ---------- Left panel ---------- --}}
        <div class="col-lg-4 planner-panel-col">
            <div class="planner-card planner-panel">

                <div class="planner-tabs">
                    <button type="button" class="planner-tab {{ $tab === 'planner' ? 'active' : '' }}" data-pane="pane-planner">
                        <i class="bi bi-stars"></i> AI Planner
                    </button>
                    <button type="button" class="planner-tab {{ $tab === 'trips' ? 'active' : '' }}" data-pane="pane-trips">
                        <i class="bi bi-calendar-check"></i> My Trips
                    </button>
                </div>

                {{-- ----- AI Planner tab (UI only — no assistant wired up yet) ----- --}}
                <div class="planner-tab-pane {{ $tab === 'planner' ? 'active' : '' }}" id="pane-planner">
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

                {{-- ----- My Trips tab ----- --}}
                <div class="planner-tab-pane {{ $tab === 'trips' ? 'active' : '' }}" id="pane-trips">
                    <div class="trips-list">
                        @forelse ($trips as $trip)
                            <div class="trip-card">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="trip-title">{{ $trip->title }}</div>
                                    <span class="trip-status status-{{ $trip->status }}">{{ $trip->status }}</span>
                                </div>
                                <div class="trip-meta">
                                    <span><i class="bi bi-geo-alt"></i> {{ $trip->destination }}</span>
                                    <span><i class="bi bi-calendar3"></i> {{ $trip->date_range_label }}</span>
                                    <span><i class="bi bi-moon"></i> {{ $trip->nights }} night{{ $trip->nights === 1 ? '' : 's' }}</span>
                                </div>
                                <div class="trip-card-footer">
                                    <div>
                                        <div class="trip-ref">{{ $trip->booking_reference }}</div>
                                        {{-- Jumps the calendar to the month and day this trip starts. --}}
                                        <a class="btn-view-trip-days"
                                           href="{{ route('itinerary.index', ['month' => $trip->start_date->format('Y-m'), 'date' => $trip->start_date->toDateString(), 'tab' => 'trips']) }}">
                                            View on calendar ({{ $trip->events_count }} item{{ $trip->events_count === 1 ? '' : 's' }})
                                        </a>
                                    </div>
                                    @if ($trip->total_price !== null)
                                        <div class="trip-total">${{ number_format($trip->total_price) }}</div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="planner-empty">
                                <i class="bi bi-suitcase"></i>
                                @guest
                                    <a href="{{ route('login') }}">Sign in</a> to see your booked trips here.
                                @else
                                    No booked trips yet. Anything you book will show up here.
                                @endguest
                            </div>
                        @endforelse
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
                    <span><i class="calendar-dot dot-ai"></i> AI planned</span>
                    <span><i class="calendar-dot dot-booking"></i> My booking</span>
                </div>
            </div>

            {{-- ---------- Selected day ---------- --}}
            <div class="planner-card day-detail">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div>
                        <div class="day-detail-date">{{ $selected->format('F j, Y') }}</div>
                        <div class="day-detail-count">
                            {{ $aiEvents->count() }} AI event{{ $aiEvents->count() === 1 ? '' : 's' }}
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

                @if ($aiEvents->isNotEmpty())
                    <div class="day-group-label"><i class="bi bi-stars"></i> AI Planned</div>
                    @foreach ($aiEvents as $event)
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

                @if ($aiEvents->isEmpty() && $bookingEvents->isEmpty())
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
</div>

@endsection

@push('scripts')
<script>
(function () {
    // ----- Panel tabs -----
    document.querySelectorAll('.planner-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.planner-tab').forEach(function (t) { t.classList.remove('active'); });
            document.querySelectorAll('.planner-tab-pane').forEach(function (p) { p.classList.remove('active'); });

            tab.classList.add('active');
            document.getElementById(tab.dataset.pane).classList.add('active');
        });
    });

    // ----- Hide / show panel -----
    var body = document.getElementById('plannerBody');
    var btn = document.getElementById('togglePanelBtn');
    var icon = document.getElementById('togglePanelIcon');
    var label = document.getElementById('togglePanelLabel');

    btn.addEventListener('click', function () {
        var hidden = body.classList.toggle('panel-hidden');

        icon.className = hidden ? 'bi bi-layout-sidebar me-1' : 'bi bi-x-lg me-1';
        label.textContent = hidden ? 'Show Panel' : 'Hide Panel';
    });
})();
</script>
@endpush
