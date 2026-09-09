@extends('layouts.homeApp')

@section('title', 'Checkout')

@push('styles')
<style>
    .checkout-hero { background: var(--navy); color: #fff; padding: 2.25rem 1.5rem 5.5rem; }
    .checkout-hero h1 { font-size: 2rem; margin-bottom: .2rem; }
    .checkout-hero p { opacity: .8; margin: 0; font-size: .9rem; }

    .checkout-body {
        max-width: 1240px;
        margin: -4.25rem auto 0;
        padding: 0 1.5rem 4rem;
        position: relative;
        z-index: 2;
    }

    .checkout-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .8rem;
        padding: 1.25rem;
    }

    .checkout-card + .checkout-card { margin-top: 1.25rem; }
    .checkout-card h2 { font-size: 1.25rem; color: var(--navy-dark); margin-bottom: 1rem; }

    /* ---------- Step indicator ---------- */
    .checkout-steps { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }

    .checkout-step {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        color: var(--text-muted);
        font-size: .95rem;
        text-decoration: none;
    }

    /* Only steps already completed are links back. */
    a.checkout-step:hover { color: var(--navy-dark); }
    a.checkout-step:hover .bubble { background: #c7ecd6; }

    .checkout-step .bubble {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #ecebe6;
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .checkout-step.done .bubble { background: #dcf3e6; color: #16a34a; }
    .checkout-step.current { color: var(--navy-dark); font-weight: 700; }
    .checkout-step.current .bubble { background: var(--navy); color: #fff; }
    .checkout-steps .sep { color: #c9c5ba; }

    /* ---------- Payment method tiles ---------- */
    .method-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }

    .method-tile {
        display: flex;
        align-items: center;
        gap: .75rem;
        border: 1px solid var(--border-soft);
        border-radius: .7rem;
        padding: .9rem 1rem;
        cursor: pointer;
        background: #fff;
        width: 100%;
        text-align: left;
    }

    .method-tile:hover { background: var(--cream); }

    .method-tile.selected {
        border-color: var(--navy);
        border-width: 2px;
        background: #f2f6fd;
        padding: calc(.9rem - 1px) calc(1rem - 1px);
    }

    .method-tile i { font-size: 1.25rem; color: var(--navy); }
    .method-tile .name { font-weight: 700; color: var(--navy-dark); font-size: .95rem; }
    .method-tile .sub { font-size: .78rem; color: var(--text-muted); }

    .account-note {
        background: #eef2f9;
        border-radius: .6rem;
        padding: .65rem .9rem;
        margin-bottom: 1rem;
        font-size: .84rem;
        color: #4b5563;
    }

    .account-note strong { color: var(--navy-dark); }

    /* ---------- Form fields ---------- */
    .field-label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: .35rem;
        display: block;
    }

    .checkout-body .form-control {
        background: var(--cream);
        border-color: var(--border-soft);
        border-radius: .6rem;
        padding: .65rem .9rem;
    }

    .checkout-body .form-control:focus {
        background: #fff;
        border-color: var(--navy);
        box-shadow: 0 0 0 .15rem rgba(30,42,69,.12);
    }

    .checkout-body .form-control.is-invalid { border-color: #b91c1c; background: #fff; }
    .field-error { color: #b91c1c; font-size: .78rem; margin-top: .3rem; }

    /* ---------- E-wallets ---------- */
    .wallet-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; }

    .wallet-tile {
        border: 1px solid var(--border-soft);
        border-radius: .7rem;
        padding: 1rem .75rem;
        background: #fff;
        text-align: center;
        cursor: pointer;
        width: 100%;
    }

    .wallet-tile:hover { background: var(--cream); }

    .wallet-tile.selected {
        border-color: var(--navy);
        border-width: 2px;
        background: #f2f6fd;
        padding: calc(1rem - 1px) calc(.75rem - 1px);
    }

    .wallet-tile i { font-size: 1.5rem; color: var(--navy); display: block; margin-bottom: .4rem; }
    .wallet-tile .name { font-weight: 700; color: var(--navy-dark); font-size: .9rem; }
    .wallet-tile .sub { font-size: .74rem; color: var(--text-muted); }

    /* Space reserved for each wallet's QR image. */
    .wallet-qr-panel {
        margin-top: 1.25rem;
        border-top: 1px solid var(--border-soft);
        padding-top: 1.25rem;
        text-align: center;
    }

    .wallet-qr-title { font-weight: 700; color: var(--navy-dark); margin-bottom: .2rem; }
    .wallet-qr-hint { font-size: .82rem; color: var(--text-muted); margin-bottom: 1rem; }

    .wallet-qr-frame {
        width: 220px;
        height: 220px;
        margin: 0 auto;
        border: 2px dashed var(--border-soft);
        border-radius: .8rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: #9ca3af;
        background: var(--cream);
        overflow: hidden;
    }

    .wallet-qr-frame img { width: 100%; height: 100%; object-fit: contain; }
    .wallet-qr-frame i { font-size: 2.4rem; }
    .wallet-qr-frame .placeholder-text { font-size: .78rem; padding: 0 1rem; }

    /* Auto-detect status under the QR code. */
    .wallet-status {
        margin-top: 1.1rem;
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        border-radius: 999px;
        padding: .55rem 1.1rem;
        font-size: .86rem;
        font-weight: 600;
    }

    .wallet-status.waiting { background: #eef2f9; color: var(--navy); }
    .wallet-status.blocked { background: #fdf1d0; color: #92610f; }
    .wallet-status.done { background: #dcf3e6; color: #16a34a; }

    /* Local-only testing control — deliberately looks unlike real UI. */
    .wallet-mock {
        margin-top: 1.1rem;
        padding-top: 1rem;
        border-top: 1px dashed var(--border-soft);
    }

    .wallet-mock-label {
        font-size: .74rem;
        color: var(--text-muted);
        margin-bottom: .5rem;
    }

    .btn-mock-paid {
        background: #fff;
        border: 1px dashed #16a34a;
        border-radius: .6rem;
        color: #16a34a;
        font-weight: 600;
        font-size: .86rem;
        padding: .55rem 1.2rem;
    }

    .btn-mock-paid:hover { background: #dcf3e6; color: #15803d; }
    .btn-mock-paid:disabled { opacity: .55; cursor: not-allowed; }

    .wallet-spinner {
        width: 15px;
        height: 15px;
        border: 2px solid rgba(30,42,69,.25);
        border-top-color: var(--navy);
        border-radius: 50%;
        animation: wallet-spin .8s linear infinite;
    }

    @keyframes wallet-spin { to { transform: rotate(360deg); } }

    /* ---------- Summary ---------- */
    .summary-card { position: sticky; top: 1.5rem; }
    .summary-title { font-weight: 700; font-size: 1.2rem; color: var(--navy-dark); margin-bottom: 1rem; }

    .summary-row {
        display: flex;
        justify-content: space-between;
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

    .summary-total .value { font-size: 1.45rem; }

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

    .btn-pay {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 700;
        padding: .85rem 1rem;
        width: 100%;
        margin-top: 1.1rem;
    }

    .btn-pay:hover { background: var(--navy-dark); color: #fff; }

    .wallet-pay-note {
        background: #eef2f9;
        border-radius: .6rem;
        padding: .8rem .9rem;
        margin-top: 1.1rem;
        font-size: .82rem;
        color: #4b5563;
        text-align: center;
    }

    .terms-note { font-size: .78rem; color: var(--text-muted); text-align: center; margin-top: .7rem; }
    .terms-note a { color: var(--navy-dark); }

    @media (max-width: 575.98px) {
        .method-grid, .wallet-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@php
    // Repopulate from old input after a validation error, so a typo in the
    // CVV doesn't wipe the whole form.
    $method = old('payment_method', 'card');
    $selectedWallet = old('wallet');
@endphp

@section('content')

<section class="checkout-hero">
    <div class="mx-auto" style="max-width: 1240px;">
        <h1 class="font-serif fw-bold">Checkout</h1>
    </div>
</section>

<form method="POST" action="{{ route('checkout.pay') }}" id="checkoutForm">
@csrf
<input type="hidden" name="payment_method" id="paymentMethodInput" value="{{ $method }}">
<input type="hidden" name="wallet" id="walletInput" value="{{ $selectedWallet }}">

<div class="checkout-body">

    {{-- ---------- Steps ---------- --}}
    <div class="checkout-card mb-4">
        <div class="checkout-steps">
            {{-- Completed steps go back; the current and future ones don't. --}}
            <a href="{{ route('cart.index') }}" class="checkout-step done">
                <span class="bubble"><i class="bi bi-check-lg"></i></span> Cart
            </a>
            <span class="sep"><i class="bi bi-chevron-right"></i></span>
            <span class="checkout-step current"><span class="bubble">2</span> Checkout</span>
            <span class="sep"><i class="bi bi-chevron-right"></i></span>
            <span class="checkout-step"><span class="bubble">3</span> Confirmation</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">

            {{-- ---------- Payment method ---------- --}}
            <div class="checkout-card">
                <h2 class="font-serif fw-bold">Payment Method</h2>
                <div class="method-grid">
                    <button type="button" class="method-tile js-method {{ $method === 'card' ? 'selected' : '' }}" data-method="card">
                        <i class="bi bi-credit-card"></i>
                        <span>
                            <span class="name d-block">Credit / Debit</span>
                            <span class="sub">Visa, Mastercard, Amex</span>
                        </span>
                    </button>
                    <button type="button" class="method-tile js-method {{ $method === 'ewallet' ? 'selected' : '' }}" data-method="ewallet">
                        <i class="bi bi-wallet2"></i>
                        <span>
                            <span class="name d-block">E-Wallet</span>
                            <span class="sub">TnG eWallet, Boost, DuitNow</span>
                        </span>
                    </button>
                </div>
                @error('payment_method')<div class="field-error">{{ $message }}</div>@enderror
                @error('wallet')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            {{-- ---------- Contact (needed either way) ---------- --}}
            <div class="checkout-card">
                <h2 class="font-serif fw-bold">Contact Details</h2>
                {{-- Prefilled from the account, but editable: someone may want
                     the confirmation sent somewhere other than their login
                     email, or be booking on another person's behalf. --}}
                <div class="account-note">
                    <i class="bi bi-person-check me-1"></i>
                    Booking as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="field-label" for="customer_name">Full Name</label>
                        <input type="text" id="customer_name" name="customer_name"
                               class="form-control @error('customer_name') is-invalid @enderror"
                               value="{{ old('customer_name', auth()->user()->name) }}" placeholder="Alex Johnson">
                        @error('customer_name')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="field-label" for="customer_email">Email</label>
                        <input type="email" id="customer_email" name="customer_email"
                               class="form-control @error('customer_email') is-invalid @enderror"
                               value="{{ old('customer_email', auth()->user()->email) }}" placeholder="alex@example.com">
                        @error('customer_email')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- ---------- Card details ---------- --}}
            <div class="checkout-card" id="cardSection">
                <h2 class="font-serif fw-bold">Card Details</h2>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="field-label" for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" inputmode="numeric" autocomplete="off"
                               class="form-control @error('card_number') is-invalid @enderror"
                               value="{{ old('card_number') }}" placeholder="1234 5678 9012 3456" maxlength="23">
                        @error('card_number')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="field-label" for="card_holder">Cardholder Name</label>
                        <input type="text" id="card_holder" name="card_holder" autocomplete="off"
                               class="form-control @error('card_holder') is-invalid @enderror"
                               value="{{ old('card_holder') }}" placeholder="Alex Johnson">
                        @error('card_holder')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="field-label" for="card_expiry">Expiry Date</label>
                        <input type="text" id="card_expiry" name="card_expiry" inputmode="numeric" autocomplete="off"
                               class="form-control @error('card_expiry') is-invalid @enderror"
                               value="{{ old('card_expiry') }}" placeholder="MM / YY" maxlength="7">
                        @error('card_expiry')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="field-label" for="card_cvv">CVV</label>
                        <input type="text" id="card_cvv" name="card_cvv" inputmode="numeric" autocomplete="off"
                               class="form-control @error('card_cvv') is-invalid @enderror"
                               {{-- Deliberately not repopulated: the CVV is the one field
                                    that should never be echoed back into the page. --}}
                               placeholder="•••" maxlength="4">
                        @error('card_cvv')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- ---------- Billing address (card only) ---------- --}}
            <div class="checkout-card" id="billingSection">
                <h2 class="font-serif fw-bold">Billing Address</h2>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="field-label" for="billing_address">Address</label>
                        <input type="text" id="billing_address" name="billing_address"
                               class="form-control @error('billing_address') is-invalid @enderror"
                               value="{{ old('billing_address') }}" placeholder="123 Main Street">
                        @error('billing_address')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="field-label" for="billing_city">City</label>
                        <input type="text" id="billing_city" name="billing_city"
                               class="form-control @error('billing_city') is-invalid @enderror"
                               value="{{ old('billing_city') }}" placeholder="New York">
                        @error('billing_city')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="field-label" for="billing_state">State</label>
                        <input type="text" id="billing_state" name="billing_state"
                               class="form-control @error('billing_state') is-invalid @enderror"
                               value="{{ old('billing_state') }}" placeholder="NY">
                        @error('billing_state')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="field-label" for="billing_zip">ZIP</label>
                        <input type="text" id="billing_zip" name="billing_zip"
                               class="form-control @error('billing_zip') is-invalid @enderror"
                               value="{{ old('billing_zip') }}" placeholder="10001">
                        @error('billing_zip')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- ---------- E-wallet ---------- --}}
            <div class="checkout-card" id="walletSection">
                <h2 class="font-serif fw-bold">Choose Your E-Wallet</h2>
                <div class="wallet-grid">
                    @foreach ($wallets as $key => $wallet)
                        <button type="button" class="wallet-tile js-wallet {{ $selectedWallet === $key ? 'selected' : '' }}"
                                data-wallet="{{ $key }}" data-name="{{ $wallet['name'] }}">
                            <i class="bi {{ $wallet['icon'] }}"></i>
                            <span class="name d-block">{{ $wallet['name'] }}</span>
                            <span class="sub">{{ $wallet['tagline'] }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Revealed once a wallet is picked; holds that wallet's QR image. --}}
                <div class="wallet-qr-panel" id="walletQrPanel" hidden>
                    <div class="wallet-qr-title">Scan to pay with <span id="walletQrName"></span></div>
                    <div class="wallet-qr-hint">
                        Open the app and scan the code below. Your booking confirms automatically
                        once the payment comes through — there's nothing else to press.
                    </div>

                    @foreach ($wallets as $key => $wallet)
                        <div class="wallet-qr-frame js-wallet-qr" data-wallet="{{ $key }}" hidden>
                            @if (file_exists(public_path($wallet['qr'])))
                                <img src="{{ asset($wallet['qr']) }}" alt="{{ $wallet['name'] }} QR code">
                            @else
                                {{-- Placeholder until an image is dropped in at public/{{ $wallet['qr'] }} --}}
                                <i class="bi bi-qr-code"></i>
                                <div class="placeholder-text">{{ $wallet['name'] }} QR code<br><code>public/{{ $wallet['qr'] }}</code></div>
                            @endif
                        </div>
                    @endforeach

                    <div>
                        <div class="wallet-status waiting" id="walletStatus">
                            <span class="wallet-spinner" id="walletSpinner"></span>
                            <span id="walletStatusText">Waiting for payment…</span>
                        </div>
                    </div>

                    @if (app()->environment('local'))
                        {{-- Testing shortcut: fires the same completion path
                             the timer does, without the wait. Local only, so
                             it can't reach a deployed site. --}}
                        <div class="wallet-mock">
                            <div class="wallet-mock-label">
                                <i class="bi bi-tools"></i> Testing only — no real wallet is contacted
                            </div>
                            <button type="button" class="btn btn-mock-paid" id="mockWalletPaid">
                                <i class="bi bi-check2-circle me-1"></i> Simulate Payment Received
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ---------- Summary ---------- --}}
        <div class="col-lg-4">
            <div class="checkout-card summary-card">
                <div class="summary-title font-serif">Order Summary</div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>${{ number_format($totals->subtotal, 2) }}</span>
                </div>

                @if ($totals->discount > 0)
                    <div class="summary-row discount">
                        <span>Promo ({{ $promoCode }})</span>
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
                    <span>Total</span>
                    <span class="value">${{ number_format($totals->total, 2) }}</span>
                </div>

                <div class="secure-note">
                    <i class="bi bi-lock"></i>
                    <span>Your payment is secured by industry-standard 256-bit SSL encryption.</span>
                </div>

                {{-- Card only. E-wallet payments confirm themselves once the
                     QR code is paid, so there is no button to press there. --}}
                <div id="payButtonWrap">
                    <button type="submit" class="btn btn-pay">
                        <i class="bi bi-shield-check me-1"></i> Pay ${{ number_format($totals->total, 2) }} Securely
                    </button>
                </div>

                <div id="walletPayNote" class="wallet-pay-note" hidden>
                    <i class="bi bi-qr-code-scan me-1"></i>
                    Scan the QR code to pay ${{ number_format($totals->total, 2) }}.
                    We'll confirm your booking automatically.
                </div>

                <div class="terms-note">
                    By completing this payment you agree to our <a href="#">Terms &amp; Conditions</a>.
                </div>
            </div>
        </div>
    </div>
</div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const methodInput = document.getElementById('paymentMethodInput');
    const walletInput = document.getElementById('walletInput');
    const cardSection = document.getElementById('cardSection');
    const billingSection = document.getElementById('billingSection');
    const walletSection = document.getElementById('walletSection');
    const qrPanel = document.getElementById('walletQrPanel');
    const payButtonWrap = document.getElementById('payButtonWrap');
    const walletPayNote = document.getElementById('walletPayNote');
    const statusEl = document.getElementById('walletStatus');
    const statusText = document.getElementById('walletStatusText');
    const spinner = document.getElementById('walletSpinner');
    const nameInput = document.getElementById('customer_name');
    const emailInput = document.getElementById('customer_email');
    const form = document.getElementById('checkoutForm');

    /**
     * How long the mock "payment received" takes to arrive. Long enough to
     * read the QR panel and talk through it during a demo before the page
     * submits itself; the Simulate button skips the wait when testing.
     */
    const DETECT_DELAY_MS = 15000;
    let detectTimer = null;

    // ----- Payment method -----
    function applyMethod(method) {
        methodInput.value = method;

        document.querySelectorAll('.js-method').forEach(function (tile) {
            tile.classList.toggle('selected', tile.dataset.method === method);
        });

        const isCard = method === 'card';
        cardSection.hidden = !isCard;
        billingSection.hidden = !isCard;
        walletSection.hidden = isCard;

        // The Pay button belongs to the card flow only — an e-wallet
        // payment completes itself when the QR code is paid.
        payButtonWrap.hidden = !isCard;
        walletPayNote.hidden = isCard;

        // Disabled fields aren't submitted, so the unused half of the form
        // never reaches the server and can't trip its own validation.
        cardSection.querySelectorAll('input').forEach(i => i.disabled = !isCard);
        billingSection.querySelectorAll('input').forEach(i => i.disabled = !isCard);

        isCard ? cancelDetection() : refreshDetection();
    }

    document.querySelectorAll('.js-method').forEach(function (tile) {
        tile.addEventListener('click', () => applyMethod(tile.dataset.method));
    });

    // ----- E-wallet choice -----
    function applyWallet(key, name) {
        walletInput.value = key;

        document.querySelectorAll('.js-wallet').forEach(function (tile) {
            tile.classList.toggle('selected', tile.dataset.wallet === key);
        });

        document.querySelectorAll('.js-wallet-qr').forEach(function (frame) {
            frame.hidden = frame.dataset.wallet !== key;
        });

        document.getElementById('walletQrName').textContent = name || '';
        qrPanel.hidden = !key;

        refreshDetection();
    }

    document.querySelectorAll('.js-wallet').forEach(function (tile) {
        tile.addEventListener('click', () => applyWallet(tile.dataset.wallet, tile.dataset.name));
    });

    // ----- Mock payment detection -----
    function setStatus(state, text, showSpinner) {
        statusEl.className = 'wallet-status ' + state;
        statusText.textContent = text;
        spinner.hidden = !showSpinner;
    }

    function cancelDetection() {
        clearTimeout(detectTimer);
        detectTimer = null;
    }

    /**
     * Restarts the watch whenever something it depends on changes. Contact
     * details are checked first: without them the submit would bounce back
     * with validation errors, which is a confusing way for an "automatic"
     * payment to fail.
     */
    function refreshDetection() {
        cancelDetection();

        if (methodInput.value !== 'ewallet' || !walletInput.value) {
            return;
        }

        if (!nameInput.value.trim() || !emailInput.value.trim()) {
            setStatus('blocked', 'Enter your name and email to activate the QR code', false);
            return;
        }

        setStatus('waiting', 'Waiting for payment…', true);

        detectTimer = setTimeout(completePayment, DETECT_DELAY_MS);
    }

    /** The single completion path — the timer and the mock button share it. */
    function completePayment() {
        cancelDetection();
        setStatus('done', 'Payment received — confirming your booking…', false);
        form.submit();
    }

    // ----- Local-only "Simulate Payment Received" button -----
    var mockBtn = document.getElementById('mockWalletPaid');

    if (mockBtn) {
        mockBtn.addEventListener('click', function () {
            // Same guards the timer honours, so the shortcut can't submit a
            // form that would only bounce back with validation errors.
            if (!walletInput.value) {
                setStatus('blocked', 'Pick an e-wallet first', false);
                return;
            }

            if (!nameInput.value.trim() || !emailInput.value.trim()) {
                setStatus('blocked', 'Enter your name and email first', false);
                return;
            }

            mockBtn.disabled = true;
            completePayment();
        });
    }

    // Typing a missing name or email should start the watch without
    // needing the wallet to be re-picked.
    [nameInput, emailInput].forEach(function (input) {
        input.addEventListener('input', refreshDetection);
    });

    // ----- Card input formatting -----
    const numberInput = document.getElementById('card_number');
    const expiryInput = document.getElementById('card_expiry');

    numberInput.addEventListener('input', function () {
        // Regroup into blocks of four as the user types.
        const digits = numberInput.value.replace(/\D/g, '').slice(0, 19);
        numberInput.value = digits.replace(/(.{4})/g, '$1 ').trim();
    });

    expiryInput.addEventListener('input', function () {
        const digits = expiryInput.value.replace(/\D/g, '').slice(0, 4);
        expiryInput.value = digits.length > 2 ? digits.slice(0, 2) + ' / ' + digits.slice(2) : digits;
    });

    document.getElementById('card_cvv').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    // Restore whatever state the page was rendered with (first load, or
    // a bounce back from a validation error).
    applyMethod(methodInput.value || 'card');

    const preselected = document.querySelector('.js-wallet.selected');
    applyWallet(preselected ? preselected.dataset.wallet : '', preselected ? preselected.dataset.name : '');
});
</script>
@endpush
