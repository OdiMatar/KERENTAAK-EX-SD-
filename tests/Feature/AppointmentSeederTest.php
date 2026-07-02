<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('toont vier gesedde afspraken in het afsprakenoverzicht van de klant', function (): void {
    $this->seed(DatabaseSeeder::class);

    $customer = User::query()
        ->where('email', 'klant@kniplokettiko.nl')
        ->firstOrFail();

    $this->actingAs($customer)
        ->get(route('appointments.index'))
        ->assertOk()
        ->assertSee('Mijn afspraken')
        ->assertSee('Knippen')
        ->assertSee('Kleuren')
        ->assertSee('Stylen')
        ->assertSee('Extensions');
});

it('toont gesedde afspraken in het medewerker afsprakenoverzicht', function (): void {
    $this->seed(DatabaseSeeder::class);

    $employee = User::query()
        ->where('email', 'medewerker@kniplokettiko.nl')
        ->firstOrFail();

    $this->actingAs($employee)
        ->get(route('appointments.index'))
        ->assertOk()
        ->assertSee('Afspraken overzicht')
        ->assertSee('Wijzigen')
        ->assertSee('Annuleren')
        ->assertSee('Knippen')
        ->assertSee('Kleuren')
        ->assertSee('Stylen')
        ->assertSee('Extensions');
});

it('laat medewerkers een gesedde afspraak wijzigen openen en annuleren', function (): void {
    $this->seed(DatabaseSeeder::class);

    $employee = User::query()
        ->where('email', 'medewerker@kniplokettiko.nl')
        ->firstOrFail();
    $appointmentId = DB::table('afspraken')
        ->join('klanten', 'klanten.id', '=', 'afspraken.klant_id')
        ->where('klanten.email', 'klant@kniplokettiko.nl')
        ->where('afspraken.status', 'Gepland')
        ->value('afspraken.id');

    $this->actingAs($employee)
        ->get(route('appointments.edit', $appointmentId))
        ->assertOk()
        ->assertSee('Wijzig je afspraak');

    $this->patch(route('appointments.cancel', $appointmentId))
        ->assertRedirect(route('appointments.index'))
        ->assertSessionHas('status', 'Je afspraak is geannuleerd.');

    $this->assertDatabaseHas('afspraken', [
        'id' => $appointmentId,
        'status' => 'Geannuleerd',
        'is_actief' => false,
    ]);
});
