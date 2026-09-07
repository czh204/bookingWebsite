{{--
    Site footer. Included by layouts/homeApp, so it appears on every page
    that uses it — the login page extends layouts/app instead and stays
    without one.
--}}
<footer class="site-footer">
    <span>&copy; {{ date('Y') }} Voyagr. All rights reserved.</span>
    @if (! request()->routeIs('faq'))
        <a href="{{ route('faq') }}">FAQ</a>
    @else
        <a href="{{ route('home') }}">Home</a>
    @endif
</footer>
