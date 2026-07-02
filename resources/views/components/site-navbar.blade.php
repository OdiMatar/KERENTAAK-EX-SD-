@props([
    'variant' => 'default',
])

@php
    $isOverlay = $variant === 'overlay';
    $headerClass = $isOverlay
        ? 'site-navbar-overlay position-absolute top-0 start-0 end-0 z-3 border-bottom border-white border-opacity-50'
        : 'sticky-top border-bottom border-line bg-white shadow-sm';
    $brandClass = $isOverlay
        ? 'site-navbar-brand text-dark text-decoration-none'
        : 'site-navbar-brand text-dark text-decoration-none';
    $linkClass = 'site-navbar-link';
    $activeLinkClass = 'site-navbar-link is-active';
@endphp

<header class="{{ $headerClass }}">
    <x-ui.container>
        <div class="site-navbar-inner">
            <a href="{{ route('home') }}" class="{{ $brandClass }}">Kniploket Tiko</a>

            <nav class="site-navbar-nav" aria-label="Hoofdnavigatie">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? $activeLinkClass : $linkClass }}">Home</a>

                @auth
                    <a href="{{ route('appointments.index') }}" class="{{ request()->routeIs('appointments.*') ? $activeLinkClass : $linkClass }}">Afspraken</a>
                    <a href="{{ route('bestellingen.index') }}" class="{{ request()->routeIs('bestellingen.*') ? $activeLinkClass : $linkClass }}">Bestellingen</a>

                    @unless (auth()->user()->isCustomer())
                        <a href="{{ route('klanten.index') }}" class="{{ request()->routeIs('klanten.*') ? $activeLinkClass : $linkClass }}">Klanten</a>
                        <a href="{{ route('medewerkers.index') }}" class="{{ request()->routeIs('medewerkers.*') ? $activeLinkClass : $linkClass }}">Medewerkers</a>
                    @endunless

                    <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? $activeLinkClass : $linkClass }}">Profiel</a>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <x-ui.button variant="secondary" size="sm" type="submit">Uitloggen</x-ui.button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? $activeLinkClass : $linkClass }}">Inloggen</a>
                    <x-ui.button size="sm" href="{{ route('register') }}">Registreren</x-ui.button>
                @endauth
            </nav>
        </div>
    </x-ui.container>
</header>
