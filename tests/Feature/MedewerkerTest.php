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

it('toont een specifieke melding als er geen managers bekend zijn', function () {
    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
    ]);

    Medewerker::query()->create([
        'name' => 'Mila de Vries',
        'email' => 'mila-geen-manager@example.com',
        'role' => Medewerker::ROLE_EMPLOYEE,
        'functie' => 'Medewerker',
        'phone' => '0612345678',
    ]);

    $this->actingAs($owner)
        ->get(route('medewerkers.index', ['role' => Medewerker::ROLE_MANAGER]))
        ->assertOk()
        ->assertSee('Er zijn geen managers bekend')
        ->assertDontSee('Er zijn momenteel geen medewerkers bekend.');
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

it('wijzigt naam en telefoon van een medewerker zichtbaar in het overzicht', function () {
    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
    ]);

    $medewerker = Medewerker::query()->create([
        'name' => 'Yassin Attiah',
        'email' => 'yassin-update@example.com',
        'role' => Medewerker::ROLE_EMPLOYEE,
        'phone' => '0612345678',
    ]);

    $this->actingAs($owner)
        ->put(route('medewerkers.update', $medewerker), [
            'name' => 'Yassin Jansen',
            'email' => 'yassin-update@example.com',
            'role' => Medewerker::ROLE_MANAGER,
            'phone' => '0698765432',
        ])
        ->assertRedirect(route('medewerkers.index'))
        ->assertSessionHas('status', 'De medewerker is succesvol gewijzigd.')
        ->assertSessionHas('highlighted_medewerker_id', $medewerker->id);

    $this->assertDatabaseHas('medewerkers', [
        'id' => $medewerker->id,
        'name' => 'Yassin Jansen',
        'voornaam' => 'Yassin',
        'achternaam' => 'Jansen',
        'phone' => '0698765432',
        'telefoonnummer' => '0698765432',
    ]);

    $this->actingAs($owner)
        ->get(route('medewerkers.index'))
        ->assertOk()
        ->assertSee('Yassin Jansen')
        ->assertSee('0698765432');
});

it('licht een toegevoegde medewerker uit in het overzicht', function () {
    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
    ]);

    $response = $this->actingAs($owner)
        ->followingRedirects()
        ->post(route('medewerkers.store'), [
            'name' => 'Nora Peters',
            'email' => 'nora@example.com',
            'role' => Medewerker::ROLE_EMPLOYEE,
            'phone' => '0610101010',
        ]);

    $response->assertOk()
        ->assertSee('De medewerker is succesvol toegevoegd.')
        ->assertSee('Nora Peters')
        ->assertSee('0610101010')
        ->assertSee('medewerker-highlight', false);
});

it('licht een gewijzigde medewerker uit in het overzicht', function () {
    $owner = User::factory()->create([
        'role' => User::ROLE_OWNER,
    ]);

    $medewerker = Medewerker::query()->create([
        'name' => 'Mila de Vries',
        'email' => 'mila-wijziging@example.com',
        'role' => Medewerker::ROLE_EMPLOYEE,
        'phone' => '0612345678',
    ]);

    $response = $this->actingAs($owner)
        ->followingRedirects()
        ->put(route('medewerkers.update', $medewerker), [
            'name' => 'Mila Bakker',
            'email' => 'mila-wijziging@example.com',
            'role' => Medewerker::ROLE_MANAGER,
            'phone' => '0699999999',
        ]);

    $response->assertOk()
        ->assertSee('De medewerker is succesvol gewijzigd.')
        ->assertSee('Mila Bakker')
        ->assertSee('0699999999')
        ->assertSee('medewerker-highlight', false);
});
