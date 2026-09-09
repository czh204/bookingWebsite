@extends('layouts.homeApp')

@section('title', 'My Account')

@push('styles')
<style>
    /* ---------- Account hero ---------- */
    .account-hero {
        background: linear-gradient(135deg, #2a3a5c 0%, var(--navy) 55%, var(--navy-dark) 100%);
        color: #fff;
        padding: 2.25rem 1.5rem 5.5rem;
    }

    .account-hero h1 { font-size: 2rem; margin-bottom: .25rem; }
    .account-hero p { opacity: .85; margin: 0; font-size: .95rem; }

    .account-body {
        max-width: 1240px;
        margin: -4.25rem auto 0;
        padding: 0 1.5rem 4rem;
        position: relative;
        z-index: 2;
    }

    .account-card {
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: .8rem;
        padding: 1.5rem;
    }

    .account-card + .account-card { margin-top: 1.25rem; }

    .account-card-title {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--navy-dark);
        margin-bottom: .15rem;
    }

    .account-card-sub {
        font-size: .85rem;
        color: var(--text-muted);
        margin-bottom: 1.25rem;
    }

    .account-label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: .35rem;
    }

    .account-input {
        border: 1px solid var(--border-soft);
        border-radius: .5rem;
        padding: .55rem .8rem;
        font-size: .92rem;
        color: var(--navy-dark);
    }

    .account-input:focus {
        border-color: var(--navy);
        box-shadow: 0 0 0 .15rem rgba(30,42,69,.12);
    }

    .field-error { color: #b91c1c; font-size: .8rem; margin-top: .3rem; }
    .field-hint { color: var(--text-muted); font-size: .78rem; margin-top: .3rem; }

    .btn-account-save {
        background: var(--navy);
        border: none;
        border-radius: .6rem;
        color: #fff;
        font-weight: 600;
        font-size: .92rem;
        padding: .55rem 1.4rem;
    }

    .btn-account-save:hover { background: var(--navy-dark); color: #fff; }

    .account-saved {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        border-radius: .6rem;
        padding: .6rem .85rem;
        font-size: .85rem;
        margin-bottom: 1rem;
    }

    /* ---------- Summary sidebar ---------- */
    .account-summary-avatar {
        width: 62px;
        height: 62px;
        border-radius: 50%;
        background: var(--navy);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .account-summary-name { font-weight: 700; color: var(--navy-dark); }
    .account-summary-email { font-size: .85rem; color: var(--text-muted); word-break: break-all; }

    .account-stat {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 1rem;
        font-size: .87rem;
        padding: .5rem 0;
    }

    .account-stat + .account-stat { border-top: 1px solid var(--border-soft); }
    .account-stat-label { color: var(--text-muted); }
    .account-stat-value { font-weight: 600; color: var(--navy-dark); text-align: right; }
</style>
@endpush

@section('content')
<section class="account-hero">
    <div style="max-width: 1240px; margin: 0 auto;">
        <h1 class="font-serif">My Account</h1>
        <p>Update the details we use for your bookings and confirmations.</p>
    </div>
</section>

<div class="account-body">
    <div class="row g-4">
        {{-- ---------- Settings forms ---------- --}}
        <div class="col-lg-8">
            {{-- Profile details --}}
            <div class="account-card">
                <div class="account-card-title">Profile details</div>
                <div class="account-card-sub">
                    Your name and email appear on booking confirmations; we use the phone number
                    if a provider needs to reach you about a trip.
                </div>

                @if (session('status_profile'))
                    <div class="account-saved">
                        <i class="bi bi-check-circle me-1"></i>{{ session('status_profile') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('account.profile.update') }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="account-label">Full name</div>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   maxlength="255"
                                   class="form-control account-input @error('name', 'profile') is-invalid @enderror"
                                   required>
                            @error('name', 'profile')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="account-label">Email address</div>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   maxlength="191"
                                   class="form-control account-input @error('email', 'profile') is-invalid @enderror"
                                   required>
                            @error('email', 'profile')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="account-label">Phone number</div>
                            <input type="tel"
                                   name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="+60123456789"
                                   class="form-control account-input @error('phone', 'profile') is-invalid @enderror"
                                   required>
                            @error('phone', 'profile')
                                <div class="field-error">{{ $message }}</div>
                            @else
                                <div class="field-hint">Malaysian mobile, international format.</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-account-save">Save changes</button>
                    </div>
                </form>
            </div>

            {{-- Password --}}
            <div class="account-card">
                <div class="account-card-title">Password</div>
                <div class="account-card-sub">
                    Changing it requires your current password, so nobody who finds this page
                    open can lock you out of your own account.
                </div>

                @if (session('status_password'))
                    <div class="account-saved">
                        <i class="bi bi-check-circle me-1"></i>{{ session('status_password') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('account.password.update') }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="account-label">Current password</div>
                            <input type="password"
                                   name="current_password"
                                   autocomplete="current-password"
                                   class="form-control account-input @error('current_password', 'password') is-invalid @enderror"
                                   required>
                            @error('current_password', 'password')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6"></div>

                        <div class="col-md-6">
                            <div class="account-label">New password</div>
                            <input type="password"
                                   name="password"
                                   autocomplete="new-password"
                                   class="form-control account-input @error('password', 'password') is-invalid @enderror"
                                   required>
                            @error('password', 'password')
                                <div class="field-error">{{ $message }}</div>
                            @else
                                <div class="field-hint">At least 8 characters.</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="account-label">Confirm new password</div>
                            <input type="password"
                                   name="password_confirmation"
                                   autocomplete="new-password"
                                   class="form-control account-input"
                                   required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-account-save">Update password</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ---------- Read-only summary ---------- --}}
        <div class="col-lg-4">
            <div class="account-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="account-summary-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <div class="overflow-hidden">
                        <div class="account-summary-name">{{ $user->name }}</div>
                        <div class="account-summary-email">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="account-stat">
                    <span class="account-stat-label">Member since</span>
                    <span class="account-stat-value">{{ $user->created_at?->format('M Y') ?? '—' }}</span>
                </div>
                <div class="account-stat">
                    <span class="account-stat-label">Bookings</span>
                    <span class="account-stat-value">{{ $orderCount }}</span>
                </div>
                <div class="account-stat">
                    <span class="account-stat-label">Last booking</span>
                    <span class="account-stat-value">
                        {{ $lastOrder ? $lastOrder->created_at->format('d M Y') : 'None yet' }}
                    </span>
                </div>

                <a href="{{ url('/ai-planner') }}" class="btn btn-account-save w-100 mt-3">
                    <i class="bi bi-calendar3 me-1"></i>View my bookings
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
