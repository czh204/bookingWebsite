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
 
        @stack('styles')
    </style>
</head>
<body>
 
    @include('partials.navBar')
 
    @yield('content')
 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
 