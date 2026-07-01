<x-app-layout title="Kniploket Tiko" nav-variant="overlay">
    <main>
        <section class="hero-section position-relative overflow-hidden">
            <img
                src="{{ asset('images/kniploket-tiko-hero.png') }}"
                alt="Moderne kapsalon van Kniploket Tiko"
                class="position-absolute top-0 start-0"
            >
            <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100"></div>

            <x-ui.container>
                <div class="position-relative z-1 d-flex flex-column justify-content-center text-white py-5" style="min-height: 92vh; max-width: 48rem; padding-top: 9rem !important;">
                    <p class="mb-3 small fw-semibold text-uppercase text-warning">Moderne kapsalon in de stad</p>
                    <h1 class="display-4 fw-semibold">Kniploket Tiko maakt jouw salonbezoek overzichtelijk en persoonlijk.</h1>
                    <p class="mt-4 fs-5 text-white-50">
                        Plan online een afspraak, kies direct de juiste specialist en bestel haarproducten om later in de salon af te halen.
                    </p>

                    <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                        @auth
                            <x-ui.button href="{{ route('profile') }}" size="lg">Naar profiel</x-ui.button>
                        @else
                            <x-ui.button href="{{ route('register') }}" size="lg">Afspraak starten</x-ui.button>
                            <x-ui.button variant="secondary" href="{{ route('login') }}" size="lg">Inloggen</x-ui.button>
                        @endauth
                    </div>

                    <dl class="row g-3 mt-5">
                        <div class="col-sm-4 border-start border-warning ps-3">
                            <dt class="h3 fw-semibold">5 jaar</dt>
                            <dd class="small text-white-50 mb-0">ervaring door oprichtster Lisa Jansen</dd>
                        </div>
                        <div class="col-sm-4 border-start border-warning ps-3">
                            <dt class="h3 fw-semibold">4</dt>
                            <dd class="small text-white-50 mb-0">specialisten met eigen agenda</dd>
                        </div>
                        <div class="col-sm-4 border-start border-warning ps-3">
                            <dt class="h3 fw-semibold">24/7</dt>
                            <dd class="small text-white-50 mb-0">online afspraak en bestelling</dd>
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
            <div class="row g-4">
                <div class="col-md-4">
                    <x-ui.card title="Kies je behandeling" description="Knippen, kleuren, stylen, extensions en verzorgende behandelingen met prijs en tijdsduur.">
                        <x-ui.badge variant="brand">Stap 1</x-ui.badge>
                    </x-ui.card>
                </div>

                <div class="col-md-4">
                    <x-ui.card title="Selecteer een specialist" description="De klant ziet alleen medewerkers die passen bij de gekozen behandeling en beschikbaarheid.">
                        <x-ui.badge variant="success">Stap 2</x-ui.badge>
                    </x-ui.card>
                </div>

                <div class="col-md-4">
                    <x-ui.card title="Boek je starttijd" description="Het systeem voorkomt dubbele afspraken door de agenda automatisch te controleren.">
                        <x-ui.badge variant="warning">Stap 3</x-ui.badge>
                    </x-ui.card>
                </div>
            </div>
        </x-ui.section>

        <section id="behandelingen" class="border-top border-bottom border-line bg-white py-5">
            <x-ui.container>
                <div class="mb-4 col-lg-8">
                    <p class="mb-2 small fw-semibold text-brand">Behandelingen</p>
                    <h2 class="h3 fw-semibold">Alles voor haar dat goed valt, kleurt en blijft zitten</h2>
                    <p class="mt-3 text-muted-custom">Iedere behandeling kan gekoppeld worden aan tijdsduur, prijs, specialist en benodigde producten.</p>
                </div>

                <div class="row g-4">
                    @foreach ([
                        ['name' => 'Knippen', 'time' => '30-45 min', 'price' => 'vanaf € 28'],
                        ['name' => 'Kleuren', 'time' => '90-150 min', 'price' => 'vanaf € 65'],
                        ['name' => 'Stylen', 'time' => '30-60 min', 'price' => 'vanaf € 35'],
                        ['name' => 'Extensions', 'time' => '120 min', 'price' => 'op afspraak'],
                    ] as $treatment)
                        <div class="col-sm-6 col-lg-3">
                            <article class="card h-100 border-line bg-soft p-4">
                                <h3 class="h5 fw-semibold">{{ $treatment['name'] }}</h3>
                                <p class="mt-3 small text-muted mb-1">{{ $treatment['time'] }}</p>
                                <p class="small fw-semibold text-brand mb-0">{{ $treatment['price'] }}</p>
                            </article>
                        </div>
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
            <div class="row g-4">
                <div class="col-lg-7">
                    <x-ui.card title="Voorraadbeheer" description="Productnaam, categorie, EAN-code, leverancier en voorraad worden centraal bijgehouden.">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="rounded bg-success-subtle p-4">
                                    <p class="small fw-semibold text-success-emphasis mb-2">Shampoo & conditioner</p>
                                    <p class="small text-muted mb-0">Klanten zien wat beschikbaar is voor afhalen.</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="rounded bg-warning-subtle p-4">
                                    <p class="small fw-semibold text-warning-emphasis mb-2">Lage voorraad</p>
                                    <p class="small text-muted mb-0">Waarschuwing zodra een product bijna op is.</p>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <div class="col-lg-5">
                    <x-ui.card title="Bestellingen" description="Orderdatum, verwachte leverdatum en status blijven inzichtelijk voor medewerker en klant.">
                        <ul class="text-muted small mb-0">
                            <li>Online bestellen door klanten</li>
                            <li>Afhalen en betalen in de salon</li>
                            <li>Historie per klant bewaren</li>
                        </ul>
                    </x-ui.card>
                </div>
            </div>
        </x-ui.section>

        <section class="bg-ink py-5 text-white">
            <x-ui.container>
                <div class="row g-4 align-items-center">
                    <div class="col-lg-5">
                        <p class="small fw-semibold text-uppercase text-warning">Voor eigenaar en medewerkers</p>
                        <h2 class="h3 fw-semibold">Een beheeromgeving voor planning, klanten en rapportages</h2>
                    </div>

                    <div class="col-lg-7">
                        <div class="row g-3">
                        @foreach (['Medewerkers en werktijden', 'Klanten en allergieën', 'Afspraken wijzigen of annuleren', 'Maandoverzichten en rapportages'] as $item)
                            <div class="col-sm-6">
                                <div class="rounded border border-white border-opacity-25 bg-white bg-opacity-10 p-4 small fw-semibold">
                                    {{ $item }}
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            </x-ui.container>
        </section>
    </main>
</x-app-layout>
