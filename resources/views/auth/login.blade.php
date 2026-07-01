<x-app-layout title="Inloggen">
    <main>
        <x-ui.section>
            <div class="mx-auto" style="max-width: 36rem;">
                <x-ui.card title="Je gegevens" description="Log in met je e-mailadres en wachtwoord.">
                    <form method="POST" action="{{ route('login.store') }}" class="d-grid gap-3">
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

                        <label class="form-check text-muted">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                class="form-check-input"
                                @checked(old('remember'))
                            >
                            <span class="form-check-label">Ingelogd blijven</span>
                        </label>

                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-sm-end pt-2">
                            <x-ui.button variant="secondary" href="{{ route('home') }}">Annuleren</x-ui.button>
                            <x-ui.button type="submit">Inloggen</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </x-ui.section>
    </main>
</x-app-layout>
