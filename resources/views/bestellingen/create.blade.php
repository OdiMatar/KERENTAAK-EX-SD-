<x-app-layout title="Bestelling toevoegen">
    <main>
        <x-ui.section
            eyebrow="Bestellingen"
            title="Bestelling toevoegen"
            description="Registreer een nieuwe klantbestelling."
        >
            <x-ui.card>
                <x-bestellingen.form
                    :action="route('bestellingen.store')"
                    :klanten="$klanten"
                    :producten="$producten"
                    button-text="Toevoegen"
                />
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
