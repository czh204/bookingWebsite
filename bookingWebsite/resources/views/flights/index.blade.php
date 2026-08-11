@extends('layouts.homeApp')

@section('title', 'Find Flights')

@push('styles')
<style>
    /* ---------- Flights hero ---------- */
    .flights-hero {
        background: linear-gradient(135deg, #2a3a5c 0%, var(--navy) 55%, var(--navy-dark) 100%);
        color: #fff;
        padding: 2.75rem 1.5rem 6rem;
    }

    .flights-hero h1 { font-size: 2rem; margin-bottom: .25rem; }
    .flights-hero p { opacity: .85; margin: 0; }

    /* ---------- Search bar ---------- */
    .flights-search-bar {
        max-width: 1180px;
        margin: -4rem auto 0;
        background: #fff;
        border-radius: .9rem;
        box-shadow: 0 20px 40px -12px rgba(0,0,0,.2);
        padding: 1.25rem;
        position: relative;
        z-index: 2;
    }

    .flights-search-bar label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #4b5563;
        margin-bottom: .35rem;
        display: block;
    }

    .flights-search-bar .form-control {
        background: var(--cream);
        border-color: var(--border-soft);
        border-radius: .6rem;
        padding: .6rem .85rem;
    }

    .btn-flights-search {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .6rem 1rem;
        white-space: nowrap;
    }

    .btn-flights-search:hover { background: var(--navy-dark); color: #fff; }

    /* ---------- Layout ---------- */
    .flights-body { max-width: 1180px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

    /* ---------- Filters sidebar ---------- */
    .filters-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .9rem;
        padding: 1.25rem;
    }

    .filters-title {
        font-weight: 700;
        color: var(--navy-dark);
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 1rem;
    }

    .filters-section + .filters-section {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-soft);
    }

    .filters-section-label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: .75rem;
    }

    .price-range-inputs { display: flex; align-items: center; gap: .5rem; }
    .price-range-inputs .form-control { border-radius: .5rem; border-color: var(--border-soft); padding: .45rem .6rem; }

    .filters-card .form-check { margin-bottom: .55rem; }
    .filters-card .form-check-label { color: var(--navy-dark); font-size: .9rem; }
    .filters-card .form-check-input:checked { background-color: var(--navy); border-color: var(--navy); }

    .btn-apply-filters {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .6rem;
        width: 100%;
    }

    .btn-apply-filters:hover { background: var(--navy-dark); color: #fff; }

    .btn-reset-filters {
        color: var(--text-muted);
        font-size: .85rem;
        text-decoration: none;
        display: inline-block;
        margin-top: .6rem;
        text-align: center;
        width: 100%;
    }

    .btn-reset-filters:hover { color: var(--navy-dark); text-decoration: underline; }

    /* ---------- Results ---------- */
    .results-count { color: var(--text-muted); font-size: .95rem; }

    .sort-select {
        border-radius: .6rem;
        border-color: var(--border-soft);
        padding: .45rem 2rem .45rem .8rem;
        font-size: .88rem;
        color: var(--navy-dark);
    }

    .flight-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .9rem;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
    }

    .flight-card + .flight-card { margin-top: 1rem; }

    .airline-logo {
        width: 46px;
        height: 46px;
        border-radius: .7rem;
        background: #f1f0ec;
        color: var(--navy-dark);
        font-weight: 700;
        font-size: .85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .flight-airline { min-width: 160px; }
    .flight-airline .name { font-weight: 700; color: var(--navy-dark); }
    .flight-airline .meta { font-size: .78rem; color: var(--text-muted); }

    .flight-time-block { text-align: center; min-width: 80px; }
    .flight-time-block .time { font-weight: 700; font-size: 1.25rem; color: var(--navy-dark); }
    .flight-time-block .code { font-size: .8rem; color: var(--text-muted); }
    .flight-time-block .city { font-size: .75rem; color: var(--text-muted); }

    .flight-duration-block { flex: 1; min-width: 140px; text-align: center; }
    .flight-duration-block .duration { font-size: .78rem; color: var(--text-muted); margin-bottom: .3rem; }
    .flight-duration-line { position: relative; border-top: 1px dashed var(--border-soft); margin: .4rem 0; }
    .flight-duration-line i { position: absolute; top: -9px; left: 50%; transform: translateX(-50%); background: #fff; color: var(--text-muted); font-size: .85rem; padding: 0 .35rem; }

    .stop-badge {
        display: inline-block;
        border-radius: 999px;
        padding: .15rem .65rem;
        font-size: .72rem;
        font-weight: 600;
    }

    .stop-badge.non-stop { background: #dcf3e6; color: #16a34a; }
    .stop-badge.one-stop { background: #fdf1d0; color: #b7791f; }
    .stop-badge.multi-stop { background: #fde3e3; color: #b91c1c; }

    .flight-price-block { text-align: right; min-width: 130px; }
    .flight-price-block .from-label { font-size: .72rem; color: var(--text-muted); }
    .flight-price-block .price { font-weight: 700; font-size: 1.4rem; color: var(--navy-dark); }
    .flight-price-block .per-person { font-size: .72rem; color: var(--text-muted); margin-bottom: .5rem; }

    .btn-select-flight {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .5rem 1rem;
        font-size: .88rem;
        white-space: nowrap;
    }

    .btn-select-flight:hover { background: var(--navy-dark); color: #fff; }

    .no-flights {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .9rem;
        padding: 3rem 1.5rem;
        text-align: center;
        color: var(--text-muted);
    }

    .pagination .page-link { color: var(--navy); border-color: var(--border-soft); }
    .pagination .page-item.active .page-link { background: var(--navy); border-color: var(--navy); }
    .pagination .page-item.disabled .page-link { color: #b8b4aa; }

    /* ---------- Flight detail modal ---------- */
    .flight-modal-content { border-radius: .9rem; border: none; overflow: hidden; }

    .flight-modal-header { align-items: flex-start; border-bottom: 1px solid var(--border-soft); padding: 1.1rem 1.25rem; }
    .fd-airline-name { font-weight: 700; color: var(--navy-dark); }
    .fd-airline-meta { font-size: .82rem; color: var(--text-muted); }

    .fd-route-bar {
        background: var(--navy);
        color: #fff;
        border-radius: .8rem;
        padding: 1.1rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        margin-bottom: 1rem;
    }

    .fd-route-time { text-align: center; }
    .fd-route-time .time { font-weight: 700; font-size: 1.6rem; }
    .fd-route-time .code { font-weight: 600; font-size: .95rem; margin-top: .15rem; }
    .fd-route-time .airport { font-size: .72rem; opacity: .8; max-width: 150px; }

    .fd-route-middle { flex: 1; text-align: center; padding: 0 .5rem; }
    .fd-route-middle .duration { font-size: .78rem; opacity: .85; margin-bottom: .35rem; }
    .fd-route-middle .fd-duration-line { position: relative; border-top: 1px dashed rgba(255,255,255,.4); margin: .5rem 0; }
    .fd-route-middle .fd-duration-line i { position: absolute; top: -9px; left: 50%; transform: translateX(-50%); background: var(--navy); padding: 0 .35rem; font-size: .85rem; }

    .fd-highlight {
        background: var(--cream);
        border-radius: .7rem;
        padding: .85rem 1rem;
        display: flex;
        gap: .6rem;
        align-items: flex-start;
        color: var(--navy-dark);
        font-size: .85rem;
        margin-bottom: 1.25rem;
    }

    .fd-highlight i { color: var(--text-muted); margin-top: .15rem; }

    .fd-fare-heading { font-weight: 700; color: var(--navy-dark); margin-bottom: .85rem; }

    .fare-card {
        border: 1px solid var(--border-soft);
        border-radius: .8rem;
        padding: 1rem;
        margin-bottom: .85rem;
        cursor: pointer;
    }

    .fare-card.selected { border-color: var(--navy); border-width: 2px; box-shadow: 0 0 0 1px var(--navy); }
    .fare-card.unavailable { opacity: .55; cursor: not-allowed; background: #faf9f6; }

    .fare-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .6rem; }
    .fare-card-name { font-weight: 700; color: var(--navy-dark); display: flex; align-items: center; gap: .5rem; }
    .fare-card-price { font-weight: 700; font-size: 1.1rem; color: var(--navy-dark); }

    .fare-card-badge {
        background: #e7ecf5;
        color: var(--navy);
        font-size: .68rem;
        font-weight: 600;
        border-radius: 999px;
        padding: .1rem .55rem;
    }

    .fare-card-perks {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .3rem .75rem;
        font-size: .82rem;
        color: var(--navy-dark);
        margin-bottom: .5rem;
    }

    .fare-card-perks div { display: flex; align-items: flex-start; gap: .35rem; }
    .fare-card-perks i { color: #16a34a; margin-top: .18rem; flex-shrink: 0; }

    .fare-card-policy { font-size: .78rem; color: var(--text-muted); display: flex; align-items: flex-start; gap: .35rem; }

    .fare-card-unavailable-note { font-size: .82rem; color: var(--text-muted); font-style: italic; }

    .fd-modal-footer {
        border-top: 1px solid var(--border-soft);
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
    }

    .fd-total-label { font-size: .75rem; color: var(--text-muted); }
    .fd-total-price { font-weight: 700; font-size: 1.5rem; color: var(--navy-dark); }
    .fd-total-caption { font-size: .78rem; color: var(--text-muted); }

    .btn-add-to-cart {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .7rem 1.3rem;
    }

    .btn-add-to-cart:hover { background: var(--navy-dark); color: #fff; }
    .btn-add-to-cart:disabled { opacity: .6; }
</style>
@endpush

@section('content')

<form method="GET" action="{{ route('flights.index') }}" id="flightsFilterForm">

{{-- ================= HERO ================= --}}
<section class="flights-hero">
    <div class="mx-auto" style="max-width: 1180px;">
        <h1 class="font-serif fw-bold">Find Flights</h1>
        <p>Search hundreds of airlines for the best fares</p>
    </div>
</section>

{{-- ================= SEARCH BAR ================= --}}
<div class="flights-search-bar">
    <div class="row g-3 align-items-end">
        <div class="col-6 col-md-3">
            <label>From</label>
            <input type="text" class="form-control" name="from" value="{{ request('from') }}" placeholder="City / Airport">
        </div>
        <div class="col-6 col-md-3">
            <label>To</label>
            <input type="text" class="form-control" name="to" value="{{ request('to') }}" placeholder="City / Airport">
        </div>
        <div class="col-6 col-md-2">
            <label>Departure</label>
            <input type="date" class="form-control" name="departure" value="{{ request('departure') }}">
        </div>
        <div class="col-6 col-md-2">
            <label>Return</label>
            <input type="date" class="form-control" name="return" value="{{ request('return') }}">
        </div>
        <div class="col-12 col-md-2">
            <button type="submit" class="btn btn-flights-search w-100">
                <i class="bi bi-search me-1"></i> Search
            </button>
        </div>
    </div>
</div>

{{-- ================= BODY ================= --}}
<div class="flights-body">
    <div class="row g-4">

        {{-- ---------- Filters sidebar ---------- --}}
        <div class="col-lg-3">
            <div class="filters-card">
                <div class="filters-title"><i class="bi bi-funnel"></i> Filters</div>

                <div class="filters-section">
                    <div class="filters-section-label">Price Range</div>
                    <div class="price-range-inputs">
                        <input type="number" min="0" class="form-control" name="min_price" value="{{ request('min_price') }}" placeholder="$0">
                        <span class="text-muted">-</span>
                        <input type="number" min="0" class="form-control" name="max_price" value="{{ request('max_price') }}" placeholder="$2,000">
                    </div>
                </div>

                <div class="filters-section">
                    <div class="filters-section-label">Stops</div>
                    @php
                        $stopOptions = ['non-stop' => 'Non-stop', '1-stop' => '1 Stop', '2-plus' => '2+ Stops'];
                        $selectedStops = request('stops', array_keys($stopOptions));
                    @endphp
                    @foreach ($stopOptions as $value => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="stops[]" value="{{ $value }}"
                                   id="stop-{{ $value }}" {{ in_array($value, $selectedStops) ? 'checked' : '' }}>
                            <label class="form-check-label" for="stop-{{ $value }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="filters-section">
                    <div class="filters-section-label">Airlines</div>
                    @php $selectedAirlines = request('airlines', $airlines->all()); @endphp
                    @foreach ($airlines as $airline)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="airlines[]" value="{{ $airline }}"
                                   id="airline-{{ \Illuminate\Support\Str::slug($airline) }}"
                                   {{ in_array($airline, $selectedAirlines) ? 'checked' : '' }}>
                            <label class="form-check-label" for="airline-{{ \Illuminate\Support\Str::slug($airline) }}">{{ $airline }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="filters-section">
                    <div class="filters-section-label">Departure Time</div>
                    @php
                        $timeOptions = ['morning' => 'Morning (6am–12pm)', 'afternoon' => 'Afternoon (12pm–6pm)', 'evening' => 'Evening (6pm–12am)'];
                        $selectedWindows = request('departure_time', []);
                    @endphp
                    @foreach ($timeOptions as $value => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="departure_time[]" value="{{ $value }}"
                                   id="window-{{ $value }}" {{ in_array($value, $selectedWindows) ? 'checked' : '' }}>
                            <label class="form-check-label" for="window-{{ $value }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-apply-filters mt-3">Apply Filters</button>
                <a href="{{ route('flights.index') }}" class="btn-reset-filters">Reset all</a>
            </div>
        </div>

        {{-- ---------- Results ---------- --}}
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="results-count">{{ $flights->total() }} flight{{ $flights->total() === 1 ? '' : 's' }} found</div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-muted"></i>
                    <select name="sort" class="form-select sort-select" onchange="document.getElementById('flightsFilterForm').submit()">
                        <option value="price_asc" {{ request('sort', 'price_asc') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="duration" {{ request('sort') === 'duration' ? 'selected' : '' }}>Duration</option>
                        <option value="departure" {{ request('sort') === 'departure' ? 'selected' : '' }}>Departure Time</option>
                    </select>
                </div>
            </div>

            @forelse ($flights as $flight)
                <div class="flight-card">
                    <div class="airline-logo">{{ $flight->airline_code }}</div>

                    <div class="flight-airline">
                        <div class="name">{{ $flight->airline_name }}</div>
                        <div class="meta">{{ $flight->flight_number }} · {{ $flight->aircraft }}</div>
                    </div>

                    <div class="flight-time-block">
                        <div class="time">{{ $flight->departure_time }}</div>
                        <div class="code">{{ $flight->origin_code }}</div>
                        <div class="city">{{ $flight->origin_city }}</div>
                    </div>

                    <div class="flight-duration-block">
                        <div class="duration"><i class="bi bi-clock me-1"></i>{{ intdiv($flight->duration_minutes, 60) }}h {{ $flight->duration_minutes % 60 }}m</div>
                        <div class="flight-duration-line"><i class="bi bi-airplane"></i></div>
                        @if ($flight->stops === 0)
                            <span class="stop-badge non-stop">Non-stop</span>
                        @elseif ($flight->stops === 1)
                            <span class="stop-badge one-stop">1 Stop</span>
                        @else
                            <span class="stop-badge multi-stop">{{ $flight->stops }} Stops</span>
                        @endif
                    </div>

                    <div class="flight-time-block">
                        <div class="time">{{ $flight->arrival_time }}</div>
                        <div class="code">{{ $flight->destination_code }}</div>
                        <div class="city">{{ $flight->destination_city }}</div>
                    </div>

                    <div class="flight-price-block">
                        <div class="from-label">from</div>
                        <div class="price">${{ number_format($flight->price) }}</div>
                        <div class="per-person">per person</div>
                        <button type="button" class="btn btn-select-flight js-select-flight" data-flight="{{ json_encode($flight) }}">Select <i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            @empty
                <div class="no-flights">
                    <i class="bi bi-airplane fs-2 d-block mb-2"></i>
                    No flights match your filters. Try adjusting your search.
                </div>
            @endforelse

            @if ($flights->hasPages())
                <div class="mt-4">
                    {{ $flights->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

</form>

{{-- ================= FLIGHT DETAIL MODAL ================= --}}
<div class="modal fade" id="flightDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content flight-modal-content">
            <div class="modal-header flight-modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="airline-logo" id="fdAirlineLogo"></span>
                    <div>
                        <div class="fd-airline-name" id="fdAirlineName"></div>
                        <div class="fd-airline-meta" id="fdAirlineMeta"></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="fd-route-bar" id="fdRouteBar"></div>
                <div class="fd-highlight"><i class="bi bi-info-circle"></i><span id="fdHighlight"></span></div>
                <h6 class="fd-fare-heading">Select Fare Class</h6>
                <div id="fdFareList"></div>
            </div>
            <div class="modal-footer fd-modal-footer">
                <div>
                    <div class="fd-total-label">Total for selected fare</div>
                    <div class="fd-total-price" id="fdTotalPrice"></div>
                    <div class="fd-total-caption" id="fdTotalCaption"></div>
                </div>
                <button type="button" class="btn btn-add-to-cart" id="fdAddToCart">Add to Cart <i class="bi bi-arrow-right"></i></button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('flightDetailModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const fareList = document.getElementById('fdFareList');
    const addToCartBtn = document.getElementById('fdAddToCart');
    let currentFlight = null;
    let selectedFareKey = null;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderRouteBar(flight) {
        document.getElementById('fdRouteBar').innerHTML = `
            <div class="fd-route-time">
                <div class="time">${flight.departure_time}</div>
                <div class="code">${flight.origin_code}</div>
                <div class="airport">${escapeHtml(flight.origin_airport_name)}</div>
            </div>
            <div class="fd-route-middle">
                <div class="duration">${Math.floor(flight.duration_minutes / 60)}h ${flight.duration_minutes % 60}m</div>
                <div class="fd-duration-line"><i class="bi bi-airplane"></i></div>
                <span class="stop-badge ${flight.stops === 0 ? 'non-stop' : (flight.stops === 1 ? 'one-stop' : 'multi-stop')}">${flight.stop_label}</span>
            </div>
            <div class="fd-route-time">
                <div class="time">${flight.arrival_time}</div>
                <div class="code">${flight.destination_code}</div>
                <div class="airport">${escapeHtml(flight.destination_airport_name)}</div>
            </div>
        `;
    }

    function renderFareCard(fare) {
        if (!fare.available) {
            return `
                <div class="fare-card unavailable">
                    <div class="fare-card-head">
                        <div class="fare-card-name"><i class="bi bi-circle text-muted"></i> ${fare.name}</div>
                    </div>
                    <div class="fare-card-unavailable-note">Not available for this flight</div>
                </div>
            `;
        }

        const isSelected = fare.key === selectedFareKey;
        const perksHtml = fare.perks
            .concat([fare.checked_bag, fare.carry_on])
            .map(p => `<div><i class="bi bi-check-lg"></i>${escapeHtml(p)}</div>`)
            .join('');

        return `
            <div class="fare-card ${isSelected ? 'selected' : ''}" data-fare-key="${fare.key}">
                <div class="fare-card-head">
                    <div class="fare-card-name">
                        <i class="bi ${isSelected ? 'bi-record-circle' : 'bi-circle'}"></i>
                        ${fare.name}
                        ${fare.badge ? `<span class="fare-card-badge">${fare.badge}</span>` : ''}
                    </div>
                    <div class="fare-card-price">$${fare.price}</div>
                </div>
                <div class="fare-card-perks">${perksHtml}</div>
                <div class="fare-card-policy"><i class="bi bi-info-circle"></i> ${fare.policy}</div>
            </div>
        `;
    }

    function renderFares() {
        fareList.innerHTML = currentFlight.fares.map(renderFareCard).join('');
    }

    function updateTotals() {
        const fare = currentFlight.fares.find(f => f.key === selectedFareKey);
        document.getElementById('fdTotalPrice').textContent = fare ? `$${fare.price}` : '—';
        document.getElementById('fdTotalCaption').textContent = fare ? `per person · ${fare.name}` : '';
        addToCartBtn.disabled = !fare;
    }

    fareList.addEventListener('click', function (e) {
        const card = e.target.closest('.fare-card:not(.unavailable)');
        if (!card) return;
        selectedFareKey = card.dataset.fareKey;
        renderFares();
        updateTotals();
    });

    addToCartBtn.addEventListener('click', function () {
        if (addToCartBtn.disabled) return;
        const original = addToCartBtn.innerHTML;
        addToCartBtn.innerHTML = 'Added <i class="bi bi-check-lg"></i>';
        addToCartBtn.disabled = true;
        setTimeout(function () {
            addToCartBtn.innerHTML = original;
            addToCartBtn.disabled = false;
        }, 1500);
    });

    document.querySelectorAll('.js-select-flight').forEach(function (btn) {
        btn.addEventListener('click', function () {
            currentFlight = JSON.parse(btn.dataset.flight);
            selectedFareKey = (currentFlight.fares.find(f => f.available) || {}).key || null;

            document.getElementById('fdAirlineLogo').textContent = currentFlight.airline_code;
            document.getElementById('fdAirlineName').textContent = currentFlight.airline_name;
            document.getElementById('fdAirlineMeta').textContent = `${currentFlight.flight_number} · ${currentFlight.aircraft}`;

            renderRouteBar(currentFlight);
            document.getElementById('fdHighlight').textContent = currentFlight.highlight;
            renderFares();
            updateTotals();

            modal.show();
        });
    });
});
</script>
@endpush
