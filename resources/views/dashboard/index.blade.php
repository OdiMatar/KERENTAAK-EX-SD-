<x-app-layout title="Dashboard">
    <main>
        <x-ui.section
            eyebrow="{{ ucfirst(auth()->user()->role) }} dashboard"
            title="Hallo, {{ auth()->user()->name }}"
            description="Je ziet hieronder alleen de onderdelen die passen bij jouw rol binnen Kniploket Tiko."
        >
            <div class="row g-4">
                <div class="col-lg-4">
                    <x-ui.card title="Naam" description="{{ auth()->user()->name }}">
                        <x-ui.badge variant="success">Actief</x-ui.badge>
                    </x-ui.card>
                </div>

                <div class="col-lg-4">
                    <x-ui.card title="E-mail" description="{{ auth()->user()->email }}">
                        <x-ui.badge variant="brand">Geverifieerde invoer</x-ui.badge>
                    </x-ui.card>
                </div>

                <div class="col-lg-4">
                    <x-ui.card title="Laatste update" description="{{ auth()->user()->updated_at->format('d-m-Y H:i') }}">
                        <x-ui.badge variant="warning">{{ ucfirst(auth()->user()->role) }}</x-ui.badge>
                    </x-ui.card>
                </div>
            </div>
        </x-ui.section>

        <x-ui.section
            eyebrow="Rechten"
            title="Wat jij kunt doen"
            description="Deze modules zijn gebaseerd op de rollen uit de casus: eigenaar, medewerker en klant."
            class="pt-0"
        >
            <div class="row g-4">
                @foreach ($modules as $module)
                    <div class="col-md-6 col-xl-4">
                        <x-ui.card title="{{ $module['title'] }}" description="{{ $module['description'] }}">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <x-ui.badge variant="brand">{{ $module['badge'] }}</x-ui.badge>

                                @if (($module['route'] ?? null) && Route::has($module['route']))
                                    <x-ui.button size="sm" href="{{ route($module['route']) }}">Openen</x-ui.button>
                                @endif
                            </div>
                        </x-ui.card>
                    </div>
                @endforeach
            </div>
        </x-ui.section>

        @if ($reports)
            <x-ui.section
                eyebrow="Managementinformatie"
                title="Rapportages voor de eigenaar"
                description="Alleen de eigenaar ziet deze managementoverzichten."
                class="pt-0"
            >
                <div class="row g-4">
                    @foreach ($reports as $report)
                        <div class="col-md-4">
                            <x-ui.card title="{{ $report }}">
                                <x-ui.badge variant="warning">Rapportage</x-ui.badge>
                            </x-ui.card>
                        </div>
                    @endforeach
                </div>
            </x-ui.section>
        @endif

        @if (auth()->user()->isOwner())
            <x-ui.section
                eyebrow="Technische log"
                title="Recente acties"
                description="Deze lijst komt uit de technische logs en gebruikt een join met de users tabel."
                class="pt-0"
            >
                <x-ui.card>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="text-uppercase small text-muted">
                                <tr>
                                    <th>Actie</th>
                                    <th>Gebruiker</th>
                                    <th>Melding</th>
                                    <th>Datum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestLogs as $log)
                                    <tr>
                                        <td class="fw-medium">{{ $log->action }}</td>
                                        <td class="text-muted">{{ $log->user_name ?? 'Onbekend' }}</td>
                                        <td class="text-muted">{{ $log->message }}</td>
                                        <td class="text-muted">{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d-m-Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-muted">Er zijn nog geen technische logregels.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </x-ui.section>
        @endif
    </main>
</x-app-layout>
