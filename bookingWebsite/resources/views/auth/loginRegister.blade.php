@extends('layouts.app')
 
@section('title', 'Sign In')
 
@push('styles')
<style>
    :root {
        --navy:        #1e2a45;
        --cream:       #f5f2ec;
        --border-soft: #e4e0d6;
        --text-muted:  #6b7280;
    }
 
    * { font-family: 'Inter', system-ui, sans-serif; }
    .font-serif { font-family: 'Playfair Display', Georgia, serif; }
 
    html, body { height: 100%; }
    body { background: var(--cream); }

    .brand-mark {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--navy);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .brand-name {
        font-weight: 700;
        font-size: 1.15rem;
        color: #16202f;
    }

    /* ---------- Centered auth card (the only thing on the page) ---------- */
    .auth-panel {
        min-height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.75rem;
        padding: 2rem 1.5rem;
    }

    .auth-panel-inner {
        width: 100%;
        max-width: 420px;
        background: #fff;
        border: 1px solid var(--border-soft);
        border-radius: 1rem;
        box-shadow: 0 20px 40px -20px rgba(0,0,0,.15);
        padding: 2.25rem;
    }
 
    .auth-panel-inner h1 {
        font-size: 1.9rem;
        color: #16202f;
        margin-bottom: .35rem;
    }
 
    .auth-subtext { color: var(--text-muted); margin-bottom: 1.5rem; }
    .auth-subtext a { color: var(--navy); font-weight: 600; text-decoration: none; }
    .auth-subtext a:hover { text-decoration: underline; }
 
    /* Pill toggle */
    .pill-toggle {
        background: #e9e6dd;
        border-radius: 999px;
        padding: .3rem;
        display: flex;
        margin-bottom: 1.5rem;
    }
 
    .pill-toggle .nav-link {
        flex: 1;
        text-align: center;
        border-radius: 999px;
        font-weight: 600;
        font-size: .9rem;
        color: #6b7280;
        padding: .55rem 0;
        border: none;
        transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
    }
 
    .pill-toggle .nav-link.active {
        background: #fff;
        color: #16202f;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
 
    /* Form fields */
    .field-label {
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #4b5563;
        margin-bottom: .4rem;
        display: block;
    }
 
    .auth-panel .form-control {
        border-radius: .65rem;
        border-color: var(--border-soft);
        padding: .7rem 1rem;
        background: #fff;
    }
 
    .auth-panel .form-control:focus {
        border-color: var(--navy);
        box-shadow: 0 0 0 .15rem rgba(30,42,69,.12);
    }
 
    .password-wrap { position: relative; }
    .password-toggle-btn {
        position: absolute;
        right: .85rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #9ca3af;
        padding: 0;
        line-height: 1;
    }
 
    .options-row { display: flex; align-items: center; justify-content: space-between; margin: 1rem 0 1.25rem; }
    .options-row a { color: var(--navy); font-size: .9rem; text-decoration: none; }
    .options-row a:hover { text-decoration: underline; }
 
    .btn-navy {
        background: var(--navy);
        border: none;
        border-radius: 999px;
        color: #fff;
        font-weight: 600;
        padding: .75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
    }
 
    .btn-navy:hover { background: #16202f; color: #fff; }
 
    .divider-text {
        display: flex;
        align-items: center;
        gap: .75rem;
        color: var(--text-muted);
        font-size: .85rem;
        margin: 1.5rem 0;
    }
 
    .divider-text::before,
    .divider-text::after {
        content: "";
        flex: 1;
        border-top: 1px solid var(--border-soft);
    }
 
    .btn-social-pill {
        border: 1px solid var(--border-soft);
        border-radius: 999px;
        background: #fff;
        padding: .6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        font-weight: 500;
        color: #16202f;
    }
 
    .btn-social-pill:hover { background: #fafafa; }
    .btn-social-pill i.bi-google { color: #4285F4; }
    .btn-social-pill i.bi-facebook { color: #1877F2; }
</style>
@endpush
 
@section('content')

{{-- ---------------- Centered auth card ---------------- --}}
<div class="auth-panel">
    <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
        <span class="brand-mark"><i class="bi bi-globe2"></i></span>
        <span class="brand-name font-serif">Voyagr.</span>
    </a>

    <div class="auth-panel-inner">
 
            @if (session('status'))
                <div class="alert alert-success py-2">{{ session('status') }}</div>
            @endif
 
            <h1 class="font-serif fw-semibold" id="auth-heading">Welcome back</h1>
            <p class="auth-subtext" id="auth-subtext">
                Don't have an account? <a href="#" id="switch-to-register">Sign up</a>
            </p>
 
            {{-- Pill toggle --}}
            <ul class="nav pill-toggle" id="authTab" role="tablist">
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link active w-100" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane"
                            type="button" role="tab" aria-controls="login-pane" aria-selected="true">
                        Sign In
                    </button>
                </li>
                <li class="nav-item flex-fill" role="presentation">
                    <button class="nav-link w-100" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane"
                            type="button" role="tab" aria-controls="register-pane" aria-selected="false">
                        Register
                    </button>
                </li>
            </ul>
 
            <div class="tab-content" id="authTabContent">
 
                {{-- ============== LOGIN PANE ============== --}}
                <div class="tab-pane fade show active" id="login-pane" role="tabpanel" aria-labelledby="login-tab">
                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf
 
                        <div class="mb-3">
                            <label for="login-email" class="field-label">Email Address</label>
                            <input type="email"
                                   class="form-control @error('email', 'login') is-invalid @enderror"
                                   id="login-email"
                                   name="email"
                                   placeholder="alex@example.com"
                                   value="{{ $errors->login->any() ? old('email') : '' }}"
                                   required autofocus>
                            @error('email', 'login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
 
                        <div class="mb-1">
                            <label for="login-password" class="field-label">Password</label>
                            <div class="password-wrap">
                                <input type="password"
                                       class="form-control @error('password', 'login') is-invalid @enderror"
                                       id="login-password"
                                       name="password"
                                       placeholder="••••••••"
                                       required>
                                <button type="button" class="password-toggle-btn" data-toggle-target="login-password" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password', 'login')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
 
                        <div class="options-row">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember-me">
                                <label class="form-check-label" for="remember-me">Remember me</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">Forgot password?</a>
                            @else
                                <a href="#">Forgot password?</a>
                            @endif
                        </div>
 
                        <button type="submit" class="btn btn-navy w-100">
                            Sign In <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>
 
                {{-- ============== REGISTER PANE ============== --}}
                <div class="tab-pane fade" id="register-pane" role="tabpanel" aria-labelledby="register-tab">
                    <form method="POST" action="{{ route('register') }}" novalidate>
                        @csrf
 
                        <div class="mb-3">
                            <label for="register-name" class="field-label">Full Name</label>
                            <input type="text"
                                   class="form-control @error('name', 'register') is-invalid @enderror"
                                   id="register-name"
                                   name="name"
                                   placeholder="Alex Rivera"
                                   value="{{ old('name') }}"
                                   required>
                            @error('name', 'register')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
 
                        <div class="mb-3">
                            <label for="register-email" class="field-label">Email Address</label>
                            <input type="email"
                                   class="form-control @error('email', 'register') is-invalid @enderror"
                                   id="register-email"
                                   name="email"
                                   placeholder="alex@example.com"
                                   value="{{ $errors->register->any() ? old('email') : '' }}"
                                   required>
                            @error('email', 'register')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="register-phone" class="field-label">Phone Number</label>
                            <input type="phone"
                                   class="form-control @error('phone', 'register') is-invalid @enderror"
                                   id="register-phone"
                                   name="phone"
                                   placeholder="+6012xxxxxxx"
                                   value="{{ old('phone') }}"
                                   required>
                            @error('phone', 'register')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
 
                        <div class="mb-3">
                            <label for="register-password" class="field-label">Password</label>
                            <div class="password-wrap">
                                <input type="password"
                                       class="form-control @error('password', 'register') is-invalid @enderror"
                                       id="register-password"
                                       name="password"
                                       placeholder="••••••••"
                                       required>
                                <button type="button" class="password-toggle-btn" data-toggle-target="register-password" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password', 'register')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
 
                        <div class="mb-3">
                            <label for="register-password-confirm" class="field-label">Confirm Password</label>
                            <input type="password"
                                   class="form-control"
                                   id="register-password-confirm"
                                   name="password_confirmation"
                                   placeholder="••••••••"
                                   required>
                        </div>
 
                        <button type="submit" class="btn btn-navy w-100 mb-3">
                            Create Account <i class="bi bi-arrow-right"></i>
                        </button>
                        </div>
                    </form>
                </div>
 
            </div>
        </div>
    </div>
@endsection
 
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const loginTab    = document.getElementById('login-tab');
    const registerTab = document.getElementById('register-tab');
    const heading      = document.getElementById('auth-heading');
    const subtext      = document.getElementById('auth-subtext');
 
    function setCopyForLogin() {
        heading.textContent = 'Welcome back';
        subtext.innerHTML = "Don't have an account? <a href=\"#\" id=\"switch-to-register\">Sign up</a>";
        document.getElementById('switch-to-register').addEventListener('click', function (e) {
            e.preventDefault();
            new bootstrap.Tab(registerTab).show();
        });
    }
 
    function setCopyForRegister() {
        heading.textContent = 'Create your account';
        subtext.innerHTML = "Already have an account? <a href=\"#\" id=\"switch-to-login\">Sign in</a>";
        document.getElementById('switch-to-login').addEventListener('click', function (e) {
            e.preventDefault();
            new bootstrap.Tab(loginTab).show();
        });
    }
 
    document.getElementById('switch-to-register').addEventListener('click', function (e) {
        e.preventDefault();
        new bootstrap.Tab(registerTab).show();
    });
 
    loginTab.addEventListener('shown.bs.tab', setCopyForLogin);
    registerTab.addEventListener('shown.bs.tab', setCopyForRegister);
 
    // Password show/hide toggle
    document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(btn.dataset.toggleTarget);
            const icon  = btn.querySelector('i');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });
 
    @if ($errors->register->any())
        new bootstrap.Tab(registerTab).show();
    @endif
});
</script>
@endpush