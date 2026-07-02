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
        $producten = [
            'Repair Shampoo 250ml' => $this->product('Repair Shampoo 250ml', 12.95, 25),
            'Matte Wax 100ml' => $this->product('Matte Wax 100ml', 9.95, 40),
            'Haarmasker 200ml' => $this->product('Haarmasker 200ml', 14.95, 15),
            'Volume Conditioner 250ml' => $this->product('Volume Conditioner 250ml', 11.95, 18),
            'Heat Protection Spray 150ml' => $this->product('Heat Protection Spray 150ml', 13.50, 22),
            'Silver Shampoo 250ml' => $this->product('Silver Shampoo 250ml', 12.50, 20),
        ];

        $bestellingen = [
            [
                'klant_naam' => 'Fatima Jansen',
                'orderdatum' => '2026-07-01',
                'verwachte_leverdatum' => '2026-07-04',
                'status' => 'Nieuw',
                'opmerking' => 'Klant haalt bestelling op in de salon',
                'regels' => [
                    'Repair Shampoo 250ml' => 1,
                    'Matte Wax 100ml' => 1,
                    'Volume Conditioner 250ml' => 2,
                    'Heat Protection Spray 150ml' => 1,
                    'Silver Shampoo 250ml' => 1,
                ],
            ],
            [
                'klant_naam' => 'Youssef El Amrani',
                'orderdatum' => '2026-07-02',
                'verwachte_leverdatum' => '2026-07-05',
                'status' => 'Verwerkt',
                'opmerking' => null,
                'regels' => [
                    'Haarmasker 200ml' => 1,
                    'Repair Shampoo 250ml' => 2,
                    'Matte Wax 100ml' => 1,
                    'Volume Conditioner 250ml' => 1,
                    'Heat Protection Spray 150ml' => 1,
                ],
            ],
            [
                'klant_naam' => 'Sanne Bakker',
                'orderdatum' => '2026-07-03',
                'verwachte_leverdatum' => '2026-07-06',
                'status' => 'Nieuw',
                'opmerking' => 'Betaalt bij afhalen.',
                'regels' => [
                    'Repair Shampoo 250ml' => 1,
                    'Haarmasker 200ml' => 1,
                    'Volume Conditioner 250ml' => 1,
                    'Heat Protection Spray 150ml' => 2,
                    'Silver Shampoo 250ml' => 1,
                ],
            ],
            [
                'klant_naam' => 'Mila de Vries',
                'orderdatum' => '2026-07-04',
                'verwachte_leverdatum' => '2026-07-07',
                'status' => 'In behandeling',
                'opmerking' => 'Levering controleren met leverancier.',
                'regels' => [
                    'Matte Wax 100ml' => 2,
                    'Haarmasker 200ml' => 1,
                    'Volume Conditioner 250ml' => 1,
                    'Heat Protection Spray 150ml' => 1,
                    'Silver Shampoo 250ml' => 2,
                ],
            ],
            [
                'klant_naam' => 'Daan Smit',
                'orderdatum' => '2026-07-05',
                'verwachte_leverdatum' => '2026-07-08',
                'status' => 'Afgerond',
                'opmerking' => 'Voorraad direct bijgewerkt.',
                'regels' => [
                    'Repair Shampoo 250ml' => 1,
                    'Matte Wax 100ml' => 1,
                    'Haarmasker 200ml' => 2,
                    'Heat Protection Spray 150ml' => 1,
                    'Silver Shampoo 250ml' => 1,
                ],
            ],
        ];

        foreach ($bestellingen as $bestellingData) {
            $bestelling = Bestelling::query()->updateOrCreate(
                [
                    'klant_naam' => $bestellingData['klant_naam'],
                    'orderdatum' => $bestellingData['orderdatum'],
                ],
                [
                    'verwachte_leverdatum' => $bestellingData['verwachte_leverdatum'],
                    'status' => $bestellingData['status'],
                    'totaalprijs' => 0,
                    'opmerking' => $bestellingData['opmerking'],
                    'is_actief' => true,
                ],
            );

            foreach ($bestellingData['regels'] as $productNaam => $aantal) {
                $this->regel($bestelling, $producten[$productNaam], $aantal);
            }

            $bestelling->updateTotaalprijs();
        }
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
            'Volume Conditioner 250ml' => ['categorie' => 'conditioner', 'ean_code' => '8710000000004', 'leverancier' => 'HairCare Supplies'],
            'Heat Protection Spray 150ml' => ['categorie' => 'styling', 'ean_code' => '8710000000005', 'leverancier' => 'StylePro'],
            'Silver Shampoo 250ml' => ['categorie' => 'shampoo', 'ean_code' => '8710000000006', 'leverancier' => 'Salon Groothandel'],
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
