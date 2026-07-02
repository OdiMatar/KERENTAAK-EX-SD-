<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function afspraakUser(): User
{
    return User::factory()->create(['role' => User::ROLE_CUSTOMER]);
}

beforeEach(function (): void {
    DB::table('behandelingen')->updateOrInsert(
        ['naam' => 'Knippen'],
        [
            'categorie' => 'Haar',
            'duur' => 30,
            'prijs' => 25.00,
            'omschrijving' => 'Knipbehandeling',
            'is_actief' => true,
            'datum_aangemaakt' => now(),
            'datum_gewijzigd' => now(),
        ],
    );

    DB::table('medewerkers')->updateOrInsert(
        ['email' => 'sara.kapper@example.com'],
        [
            'name' => 'Sara Kapper',
            'voornaam' => 'Sara',
            'achternaam' => 'Kapper',
            'role' => 'medewerker',
            'phone' => '0612345678',
            'telefoonnummer' => '0612345678',
            'functie' => 'Medewerker',
            'is_active' => true,
            'is_actief' => true,
            'datum_aangemaakt' => now(),
            'datum_gewijzigd' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    );
});

it('toont de pagina om een afspraak te maken', function (): void {
    $this->actingAs(afspraakUser())
        ->get(route('appointments.create'))
        ->assertOk()
        ->assertSee('Plan je afspraak')
        ->assertSee('Knippen')
        ->assertSee('Sara Kapper')
        ->assertSee('Afspraak bevestigen');
});

it('stuurt gasten naar de inlogpagina voor afspraken', function (): void {
    $this->get(route('appointments.create'))
        ->assertRedirect(route('login'));
});
