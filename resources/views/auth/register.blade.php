<x-app-layout title="Registreren">
    <header class="border-b border-line bg-surface">
        <x-ui.container size="narrow">
            <div class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-brand-600">Nieuw account</p>
                    <h1 class="text-2xl font-semibold leading-tight text-ink sm:text-3xl">Registreren</h1>
                </div>

                <x-ui.button variant="secondary" href="{{ route('login') }}">Ik heb al een account</x-ui.button>
            </div>
        </x-ui.container>
    </header>

    <main>
        <x-ui.section class="py-8 sm:py-10">
            <x-ui.card title="Accountgegevens" description="Vul je gegevens in. Velden worden in de browser en op de server gecontroleerd.">
                <form method="POST" action="{{ route('register.store') }}" class="grid gap-4">
                    @csrf

                    <x-ui.input
                        name="name"
                        label="Naam"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        minlength="2"
                        maxlength="255"
                        required
                        error="{{ $errors->first('name') }}"
                    />

                    <x-ui.input
                        name="email"
                        type="email"
                        label="E-mail"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        maxlength="255"
                        required
                        error="{{ $errors->first('email') }}"
                    />

                    <x-ui.input
                        name="password"
                        type="password"
                        label="Wachtwoord"
                        autocomplete="new-password"
                        minlength="8"
                        pattern="(?=.*[A-Za-z])(?=.*\d).{8,}"
                        required
                        hint="Minimaal 8 tekens, met letters en cijfers."
                        error="{{ $errors->first('password') }}"
                    />

                    <x-ui.input
                        name="password_confirmation"
                        type="password"
                        label="Herhaal wachtwoord"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    />

                    <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:justify-end">
                        <x-ui.button variant="secondary" href="{{ route('home') }}">Annuleren</x-ui.button>
                        <x-ui.button type="submit">Account aanmaken</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
