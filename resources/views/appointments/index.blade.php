<x-app-layout title="Mijn afspraken">
    <main>
        <x-ui.section
            eyebrow="Afspraken"
            title="Mijn afspraken"
            description="Bekijk, wijzig of annuleer je geplande afspraken."
        >
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-sm-center mb-4">
                <x-ui.button href="{{ route('appointments.create') }}">Maak Nieuw Afspraak</x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('home') }}">Terug Naar Home</x-ui.button>
            </div>

            <x-ui.card>
                @if ($appointments->isEmpty())
                    <p class="mb-0 text-muted-custom">Je hebt nog geen afspraken</p>
                @else
                    <div class="table-responsive">
                        <table class="table appointment-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Naam</th>
                                    <th>Starttijd</th>
                                    <th>EndTijd</th>
                                    <th>Datum</th>
                                    <th>Kapper</th>
                                    <th>Soort</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($appointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->customer_name }}</td>
                                        <td>{{ $appointment->start_time }}</td>
                                        <td>{{ $appointment->end_time }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($appointment->date)->format('d/m/Y') }}</td>
                                        <td>{{ $appointment->employee_name }}</td>
                                        <td>{{ $appointment->treatment_name }}</td>
                                        <td>
                                            <div class="d-flex flex-column flex-lg-row gap-2">
                                                <x-ui.button
                                                    size="sm"
                                                    variant="ghost"
                                                    class="btn-warning text-white"
                                                    href="{{ route('appointments.edit', $appointment->id) }}"
                                                >
                                                    Wijzigen
                                                </x-ui.button>

                                                <form
                                                    method="POST"
                                                    action="{{ route('appointments.cancel', $appointment->id) }}"
                                                    data-confirm="Weet je zeker dat je deze afspraak wilt annuleren?"
                                                >
                                                    @csrf
                                                    @method('PATCH')
                                                    <x-ui.button size="sm" variant="danger" type="submit">Annuleren</x-ui.button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
