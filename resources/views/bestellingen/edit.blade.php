<x-app-layout title="Bestelling wijzigen">
    <main>
        <x-ui.section
            eyebrow="Bestellingen"
            title="Bestelling wijzigen"
            description="Pas de besteldatum, verwachte leverdatum of status aan."
        >
            <x-ui.card>
                <x-bestellingen.form
                    :bestelling="$bestelling"
                    :action="route('bestellingen.update', $bestelling->id)"
                    method="PUT"
                    button-text="Wijzigen"
                />
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
