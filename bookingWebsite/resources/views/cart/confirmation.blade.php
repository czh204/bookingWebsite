@extends('layouts.homeApp')

@section('title', 'Booking Confirmed')

@push('styles')
<style>
    .conf-hero { background: var(--navy); color: #fff; padding: 2.25rem 1.5rem 5.5rem; }
    .conf-hero h1 { font-size: 2rem; margin-bottom: .2rem; }
    .conf-hero p { opacity: .8; margin: 0; font-size: .9rem; }

    .conf-body {
        max-width: 860px;
        margin: -4.25rem auto 0;
        padding: 0 1.5rem 4rem;
        position: relative;
        z-index: 2;
    }

    .conf-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .8rem;
        padding: 1.25rem;
    }

    .conf-card + .conf-card { margin-top: 1.25rem; }

    .checkout-steps { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
    .checkout-step { display: inline-flex; align-items: center; gap: .5rem; color: var(--text-muted); font-size: .95rem; }

    .checkout-step .bubble {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #dcf3e6;
        color: #16a34a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .checkout-step.current { color: var(--navy-dark); font-weight: 700; }
    .checkout-step.current .bubble { background: var(--navy); color: #fff; }
    .checkout-steps .sep { color: #c9c5ba; }

    .conf-success { text-align: center; padding: 2rem 1.25rem 1.5rem; }

    .conf-success-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #dcf3e6;
        color: #16a34a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .conf-success h2 { font-size: 1.6rem; color: var(--navy-dark); margin-bottom: .3rem; }
    .conf-success p { color: var(--text-muted); margin-bottom: 1.25rem; }

    .conf-reference {
        display: inline-block;
        border: 1px dashed var(--border-soft);
        border-radius: .6rem;
        background: var(--cream);
        padding: .7rem 1.4rem;
    }

    .conf-reference .label {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .conf-reference .value {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--navy-dark);
    }

    .conf-section-title { font-size: 1.15rem; color: var(--navy-dark); margin-bottom: 1rem; }

    .conf-item { display: flex; justify-content: space-between; gap: 1rem; padding: .75rem 0; }
    .conf-item + .conf-item { border-top: 1px solid var(--border-soft); }
    .conf-item .title { font-weight: 600; color: var(--navy-dark); }
    .conf-item .meta { font-size: .82rem; color: var(--text-muted); }
    .conf-item .price { font-weight: 700; color: var(--navy-dark); white-space: nowrap; }

    .summary-row { display: flex; justify-content: space-between; font-size: .9rem; color: #4b5563; margin-bottom: .55rem; }
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

    .summary-total .value { font-size: 1.4rem; }

    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .detail-grid .label { font-size: .7rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); }
    .detail-grid .value { color: var(--navy-dark); font-weight: 600; }

    .btn-conf-primary {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        padding: .75rem 1.4rem;
        text-decoration: none;
        display: inline-block;
    }

    .btn-conf-primary:hover { background: var(--navy-dark); color: #fff; }

    @media (max-width: 575.98px) { .detail-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

<section class="conf-hero">
    <div class="mx-auto" style="max-width: 860px;">
        <h1 class="font-serif fw-bold">Confirmation</h1>
        <p>Your booking is confirmed</p>
    </div>
</section>

<div class="conf-body">

    <div class="conf-card mb-4">
        <div class="checkout-steps">
            <span class="checkout-step"><span class="bubble"><i class="bi bi-check-lg"></i></span> Cart</span>
            <span class="sep"><i class="bi bi-chevron-right"></i></span>
            <span class="checkout-step"><span class="bubble"><i class="bi bi-check-lg"></i></span> Checkout</span>
            <span class="sep"><i class="bi bi-chevron-right"></i></span>
            <span class="checkout-step current"><span class="bubble">3</span> Confirmation</span>
        </div>
    </div>

    <div class="conf-card conf-success">
        <div class="conf-success-icon"><i class="bi bi-check-lg"></i></div>
        <h2 class="font-serif fw-bold">Thank you, {{ $order->customer_name }}!</h2>
        <p>We've sent your booking details to {{ $order->customer_email }}.</p>
        <div class="conf-reference">
            <div class="label">Booking Reference</div>
            <div class="value">{{ $order->reference }}</div>
        </div>
    </div>

    <div class="conf-card">
        <h2 class="conf-section-title font-serif fw-bold">Your Booking</h2>

        @foreach ($order->items as $item)
            <div class="conf-item">
                <div>
                    <div class="title">{{ $item->title }}</div>
                    <div class="meta">{{ $item->meta }}</div>
                    @if ($item->booking_date)
                        <div class="meta"><i class="bi bi-calendar3"></i> {{ $item->booking_date->format('D, j M Y') }}</div>
                    @endif
                    @if ($item->quantity > 1)
                        <div class="meta">Quantity: {{ $item->quantity }}</div>
                    @endif
                </div>
                <div class="price">${{ number_format($item->line_total, 2) }}</div>
            </div>
        @endforeach

        <div class="mt-4">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>${{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if ($order->discount > 0)
                <div class="summary-row discount">
                    <span>Promo ({{ $order->promo_code }})</span>
                    <span>−${{ number_format($order->discount, 2) }}</span>
                </div>
            @endif
            <div class="summary-row">
                <span>Service Fee</span>
                <span>${{ number_format($order->service_fee, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Tax</span>
                <span>${{ number_format($order->tax, 2) }}</span>
            </div>
            <div class="summary-total">
                <span>Total Paid</span>
                <span class="value">${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="conf-card">
        <h2 class="conf-section-title font-serif fw-bold">Payment</h2>
        <div class="detail-grid">
            <div>
                <div class="label">Method</div>
                <div class="value">{{ $order->payment_label }}</div>
            </div>
            <div>
                <div class="label">Booked On</div>
                <div class="value">{{ $order->created_at->format('j M Y, H:i') }}</div>
            </div>
            @if ($order->billing_address)
                <div style="grid-column: 1 / -1;">
                    <div class="label">Billing Address</div>
                    <div class="value">
                        {{ $order->billing_address }}, {{ $order->billing_city }},
                        {{ $order->billing_state }} {{ $order->billing_zip }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('flights.index') }}" class="btn-conf-primary">Continue Shopping <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
</div>

@endsection
