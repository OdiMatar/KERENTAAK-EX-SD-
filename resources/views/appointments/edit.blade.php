<x-app-layout title="Afspraak wijzigen">
    <main>
        <x-ui.section
            eyebrow="Afspraken / Afspraak wijzigen"
            title="Wijzig je afspraak"
            description="Selecteer een nieuwe beschikbare datum en starttijd."
        >
            <x-ui.card>
                @include('appointments._form', [
                    'action' => route('appointments.update', $appointment->id),
                    'method' => 'PUT',
                    'submitLabel' => 'Opslaan',
                    'treatments' => $treatments,
                    'employees' => $employees,
                    'appointment' => $appointment,
                ])
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
