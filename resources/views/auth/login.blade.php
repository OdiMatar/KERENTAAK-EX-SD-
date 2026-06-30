<x-app-layout title="Inloggen">
    <header class="border-b border-line bg-surface">
        <x-ui.container size="narrow">
            <div class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-brand-600">Welkom terug</p>
                    <h1 class="text-2xl font-semibold leading-tight text-ink sm:text-3xl">Inloggen</h1>
                </div>

                <x-ui.button variant="secondary" href="{{ route('register') }}">Account maken</x-ui.button>
            </div>
        </x-ui.container>
    </header>

    <main>
        <x-ui.section class="py-8 sm:py-10">
            <x-ui.card title="Je gegevens" description="Log in met je e-mailadres en wachtwoord.">
                <form method="POST" action="{{ route('login.store') }}" class="grid gap-4">
                    @csrf

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
                        autocomplete="current-password"
                        required
                        error="{{ $errors->first('password') }}"
                    />

                    <label class="flex items-center gap-3 text-sm text-muted">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="app-focus size-4 rounded border-line text-brand-600"
                            @checked(old('remember'))
                        >
                        Ingelogd blijven
                    </label>

                    <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:justify-end">
                        <x-ui.button variant="secondary" href="{{ route('home') }}">Annuleren</x-ui.button>
                        <x-ui.button type="submit">Inloggen</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
