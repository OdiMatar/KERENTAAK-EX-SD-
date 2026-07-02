<?php

namespace Database\Seeders;

use App\Models\Bestelling;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BestellingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shampoo = $this->product('Repair Shampoo 250ml', 12.95, 25);
        $wax = $this->product('Matte Wax 100ml', 9.95, 40);
        $haarmasker = $this->product('Haarmasker 200ml', 14.95, 15);

        $fatima = Bestelling::query()->updateOrCreate(
            ['klant_naam' => 'Fatima Jansen', 'orderdatum' => '2026-07-01'],
            [
                'verwachte_leverdatum' => '2026-07-04',
                'status' => 'Nieuw',
                'totaalprijs' => 22.90,
                'opmerking' => 'Klant haalt bestelling op in de salon',
                'is_actief' => true,
            ],
        );

        $youssef = Bestelling::query()->updateOrCreate(
            ['klant_naam' => 'Youssef El Amrani', 'orderdatum' => '2026-07-02'],
            [
                'verwachte_leverdatum' => '2026-07-05',
                'status' => 'Verwerkt',
                'totaalprijs' => 14.95,
                'opmerking' => null,
                'is_actief' => true,
            ],
        );

        $this->regel($fatima, $shampoo, 1);
        $this->regel($fatima, $wax, 1);
        $this->regel($youssef, $haarmasker, 1);
    }

    private function regel(Bestelling $bestelling, object $product, int $aantal): void
    {
        DB::table('bestelregels')->updateOrInsert(
            ['bestelling_id' => $bestelling->id, 'product_id' => $product->id],
            [
                'aantal' => $aantal,
                'prijs_per_stuk' => $product->prijs,
                'subtotaal' => $product->prijs * $aantal,
            ],
        );
    }

    private function product(string $naam, float $prijs, int $voorraad): object
    {
        $info = match ($naam) {
            'Repair Shampoo 250ml' => ['categorie' => 'shampoo', 'ean_code' => '8710000000001', 'leverancier' => 'HairCare Supplies'],
            'Matte Wax 100ml' => ['categorie' => 'styling', 'ean_code' => '8710000000002', 'leverancier' => 'StylePro'],
            'Haarmasker 200ml' => ['categorie' => 'conditioner', 'ean_code' => '8710000000003', 'leverancier' => 'Salon Groothandel'],
            default => ['categorie' => 'overig', 'ean_code' => '871'.str_pad((string) abs(crc32($naam)), 10, '0', STR_PAD_LEFT), 'leverancier' => 'Kniploket Tiko'],
        };

        $data = [
            'naam' => $naam,
            'categorie' => $info['categorie'],
            'ean_code' => $info['ean_code'],
            'prijs' => $prijs,
            'voorraad' => $voorraad,
            'leverancier' => $info['leverancier'],
            'is_actief' => true,
        ];

        if (Schema::hasColumn('products', 'categorie_id')) {
            DB::table('categories')->updateOrInsert(
                ['naam' => 'Algemeen'],
                ['omschrijving' => 'Standaardcategorie voor producten', 'is_actief' => true],
            );

            $data['categorie_id'] = DB::table('categories')->where('naam', 'Algemeen')->value('id');
        }

        if (Schema::hasColumn('products', 'leverancier_id')) {
            DB::table('leveranciers')->updateOrInsert(
                ['naam' => 'Kniploket Tiko'],
                ['contactpersoon' => 'Lisa Jansen', 'email' => 'info@kniplokettiko.nl', 'is_actief' => true],
            );

            $data['leverancier_id'] = DB::table('leveranciers')->where('naam', 'Kniploket Tiko')->value('id');
        }

        DB::table('products')->updateOrInsert(['naam' => $naam], $data);

        return DB::table('products')->where('naam', $naam)->first();
    }
}
