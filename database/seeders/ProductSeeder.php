<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->updateOrInsert(
            ['naam' => 'Algemeen'],
            [
                'omschrijving' => 'Standaardcategorie voor producten',
                'is_actief' => true,
                'datum_gewijzigd' => now(),
            ],
        );

        DB::table('leveranciers')->updateOrInsert(
            ['naam' => 'Kniploket Tiko'],
            [
                'contactpersoon' => 'Lisa Jansen',
                'email' => 'info@kniplokettiko.nl',
                'is_actief' => true,
                'datum_gewijzigd' => now(),
            ],
        );

        $categoryId = DB::table('categories')->where('naam', 'Algemeen')->value('id');
        $supplierId = DB::table('leveranciers')->where('naam', 'Kniploket Tiko')->value('id');

        DB::table('products')->updateOrInsert(
            ['barcode' => '871000000001'],
            [
                'categorie_id' => $categoryId,
                'leverancier_id' => $supplierId,
                'naam' => 'Repair Shampoo 250ml',
                'prijs' => 12.95,
                'voorraad' => 25,
                'omschrijving' => 'Shampoo voor beschadigd haar',
                'status' => 'Beschikbaar',
                'is_actief' => true,
                'datum_gewijzigd' => now(),
            ],
        );

        DB::table('products')->updateOrInsert(
            ['barcode' => '871000000002'],
            [
                'categorie_id' => $categoryId,
                'leverancier_id' => $supplierId,
                'naam' => 'Matte Wax 100ml',
                'prijs' => 9.95,
                'voorraad' => 40,
                'omschrijving' => 'Wax met matte finish',
                'status' => 'Beschikbaar',
                'is_actief' => true,
                'datum_gewijzigd' => now(),
            ],
        );
    }
}
