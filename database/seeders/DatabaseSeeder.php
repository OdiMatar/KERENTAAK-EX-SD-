<?php

namespace Database\Seeders;

use App\Models\Bestelling;
use App\Models\Medewerker;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Lisa Jansen',
                'email' => 'eigenaar@kniplokettiko.nl',
                'role' => User::ROLE_OWNER,
            ],
            [
                'name' => 'Mila de Vries',
                'email' => 'medewerker@kniplokettiko.nl',
                'role' => User::ROLE_EMPLOYEE,
            ],
            [
                'name' => 'Sanne Bakker',
                'email' => 'klant@kniplokettiko.nl',
                'role' => User::ROLE_CUSTOMER,
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('Welkom123'),
                    'role' => $user['role'],
                ],
            );
        }

        Medewerker::query()->updateOrCreate(
            ['email' => 'mila@example.com'],
            [
                'name' => 'Mila de Vries',
                'role' => 'medewerker',
                'phone' => '0612345678',
            ],
        );

        Medewerker::query()->updateOrCreate(
            ['email' => 'daan@example.com'],
            [
                'name' => 'Daan Smit',
                'role' => 'medewerker',
                'phone' => '0687654321',
            ],
        );

        Medewerker::query()->updateOrCreate(
            ['email' => 'sara@example.com'],
            [
                'name' => 'Sara de Groot',
                'role' => 'medewerker',
                'phone' => '0611223344',
            ],
        );

        Medewerker::query()->updateOrCreate(
            ['email' => 'tim@example.com'],
            [
                'name' => 'Tim van den Berg',
                'role' => 'medewerker',
                'phone' => '0681122334',
            ],
        );

        Medewerker::query()->updateOrCreate(
            ['email' => 'julia@example.com'],
            [
                'name' => 'Julia Visser',
                'role' => Medewerker::ROLE_INTERN,
                'phone' => '0619988776',
            ],
        );

        Medewerker::query()->updateOrCreate(
            ['email' => 'noah@example.com'],
            [
                'name' => 'Noah de Wit',
                'role' => Medewerker::ROLE_INTERN,
                'phone' => '0613344556',
            ],
        );

        $bestellingen = [
            [
                'klant_naam' => 'Sanne Bakker',
                'orderdatum' => now()->subDays(2)->toDateString(),
                'verwachte_leverdatum' => now()->addDays(3)->toDateString(),
                'status' => 'Nieuw',
                'totaalprijs' => 0,
                'opmerking' => 'Behandelingen voor klant.',
                'is_actief' => true,
            ],
            [
                'klant_naam' => 'Lisa Jansen',
                'orderdatum' => now()->subDays(5)->toDateString(),
                'verwachte_leverdatum' => now()->subDays(1)->toDateString(),
                'status' => 'In behandeling',
                'totaalprijs' => 0,
                'opmerking' => 'Reeds bevestigd.',
                'is_actief' => true,
            ],
            [
                'klant_naam' => 'Daan Smit',
                'orderdatum' => now()->subDays(10)->toDateString(),
                'verwachte_leverdatum' => now()->addDays(1)->toDateString(),
                'status' => 'Afgerond',
                'totaalprijs' => 0,
                'opmerking' => 'Voltooide order.',
                'is_actief' => true,
            ],
        ];

        $products = [
            [
                'naam' => 'Knipbeurt basis',
                'categorie' => 'Behandeling',
                'ean_code' => '8712345678901',
                'prijs' => 45.00,
                'voorraad' => 20,
                'leverancier' => 'KnipTako',
            ],
            [
                'naam' => 'Knipbeurt premium',
                'categorie' => 'Behandeling',
                'ean_code' => '8712345678902',
                'prijs' => 75.00,
                'voorraad' => 12,
                'leverancier' => 'KnipTako',
            ],
            [
                'naam' => 'Kleurbehandeling',
                'categorie' => 'Service',
                'ean_code' => '8712345678903',
                'prijs' => 60.00,
                'voorraad' => 8,
                'leverancier' => 'KnipTako',
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['ean_code' => $product['ean_code']],
                [
                    ...$product,
                    'is_actief' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        foreach ($bestellingen as $index => $bestellingData) {
            $bestelling = Bestelling::query()->updateOrCreate(
                [
                    'klant_naam' => $bestellingData['klant_naam'],
                    'orderdatum' => $bestellingData['orderdatum'],
                ],
                $bestellingData,
            );

            $product = DB::table('products')->where('naam', $products[$index % count($products)]['naam'])->first();

            if ($product) {
                DB::table('bestelregels')->insertOrIgnore([
                    'bestelling_id' => $bestelling->id,
                    'product_id' => $product->id,
                    'aantal' => $index + 1,
                    'prijs_per_stuk' => $product->prijs,
                    'subtotaal' => $product->prijs * ($index + 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach (Bestelling::query()->get() as $bestelling) {
            $bestelling->updateTotaalprijs();
        }

        $this->loadAppointmentStoredProcedures();
        DB::statement('CALL sp_seed_appointment_basisdata()');
    }

    private function loadAppointmentStoredProcedures(): void
    {
        $sql = file_get_contents(database_path('stored-procedures/appointments_procedures.sql'));

        if ($sql === false) {
            return;
        }

        [$beforeDelimiter, $afterDelimiter] = explode('DELIMITER //', $sql, 2);
        [$procedures, $afterProcedures] = explode('DELIMITER ;', $afterDelimiter, 2);

        foreach (array_filter(array_map('trim', explode(';', $beforeDelimiter))) as $statement) {
            DB::unprepared($statement);
        }

        foreach (array_filter(array_map('trim', preg_split('/\s*\/\/\s*/', $procedures))) as $statement) {
            DB::unprepared($statement);
        }

        foreach (array_filter(array_map('trim', explode(';', $afterProcedures))) as $statement) {
            if ($statement !== 'CALL sp_seed_appointment_basisdata()') {
                DB::unprepared($statement);
            }
        }
    }
}
