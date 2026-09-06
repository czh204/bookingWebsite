<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
 
    <title>{{ config('app.name', 'Voyagr') }} - @yield('title', 'Plan your journey')</title>
 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
 
    <style>
        :root {
            --navy:        #1e2a45;
            --navy-dark:   #16202f;
            --gold:        #d6a83c;
            --cream:       #f5f2ec;
            --border-soft: #e4e0d6;
            --text-muted:  #6b7280;
        }
 
        * { font-family: 'Inter', system-ui, sans-serif; }
        .font-serif { font-family: 'Playfair Display', Georgia, serif; }
        body { background: var(--cream); }
 
        /* ---------- Shared navbar (used on every page) ---------- */
        .site-nav {
            background: #f8f7f4;
            border-bottom: 1px solid var(--border-soft);
            padding: .85rem 2rem;
            position: relative;
            z-index: 20;
        }
 
        .brand-mark {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--navy);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
 
        .brand-name { font-weight: 700; font-size: 1.15rem; color: var(--navy-dark); }
 
        .nav-pill-link {
            color: #4b5563;
            font-size: .95rem;
            padding: .4rem .9rem;
            border-radius: 999px;
            text-decoration: none;
        }
 
        .nav-pill-link:hover { color: var(--navy-dark); }
 
        .nav-pill-link.active {
            background: #e7ecf5;
            color: var(--navy);
            font-weight: 600;
        }
 
        .btn-signin-outline {
            border: 1px solid var(--navy-dark);
            border-radius: 999px;
            color: var(--navy-dark);
            font-weight: 500;
            padding: .4rem 1.1rem;
            text-decoration: none;
        }
 
        .btn-signin-outline:hover { background: var(--navy-dark); color: #fff; }
 
        .user-menu-btn {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: #fff;
            border: 1px solid var(--border-soft);
            border-radius: 999px;
            padding: .3rem .9rem .3rem .3rem;
            font-weight: 500;
            color: var(--navy-dark);
        }
 
        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--navy);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
        }
 
        /* ---------- Booking date picker (shared by the three modals) ---------- */
        .booking-date-field {
            background: var(--cream);
            border: 1px solid var(--border-soft);
            border-radius: .7rem;
            padding: .75rem .9rem;
            margin-bottom: 1rem;
        }

        .booking-date-label {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: .45rem;
        }

        .booking-date-input {
            background: #fff;
            border-color: var(--border-soft);
            border-radius: .5rem;
            padding: .5rem .75rem;
            font-size: .9rem;
            color: var(--navy-dark);
            max-width: 220px;
        }

        .booking-date-input:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 .15rem rgba(30,42,69,.12);
        }

        .booking-date-input.is-invalid { border-color: #b91c1c; }
        .booking-date-error { color: #b91c1c; font-size: .78rem; margin-top: .35rem; }

        /* ---------- Cart badge ---------- */
        .cart-count-badge {
            position: absolute;
            top: -6px;
            right: -8px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 999px;
            background: var(--gold);
            color: var(--navy-dark);
            font-size: .68rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .cart-link-active {
            background: #e7ecf5;
            border-radius: .5rem;
            padding: .25rem .5rem;
        }

        /* ---------- Support chat widget ---------- */
        .chat-bubble-btn {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--navy);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            box-shadow: 0 10px 25px -8px rgba(0,0,0,.4);
            cursor: pointer;
            z-index: 1050;
        }

        .chat-bubble-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #22c55e;
            border: 2px solid #fff;
        }

        .chat-panel {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 340px;
            max-width: calc(100vw - 2rem);
            max-height: min(560px, calc(100vh - 3rem));
            background: #fff;
            border-radius: .9rem;
            box-shadow: 0 20px 45px -12px rgba(0,0,0,.35);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 1050;
        }

        .chat-panel-header {
            background: var(--navy);
            color: #fff;
            padding: .9rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .chat-header-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255,255,255,.15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .chat-header-title { font-weight: 700; font-size: .95rem; }
        .chat-header-subtitle { font-size: .75rem; opacity: .75; }

        .chat-icon-btn {
            background: none;
            border: none;
            color: rgba(255,255,255,.85);
            padding: .2rem;
            line-height: 1;
        }

        .chat-icon-btn:hover { color: #fff; }

        .chat-quick-questions {
            padding: 1rem;
            background: var(--cream);
            border-bottom: 1px solid var(--border-soft);
            flex-shrink: 0;
        }

        .chat-quick-questions-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: .6rem;
        }

        .chat-quick-btn {
            text-align: left;
            background: #fff;
            border: 1px solid var(--border-soft);
            border-radius: 999px;
            padding: .55rem 1rem;
            font-size: .82rem;
            color: var(--navy-dark);
        }

        .chat-quick-btn:hover { background: #f1f0ec; }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: .75rem;
            background: #fff;
        }

        .chat-message { display: flex; align-items: flex-start; gap: .5rem; }
        .chat-message-user { justify-content: flex-end; }

        .chat-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--navy);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            flex-shrink: 0;
        }

        .chat-bubble {
            background: var(--cream);
            border-radius: .8rem;
            padding: .6rem .85rem;
            font-size: .85rem;
            color: var(--navy-dark);
            max-width: 78%;
            line-height: 1.4;
        }

        .chat-bubble-user {
            background: var(--navy);
            color: #fff;
        }

        .chat-bubble a { color: var(--navy); font-weight: 600; text-decoration: underline; }
        .chat-bubble-user a { color: #fff; }

        .chat-input-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem;
            border-top: 1px solid var(--border-soft);
            flex-shrink: 0;
        }

        .chat-input {
            flex: 1;
            border: none;
            background: var(--cream);
            border-radius: 999px;
            padding: .6rem 1rem;
            font-size: .85rem;
        }

        .chat-input:focus { outline: none; box-shadow: 0 0 0 .15rem rgba(30,42,69,.15); }

        .chat-send-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--navy);
            color: #fff;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .chat-send-btn:hover { background: var(--navy-dark); }
        .chat-send-btn:disabled, .chat-quick-btn:disabled { opacity: .5; cursor: not-allowed; }
        .chat-input:disabled { opacity: .6; }

        /* Typing indicator shown while the assistant is thinking */
        .chat-typing { display: inline-flex; align-items: center; gap: .25rem; padding: .7rem .85rem; }

        .chat-typing span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--text-muted);
            animation: chat-typing-bounce 1.2s infinite ease-in-out;
        }

        .chat-typing span:nth-child(2) { animation-delay: .15s; }
        .chat-typing span:nth-child(3) { animation-delay: .3s; }

        @keyframes chat-typing-bounce {
            0%, 60%, 100% { transform: translateY(0); opacity: .4; }
            30% { transform: translateY(-4px); opacity: 1; }
        }
    </style>

    @stack('styles')
</head>
<body>
 
    @include('partials.navBar')
 
    @yield('content')

    @include('partials.chatWidget')
 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.cartClient')
    @stack('scripts')
</body>
</html>
 