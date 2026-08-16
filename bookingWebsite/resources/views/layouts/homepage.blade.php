@extends('layouts.homeApp')
 
@section('title', 'Your World, Beautifully Planned')
 
@push('styles')
<style>
    /* ---------- Hero ---------- */
    .hero {
        position: relative;
        min-height: 640px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem 1.5rem 8rem;
        background: linear-gradient(135deg, #2a3a5c 0%, var(--navy) 55%, var(--navy-dark) 100%);
        color: #fff;
        text-align: center;
        overflow: hidden;
    }
 
    .hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(20,30,50,.35) 0%, rgba(15,22,38,.6) 100%);
    }
 
    .hero-placeholder-tag {
        position: absolute;
        z-index: 1;
        bottom: 6.5rem;
        right: 1.25rem;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        background: rgba(0,0,0,.35);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 999px;
        padding: .3rem .8rem;
        font-size: .7rem;
        color: rgba(255,255,255,.75);
    }

    .hero-content { position: relative; z-index: 1; max-width: 46rem; }
 
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.25);
        border-radius: 999px;
        padding: .35rem 1rem;
        font-size: .8rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: 1.25rem;
    }
 
    .hero h1 {
        font-size: 3rem;
        line-height: 1.15;
        margin-bottom: 1rem;
    }
 
    .hero h1 .accent { color: var(--gold); }
 
    .hero p.lead-copy {
        font-size: 1.05rem;
        opacity: .9;
        margin-bottom: 0;
    }
 
    /* ---------- Search widget (overlaps hero bottom) ---------- */
    .search-widget {
        position: relative;
        z-index: 2;
        margin-top: -5rem;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
        background: #fff;
        border-radius: .9rem;
        box-shadow: 0 20px 40px -12px rgba(0,0,0,.25);
        overflow: hidden;
    }
 
    .search-tabs {
        display: flex;
        border-bottom: 1px solid var(--border-soft);
    }
 
    .search-tabs .nav-link {
        flex: 1;
        width: 100%;
        text-align: center;
        border: none;
        border-radius: 0;
        padding: .9rem 0;
        font-weight: 600;
        color: var(--text-muted);
    }
 
    .search-tabs .nav-link.active {
        color: var(--navy);
        border-bottom: 2px solid var(--navy);
        background: #fbfaf8;
    }
 
    .search-body { padding: 1.5rem; }
 
    .search-field label {
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #4b5563;
        margin-bottom: .35rem;
        display: block;
    }
 
    .search-field .form-control {
        border-radius: .6rem;
        border-color: var(--border-soft);
        padding: .65rem .85rem;
    }
 
    .btn-search {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .8rem;
    }
 
    .btn-search:hover { background: var(--navy-dark); color: #fff; }
 
    /* ---------- Quick links ---------- */
    .quick-links { padding: 6rem 1.5rem 3rem; max-width: 1180px; margin: 0 auto; }
 
    .quick-link-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .9rem;
        padding: 1.5rem;
        height: 100%;
        text-decoration: none;
        display: block;
        transition: box-shadow .15s ease, transform .15s ease;
    }
 
    .quick-link-card:hover { box-shadow: 0 8px 20px -8px rgba(0,0,0,.15); transform: translateY(-2px); }
 
    .quick-link-icon {
        width: 44px;
        height: 44px;
        border-radius: .7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: .9rem;
    }
 
    .icon-flights { background: #dbe8fd; color: #2563eb; }
    .icon-hotels  { background: #dcf3e6; color: #16a34a; }
    .icon-experiences { background: #fdf1d0; color: #b7791f; }
    .icon-planner { background: #fde3e9; color: #db2777; }
 
    .quick-link-card h3 { font-size: 1.05rem; color: var(--navy-dark); margin-bottom: .2rem; }
    .quick-link-card p  { font-size: .85rem; color: var(--text-muted); margin: 0; }
 
    /* ---------- Destinations ---------- */
    .destinations { padding: 2rem 1.5rem 5rem; max-width: 1180px; margin: 0 auto; }
 
    .section-eyebrow {
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--gold);
        margin-bottom: .35rem;
    }
 
    .section-title { font-size: 1.9rem; color: var(--navy-dark); margin-bottom: 0; }
 
    .view-all-link {
        color: var(--navy);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }
 
    .destination-card {
        position: relative;
        border-radius: .9rem;
        overflow: hidden;
        aspect-ratio: 4 / 3;
        background-size: cover;
        background-position: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        color: #fff;
        text-decoration: none;
    }
 
    .destination-card::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,.65) 100%);
        z-index: 0;
    }

    /* Photo placeholders (no real destination photos yet) */
    .destination-card.ph-1 { background-image: linear-gradient(135deg, #3b5b8f, #1e2a45); }
    .destination-card.ph-2 { background-image: linear-gradient(135deg, #2f8f6e, #16532f); }
    .destination-card.ph-3 { background-image: linear-gradient(135deg, #b3502f, #7a2e12); }
    .destination-card.ph-4 { background-image: linear-gradient(135deg, #5a4a8f, #2c1f57); }
    .destination-card.ph-5 { background-image: linear-gradient(135deg, #b7791f, #7a4e0f); }
    .destination-card.ph-6 { background-image: linear-gradient(135deg, #2f7f8f, #163f57); }

    .destination-photo-icon {
        position: relative;
        z-index: 0;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        color: rgba(255,255,255,.35);
    }
 
    .destination-badge, .destination-info {
        position: relative;
        z-index: 1;
    }
 
    .destination-badge {
        align-self: flex-start;
        margin: .8rem;
        background: rgba(0,0,0,.5);
        border-radius: 999px;
        padding: .3rem .8rem;
        font-size: .75rem;
        font-weight: 600;
    }
 
    .destination-info { padding: 1rem; }
    .destination-info h4 { font-size: 1.15rem; margin-bottom: .1rem; }
    .destination-info .country { font-size: .85rem; opacity: .85; }
    .destination-info .price { color: var(--gold); font-weight: 700; font-size: .9rem; }
 
    /* ---------- AI CTA ---------- */
    .ai-cta {
        background: var(--navy);
        color: #fff;
        text-align: center;
        padding: 4.5rem 1.5rem;
    }
 
    .ai-cta .hero-badge { background: rgba(255,255,255,.08); }
 
    .ai-cta h2 {
        font-size: 2.3rem;
        max-width: 38rem;
        margin: 0 auto 1rem;
        line-height: 1.25;
    }
 
    .ai-cta p { max-width: 34rem; margin: 0 auto 2rem; opacity: .85; }
 
    .btn-gold {
        background: var(--gold);
        border: none;
        border-radius: 999px;
        color: var(--navy-dark);
        font-weight: 700;
        padding: .8rem 1.75rem;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        text-decoration: none;
    }
 
    .btn-gold:hover { background: #c99a2f; color: var(--navy-dark); }
 
    /* ---------- Trust badges ---------- */
    .trust-section {
        background: #fff;
        padding: 3.5rem 1.5rem;
    }
 
    .trust-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #f1f0ec;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: var(--navy);
        margin: 0 auto 1rem;
    }
 
    .trust-item h5 { font-weight: 700; color: var(--navy-dark); }
    .trust-item p { color: var(--text-muted); font-size: .9rem; max-width: 18rem; margin: .25rem auto 0; }
</style>
@endpush
 
@section('content')
 
{{-- ================= HERO ================= --}}
<section class="hero">
    <div class="hero-content">
        <span class="hero-badge"><i class="bi bi-stars"></i> AI-Powered Travel Planning</span>
        <h1 class="font-serif fw-bold">Your World, <span class="accent">Beautifully</span> Planned</h1>
        <p class="lead-copy">Book flights, hotels, and experiences - or let our AI craft the perfect itinerary for your journey.</p>
    </div>
    <span class="hero-placeholder-tag"><i class="bi bi-image"></i> Hero photo placeholder</span>
</section>
 
{{-- ================= SEARCH WIDGET ================= --}}
<div class="search-widget">
    <ul class="nav search-tabs" id="searchTab" role="tablist">
        <li class="nav-item flex-fill" role="presentation">
            <button class="nav-link active" id="flights-tab" data-bs-toggle="tab" data-bs-target="#flights-pane" type="button" role="tab">
                <i class="bi bi-airplane me-1"></i> Flights
            </button>
        </li>
        <li class="nav-item flex-fill" role="presentation">
            <button class="nav-link" id="hotels-tab" data-bs-toggle="tab" data-bs-target="#hotels-pane" type="button" role="tab">
                <i class="bi bi-building me-1"></i> Hotels
            </button>
        </li>
        <li class="nav-item flex-fill" role="presentation">
            <button class="nav-link" id="attractions-tab" data-bs-toggle="tab" data-bs-target="#attractions-pane" type="button" role="tab">
                <i class="bi bi-geo-alt me-1"></i> Attractions
            </button>
        </li>
    </ul>
 
    <div class="tab-content search-body">
 
        {{-- Flights search --}}
        <div class="tab-pane fade show active" id="flights-pane" role="tabpanel">
            <form method="GET" action="{{ url('/flights') }}">
                <div class="row g-3 mb-3">
                    <div class="col-md-3 search-field">
                        <label>From</label>
                        <input type="text" class="form-control" name="from" value="New York (JFK)">
                    </div>
                    <div class="col-md-3 search-field">
                        <label>To</label>
                        <input type="text" class="form-control" name="to" placeholder="Where to?">
                    </div>
                    <div class="col-md-3 search-field">
                        <label>Departure</label>
                        <input type="date" class="form-control" name="departure">
                    </div>
                    <div class="col-md-3 search-field">
                        <label>Return</label>
                        <input type="date" class="form-control" name="return">
                    </div>
                </div>
                <button type="submit" class="btn btn-search w-100">
                    <i class="bi bi-search me-1"></i> Search Flights
                </button>
            </form>
        </div>
 
        {{-- Hotels search --}}
        <div class="tab-pane fade" id="hotels-pane" role="tabpanel">
            <form method="GET" action="{{ url('/hotels') }}">
                <div class="row g-3 mb-3">
                    <div class="col-md-4 search-field">
                        <label>Destination</label>
                        <input type="text" class="form-control" name="destination" placeholder="City or hotel name">
                    </div>
                    <div class="col-md-4 search-field">
                        <label>Check-in</label>
                        <input type="date" class="form-control" name="check_in">
                    </div>
                    <div class="col-md-4 search-field">
                        <label>Check-out</label>
                        <input type="date" class="form-control" name="check_out">
                    </div>
                </div>
                <button type="submit" class="btn btn-search w-100">
                    <i class="bi bi-search me-1"></i> Search Hotels
                </button>
            </form>
        </div>
 
        {{-- Attractions search --}}
        <div class="tab-pane fade" id="attractions-pane" role="tabpanel">
            <form method="GET" action="{{ url('/attractions') }}">
                <div class="row g-3 mb-3">
                    <div class="col-md-6 search-field">
                        <label>Destination</label>
                        <input type="text" class="form-control" name="destination" placeholder="Where are you going?">
                    </div>
                    <div class="col-md-6 search-field">
                        <label>Date</label>
                        <input type="date" class="form-control" name="date">
                    </div>
                </div>
                <button type="submit" class="btn btn-search w-100">
                    <i class="bi bi-search me-1"></i> Search Attractions
                </button>
            </form>
        </div>
 
    </div>
</div>
 
{{-- ================= QUICK LINKS ================= --}}
<section class="quick-links">
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <a href="{{ url('/flights') }}" class="quick-link-card">
                <div class="quick-link-icon icon-flights"><i class="bi bi-airplane"></i></div>
                <h3>Flights</h3>
                <p>Search 500+ airlines</p>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('hotels.index') }}" class="quick-link-card">
                <div class="quick-link-icon icon-hotels"><i class="bi bi-building"></i></div>
                <h3>Hotels</h3>
                <p>2M+ properties</p>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ url('/attractions') }}" class="quick-link-card">
                <div class="quick-link-icon icon-experiences"><i class="bi bi-geo-alt"></i></div>
                <h3>Experiences</h3>
                <p>Curated activities</p>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ url('/ai-planner') }}" class="quick-link-card">
                <div class="quick-link-icon icon-planner"><i class="bi bi-calendar-check"></i></div>
                <h3>AI Planner</h3>
                <p>Smart itineraries</p>
            </a>
        </div>
    </div>
</section>
 
{{-- ================= POPULAR DESTINATIONS ================= --}}
<section class="destinations">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <div class="section-eyebrow">Explore the World</div>
            <h2 class="font-serif fw-bold section-title">Popular Destinations</h2>
        </div>
        <a href="{{ url('/destinations') }}" class="view-all-link">View all <i class="bi bi-arrow-right"></i></a>
    </div>
 
    <div class="row g-3">
        @foreach ($destinations as $index => $destination)
            <div class="col-md-4">
                <a href="{{ url('/destinations/' . \Illuminate\Support\Str::slug($destination['city'])) }}"
                   class="destination-card ph-{{ ($index % 6) + 1 }}">
                    <span class="destination-badge">{{ $destination['badge'] }}</span>
                    <i class="bi bi-image destination-photo-icon"></i>
                    <div class="destination-info">
                        <h4 class="font-serif">{{ $destination['city'] }}</h4>
                        <div class="country">{{ $destination['country'] }}</div>
                        <div class="price">from ${{ number_format($destination['price']) }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</section>
 
{{-- ================= AI CTA ================= --}}
<section class="ai-cta">
    <span class="hero-badge"><i class="bi bi-stars"></i> Powered by AI</span>
    <h2 class="font-serif fw-bold">Let AI Build Your Perfect Itinerary</h2>
    <p>Chat with our AI travel assistant to create personalized day-by-day plans, discover hidden gems, and organize everything in one place.</p>
    <a href="{{ url('/ai-planner') }}" class="btn-gold">Start Planning <i class="bi bi-arrow-right"></i></a>
</section>
 
{{-- ================= TRUST BADGES ================= --}}
<section class="trust-section">
    <div class="row text-center g-4 max-w-100" style="max-width: 1180px; margin: 0 auto;">
        <div class="col-md-4 trust-item">
            <div class="trust-icon"><i class="bi bi-shield-check"></i></div>
            <h5>Secure Booking</h5>
            <p>Bank-level encryption protects every transaction</p>
        </div>
        <div class="col-md-4 trust-item">
            <div class="trust-icon"><i class="bi bi-star"></i></div>
            <h5>4.9/5 Rating</h5>
            <p>Trusted by over 2 million happy travellers</p>
        </div>
        <div class="col-md-4 trust-item">
            <div class="trust-icon"><i class="bi bi-clock"></i></div>
            <h5>24/7 Support</h5>
            <p>AI chatbot and human agents always available</p>
        </div>
    </div>
</section>
 
@endsection
 