<x-app-layout title="Klant Toevoegen">
    <x-ui.container class="py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="text-uppercase fw-semibold small text-muted mb-2">Klanten</p>
                <h1 class="h2 mb-0">Klant Toevoegen</h1>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                @include('klanten._form', [
                    'action' => route('klanten.store'),
                    'submitLabel' => 'Opslaan',
                    'bestaandeKlanten' => $bestaandeKlanten,
                ])
            </div>
        </div>
    </x-ui.container>
</x-app-layout>
