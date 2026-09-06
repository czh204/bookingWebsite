@extends('layouts.homeApp')

@section('title', 'Experiences & Activities')

@push('styles')
<style>
    /* ---------- Attractions hero ---------- */
    .attractions-hero {
        background: linear-gradient(135deg, #2a3a5c 0%, var(--navy) 55%, var(--navy-dark) 100%);
        color: #fff;
        padding: 2.75rem 1.5rem 6rem;
    }

    .attractions-hero h1 { font-size: 2rem; margin-bottom: .25rem; }
    .attractions-hero p { opacity: .85; margin: 0; }

    /* ---------- Search bar ---------- */
    .attractions-search-bar {
        max-width: 1180px;
        margin: -4rem auto 0;
        background: #fff;
        border-radius: .9rem;
        box-shadow: 0 20px 40px -12px rgba(0,0,0,.2);
        padding: 1.25rem;
        position: relative;
        z-index: 2;
    }

    .attractions-search-bar label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #4b5563;
        margin-bottom: .35rem;
        display: block;
    }

    .attractions-search-bar .form-control {
        background: var(--cream);
        border-color: var(--border-soft);
        border-radius: .6rem;
        padding: .6rem .85rem;
    }

    .btn-attractions-search {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .6rem 1rem;
        white-space: nowrap;
    }

    .btn-attractions-search:hover { background: var(--navy-dark); color: #fff; }

    /* ---------- Layout ---------- */
    .attractions-body { max-width: 1180px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

    /* ---------- Category colors ---------- */
    .cat-museum { background: #dbe8fd; color: #2563eb; }
    .cat-tour { background: #dcf3e6; color: #16a34a; }
    .cat-food-drink { background: #fdf1d0; color: #b7791f; }
    .cat-adventure { background: #fde3e3; color: #b91c1c; }

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
    .filters-card .form-check-label { font-size: .85rem; }
    .filters-card .form-check-input:checked { background-color: var(--navy); border-color: var(--navy); }

    .category-chip {
        display: inline-block;
        border-radius: 999px;
        padding: .1rem .65rem;
        font-size: .8rem;
        font-weight: 600;
    }

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

    .quick-category-pills { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.25rem; }

    .quick-pill {
        border: 1px solid var(--border-soft);
        background: #fff;
        border-radius: 999px;
        padding: .4rem 1rem;
        font-size: .85rem;
        font-weight: 600;
        color: var(--navy-dark);
        text-decoration: none;
    }

    .quick-pill.active { background: var(--navy); border-color: var(--navy); color: #fff; }
    .quick-pill.cat-museum.active { background: #2563eb; border-color: #2563eb; color: #fff; }
    .quick-pill.cat-tour.active { background: #16a34a; border-color: #16a34a; color: #fff; }
    .quick-pill.cat-food-drink.active { background: #b7791f; border-color: #b7791f; color: #fff; }
    .quick-pill.cat-adventure.active { background: #b91c1c; border-color: #b91c1c; color: #fff; }

    .attraction-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .9rem;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .attraction-thumb {
        position: relative;
        height: 170px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.4);
        font-size: 2rem;
    }

    .attraction-thumb-badge {
        position: absolute;
        top: .6rem;
        left: .6rem;
        border-radius: 999px;
        padding: .25rem .75rem;
        font-size: .72rem;
        font-weight: 600;
    }

    .attraction-body { padding: 1rem; display: flex; flex-direction: column; flex: 1; }
    .attraction-body .title { font-weight: 700; color: var(--navy-dark); margin-bottom: .3rem; }


    .attraction-meta { font-size: .82rem; color: var(--text-muted); display: flex; flex-direction: column; gap: .3rem; margin-bottom: .9rem; }
    .attraction-meta span { display: flex; align-items: center; gap: .4rem; }

    .attraction-footer {
        margin-top: auto;
        padding-top: .85rem;
        border-top: 1px solid var(--border-soft);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .attraction-footer .price { font-weight: 700; font-size: 1.15rem; color: var(--navy-dark); }
    .attraction-footer .per-person { font-size: .72rem; color: var(--text-muted); font-weight: 400; }

    .btn-view-book {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .55rem 1.1rem;
        font-size: .85rem;
        white-space: nowrap;
    }

    .btn-view-book:hover { background: var(--navy-dark); color: #fff; }

    .no-attractions {
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

    /* Photo placeholders (no real attraction photos yet) */
    .ph-1 { background-image: linear-gradient(135deg, #3b5b8f, #1e2a45); }
    .ph-2 { background-image: linear-gradient(135deg, #2f8f6e, #16532f); }
    .ph-3 { background-image: linear-gradient(135deg, #b3502f, #7a2e12); }
    .ph-4 { background-image: linear-gradient(135deg, #5a4a8f, #2c1f57); }
    .ph-5 { background-image: linear-gradient(135deg, #b7791f, #7a4e0f); }
    .ph-6 { background-image: linear-gradient(135deg, #2f7f8f, #163f57); }

    /* ---------- Attraction detail modal ---------- */
    .attraction-modal-content { border-radius: .9rem; border: none; overflow: hidden; }

    .ad-carousel { position: relative; height: 240px; }
    .ad-carousel-slide {
        position: absolute;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.4);
        font-size: 3rem;
    }
    .ad-carousel-slide.active { display: flex; }

    .ad-carousel-badge {
        position: absolute;
        top: .85rem;
        left: .85rem;
        border-radius: 999px;
        padding: .3rem .85rem;
        font-size: .75rem;
        font-weight: 600;
        z-index: 2;
    }

    .ad-carousel-close {
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

    .ad-carousel-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
        background: rgba(0,0,0,.45);
        border: none;
        color: #fff;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ad-carousel-arrow.prev { left: .75rem; }
    .ad-carousel-arrow.next { right: .75rem; }

    .ad-carousel-dots {
        position: absolute;
        bottom: .75rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
        display: flex;
        gap: .3rem;
    }

    .ad-carousel-dots span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(255,255,255,.5);
    }

    .ad-carousel-dots span.active { background: #fff; }

    .ad-body { padding: 1.25rem; }
    .ad-name { font-size: 1.3rem; font-weight: 700; color: var(--navy-dark); margin-bottom: .4rem; }

    .ad-meta-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .3rem 1rem;
        font-size: .88rem;
        color: var(--navy-dark);
        margin-bottom: .9rem;
    }

    .ad-meta-row .meta-item { display: flex; align-items: center; gap: .35rem; color: var(--text-muted); }

    .ad-description { color: var(--text-muted); font-size: .9rem; line-height: 1.55; margin-bottom: 1.1rem; }

    .ad-info-tile {
        background: var(--cream);
        border-radius: .7rem;
        padding: .75rem .9rem;
        display: flex;
        gap: .55rem;
        align-items: flex-start;
        margin-bottom: 1.1rem;
    }

    .ad-info-tile i { color: var(--text-muted); margin-top: .15rem; }
    .ad-info-tile .label { font-weight: 700; color: var(--navy-dark); font-size: .85rem; }
    .ad-info-tile .value { color: var(--text-muted); font-size: .85rem; }

    .ad-section-heading { font-weight: 700; color: var(--navy-dark); font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; margin-bottom: .6rem; }

    .ad-list-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.1rem; font-size: .85rem; }
    .ad-list-grid .list-items { display: flex; flex-direction: column; gap: .55rem; margin-top: .65rem; }
    .ad-list-grid .list-items div { display: flex; align-items: flex-start; gap: .5rem; color: var(--text-muted); }
    .ad-list-grid i { margin-top: .18rem; }

    .bring-chip {
        display: inline-block;
        background: var(--cream);
        border-radius: 999px;
        padding: .25rem .8rem;
        font-size: .78rem;
        color: var(--navy-dark);
        margin: 0 .4rem .4rem 0;
    }

    .ad-two-tile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: 1.1rem; }

    .ad-slot-btn {
        border: 1px solid var(--border-soft);
        background: #fff;
        border-radius: .6rem;
        padding: .6rem 1rem;
        font-weight: 600;
        color: var(--navy-dark);
        font-size: .9rem;
    }

    .ad-slot-btn.selected { border-color: var(--navy); border-width: 2px; color: var(--navy); }
    .ad-slot-btn.unavailable { opacity: .45; cursor: not-allowed; text-decoration: line-through; }

    .ad-policy-tile {
        background: #eaf7ee;
        border: 1px solid #cdeed7;
        border-radius: .7rem;
        padding: .85rem 1rem;
        display: flex;
        gap: .6rem;
        align-items: flex-start;
        margin-top: 1.1rem;
    }

    .ad-policy-tile i { color: #16a34a; margin-top: .15rem; }
    .ad-policy-tile .title { font-weight: 700; color: #15803d; font-size: .85rem; }
    .ad-policy-tile .description { color: #276b45; font-size: .82rem; }

    .ad-modal-footer {
        border-top: 1px solid var(--border-soft);
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
    }

    .ad-total-label { font-size: .75rem; color: var(--text-muted); }
    .ad-total-time { font-weight: 700; color: var(--navy-dark); }
    .ad-total-price { font-weight: 700; font-size: 1.4rem; color: var(--navy-dark); }
    .ad-total-price .per-person { font-size: .72rem; font-weight: 400; color: var(--text-muted); }

    .btn-book-now {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .7rem 1.3rem;
    }

    .btn-book-now:hover { background: var(--navy-dark); color: #fff; }
    .btn-book-now:disabled { opacity: .6; }
</style>
@endpush

@php
    $categorySlugs = [
        'Museum' => 'museum',
        'Tour' => 'tour',
        'Food & Drink' => 'food-drink',
        'Adventure' => 'adventure',
    ];
@endphp

@section('content')

<form method="GET" action="{{ route('attractions.index') }}" id="attractionsFilterForm">

{{-- ================= HERO ================= --}}
<section class="attractions-hero">
    <div class="mx-auto" style="max-width: 1180px;">
        <h1 class="font-serif fw-bold">Experiences &amp; Activities</h1>
        <p>Handpicked experiences in top destinations</p>
    </div>
</section>

{{-- ================= SEARCH BAR ================= --}}
<div class="attractions-search-bar">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-6">
            <label>Location</label>
            <input type="text" class="form-control" name="location" value="{{ request('location') }}" placeholder="City or area">
        </div>
        <div class="col-6 col-md-3">
            <label>Date</label>
            <input type="date" class="form-control" name="date" value="{{ request('date') }}">
        </div>
        <div class="col-6 col-md-3">
            <button type="submit" class="btn btn-attractions-search w-100">
                <i class="bi bi-search me-1"></i> Search Experiences
            </button>
        </div>
    </div>
</div>

{{-- ================= BODY ================= --}}
<div class="attractions-body">
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
                        <input type="number" min="0" class="form-control" name="max_price" value="{{ request('max_price') }}" placeholder="$300">
                    </div>
                </div>

                <div class="filters-section">
                    <div class="filters-section-label">Duration</div>
                    @php
                        $durationOptions = ['under-2' => 'Under 2 hours', '2-4' => '2–4 hours', '4-8' => '4–8 hours', 'full-day' => 'Full day'];
                        $selectedDurations = request('duration', []);
                    @endphp
                    @foreach ($durationOptions as $value => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="duration[]" value="{{ $value }}"
                                   id="duration-{{ $value }}" {{ in_array($value, $selectedDurations) ? 'checked' : '' }}>
                            <label class="form-check-label" for="duration-{{ $value }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-apply-filters mt-3">Apply Filters</button>
                <a href="{{ route('attractions.index') }}" class="btn-reset-filters">Reset all</a>
            </div>
        </div>

        {{-- ---------- Results ---------- --}}
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="results-count">{{ $attractions->total() }} experience{{ $attractions->total() === 1 ? '' : 's' }}</div>
                <div class="d-flex align-items-center gap-2">
                    <select name="sort" class="form-select sort-select" onchange="document.getElementById('attractionsFilterForm').submit()">
                        <option value="recommended" {{ request('sort', 'recommended') === 'recommended' ? 'selected' : '' }}>Recommended</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>
            </div>

            {{-- Quick category pills --}}
            @php $selectedCategories = request('categories', $categories); @endphp
            @php $isAllSelected = count(array_diff($categories, $selectedCategories)) === 0 && count($selectedCategories) === count($categories); @endphp
            <div class="quick-category-pills">
                <a href="{{ route('attractions.index', array_merge(request()->except(['categories', 'page']), [])) }}"
                   class="quick-pill {{ $isAllSelected ? 'active' : '' }}">All</a>
                @foreach ($categories as $category)
                    <a href="{{ route('attractions.index', array_merge(request()->except('page'), ['categories' => [$category]])) }}"
                       class="quick-pill cat-{{ $categorySlugs[$category] }} {{ (!$isAllSelected && in_array($category, $selectedCategories) && count($selectedCategories) === 1) ? 'active' : '' }}">
                        {{ $category }}
                    </a>
                @endforeach
            </div>

            <div class="row row-cols-1 row-cols-md-2 g-3">
                @forelse ($attractions as $index => $attraction)
                    <div class="col">
                        <div class="attraction-card">
                            <div class="attraction-thumb ph-{{ ($index % 6) + 1 }}">
                                <span class="attraction-thumb-badge cat-{{ $categorySlugs[$attraction->category] }}">{{ $attraction->category }}</span>
                                <i class="bi bi-image"></i>
                            </div>
                            <div class="attraction-body">
                                <div class="title">{{ $attraction->title }}</div>
                                <div class="attraction-meta">
                                    <span><i class="bi bi-clock"></i> {{ $attraction->duration_label }}</span>
                                    <span><i class="bi bi-people"></i> Up to {{ $attraction->capacity }}</span>
                                    <span><i class="bi bi-geo-alt"></i> {{ $attraction->location_label }}</span>
                                </div>
                                <div class="attraction-footer">
                                    <div>
                                        <div class="price">${{ number_format($attraction->price) }}</div>
                                        <div class="per-person">/ person</div>
                                    </div>
                                    <button type="button" class="btn btn-view-book js-view-attraction" data-attraction="{{ json_encode($attraction) }}">
                                        View &amp; Book <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="no-attractions">
                            <i class="bi bi-compass fs-2 d-block mb-2"></i>
                            No experiences match your filters. Try adjusting your search.
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($attractions->hasPages())
                <div class="mt-4">
                    {{ $attractions->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

</form>

{{-- ================= ATTRACTION DETAIL MODAL ================= --}}
<div class="modal fade" id="attractionDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" style="max-width: 760px;">
        <div class="modal-content attraction-modal-content">
            <div class="ad-carousel" id="adCarousel">
                <span class="ad-carousel-badge" id="adCarouselBadge"></span>
                <button type="button" class="ad-carousel-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                <button type="button" class="ad-carousel-arrow prev" id="adPrevSlide"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="ad-carousel-arrow next" id="adNextSlide"><i class="bi bi-chevron-right"></i></button>
                <div class="ad-carousel-dots" id="adCarouselDots"></div>
                <div id="adCarouselSlides"></div>
            </div>

            <div class="ad-body">
                <div class="ad-name" id="adName"></div>
                <div class="ad-meta-row">
                    <span class="meta-item"><i class="bi bi-clock"></i> <span id="adDuration"></span></span>
                    <span class="meta-item"><i class="bi bi-people"></i> <span id="adCapacity"></span></span>
                </div>
                <div class="ad-description" id="adDescription"></div>

                <div class="ad-info-tile">
                    <i class="bi bi-geo-alt"></i>
                    <div>
                        <div class="label">Meeting Point</div>
                        <div class="value" id="adMeetingPoint"></div>
                    </div>
                </div>

                <div class="ad-list-grid">
                    <div>
                        <div class="ad-section-heading">What's Included</div>
                        <div class="list-items" id="adIncluded"></div>
                    </div>
                    <div>
                        <div class="ad-section-heading">Not Included</div>
                        <div class="list-items" id="adNotIncluded"></div>
                    </div>
                </div>

                <div class="ad-section-heading"><i class="bi bi-camera me-1"></i> What to Bring</div>
                <div class="mb-3" id="adBring"></div>

                <div class="ad-two-tile-grid">
                    <div class="ad-info-tile mb-0">
                        <div>
                            <div class="label">Min. Age / Fitness</div>
                            <div class="value" id="adMinAge"></div>
                        </div>
                    </div>
                    <div class="ad-info-tile mb-0">
                        <div>
                            <div class="label">Languages</div>
                            <div class="value" id="adLanguages"></div>
                        </div>
                    </div>
                </div>

                <div class="ad-section-heading"><i class="bi bi-calendar-event me-1"></i> Select Time Slot</div>
                <div class="d-flex flex-wrap gap-2" id="adSlotList"></div>

                <div class="ad-policy-tile">
                    <i class="bi bi-shield-check"></i>
                    <div>
                        <div class="title">Cancellation Policy</div>
                        <div class="description" id="adCancellationPolicy"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer ad-modal-footer">
                <div>
                    <div class="ad-total-label">Selected time</div>
                    <div class="ad-total-time" id="adSelectedSlot"></div>
                    <div class="ad-total-price" id="adTotalPrice"></div>
                </div>
                <button type="button" class="btn btn-book-now" id="adBookNow">Book Now <i class="bi bi-arrow-right"></i></button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('attractionDetailModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const slotList = document.getElementById('adSlotList');
    const bookNowBtn = document.getElementById('adBookNow');
    let currentAttraction = null;
    let selectedSlotKey = null;
    let currentSlide = 0;
    const slideCount = 3;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderCarousel(attraction, index) {
        const shades = ['ph-1', 'ph-2', 'ph-3', 'ph-4', 'ph-5', 'ph-6'];
        const slidesEl = document.getElementById('adCarouselSlides');
        const dotsEl = document.getElementById('adCarouselDots');
        let slidesHtml = '';
        let dotsHtml = '';
        for (let i = 0; i < slideCount; i++) {
            const shade = shades[(index + i) % shades.length];
            slidesHtml += `<div class="ad-carousel-slide ${shade} ${i === 0 ? 'active' : ''}" data-slide="${i}"><i class="bi bi-image"></i></div>`;
            dotsHtml += `<span class="${i === 0 ? 'active' : ''}"></span>`;
        }
        slidesEl.innerHTML = slidesHtml;
        dotsEl.innerHTML = dotsHtml;
        currentSlide = 0;

        const categorySlugs = { 'Museum': 'museum', 'Tour': 'tour', 'Food & Drink': 'food-drink', 'Adventure': 'adventure' };
        const badge = document.getElementById('adCarouselBadge');
        badge.textContent = attraction.category;
        badge.className = 'ad-carousel-badge cat-' + categorySlugs[attraction.category];
    }

    function showSlide(n) {
        const slides = document.querySelectorAll('.ad-carousel-slide');
        const dots = document.querySelectorAll('.ad-carousel-dots span');
        if (!slides.length) return;
        currentSlide = (n + slides.length) % slides.length;
        slides.forEach((s, i) => s.classList.toggle('active', i === currentSlide));
        dots.forEach((d, i) => d.classList.toggle('active', i === currentSlide));
    }

    document.getElementById('adPrevSlide').addEventListener('click', () => showSlide(currentSlide - 1));
    document.getElementById('adNextSlide').addEventListener('click', () => showSlide(currentSlide + 1));

    function renderList(elId, items, iconClass) {
        document.getElementById(elId).innerHTML = items.map(item => `<div><i class="bi ${iconClass}"></i>${escapeHtml(item)}</div>`).join('');
    }

    function renderSlots() {
        slotList.innerHTML = currentAttraction.time_slots.map(slot => {
            if (!slot.available) {
                return `<button type="button" class="ad-slot-btn unavailable" disabled>${slot.label}</button>`;
            }
            const isSelected = slot.key === selectedSlotKey;
            return `<button type="button" class="ad-slot-btn ${isSelected ? 'selected' : ''}" data-slot-key="${slot.key}">${slot.label}</button>`;
        }).join('');
    }

    function updateTotals() {
        const slot = currentAttraction.time_slots.find(s => s.key === selectedSlotKey);
        document.getElementById('adSelectedSlot').textContent = slot ? slot.label : '—';
        document.getElementById('adTotalPrice').innerHTML = slot ? `$${slot.price}<span class="per-person"> / person</span>` : '—';
        bookNowBtn.disabled = !slot;
    }

    slotList.addEventListener('click', function (e) {
        const btn = e.target.closest('.ad-slot-btn:not(.unavailable)');
        if (!btn) return;
        selectedSlotKey = btn.dataset.slotKey;
        renderSlots();
        updateTotals();
    });

    bookNowBtn.addEventListener('click', function () {
        if (!currentAttraction || !selectedSlotKey) return;

        window.Voyagr.addToCart({
            type: 'attraction',
            item_id: currentAttraction.id,
            option_key: selectedSlotKey,
        }, bookNowBtn);
    });

    document.querySelectorAll('.js-view-attraction').forEach(function (btn, index) {
        btn.addEventListener('click', function () {
            currentAttraction = JSON.parse(btn.dataset.attraction);
            selectedSlotKey = (currentAttraction.time_slots.find(s => s.available) || {}).key || null;

            renderCarousel(currentAttraction, index);
            document.getElementById('adName').textContent = currentAttraction.title;
            document.getElementById('adDuration').textContent = currentAttraction.duration_label;
            document.getElementById('adCapacity').textContent = `Up to ${currentAttraction.capacity}`;
            document.getElementById('adDescription').textContent = currentAttraction.description;
            document.getElementById('adMeetingPoint').textContent = currentAttraction.meeting_point;
            document.getElementById('adMinAge').textContent = currentAttraction.min_age_fitness;
            document.getElementById('adLanguages').textContent = currentAttraction.languages;
            document.getElementById('adCancellationPolicy').textContent = currentAttraction.cancellation_policy;

            renderList('adIncluded', currentAttraction.included, 'bi-check-lg text-success');
            renderList('adNotIncluded', currentAttraction.not_included, 'bi-x-lg text-danger');
            document.getElementById('adBring').innerHTML = currentAttraction.what_to_bring.map(item => `<span class="bring-chip">${escapeHtml(item)}</span>`).join('');

            renderSlots();
            updateTotals();

            modal.show();
        });
    });
});
</script>
@endpush
