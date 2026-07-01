<x-app-layout title="Profiel">
    <main>
        <x-ui.section
            eyebrow="Gegevens"
            title="Je profielgegevens"
            description="Hier zie je de gegevens waarmee je bent ingelogd."
        >
            <div class="row g-4">
                <div class="col-md-4">
                    <x-ui.card title="Naam" description="{{ auth()->user()->name }}">
                        <x-ui.badge variant="success">Actief</x-ui.badge>
                    </x-ui.card>
                </div>

                <div class="col-md-4">
                    <x-ui.card title="E-mail" description="{{ auth()->user()->email }}">
                        <x-ui.badge variant="brand">Account</x-ui.badge>
                    </x-ui.card>
                </div>

                <div class="col-md-4">
                    <x-ui.card title="Rol" description="{{ ucfirst(auth()->user()->role) }}">
                        <x-ui.badge variant="warning">Toegang</x-ui.badge>
                    </x-ui.card>
                </div>
            </div>
        </x-ui.section>
    </main>
</x-app-layout>
