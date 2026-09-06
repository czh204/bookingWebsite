<nav class="site-nav d-flex align-items-center justify-content-between">
    <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
        <span class="brand-mark"><i class="bi bi-globe2"></i></span>
        <span class="brand-name font-serif">Voyagr.</span>
    </a>
 
    <div class="d-none d-md-flex gap-1">
        <a href="{{ url('/') }}" class="nav-pill-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
        <a href="{{ url('/flights') }}" class="nav-pill-link {{ request()->is('flights*') ? 'active' : '' }}">Flights</a>
        <a href="{{ url('/hotels') }}" class="nav-pill-link {{ request()->is('hotels*') ? 'active' : '' }}">Hotels</a>
        <a href="{{ url('/attractions') }}" class="nav-pill-link {{ request()->is('attractions*') ? 'active' : '' }}">Attractions</a>
        {{-- Path stays /ai-planner so existing links keep working; the page
             itself is now My Bookings, with the planner inside it. --}}
        <a href="{{ url('/ai-planner') }}" class="nav-pill-link {{ request()->is('ai-planner*') ? 'active' : '' }}">My Bookings</a>
    </div>
 
    <div class="d-flex align-items-center gap-3">
        @php
            // Read straight from the cart service so every page's navbar
            // shows the live count without each controller passing it in.
            $cartCount = app(\App\Services\Cart::class)->count();
        @endphp
        <a href="{{ route('cart.index') }}" class="text-dark fs-5 position-relative {{ request()->is('cart*') || request()->is('checkout*') ? 'cart-link-active' : '' }}">
            <i class="bi bi-cart3"></i>
            @if ($cartCount > 0)
                <span class="cart-count-badge">{{ $cartCount }}</span>
            @endif
        </a>
 
        @auth
            {{-- Logged-in: show avatar + name with a dropdown --}}
            <div class="dropdown">
                <button class="user-menu-btn dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="d-none d-lg-inline">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                    <li><a class="dropdown-item" href="{{ url('/account') }}"><i class="bi bi-person me-2"></i>My Account</a></li>
                    {{-- Was /bookings, which has no route and 404s. --}}
                    <li><a class="dropdown-item" href="{{ route('itinerary.index') }}"><i class="bi bi-suitcase me-2"></i>My Bookings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Sign out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            {{-- Guest: show the Sign in button --}}
            <a href="{{ route('login') }}" class="btn-signin-outline d-inline-flex align-items-center gap-1">
                <i class="bi bi-person"></i> Sign in
            </a>
        @endauth
    </div>
</nav>