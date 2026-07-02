<?php

use App\Models\Medewerker;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the employee overview for authenticated users', function () {
    $owner = User::factory()->create([
        'name' => 'Lisa Jansen',
        'email' => 'owner@example.com',
        'role' => User::ROLE_OWNER,
    ]);

    Medewerker::query()->create([
        'name' => 'Mila de Vries',
        'email' => 'mila@example.com',
        'role' => Medewerker::ROLE_EMPLOYEE,
        'phone' => '0612345678',
    ]);

    $this->actingAs($owner);

    $response = $this->get(route('medewerkers.index'));

    $response->assertOk()
        ->assertSee('Overzicht medewerkers')
        ->assertSee('Mila de Vries')
        ->assertSee('mila@example.com')
        ->assertSee('0612345678');
});

it('verbergt een verwijderde medewerker met afspraken uit het overzicht', function () {
    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
    ]);

    $klantId = DB::table('klanten')->insertGetId([
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Teststraat 1, Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'lisa-medewerker-test@example.com',
        'is_actief' => true,
    ]);

    $medewerker = Medewerker::query()->create([
        'name' => 'Yassin Attiah',
        'email' => 'yassin-test@example.com',
        'role' => Medewerker::ROLE_EMPLOYEE,
        'phone' => '0612345678',
    ]);

    DB::table('afspraken')->insert([
        'klant_id' => $klantId,
        'medewerker_id' => $medewerker->id,
        'datum' => now()->addDay()->toDateString(),
        'starttijd' => '10:00:00',
        'eindtijd' => '10:45:00',
        'status' => 'Gepland',
        'is_actief' => true,
    ]);

    $this->actingAs($owner)
        ->from(route('medewerkers.index'))
        ->delete(route('medewerkers.destroy', $medewerker))
        ->assertRedirect(route('medewerkers.index'))
        ->assertSessionHas('status', 'De medewerker is succesvol verwijderd.');

    $this->assertDatabaseHas('medewerkers', [
        'id' => $medewerker->id,
        'is_active' => false,
        'is_actief' => false,
    ]);

    $this->actingAs($owner)
        ->get(route('medewerkers.index'))
        ->assertOk()
        ->assertDontSee('Yassin Attiah')
        ->assertDontSee('yassin-test@example.com');
});
