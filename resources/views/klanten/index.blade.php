<x-app-layout title="Klanten Overzicht">
    <x-ui.container class="py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="text-uppercase fw-semibold small text-muted mb-2">Klanten</p>
                <h1 class="h2 mb-0">Klanten Overzicht</h1>
            </div>
            <a href="{{ route('klanten.create') }}" class="btn btn-primary">Klant Toevoegen</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="GET" action="{{ route('klanten.index') }}" class="mb-3">
                    <label for="zoekterm" class="form-label fw-semibold">Zoeken op naam</label>
                    <input
                        id="zoekterm"
                        type="search"
                        name="zoekterm"
                        class="form-control"
                        value="{{ $zoekterm }}"
                        placeholder="Bijvoorbeeld Lisa"
                        autocomplete="off"
                        data-klant-search
                    >
                </form>

                @if ($klanten->isEmpty() && $zoekterm !== '')
                    <div class="text-center text-muted py-5">
                        Geen klanten gevonden die voldoen aan deze zoekterm
                    </div>
                @else
                    <div class="table-responsive" data-klant-table>
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Naam</th>
                                    <th>Adres</th>
                                    <th>Telefoonnummer</th>
                                    <th>E-mailadres</th>
                                    <th>Acties</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($klanten as $klant)
                                    <tr data-klant-row data-klant-name="{{ strtolower($klant->naam) }}">
                                        <td>{{ $klant->naam }}</td>
                                        <td>{{ $klant->adres ?? '-' }}</td>
                                        <td>{{ $klant->telefoonnummer ?? '-' }}</td>
                                        <td>{{ $klant->email ?? '-' }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('klanten.show', $klant->id) }}" class="btn btn-sm btn-outline-primary">Details</a>
                                                <a href="{{ route('klanten.edit', $klant->id) }}" class="btn btn-sm btn-outline-secondary">Wijzigen</a>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-delete-modal-open
                                                    data-delete-action="{{ route('klanten.destroy', $klant->id) }}"
                                                    data-delete-name="{{ $klant->naam }}"
                                                >
                                                    Verwijder
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted">Er zijn momenteel geen klanten bekend.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center text-muted py-5 d-none" data-klant-empty>
                        Geen klanten gevonden die voldoen aan deze zoekterm
                    </div>
                @endif
            </div>
        </div>

        <div class="klant-delete-modal d-none" data-delete-modal aria-hidden="true">
            <div class="klant-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="delete-klant-title">
                <h2 id="delete-klant-title" class="h5 mb-2">Klant verwijderen</h2>
                <p class="mb-4">Weet je zeker dat je <strong data-delete-name></strong> definitief wilt verwijderen?</p>

                <form method="POST" action="" data-delete-form class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-outline-secondary" data-delete-modal-close>Nee, Terug</button>
                    <button type="submit" class="btn btn-danger">Ja, Verwijder</button>
                </form>
            </div>
        </div>
    </x-ui.container>
</x-app-layout>
