<?php

use App\Models\Klant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function klantBeheerder(string $role = User::ROLE_EMPLOYEE): User
{
    return User::factory()->create(['role' => $role]);
}

function klantPayload(array $overrides = []): array
{
    return [
        'naam' => 'Lisa Tiko',
        'adres' => 'Teststraat 1, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'lisa@tiko.nl',
        'is_actief' => '1',
        ...$overrides,
    ];
}

it('toont en filtert klanten op naam', function (): void {
    Klant::query()->create([
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Teststraat 1, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'lisa@tiko.nl',
    ]);
    Klant::query()->create([
        'voornaam' => 'Mila',
        'achternaam' => 'Jansen',
        'adres' => 'Voorbeeldlaan 2, 2345 CD Utrecht',
        'telefoonnummer' => '0687654321',
        'email' => 'mila@example.com',
    ]);

    $this->actingAs(klantBeheerder())
        ->get(route('klanten.index'))
        ->assertOk()
        ->assertSee('Klanten Overzicht')
        ->assertSee('Lisa Tiko')
        ->assertSee('0612345678')
        ->assertSee('lisa@tiko.nl')
        ->assertSee('Mila Jansen');

    $this->get(route('klanten.index', ['zoekterm' => 'Lisa']))
        ->assertOk()
        ->assertSee('Lisa Tiko')
        ->assertDontSee('Mila Jansen');
});

it('toont een melding als de zoekopdracht geen klanten vindt', function (): void {
    Klant::query()->create([
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Teststraat 1, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'lisa@tiko.nl',
        'is_actief' => false,
    ]);

    $this->actingAs(klantBeheerder())
        ->get(route('klanten.index', ['zoekterm' => 'Xyz123']))
        ->assertOk()
        ->assertSee('Geen klanten gevonden die voldoen aan deze zoekterm')
        ->assertDontSee('Lisa Tiko');
});

it('voegt een nieuwe klant toe', function (): void {
    $this->actingAs(klantBeheerder())
        ->post(route('klanten.store'), klantPayload())
        ->assertRedirect(route('klanten.index'))
        ->assertSessionHas('status', 'Klant met succes toegevoegd');

    $this->assertDatabaseHas('klanten', [
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Teststraat 1, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'lisa@tiko.nl',
        'is_actief' => true,
    ]);
});

it('weigert een klantadres zonder postcode of stad', function (): void {
    $this->actingAs(klantBeheerder())
        ->post(route('klanten.store'), klantPayload(['adres' => 'Teststraat 1']))
        ->assertSessionHasErrors([
            'adres' => 'Straatnaam, huisnummer, postcode en stad zijn verplicht om te vullen.',
        ]);

    $this->assertDatabaseMissing('klanten', [
        'voornaam' => 'Lisa',
        'adres' => 'Teststraat 1',
    ]);
});

it('blokkeert verwijderen zolang een klant actief is', function (): void {
    $klant = Klant::query()->create([
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Teststraat 1, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'actief@example.com',
        'is_actief' => true,
    ]);

    $this->actingAs(klantBeheerder(User::ROLE_OWNER))
        ->delete(route('klanten.destroy', $klant))
        ->assertRedirect()
        ->assertSessionHas('error', 'Deze klant is nog actief. Zet de klant eerst op inactief voordat je deze verwijdert.');

    $this->assertDatabaseHas('klanten', [
        'id' => $klant->id,
    ]);
});

it('slaat geen klant op met een ongeldig e-mailadres', function (): void {
    $this->actingAs(klantBeheerder())
        ->post(route('klanten.store'), klantPayload(['email' => 'lisatiko.nl']))
        ->assertSessionHasErrors(['email' => 'Vul een geldig e-mailadres in']);

    $this->assertDatabaseMissing('klanten', [
        'voornaam' => 'Lisa',
        'email' => 'lisatiko.nl',
    ]);
});

it('wijzigt een bestaande klant', function (): void {
    $klant = Klant::query()->create([
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Teststraat 1, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'lisa@tiko.nl',
    ]);

    $this->actingAs(klantBeheerder())
        ->put(route('klanten.update', $klant), klantPayload(['telefoonnummer' => '0699999999']))
        ->assertRedirect(route('klanten.index'))
        ->assertSessionHas('status', 'Klant met succes gewijzigd');

    $this->assertDatabaseHas('klanten', [
        'id' => $klant->id,
        'telefoonnummer' => '0699999999',
    ]);
});

it('toont een melding als er niks is gewijzigd', function (): void {
    $klant = Klant::query()->create([
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Teststraat 1, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'lisa@tiko.nl',
    ]);

    $this->actingAs(klantBeheerder())
        ->put(route('klanten.update', $klant), klantPayload())
        ->assertRedirect()
        ->assertSessionHas('status', 'Er is niks gewijzigd');

    $this->assertDatabaseHas('klanten', [
        'id' => $klant->id,
        'telefoonnummer' => '0612345678',
    ]);
});

it('weigert een klant met hetzelfde adres en e-mailadres', function (): void {
    Klant::query()->create([
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Dubbelstraat 10, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'dubbel@example.com',
    ]);

    $this->actingAs(klantBeheerder())
        ->post(route('klanten.store'), klantPayload([
            'adres' => 'Dubbelstraat 10, 1234 AB Rotterdam',
            'email' => 'dubbel@example.com',
        ]))
        ->assertSessionHas('error', 'Er bestaat al een klant met dit adres en e-mailadres');
});

it('weigert wijzigingen als naam leeg is', function (): void {
    $klant = Klant::query()->create([
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Teststraat 1, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'lisa@tiko.nl',
    ]);

    $this->actingAs(klantBeheerder())
        ->put(route('klanten.update', $klant), klantPayload(['naam' => '']))
        ->assertSessionHasErrors(['naam' => 'Naam is een verplicht veld en mag niet leeg zijn']);
});

it('verwijdert een klant definitief', function (): void {
    $klant = Klant::query()->create([
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Teststraat 1, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'lisa@tiko.nl',
        'is_actief' => false,
    ]);

    $this->actingAs(klantBeheerder(User::ROLE_OWNER))
        ->delete(route('klanten.destroy', $klant))
        ->assertRedirect(route('klanten.index'))
        ->assertSessionHas('status', 'Klant met succes verwijderd');

    $this->assertDatabaseMissing('klanten', [
        'id' => $klant->id,
    ]);
});

it('blokkeert verwijderen als een klant een afspraak heeft', function (): void {
    $klant = Klant::query()->create([
        'voornaam' => 'Lisa',
        'achternaam' => 'Tiko',
        'adres' => 'Teststraat 1, 1234 AB Rotterdam',
        'telefoonnummer' => '0612345678',
        'email' => 'lisa-afspraak@tiko.nl',
        'is_actief' => false,
    ]);
    $medewerkerId = DB::table('medewerkers')->insertGetId([
        'name' => 'Noor Smit',
        'voornaam' => 'Noor',
        'achternaam' => 'Smit',
        'email' => 'noor-test@example.com',
        'role' => 'medewerker',
        'functie' => 'Kapper',
        'is_active' => true,
        'is_actief' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('afspraken')->insert([
        'klant_id' => $klant->id,
        'medewerker_id' => $medewerkerId,
        'datum' => now()->addDay()->toDateString(),
        'starttijd' => '10:00:00',
        'eindtijd' => '10:45:00',
        'status' => 'Gepland',
        'is_actief' => true,
    ]);

    $this->actingAs(klantBeheerder())
        ->delete(route('klanten.destroy', $klant))
        ->assertSessionHas('error', 'Deze klant kan niet worden verwijderd omdat er afspraken aan gekoppeld zijn');

    $this->assertDatabaseHas('klanten', ['id' => $klant->id]);
});

it('toont klantdetails met wensen allergieen en historie', function (): void {
    $klant = Klant::query()->where('email', 'lisa.tiko@example.com')->firstOrFail();

    $this->actingAs(klantBeheerder())
        ->get(route('klanten.show', $klant))
        ->assertOk()
        ->assertSee('Klantgegevens')
        ->assertSee('Wensen en allergieën')
        ->assertSee('Historie behandelingen en producten');
});

it('staat klantbeheer niet toe voor ingelogde klanten', function (): void {
    $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

    $this->actingAs($customer)
        ->get(route('klanten.index'))
        ->assertForbidden();
});
