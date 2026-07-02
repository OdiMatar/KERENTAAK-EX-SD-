<?php

use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(ProductSeeder::class);
});

function productUser(string $role = User::ROLE_OWNER): User
{
    return User::factory()->create(['role' => $role]);
}

function productPayload(array $overrides = []): array
{
    return [
        'naam' => 'Volume Mousse',
        'barcode' => '871000000099',
        'prijs' => '11.95',
        'voorraad' => '12',
        'houdbaarheidsdatum' => null,
        'omschrijving' => 'Mousse voor extra volume',
        'opmerking' => null,
        ...$overrides,
    ];
}

it('toont een overzicht met alle productgegevens als er producten zijn', function (): void {
    $this->actingAs(productUser())
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Producten');

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('Repair Shampoo 250ml')
        ->assertSee('871000000001')
        ->assertSee('Voorraad')
        ->assertSee('Wijzigen')
        ->assertSee('Verwijderen');
});

it('toont een melding als er geen producten beschikbaar zijn', function (): void {
    DB::table('products')->delete();

    $this->actingAs(productUser())
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('Er zijn geen producten beschikbaar.');
});

it('voegt een nieuw product toe', function (): void {
    $this->actingAs(productUser())
        ->post(route('products.store'), productPayload())
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('status', 'Product is toegevoegd.');

    $this->assertDatabaseHas('products', [
        'naam' => 'Volume Mousse',
        'barcode' => '871000000099',
        'is_actief' => true,
    ]);
});

it('toont een foutmelding als het product al bestaat', function (): void {
    $this->actingAs(productUser())
        ->post(route('products.store'), productPayload([
            'naam' => 'Repair Shampoo 250ml',
            'barcode' => '871000000001',
        ]))
        ->assertSessionHas('error', 'Product is niet toegevoegd.');
});

it('wijzigt productgegevens', function (): void {
    $productId = DB::table('products')->where('barcode', '871000000001')->value('id');

    $this->actingAs(productUser())
        ->put(route('products.update', $productId), productPayload([
            'naam' => 'Repair Shampoo 500ml',
            'barcode' => '871000000001',
            'prijs' => '18.95',
            'voorraad' => '30',
        ]))
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('status', 'Product is gewijzigd.');

    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'naam' => 'Repair Shampoo 500ml',
        'voorraad' => 30,
    ]);
});

it('toont een foutmelding als er geen nieuwe gegevens zijn ingevuld', function (): void {
    $product = DB::table('products')->where('barcode', '871000000001')->first();

    $this->actingAs(productUser())
        ->put(route('products.update', $product->id), productPayload([
            'naam' => $product->naam,
            'barcode' => $product->barcode,
            'prijs' => (string) $product->prijs,
            'voorraad' => (string) $product->voorraad,
            'omschrijving' => $product->omschrijving,
            'opmerking' => $product->opmerking,
        ]))
        ->assertSessionHas('error', 'Product is niet gewijzigd.');
});

it('verwijdert een product', function (): void {
    $productId = DB::table('products')->where('barcode', '871000000001')->value('id');

    $this->actingAs(productUser())
        ->delete(route('products.destroy', $productId))
        ->assertSessionHas('status', 'Product is verwijderd.');

    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'is_actief' => false,
    ]);
});

it('toont een melding als een product al verwijderd was', function (): void {
    $productId = DB::table('products')->where('barcode', '871000000001')->value('id');
    DB::table('products')->where('id', $productId)->update(['is_actief' => false]);

    $this->actingAs(productUser())
        ->delete(route('products.destroy', $productId))
        ->assertSessionHas('error', 'Product was al verwijderd.');
});
