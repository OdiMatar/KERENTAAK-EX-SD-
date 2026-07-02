<?php

use App\Models\User;
use Database\Seeders\BestellingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(BestellingSeeder::class);
});

function bestellingUser(string $role = User::ROLE_OWNER): User
{
    return User::factory()->create(['role' => $role]);
}

function bestellingPayload(array $overrides = []): array
{
    $productId = DB::table('products')->where('naam', 'Repair Shampoo 250ml')->value('id');

    return [
        'klant_naam' => 'Sara De Vries',
        'orderdatum' => '2026-07-10',
        'verwachte_leverdatum' => '2026-07-13',
        'status' => 'Nieuw',
        'opmerking' => 'Afhalen in de salon',
        'product_id' => $productId,
        'aantal' => 2,
        ...$overrides,
    ];
}

it('toont een overzicht met alle bestellingen', function (): void {
    $this->actingAs(bestellingUser())
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Bestellingen');

    $this->get(route('bestellingen.index'))
        ->assertOk()
        ->assertSee('Fatima Jansen')
        ->assertSee('Besteldatum')
        ->assertSee('Verwachte leverdatum')
        ->assertSee('Status')
        ->assertSee('Details')
        ->assertDontSee('Wijzigen')
        ->assertSee('Verwijderen')
        ->assertSee("return confirm('Weet je zeker dat je deze bestelling wilt verwijderen?')", false);
});

it('toont een melding als er geen bestellingen beschikbaar zijn', function (): void {
    DB::table('bestellingen')->delete();

    $this->actingAs(bestellingUser())
        ->get(route('bestellingen.index'))
        ->assertOk()
        ->assertSee('Er zijn geen bestellingen beschikbaar.');
});

it('voegt een nieuwe bestelling toe', function (): void {
    $this->actingAs(bestellingUser())
        ->post(route('bestellingen.store'), bestellingPayload())
        ->assertRedirect()
        ->assertSessionHas('status', 'Bestelling is toegevoegd.');

    $this->assertDatabaseHas('bestellingen', [
        'klant_naam' => 'Sara De Vries',
        'status' => 'Nieuw',
        'is_actief' => true,
    ]);
    $this->assertDatabaseHas('bestelregels', [
        'product_id' => DB::table('products')->where('naam', 'Repair Shampoo 250ml')->value('id'),
        'aantal' => 2,
    ]);
});

it('wijzigt een bestelling', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');

    $this->actingAs(bestellingUser())
        ->put(route('bestellingen.update', $bestellingId), bestellingPayload([
            'klant_naam' => 'Fatima Jansen',
            'status' => 'Verwerkt',
        ]))
        ->assertRedirect(route('bestellingen.index'))
        ->assertSessionHas('status', 'Bestelling is gewijzigd.');

    $this->assertDatabaseHas('bestellingen', [
        'id' => $bestellingId,
        'status' => 'Verwerkt',
    ]);
});

it('toont een foutmelding als er geen nieuwe bestelgegevens zijn ingevuld', function (): void {
    $bestelling = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->first();

    $this->actingAs(bestellingUser())
        ->put(route('bestellingen.update', $bestelling->id), bestellingPayload([
            'klant_naam' => $bestelling->klant_naam,
            'orderdatum' => $bestelling->orderdatum,
            'verwachte_leverdatum' => $bestelling->verwachte_leverdatum,
            'status' => $bestelling->status,
            'opmerking' => $bestelling->opmerking,
        ]))
        ->assertSessionHas('error', 'Bestelling is niet gewijzigd.');
});

it('verwijdert een bestelling', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');

    $this->actingAs(bestellingUser())
        ->delete(route('bestellingen.destroy', $bestellingId))
        ->assertSessionHas('status', 'Bestelling is verwijderd.');

    $this->assertDatabaseHas('bestellingen', [
        'id' => $bestellingId,
        'is_actief' => false,
    ]);
});

it('toont een melding als een bestelling al verwijderd was', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');
    DB::table('bestellingen')->where('id', $bestellingId)->update(['is_actief' => false]);

    $this->actingAs(bestellingUser())
        ->delete(route('bestellingen.destroy', $bestellingId))
        ->assertSessionHas('error', 'Bestelling was al verwijderd.');
});

it('toont de bestelde producten in een tabel op de bestelling detailpagina', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');

    $this->actingAs(bestellingUser())
        ->get(route('bestellingen.show', $bestellingId))
        ->assertOk()
        ->assertSee('Product')
        ->assertSee('Aantal')
        ->assertSee('Prijs per stuk')
        ->assertSee('Subtotaal')
        ->assertSee('Categorie')
        ->assertSee('EAN-code')
        ->assertSee('Leverancier')
        ->assertSee('Voorraad')
        ->assertSee('Acties')
        ->assertSee('Nieuwe product aanmaken')
        ->assertSee('Product toevoegen')
        ->assertSee('Opslaan')
        ->assertSee('Product wijzigen')
        ->assertSee('Uit bestelling')
        ->assertSee("return confirm('Weet je zeker dat je dit product uit deze bestelling wilt verwijderen?')", false)
        ->assertSee('Repair Shampoo 250ml')
        ->assertSee('Matte Wax 100ml');
});

it('voegt een nieuw product toe aan de voorraad', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');

    $this->actingAs(bestellingUser())
        ->get(route('bestellingen.producten.create', $bestellingId))
        ->assertOk()
        ->assertSee('Nieuwe product aanmaken')
        ->assertSee('Productnaam')
        ->assertSee('EAN-code')
        ->assertSee('Leverancier');

    $this->post(route('bestellingen.producten.store', $bestellingId), [
        'naam' => 'Color Protect Shampoo 250ml',
        'categorie' => 'shampoo',
        'ean_code' => '8710000000100',
        'prijs' => '11.95',
        'voorraad' => 12,
        'leverancier' => 'HairCare Supplies',
    ])
        ->assertRedirect(route('bestellingen.show', $bestellingId))
        ->assertSessionHas('status', 'Product is toegevoegd.');

    $this->assertDatabaseHas('products', [
        'naam' => 'Color Protect Shampoo 250ml',
        'categorie' => 'shampoo',
        'ean_code' => '8710000000100',
        'prijs' => 11.95,
        'voorraad' => 12,
        'leverancier' => 'HairCare Supplies',
        'is_actief' => true,
    ]);
});

it('voegt geen nieuw product toe als naam of ean-code al bestaat', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');

    $this->actingAs(bestellingUser())
        ->post(route('bestellingen.producten.store', $bestellingId), [
            'naam' => 'Repair Shampoo 250ml',
            'categorie' => 'shampoo',
            'ean_code' => '8710000000001',
            'prijs' => '11.95',
            'voorraad' => 12,
            'leverancier' => 'HairCare Supplies',
        ])
        ->assertSessionHasErrors(['naam', 'ean_code']);

    expect(DB::table('products')->where('naam', 'Repair Shampoo 250ml')->count())->toBe(1);
});

it('voegt een product toe aan een bestaande bestelling', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');
    $productId = DB::table('products')->where('naam', 'Haarmasker 200ml')->value('id');

    $this->actingAs(bestellingUser())
        ->post(route('bestellingen.regels.store', $bestellingId), [
            'product_id' => $productId,
            'aantal' => 3,
        ])
        ->assertSessionHas('status', 'Product is toegevoegd aan de bestelling.');

    $this->assertDatabaseHas('bestelregels', [
        'bestelling_id' => $bestellingId,
        'product_id' => $productId,
        'aantal' => 3,
    ]);
});

it('werkt bestaande bestelregels bij als de productprijs wijzigt', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');
    $productId = DB::table('products')->where('naam', 'Repair Shampoo 250ml')->value('id');

    $this->actingAs(bestellingUser())
        ->put(route('bestellingen.producten.update', [$bestellingId, $productId]), [
            'naam' => 'Repair Shampoo 250ml',
            'categorie' => 'shampoo',
            'ean_code' => '8710000000001',
            'prijs' => '15.95',
            'voorraad' => 25,
            'leverancier' => 'HairCare Supplies',
            'is_actief' => true,
        ])
        ->assertSessionHas('status', 'Product is gewijzigd.');

    $this->assertDatabaseHas('bestelregels', [
        'bestelling_id' => $bestellingId,
        'product_id' => $productId,
        'prijs_per_stuk' => 15.95,
        'subtotaal' => 15.95,
    ]);
});

it('wijzigt het aantal van een product in de bestelling', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');
    $regelId = DB::table('bestelregels')->where('bestelling_id', $bestellingId)->value('id');

    $this->actingAs(bestellingUser())
        ->put(route('bestellingen.regels.update', [$bestellingId, $regelId]), ['aantal' => 4])
        ->assertSessionHas('status', 'Aantal is gewijzigd.');

    $this->assertDatabaseHas('bestelregels', [
        'id' => $regelId,
        'aantal' => 4,
    ]);
});

it('wijzigt alle gegevens van een product vanuit de bestelling', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');
    $productId = DB::table('products')->where('naam', 'Repair Shampoo 250ml')->value('id');

    $this->actingAs(bestellingUser())
        ->get(route('bestellingen.producten.edit', [$bestellingId, $productId]))
        ->assertOk()
        ->assertSee('Productnaam')
        ->assertSee('Categorie')
        ->assertSee('EAN-code')
        ->assertSee('Prijs')
        ->assertSee('Voorraad')
        ->assertSee('Leverancier')
        ->assertSee('Product is actief')
        ->assertSee('Verwijder uit voorraad');

    $this->put(route('bestellingen.producten.update', [$bestellingId, $productId]), [
        'naam' => 'Repair Shampoo 500ml',
        'categorie' => 'shampoo',
        'ean_code' => '8710000000099',
        'prijs' => '14.95',
        'voorraad' => 8,
        'leverancier' => 'HairCare Supplies',
        'is_actief' => true,
    ])
        ->assertRedirect(route('bestellingen.show', $bestellingId))
        ->assertSessionHas('status', 'Product is gewijzigd.');

    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'naam' => 'Repair Shampoo 500ml',
        'categorie' => 'shampoo',
        'ean_code' => '8710000000099',
        'prijs' => 14.95,
        'voorraad' => 8,
        'leverancier' => 'HairCare Supplies',
        'is_actief' => true,
    ]);
});

it('verwijdert een product vanuit de bestelling', function (): void {
    $bestellingId = DB::table('bestellingen')->where('klant_naam', 'Fatima Jansen')->value('id');
    $productId = DB::table('products')->where('naam', 'Repair Shampoo 250ml')->value('id');

    $this->actingAs(bestellingUser())
        ->delete(route('bestellingen.producten.destroy', [$bestellingId, $productId]))
        ->assertRedirect(route('bestellingen.show', $bestellingId))
        ->assertSessionHas('status', 'Product is verwijderd uit de voorraad.');

    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'is_actief' => false,
    ]);
});
