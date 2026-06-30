<x-app-layout title="Kerentaak EX">
    <header class="border-b border-line bg-surface">
        <x-ui.container>
            <div class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-brand-600">Kerentaak EX</p>
                    <h1 class="text-2xl font-semibold leading-tight text-ink sm:text-3xl">Login en registratie systeem</h1>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    @auth
                        <x-ui.button href="{{ route('dashboard') }}">Dashboard</x-ui.button>
                    @else
                        <x-ui.button variant="secondary" href="{{ route('login') }}">Inloggen</x-ui.button>
                        <x-ui.button href="{{ route('register') }}">Registreren</x-ui.button>
                    @endauth
                </div>
            </div>
        </x-ui.container>
    </header>

    <main>
        <x-ui.section
            eyebrow="MVC Laravel"
            title="Veilig aanmelden met duidelijke terugkoppeling"
            description="Deze applicatie gebruikt controllers, models, views, servervalidatie, client-side formulierregels, technische logging en MySQL-configuratie."
        >
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <x-ui.card title="Registreren" description="Maak een account aan met gevalideerde naam, e-mail en sterk wachtwoord.">
                    <x-ui.button href="{{ route('register') }}" class="w-full">Account maken</x-ui.button>
                </x-ui.card>

                <x-ui.card title="Inloggen" description="Gebruik je account om beveiligd naar het dashboard te gaan.">
                    <x-ui.button variant="secondary" href="{{ route('login') }}" class="w-full">Naar inloggen</x-ui.button>
                </x-ui.card>

                <x-ui.card title="Technische log" description="Acties zoals registratie, login en logout worden vastgelegd.">
                    <p class="text-sm leading-6 text-muted">Op het dashboard worden recente logregels via een join met gebruikers getoond.</p>
                </x-ui.card>
            </div>
        </x-ui.section>
    </main>
</x-app-layout>
