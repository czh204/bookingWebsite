@extends('layouts.homeApp')

@section('title', 'Find Hotels')

@push('styles')
<style>
    /* ---------- Hotels hero ---------- */
    .hotels-hero {
        background: linear-gradient(135deg, #2a3a5c 0%, var(--navy) 55%, var(--navy-dark) 100%);
        color: #fff;
        padding: 2.75rem 1.5rem 6rem;
    }

    .hotels-hero h1 { font-size: 2rem; margin-bottom: .25rem; }
    .hotels-hero p { opacity: .85; margin: 0; }

    /* ---------- Search bar ---------- */
    .hotels-search-bar {
        max-width: 1180px;
        margin: -4rem auto 0;
        background: #fff;
        border-radius: .9rem;
        box-shadow: 0 20px 40px -12px rgba(0,0,0,.2);
        padding: 1.25rem;
        position: relative;
        z-index: 2;
    }

    .hotels-search-bar label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #4b5563;
        margin-bottom: .35rem;
        display: block;
    }

    .hotels-search-bar .form-control {
        background: var(--cream);
        border-color: var(--border-soft);
        border-radius: .6rem;
        padding: .6rem .85rem;
    }

    .btn-hotels-search {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .6rem 1rem;
        white-space: nowrap;
    }

    .btn-hotels-search:hover { background: var(--navy-dark); color: #fff; }

    /* ---------- Layout ---------- */
    .hotels-body { max-width: 1180px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

    /* ---------- Filters sidebar ---------- */
    .filters-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .9rem;
        padding: 1.25rem;
        position: sticky;
        top: 1.5rem;
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

    .star-rating-option .bi-star-fill { color: var(--gold); font-size: .8rem; }

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

    .hotel-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .9rem;
        padding: 1rem;
        display: flex;
        gap: 1.25rem;
        flex-wrap: wrap;
    }

    .hotel-card + .hotel-card { margin-top: 1rem; }

    .hotel-thumb {
        position: relative;
        width: 220px;
        min-height: 160px;
        flex-shrink: 0;
        border-radius: .7rem;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.4);
        font-size: 2rem;
    }

    .hotel-thumb img,
    .hd-carousel-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .hotel-thumb-badge {
        position: absolute;
        top: .6rem;
        left: .6rem;
        background: rgba(0,0,0,.55);
        color: #fff;
        border-radius: 999px;
        padding: .25rem .75rem;
        font-size: .72rem;
        font-weight: 600;
    }

    .hotel-info { flex: 1; min-width: 220px; display: flex; flex-direction: column; }
    .hotel-info .name { font-weight: 700; font-size: 1.1rem; color: var(--navy-dark); }
    .hotel-info .location { font-size: .85rem; color: var(--text-muted); margin-bottom: .35rem; }
    .amenity-pill {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        background: var(--cream);
        border-radius: 999px;
        padding: .2rem .7rem;
        font-size: .76rem;
        color: var(--navy-dark);
        margin: 0 .35rem .35rem 0;
    }

    .hotel-price-block {
        text-align: right;
        min-width: 130px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: flex-end;
    }

    .hotel-price-block .price { font-weight: 700; font-size: 1.4rem; color: var(--navy-dark); }
    .hotel-price-block .per-night { font-size: .72rem; color: var(--text-muted); margin-bottom: .75rem; }

    .btn-view-details {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .55rem 1.2rem;
        font-size: .88rem;
        white-space: nowrap;
    }

    .btn-view-details:hover { background: var(--navy-dark); color: #fff; }

    .no-hotels {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .9rem;
        padding: 3rem 1.5rem;
        text-align: center;
        color: var(--text-muted);
    }

    .pagination .page-link { color: var(--navy); border-color: var(--border-soft); }
    .pagination .page-item.active .page-link { background: var(--navy); border-color: var(--navy); color: #fff; }
    .pagination .page-item.disabled .page-link { color: #b8b4aa; }

    /* Photo placeholders (no real hotel photos yet) */
    .ph-1 { background-image: linear-gradient(135deg, #3b5b8f, #1e2a45); }
    .ph-2 { background-image: linear-gradient(135deg, #2f8f6e, #16532f); }
    .ph-3 { background-image: linear-gradient(135deg, #b3502f, #7a2e12); }
    .ph-4 { background-image: linear-gradient(135deg, #5a4a8f, #2c1f57); }
    .ph-5 { background-image: linear-gradient(135deg, #b7791f, #7a4e0f); }
    .ph-6 { background-image: linear-gradient(135deg, #2f7f8f, #163f57); }

    /* ---------- Hotel detail modal ---------- */
    .hotel-modal-content { border-radius: .9rem; border: none; overflow: hidden; }

    /* flex-shrink:0 is load-bearing: .modal-content is a flex column with
       max-height:100%, so without it the carousel gets squashed to 0px and
       its absolutely-positioned badge, close button and arrows spill over
       the hotel name below. */
    .hd-carousel { position: relative; height: 260px; flex-shrink: 0; }
    .hd-carousel-slide {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.4);
        font-size: 3rem;
    }
    .hd-carousel-slide.active { display: flex; }

    .hd-carousel-badge {
        position: absolute;
        top: .85rem;
        left: .85rem;
        background: rgba(0,0,0,.55);
        color: #fff;
        border-radius: 999px;
        padding: .3rem .85rem;
        font-size: .75rem;
        font-weight: 600;
        z-index: 2;
    }

    .hd-carousel-close {
        position: absolute;
        top: .75rem;
        right: .75rem;
        z-index: 2;
        background: rgba(0,0,0,.5);
        border: none;
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Overrides Bootstrap's .modal-body padding; both are single classes,
       so this wins on source order (our styles load after the CDN). */
    .hd-body { padding: 1.25rem; }
    .hd-name-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
    .hd-name { font-size: 1.4rem; font-weight: 700; color: var(--navy-dark); }

    .hd-location { color: var(--text-muted); font-size: .88rem; margin: .3rem 0 .9rem; display: flex; align-items: center; gap: .35rem; }
    .hd-description { color: var(--text-muted); font-size: .9rem; line-height: 1.55; margin-bottom: 1.1rem; }

    .hd-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .6rem;
        margin-bottom: 1.25rem;
    }

    .hd-info-tile {
        background: var(--cream);
        border-radius: .7rem;
        padding: .7rem .85rem;
        display: flex;
        align-items: flex-start;
        gap: .55rem;
    }

    .hd-info-tile i { color: var(--text-muted); margin-top: .15rem; }
    .hd-info-tile .label { font-size: .72rem; color: var(--text-muted); }
    .hd-info-tile .value { font-weight: 600; color: var(--navy-dark); font-size: .9rem; }

    .hd-section-heading { font-weight: 700; color: var(--navy-dark); margin-bottom: .85rem; }

    .room-card {
        border: 1px solid var(--border-soft);
        border-radius: .8rem;
        padding: 1rem;
        margin-bottom: .85rem;
        cursor: pointer;
    }

    .room-card.selected { border-color: var(--navy); border-width: 2px; box-shadow: 0 0 0 1px var(--navy); }
    .room-card.unavailable { opacity: .55; cursor: not-allowed; background: #faf9f6; }

    .room-card-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: .6rem; }
    .room-card-name { font-weight: 700; color: var(--navy-dark); display: flex; align-items: center; gap: .5rem; }
    .room-card-meta { font-size: .78rem; color: var(--text-muted); margin-top: .15rem; }
    .room-card-price { font-weight: 700; font-size: 1.1rem; color: var(--navy-dark); text-align: right; }
    .room-card-price .per-night { display: block; font-weight: 400; font-size: .7rem; color: var(--text-muted); }

    .room-card-perks {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .3rem .75rem;
        font-size: .82rem;
        color: var(--navy-dark);
    }

    .room-card-perks div { display: flex; align-items: flex-start; gap: .35rem; }
    .room-card-perks i { color: #16a34a; margin-top: .18rem; flex-shrink: 0; }

    .room-card-unavailable-note { font-size: .82rem; color: var(--text-muted); font-style: italic; }

    .hd-policy-tile {
        background: var(--cream);
        border-radius: .7rem;
        padding: .85rem 1rem;
        display: flex;
        gap: .6rem;
        align-items: flex-start;
        margin-bottom: .6rem;
    }

    .hd-policy-tile i { color: var(--text-muted); margin-top: .15rem; }
    .hd-policy-tile .title { font-weight: 700; color: var(--navy-dark); font-size: .85rem; }
    .hd-policy-tile .description { color: var(--text-muted); font-size: .82rem; }

    .hd-modal-footer {
        border-top: 1px solid var(--border-soft);
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
    }

    .hd-total-label { font-size: .75rem; color: var(--text-muted); }
    .hd-total-room { font-weight: 700; color: var(--navy-dark); }
    .hd-total-price { font-weight: 700; font-size: 1.4rem; color: var(--navy-dark); }
    .hd-total-price .per-night { font-size: .72rem; font-weight: 400; color: var(--text-muted); }

    .btn-book-room {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .7rem 1.3rem;
    }

    .btn-book-room:hover { background: var(--navy-dark); color: #fff; }
    .btn-book-room:disabled { opacity: .6; }
</style>
@endpush

@section('content')

<form method="GET" action="{{ route('hotels.index') }}" id="hotelsFilterForm">

{{-- ================= HERO ================= --}}
<section class="hotels-hero">
    <div class="mx-auto" style="max-width: 1180px;">
        <h1 class="font-serif fw-bold">Find Hotels</h1>
        <p>Search for your next stay right at Voyagr</p>
    </div>
</section>

{{-- ================= SEARCH BAR ================= --}}
<div class="hotels-search-bar">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-5">
            <label>Destination</label>
            <input type="text" class="form-control" name="destination" value="{{ request('destination') }}" placeholder="City or hotel name">
        </div>
        <div class="col-6 col-md-3">
            <label>Check-in</label>
            <input type="date" class="form-control" name="check_in" value="{{ request('check_in') }}">
        </div>
        <div class="col-6 col-md-2">
            <label>Check-out</label>
            <input type="date" class="form-control" name="check_out" value="{{ request('check_out') }}">
        </div>
        <div class="col-12 col-md-2">
            <button type="submit" class="btn btn-hotels-search w-100">
                <i class="bi bi-search me-1"></i> Search Hotels
            </button>
        </div>
    </div>
</div>

{{-- ================= BODY ================= --}}
<div class="hotels-body">
    <div class="row g-4">

        {{-- ---------- Filters sidebar ---------- --}}
        <div class="col-lg-3">
            <div class="filters-card">
                <div class="filters-title"><i class="bi bi-funnel"></i> Filters</div>

                <div class="filters-section">
                    <div class="filters-section-label">Price per Night</div>
                    <div class="price-range-inputs">
                        <input type="number" min="0" class="form-control" name="min_price" value="{{ request('min_price') }}" placeholder="$0">
                        <span class="text-muted">-</span>
                        <input type="number" min="0" class="form-control" name="max_price" value="{{ request('max_price') }}" placeholder="$1,000">
                    </div>
                </div>

                <div class="filters-section">
                    <div class="filters-section-label">Star Rating</div>
                    @php $selectedStars = request('star_rating', ['5', '4', '3', '2']); @endphp
                    @foreach ([5, 4, 3, 2] as $stars)
                        <div class="form-check star-rating-option">
                            <input class="form-check-input" type="checkbox" name="star_rating[]" value="{{ $stars }}"
                                   id="star-{{ $stars }}" {{ in_array((string) $stars, $selectedStars) ? 'checked' : '' }}>
                            <label class="form-check-label" for="star-{{ $stars }}">
                                @for ($i = 0; $i < $stars; $i++)<i class="bi bi-star-fill"></i>@endfor
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="filters-section">
                    <div class="filters-section-label">Amenities</div>
                    @php
                        $amenityOptions = ['WiFi', 'Pool', 'Gym', 'Spa', 'Parking', 'Restaurant', 'Breakfast'];
                        $selectedAmenities = request('amenities', []);
                    @endphp
                    @foreach ($amenityOptions as $amenity)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $amenity }}"
                                   id="amenity-{{ \Illuminate\Support\Str::slug($amenity) }}"
                                   {{ in_array($amenity, $selectedAmenities) ? 'checked' : '' }}>
                            <label class="form-check-label" for="amenity-{{ \Illuminate\Support\Str::slug($amenity) }}">{{ $amenity }}</label>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-apply-filters mt-3">Apply Filters</button>
                <a href="{{ route('hotels.index') }}" class="btn-reset-filters">Reset all</a>
            </div>
        </div>

        {{-- ---------- Results ---------- --}}
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="results-count">{{ $hotels->total() }} hotel{{ $hotels->total() === 1 ? '' : 's' }} found</div>
                <div class="d-flex align-items-center gap-2">
                    <select name="sort" class="form-select sort-select" onchange="document.getElementById('hotelsFilterForm').submit()">
                        <option value="recommended" {{ request('sort', 'recommended') === 'recommended' ? 'selected' : '' }}>Recommended</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>
            </div>

            @forelse ($hotels as $index => $hotel)
                <div class="hotel-card">
                    <div class="hotel-thumb {{ $hotel->image ? '' : 'ph-'.$hotel->placeholder_shade }}">
                        @if ($hotel->badge)
                            <span class="hotel-thumb-badge">{{ $hotel->badge }}</span>
                        @endif
                        @if ($hotel->image)
                            <img src="{{ $hotel->image }}" alt="{{ $hotel->name }}" loading="lazy">
                        @else
                            <i class="bi bi-image"></i>
                        @endif
                    </div>

                    <div class="hotel-info">
                        <div class="name">{{ $hotel->name }}</div>
                        <div class="location"><i class="bi bi-geo-alt"></i> {{ $hotel->city }}, {{ $hotel->country }}</div>
                        <div class="mb-3">
                            @foreach ($hotel->amenities as $amenity)
                                <span class="amenity-pill">{{ $amenity }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="hotel-price-block">
                        <div>
                            <div class="price">${{ number_format($hotel->price_per_night) }}</div>
                            <div class="per-night">/ night</div>
                        </div>
                        <button type="button" class="btn btn-view-details js-view-hotel" data-hotel="{{ json_encode($hotel) }}">View Details</button>
                    </div>
                </div>
            @empty
                <div class="no-hotels">
                    <i class="bi bi-building fs-2 d-block mb-2"></i>
                    No hotels match your filters. Try adjusting your search.
                </div>
            @endforelse

            @if ($hotels->hasPages())
                <div class="mt-4">
                    {{ $hotels->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

</form>

{{-- ================= HOTEL DETAIL MODAL ================= --}}
<div class="modal fade" id="hotelDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 560px;">
        <div class="modal-content hotel-modal-content">
            <div class="hd-carousel" id="hdCarousel">
                <span class="hd-carousel-badge" id="hdCarouselBadge"></span>
                <button type="button" class="hd-carousel-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                <div id="hdCarouselSlides"></div>
            </div>

            {{-- modal-body is required, not decorative: modal-dialog-scrollable
                 only makes a .modal-body scrollable. Without it the content was
                 clipped by .modal-content's overflow:hidden with no scrollbar,
                 and the footer's Book Room button sat outside the viewport. --}}
            <div class="modal-body hd-body">
                <div class="hd-name-row">
                    <div class="hd-name" id="hdName"></div>
                </div>
                <div class="hd-location"><i class="bi bi-geo-alt"></i> <span id="hdAddress"></span></div>
                <div class="hd-description" id="hdDescription"></div>

                <div class="hd-info-grid">
                    <div class="hd-info-tile">
                        <i class="bi bi-calendar-check"></i>
                        <div><div class="label">Check-in</div><div class="value" id="hdCheckIn"></div></div>
                    </div>
                    <div class="hd-info-tile">
                        <i class="bi bi-clock"></i>
                        <div><div class="label">Check-out</div><div class="value" id="hdCheckOut"></div></div>
                    </div>
                    <div class="hd-info-tile">
                        <i class="bi bi-people"></i>
                        <div><div class="label">Rooms available</div><div class="value" id="hdRoomsAvailable"></div></div>
                    </div>
                    <div class="hd-info-tile">
                        <i class="bi bi-telephone"></i>
                        <div><div class="label">Contact</div><div class="value" id="hdContact"></div></div>
                    </div>
                </div>

                @include('partials.bookingDateField', ['id' => 'hdBookingDate'])

                <h6 class="hd-section-heading">Choose a Room</h6>
                <div id="hdRoomList"></div>

                <h6 class="hd-section-heading mt-3">Hotel Policies</h6>
                <div id="hdPolicyList"></div>
            </div>

            <div class="modal-footer hd-modal-footer">
                <div>
                    <div class="hd-total-label">Selected room</div>
                    <div class="hd-total-room" id="hdSelectedRoomName"></div>
                    <div class="hd-total-price" id="hdTotalPrice"></div>
                </div>
                <button type="button" class="btn btn-book-room" id="hdBookRoom">Book Room <i class="bi bi-arrow-right"></i></button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('hotelDetailModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const roomList = document.getElementById('hdRoomList');
    const bookRoomBtn = document.getElementById('hdBookRoom');
    let currentHotel = null;
    let selectedRoomKey = null;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /** The hotel's photo, or the gradient placeholder when it has none. */
    function renderHeroImage(hotel) {
        document.getElementById('hdCarouselSlides').innerHTML = hotel.image
            ? `<div class="hd-carousel-slide active"><img src="${escapeHtml(hotel.image)}" alt="${escapeHtml(hotel.name)}"></div>`
            : `<div class="hd-carousel-slide ph-${hotel.placeholder_shade} active"><i class="bi bi-image"></i></div>`;

        const badge = document.getElementById('hdCarouselBadge');
        badge.textContent = hotel.badge || '';
        badge.style.display = hotel.badge ? '' : 'none';
    }

    function renderRoomCard(room) {
        if (!room.available) {
            return `
                <div class="room-card unavailable">
                    <div class="room-card-head">
                        <div class="room-card-name"><i class="bi bi-circle text-muted"></i> ${room.name}</div>
                    </div>
                    <div class="room-card-unavailable-note">Not available at this hotel</div>
                </div>
            `;
        }

        const isSelected = room.key === selectedRoomKey;
        const perksHtml = room.perks
            .map(p => `<div><i class="bi bi-check-lg"></i>${escapeHtml(p)}</div>`)
            .join('');

        return `
            <div class="room-card ${isSelected ? 'selected' : ''}" data-room-key="${room.key}">
                <div class="room-card-head">
                    <div>
                        <div class="room-card-name">
                            <i class="bi ${isSelected ? 'bi-record-circle' : 'bi-circle'}"></i>
                            ${room.name}
                        </div>
                        <div class="room-card-meta">${room.size_sqm} m² · ${escapeHtml(room.bed_info)}</div>
                    </div>
                    <div class="room-card-price">$${room.price}<span class="per-night">/ night</span></div>
                </div>
                <div class="room-card-perks">${perksHtml}</div>
            </div>
        `;
    }

    function renderRooms() {
        roomList.innerHTML = currentHotel.rooms.map(renderRoomCard).join('');
    }

    function renderPolicies() {
        document.getElementById('hdPolicyList').innerHTML = currentHotel.policies.map(policy => `
            <div class="hd-policy-tile">
                <i class="bi bi-info-circle"></i>
                <div>
                    <div class="title">${escapeHtml(policy.title)}</div>
                    <div class="description">${escapeHtml(policy.description)}</div>
                </div>
            </div>
        `).join('');
    }

    function updateTotals() {
        const room = currentHotel.rooms.find(r => r.key === selectedRoomKey);
        document.getElementById('hdSelectedRoomName').textContent = room ? room.name : '—';
        document.getElementById('hdTotalPrice').innerHTML = room ? `$${room.price}<span class="per-night"> / night</span>` : '—';
        bookRoomBtn.disabled = !room;
    }

    roomList.addEventListener('click', function (e) {
        const card = e.target.closest('.room-card:not(.unavailable)');
        if (!card) return;
        selectedRoomKey = card.dataset.roomKey;
        renderRooms();
        updateTotals();
    });

    bookRoomBtn.addEventListener('click', function () {
        if (!currentHotel || !selectedRoomKey) return;

        const bookingDate = window.Voyagr.readBookingDate(document.getElementById('hdBookingDate'));
        if (!bookingDate) return;

        window.Voyagr.addToCart({
            type: 'hotel',
            item_id: currentHotel.id,
            option_key: selectedRoomKey,
            booking_date: bookingDate,
        }, bookRoomBtn);
    });

    document.querySelectorAll('.js-view-hotel').forEach(function (btn) {
        btn.addEventListener('click', function () {
            currentHotel = JSON.parse(btn.dataset.hotel);
            selectedRoomKey = (currentHotel.rooms.find(r => r.available) || {}).key || null;

            renderHeroImage(currentHotel);
            document.getElementById('hdName').textContent = currentHotel.name;
            document.getElementById('hdAddress').textContent = currentHotel.address;
            document.getElementById('hdDescription').textContent = currentHotel.description;
            document.getElementById('hdCheckIn').textContent = currentHotel.check_in_time;
            document.getElementById('hdCheckOut').textContent = currentHotel.check_out_time;
            document.getElementById('hdRoomsAvailable').textContent = `${currentHotel.rooms.filter(r => r.available).length} types`;
            document.getElementById('hdContact').textContent = currentHotel.contact_phone || 'N/A';

            renderRooms();
            renderPolicies();
            updateTotals();

            modal.show();
        });
    });
});
</script>
@endpush
