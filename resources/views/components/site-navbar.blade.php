@props([
    'variant' => 'default',
])

@php
    $isOverlay = $variant === 'overlay';
    $headerClass = $isOverlay
        ? 'site-navbar-overlay position-absolute top-0 start-0 end-0 z-3 border-bottom border-white border-opacity-50'
        : 'sticky-top border-bottom border-line bg-white shadow-sm';
    $brandClass = $isOverlay
        ? 'fs-4 fw-semibold text-dark text-decoration-none'
        : 'fs-4 fw-semibold text-dark text-decoration-none';
    $secondaryVariant = 'secondary';
@endphp

<header class="{{ $headerClass }}">
    <x-ui.container>
        <div class="d-flex flex-column flex-sm-row gap-3 py-3 align-items-sm-center justify-content-sm-between">
            <a href="{{ route('home') }}" class="{{ $brandClass }}">Kniploket Tiko</a>

            <nav class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center">
                @auth
                    @unless (auth()->user()->isCustomer())
                        <x-ui.button :variant="$secondaryVariant" href="{{ route('medewerkers.index') }}">Overzicht medewerkers</x-ui.button>
                    @endunless
                @endauth

                <x-ui.button :variant="$secondaryVariant" href="{{ route('home') }}">Homepage</x-ui.button>

                @auth
                    <x-ui.button href="{{ route('appointments.index') }}">Afspraken</x-ui.button>
                    <x-ui.button href="{{ route('profile') }}">Profiel</x-ui.button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button :variant="$secondaryVariant" type="submit">Uitloggen</x-ui.button>
                    </form>
                @else
                    <x-ui.button :variant="$secondaryVariant" href="{{ route('login') }}">Inloggen</x-ui.button>
                    <x-ui.button href="{{ route('register') }}">Registreren</x-ui.button>
                @endauth
            </nav>
        </div>
    </x-ui.container>
</header>
