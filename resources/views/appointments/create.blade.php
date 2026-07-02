<x-app-layout title="Afspraak aanmaken">
    <main>
        <x-ui.section
            eyebrow="Afspraken / Afspraak aanmaken"
            title="Plan je afspraak"
            description="Kies een behandeling, medewerker, datum en starttijd."
        >
            <x-ui.card>
                @include('appointments._form', [
                    'action' => route('appointments.store'),
                    'submitLabel' => 'Afspraak bevestigen',
                    'treatments' => $treatments,
                    'employees' => $employees,
                    'selectedTreatmentId' => $selectedTreatmentId,
                ])
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
