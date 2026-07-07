<x-app-layout title="Klant Details">
    <x-ui.container class="py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="text-uppercase fw-semibold small text-muted mb-2">Klanten</p>
                <h1 class="h2 mb-0">{{ $klant->naam }}</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('klanten.edit', $klant) }}" class="btn btn-primary">Wijzigen</a>
                <a href="{{ route('klanten.index') }}" class="btn btn-outline-secondary">Terug naar overzicht</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Klantgegevens</h2>
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Adres</dt>
                            <dd class="col-sm-7">{{ $klant->adres }}</dd>
                            <dt class="col-sm-5">Telefoonnummer</dt>
                            <dd class="col-sm-7">{{ $klant->telefoonnummer }}</dd>
                            <dt class="col-sm-5">E-mailadres</dt>
                            <dd class="col-sm-7">{{ $klant->email }}</dd>
                            <dt class="col-sm-5">Status</dt>
                            <dd class="col-sm-7">
                                @if ($klant->is_actief)
                                    <span class="badge text-bg-success">Actief</span>
                                @else
                                    <span class="badge text-bg-secondary">Inactief</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Wensen en allergieën</h2>
                        <p class="fw-semibold mb-1">Specifieke wensen</p>
                        <p class="text-muted">{{ $wensen->isEmpty() ? 'Geen wensen bekend.' : $wensen->implode(', ') }}</p>
                        <p class="fw-semibold mb-1">Allergieën</p>
                        <p class="text-muted mb-0">{{ $allergieen->isEmpty() ? 'Geen allergieën bekend.' : $allergieen->implode(', ') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Historie behandelingen en producten</h2>

                @if ($historie->isEmpty())
                    <p class="text-muted mb-0">Er is nog geen historie bekend voor deze klant.</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Naam</th>
                                    <th>Datum</th>
                                    <th>Status</th>
                                    <th>Extra informatie</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($historie as $item)
                                    <tr>
                                        <td>{{ ucfirst($item->type) }}</td>
                                        <td>{{ $item->titel }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($item->datum)->format('d-m-Y') }}</td>
                                        <td>{{ $item->status }}</td>
                                        <td>{{ $item->extra }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </x-ui.container>
</x-app-layout>
