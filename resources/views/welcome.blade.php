<x-app-layout title="Kniploket Tiko">
    <header class="absolute inset-x-0 top-0 z-20">
        <x-ui.container>
            <div class="flex flex-col gap-3 py-5 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('home') }}" class="text-xl font-semibold text-white drop-shadow">Kniploket Tiko</a>

                <nav class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    @auth
                        <x-ui.button href="{{ route('dashboard') }}">Dashboard</x-ui.button>
                    @else
                        <x-ui.button variant="secondary" href="{{ route('login') }}">Inloggen</x-ui.button>
                        <x-ui.button href="{{ route('register') }}">Account maken</x-ui.button>
                    @endauth
                </nav>
            </div>
        </x-ui.container>
    </header>

    <main>
        <section class="relative min-h-[92vh] overflow-hidden bg-ink">
            <img
                src="{{ asset('images/kniploket-tiko-hero.png') }}"
                alt="Moderne kapsalon van Kniploket Tiko"
                class="absolute inset-0 size-full object-cover"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/45 to-black/10"></div>

            <x-ui.container>
                <div class="relative z-10 flex min-h-[92vh] max-w-3xl flex-col justify-center pb-14 pt-32 text-white">
                    <p class="mb-4 text-sm font-semibold uppercase tracking-[0.18em] text-[#f0c879]">Moderne kapsalon in de stad</p>
                    <h1 class="text-4xl font-semibold leading-tight sm:text-5xl lg:text-6xl">Kniploket Tiko maakt jouw salonbezoek overzichtelijk en persoonlijk.</h1>
                    <p class="mt-5 max-w-2xl text-base leading-8 text-white/82 sm:text-lg">
                        Plan online een afspraak, kies direct de juiste specialist en bestel haarproducten om later in de salon af te halen.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @auth
                            <x-ui.button href="{{ route('dashboard') }}" size="lg">Naar dashboard</x-ui.button>
                        @else
                            <x-ui.button href="{{ route('register') }}" size="lg">Afspraak starten</x-ui.button>
                            <x-ui.button variant="secondary" href="{{ route('login') }}" size="lg">Inloggen</x-ui.button>
                        @endauth
                    </div>

                    <dl class="mt-12 grid gap-3 sm:grid-cols-3">
                        <div class="border-l-2 border-[#f0c879] pl-4">
                            <dt class="text-2xl font-semibold">5 jaar</dt>
                            <dd class="mt-1 text-sm text-white/75">ervaring door oprichtster Lisa Jansen</dd>
                        </div>
                        <div class="border-l-2 border-[#f0c879] pl-4">
                            <dt class="text-2xl font-semibold">4</dt>
                            <dd class="mt-1 text-sm text-white/75">specialisten met eigen agenda</dd>
                        </div>
                        <div class="border-l-2 border-[#f0c879] pl-4">
                            <dt class="text-2xl font-semibold">24/7</dt>
                            <dd class="mt-1 text-sm text-white/75">online afspraak en bestelling</dd>
                        </div>
                    </dl>
                </div>
            </x-ui.container>
        </section>

        <x-ui.section
            eyebrow="Voor klanten"
            title="Van afspraak tot afhalen zonder misverstanden"
            description="De homepage sluit aan op de casus: klanten kunnen behandelingen bekijken, online plannen en producten bestellen voor afhalen in de salon."
        >
            <div class="grid gap-4 md:grid-cols-3">
                <x-ui.card title="Kies je behandeling" description="Knippen, kleuren, stylen, extensions en verzorgende behandelingen met prijs en tijdsduur.">
                    <x-ui.badge variant="brand">Stap 1</x-ui.badge>
                </x-ui.card>

                <x-ui.card title="Selecteer een specialist" description="De klant ziet alleen medewerkers die passen bij de gekozen behandeling en beschikbaarheid.">
                    <x-ui.badge variant="success">Stap 2</x-ui.badge>
                </x-ui.card>

                <x-ui.card title="Boek je starttijd" description="Het systeem voorkomt dubbele afspraken door de agenda automatisch te controleren.">
                    <x-ui.badge variant="warning">Stap 3</x-ui.badge>
                </x-ui.card>
            </div>
        </x-ui.section>

        <section id="behandelingen" class="border-y border-line bg-surface py-8 sm:py-10 lg:py-14">
            <x-ui.container>
                <div class="mb-6 max-w-3xl sm:mb-8">
                    <p class="mb-2 text-sm font-semibold text-brand-600">Behandelingen</p>
                    <h2 class="text-2xl font-semibold leading-tight text-ink sm:text-3xl">Alles voor haar dat goed valt, kleurt en blijft zitten</h2>
                    <p class="mt-3 text-base leading-7 text-muted">Iedere behandeling kan gekoppeld worden aan tijdsduur, prijs, specialist en benodigde producten.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['name' => 'Knippen', 'time' => '30-45 min', 'price' => 'vanaf € 28'],
                        ['name' => 'Kleuren', 'time' => '90-150 min', 'price' => 'vanaf € 65'],
                        ['name' => 'Stylen', 'time' => '30-60 min', 'price' => 'vanaf € 35'],
                        ['name' => 'Extensions', 'time' => '120 min', 'price' => 'op afspraak'],
                    ] as $treatment)
                        <article class="rounded-lg border border-line bg-[#fffaf2] p-5">
                            <h3 class="text-lg font-semibold text-ink">{{ $treatment['name'] }}</h3>
                            <p class="mt-3 text-sm text-muted">{{ $treatment['time'] }}</p>
                            <p class="mt-1 text-sm font-semibold text-brand-700">{{ $treatment['price'] }}</p>
                        </article>
                    @endforeach
                </div>
            </x-ui.container>
        </section>

        <x-ui.section
            id="producten"
            eyebrow="Producten en voorraad"
            title="Bestel online, haal op in de salon"
            description="Kniploket Tiko verkoopt shampoo, conditioner, stylingproducten en verfproducten. Lage voorraad krijgt een duidelijke waarschuwing."
        >
            <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                <x-ui.card title="Voorraadbeheer" description="Productnaam, categorie, EAN-code, leverancier en voorraad worden centraal bijgehouden.">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-lg bg-brand-50 p-4">
                            <p class="text-sm font-semibold text-brand-700">Shampoo & conditioner</p>
                            <p class="mt-2 text-sm leading-6 text-muted">Klanten zien wat beschikbaar is voor afhalen.</p>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-4">
                            <p class="text-sm font-semibold text-amber-800">Lage voorraad</p>
                            <p class="mt-2 text-sm leading-6 text-muted">Waarschuwing zodra een product bijna op is.</p>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card title="Bestellingen" description="Orderdatum, verwachte leverdatum en status blijven inzichtelijk voor medewerker en klant.">
                    <ul class="space-y-3 text-sm leading-6 text-muted">
                        <li>Online bestellen door klanten</li>
                        <li>Afhalen en betalen in de salon</li>
                        <li>Historie per klant bewaren</li>
                    </ul>
                </x-ui.card>
            </div>
        </x-ui.section>

        <section class="bg-[#1f2f27] py-8 text-white sm:py-10 lg:py-14">
            <x-ui.container>
                <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#f0c879]">Voor eigenaar en medewerkers</p>
                        <h2 class="mt-3 text-2xl font-semibold leading-tight sm:text-3xl">Een beheeromgeving voor planning, klanten en rapportages</h2>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach (['Medewerkers en werktijden', 'Klanten en allergieën', 'Afspraken wijzigen of annuleren', 'Maandoverzichten en rapportages'] as $item)
                            <div class="rounded-lg border border-white/15 bg-white/8 p-4 text-sm font-semibold text-white/90">
                                {{ $item }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-ui.container>
        </section>
    </main>
</x-app-layout>
