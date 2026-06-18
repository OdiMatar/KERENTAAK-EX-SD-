<x-app-layout title="Style componenten">
    <header class="border-b border-line bg-surface">
        <x-ui.container>
            <div class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-brand-600">Kerentaak EX</p>
                    <h1 class="text-2xl font-semibold leading-tight text-ink sm:text-3xl">Gedeelde style componenten</h1>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <x-ui.button variant="secondary">Secundair</x-ui.button>
                    <x-ui.button>Primaire actie</x-ui.button>
                </div>
            </div>
        </x-ui.container>
    </header>

    <main>
        <x-ui.section
            eyebrow="Responsive basis"
            title="Een vaste opbouw voor mobiel, tablet en desktop"
            description="Gebruik deze componenten als standaard laag voor pagina's, formulieren en contentblokken. De spacing, breedtes, randen en tekstgroottes blijven overal consistent."
        >
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <x-ui.card title="Container" description="Centrale paginabreedte met vaste padding per breakpoint.">
                    <p class="text-sm leading-6 text-muted">Mobiel krijgt compacte padding, tablet iets meer ruimte en desktop een rustige max-breedte.</p>
                </x-ui.card>

                <x-ui.card title="Card" description="Voor herhaalde items, dashboardpanelen en korte content.">
                    <div class="flex flex-wrap gap-2">
                        <x-ui.badge variant="brand">Nieuw</x-ui.badge>
                        <x-ui.badge variant="success">Actief</x-ui.badge>
                        <x-ui.badge variant="warning">Concept</x-ui.badge>
                    </div>
                </x-ui.card>

                <x-ui.card title="Buttons" description="Varianten met dezelfde hoogte, radius en focus-state.">
                    <div class="flex flex-col gap-2">
                        <x-ui.button class="w-full">Opslaan</x-ui.button>
                        <x-ui.button variant="secondary" class="w-full">Annuleren</x-ui.button>
                        <x-ui.button variant="ghost" class="w-full">Meer opties</x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </x-ui.section>

        <x-ui.section
            eyebrow="Formulieren"
            title="Inputs met dezelfde maatvoering"
            description="Labels, hints, focus en foutmeldingen zitten in het component, zodat formulieren overal hetzelfde aanvoelen."
            class="pt-0"
        >
            <x-ui.card class="max-w-3xl">
                <form class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" label="Naam" placeholder="Jouw naam" />
                    <x-ui.input name="email" type="email" label="E-mail" placeholder="naam@example.com" />
                    <div class="sm:col-span-2">
                        <x-ui.textarea name="message" label="Bericht" hint="Dit veld schaalt netjes mee op alle schermen." />
                    </div>
                    <div class="flex flex-col gap-2 sm:col-span-2 sm:flex-row sm:justify-end">
                        <x-ui.button variant="secondary" type="reset">Leegmaken</x-ui.button>
                        <x-ui.button type="submit">Versturen</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </x-ui.section>
    </main>
</x-app-layout>
