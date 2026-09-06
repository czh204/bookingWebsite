@extends('layouts.homeApp')

@section('title', 'Your Cart')

@push('styles')
<style>
    /* ---------- Cart hero ---------- */
    .cart-hero {
        background: var(--navy);
        color: #fff;
        padding: 2.25rem 1.5rem 5.5rem;
    }

    .cart-hero h1 { font-size: 2rem; margin-bottom: .2rem; }
    .cart-hero p { opacity: .8; margin: 0; font-size: .9rem; }

    .cart-body {
        max-width: 1240px;
        margin: -4.25rem auto 0;
        padding: 0 1.5rem 4rem;
        position: relative;
        z-index: 2;
    }

    .cart-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .8rem;
        padding: 1.25rem;
    }

    /* ---------- Line items ---------- */
    .cart-line { display: flex; align-items: flex-start; gap: 1rem; }
    .cart-line + .cart-line { margin-top: 1rem; }

    .cart-line-icon {
        width: 46px;
        height: 46px;
        border-radius: .6rem;
        background: #e7eefb;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .cart-line-type {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .cart-line-title { font-weight: 700; font-size: 1.05rem; color: var(--navy-dark); }
    .cart-line-sub, .cart-line-meta { font-size: .85rem; color: var(--text-muted); }
    .cart-line-qty { font-size: .8rem; color: var(--text-muted); }

    .cart-line-date {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-top: .4rem;
        background: #eef2f9;
        border-radius: 999px;
        padding: .15rem .7rem;
        font-size: .8rem;
        font-weight: 600;
        color: var(--navy);
    }
    .cart-line-price { font-weight: 700; font-size: 1.15rem; color: var(--navy-dark); text-align: right; white-space: nowrap; }

    .btn-remove-line {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 1.05rem;
        padding: .2rem;
        line-height: 1;
    }

    .btn-remove-line:hover { color: #b91c1c; }

    /* ---------- Order summary ---------- */
    .summary-card { position: sticky; top: 1.5rem; }
    .summary-title { font-weight: 700; font-size: 1.2rem; color: var(--navy-dark); margin-bottom: 1rem; }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: .9rem;
        color: #4b5563;
        margin-bottom: .6rem;
    }

    .summary-row.discount { color: #16a34a; }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--border-soft);
        padding-top: .9rem;
        margin-top: .9rem;
        font-weight: 700;
        color: var(--navy-dark);
    }

    .summary-total .label { font-size: 1.05rem; }
    .summary-total .value { font-size: 1.45rem; }

    .promo-label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin: 1.25rem 0 .5rem;
    }

    .promo-row { display: flex; gap: .5rem; }

    .promo-row .form-control {
        background: var(--cream);
        border-color: var(--border-soft);
        border-radius: .6rem;
    }

    .btn-apply-promo {
        border: 1px solid var(--border-soft);
        background: #fff;
        border-radius: .6rem;
        color: var(--navy-dark);
        font-weight: 600;
        padding: .5rem 1.1rem;
        white-space: nowrap;
    }

    .btn-apply-promo:hover { background: var(--cream); }

    .btn-checkout {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 700;
        padding: .8rem 1rem;
        width: 100%;
        margin-top: 1.1rem;
    }

    .btn-checkout:hover { background: var(--navy-dark); color: #fff; }
    .btn-checkout:disabled { opacity: .55; }

    .btn-continue {
        border: 1px solid var(--border-soft);
        background: #fff;
        border-radius: .6rem;
        color: var(--navy-dark);
        font-weight: 500;
        padding: .7rem 1rem;
        width: 100%;
        margin-top: .6rem;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    .btn-continue:hover { background: var(--cream); color: var(--navy-dark); }

    .signin-note {
        font-size: .8rem;
        color: var(--text-muted);
        text-align: center;
        margin-top: .6rem;
    }

    .signin-note a { color: var(--navy-dark); }

    .secure-note {
        display: flex;
        gap: .6rem;
        align-items: flex-start;
        background: #eef2f9;
        border-radius: .6rem;
        padding: .8rem .9rem;
        margin-top: 1.1rem;
        font-size: .8rem;
        color: #4b5563;
    }

    .secure-note i { color: var(--navy); margin-top: .1rem; }

    /* ---------- Empty state ---------- */
    .cart-empty { text-align: center; padding: 3.5rem 1.5rem; }

    .cart-empty-icon {
        width: 78px;
        height: 78px;
        border-radius: 50%;
        background: #ecebe6;
        color: #9ca3af;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.9rem;
        margin-bottom: 1.1rem;
    }

    .cart-empty h2 { font-size: 1.5rem; color: var(--navy-dark); margin-bottom: .4rem; }
    .cart-empty p { color: var(--text-muted); margin-bottom: 1.4rem; }

    .btn-explore {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .75rem 1.5rem;
        text-decoration: none;
        display: inline-block;
    }

    .btn-explore:hover { background: var(--navy-dark); color: #fff; }
</style>
@endpush

@section('content')

<section class="cart-hero">
    <div class="mx-auto" style="max-width: 1240px;">
        <h1 class="font-serif fw-bold">Your Cart</h1>
    </div>
</section>

<div class="cart-body">
    @if ($lines->isEmpty())
        <div class="cart-card cart-empty">
            <div class="cart-empty-icon"><i class="bi bi-cart3"></i></div>
            <h2 class="font-serif fw-bold">Your cart is empty</h2>
            <p>Start exploring flights, hotels, and activities to add to your trip.</p>
            <a href="{{ route('flights.index') }}" class="btn-explore">Explore destinations <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    @else
        <div class="row g-4">

            {{-- ---------- Line items ---------- --}}
            <div class="col-lg-8">
                <div class="cart-card">
                    @foreach ($lines as $line)
                        <div class="cart-line">
                            <span class="cart-line-icon"><i class="bi {{ $line->icon }}"></i></span>

                            <div class="flex-grow-1">
                                <div class="cart-line-type">{{ $line->type }}</div>
                                <div class="cart-line-title">{{ $line->title }}</div>
                                <div class="cart-line-sub">{{ $line->subtitle }}</div>
                                <div class="cart-line-meta">{{ $line->meta }}</div>
                                <div class="cart-line-date">
                                    <i class="bi bi-calendar3"></i>
                                    {{ \Carbon\Carbon::parse($line->booking_date)->format('D, j M Y') }}
                                </div>
                                @if ($line->quantity > 1)
                                    <div class="cart-line-qty">Quantity: {{ $line->quantity }} × ${{ number_format($line->unit_price) }}</div>
                                @endif
                            </div>

                            <div class="text-end">
                                <div class="cart-line-price">${{ number_format($line->unit_price * $line->quantity) }}</div>
                                {{-- DELETE, so a crawler or a prefetch can't empty the cart. --}}
                                <form method="POST" action="{{ route('cart.destroy', $line->line_id) }}" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-remove-line" aria-label="Remove {{ $line->title }}">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if (! $loop->last)
                            <hr class="my-3">
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- ---------- Order summary ---------- --}}
            <div class="col-lg-4">
                <div class="cart-card summary-card">
                    <div class="summary-title font-serif">Order Summary</div>

                    <div class="summary-row">
                        <span>Subtotal ({{ $totals->item_count }} item{{ $totals->item_count === 1 ? '' : 's' }})</span>
                        <span>${{ number_format($totals->subtotal, 2) }}</span>
                    </div>

                    @if ($totals->discount > 0)
                        <div class="summary-row discount">
                            <span>Promo ({{ $promoCode }}{{ $promoLabel ? " · {$promoLabel}" : '' }})</span>
                            <span>−${{ number_format($totals->discount, 2) }}</span>
                        </div>
                    @endif

                    <div class="summary-row">
                        <span>Service Fee</span>
                        <span>${{ number_format($totals->service_fee, 2) }}</span>
                    </div>

                    <div class="summary-row">
                        <span>Tax ({{ (int) (\App\Services\Cart::TAX_RATE * 100) }}%)</span>
                        <span>${{ number_format($totals->tax, 2) }}</span>
                    </div>

                    <div class="summary-total">
                        <span class="label">Total</span>
                        <span class="value">${{ number_format($totals->total, 2) }}</span>
                    </div>

                    <form method="POST" action="{{ route('cart.promo') }}">
                        @csrf
                        <div class="promo-label">Promo Code</div>
                        <div class="promo-row">
                            <input type="text" name="promo_code" class="form-control"
                                   value="{{ $promoCode }}" placeholder="e.g. TRAVEL10">
                            <button type="submit" class="btn-apply-promo">Apply</button>
                        </div>
                        @if (session('promo_error'))
                            <div class="text-danger small mt-2">{{ session('promo_error') }}</div>
                        @elseif (session('promo_success'))
                            <div class="text-success small mt-2">{{ session('promo_success') }}</div>
                        @endif
                    </form>

                    @auth
                        <form method="GET" action="{{ route('checkout.show') }}">
                            <button type="submit" class="btn btn-checkout">
                                Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </form>
                    @else
                        {{-- Points at the checkout, not at the login page: the
                             auth middleware bounces the guest to sign-in and
                             records /checkout as the intended URL, so they land
                             back on the checkout afterwards with the cart
                             intact. Linking straight to login would lose that
                             and drop them on the home page. --}}
                        <a href="{{ route('checkout.show') }}" class="btn btn-checkout d-block text-center text-decoration-none">
                            <i class="bi bi-person me-1"></i> Sign in to Checkout
                        </a>
                        <div class="signin-note">
                            You'll need an account to complete a booking — sign in or
                            create one on the next page. Your cart is kept either way.
                        </div>
                    @endauth

                    <a href="{{ route('flights.index') }}" class="btn-continue">Continue Shopping</a>

                    <div class="secure-note">
                        <i class="bi bi-shield-check"></i>
                        <span>Your booking is protected by 256-bit SSL encryption and our Secure Payment Guarantee.</span>
                    </div>
                </div>
            </div>

        </div>
    @endif
</div>

@endsection
