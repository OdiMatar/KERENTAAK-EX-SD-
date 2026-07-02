<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klanten', function (Blueprint $table): void {
            if (! Schema::hasColumn('klanten', 'adres')) {
                $table->string('adres')->nullable()->after('email');
            }

            if (! Schema::hasColumn('klanten', 'opmerking')) {
                $table->string('opmerking')->nullable()->after('is_actief');
            }
        });

        if (! Schema::hasTable('wens_allergies')) {
            Schema::create('wens_allergies', function (Blueprint $table): void {
                $table->id();
                $table->string('type', 20);
                $table->string('beschrijving');
                $table->boolean('is_actief')->default(true);
                $table->timestamp('datum_aangemaakt')->nullable();
                $table->timestamp('datum_gewijzigd')->nullable();
            });
        }

        if (! Schema::hasTable('klant_wensen')) {
            Schema::create('klant_wensen', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('klant_id')->constrained('klanten')->cascadeOnDelete();
                $table->foreignId('wens_allergie_id')->constrained('wens_allergies')->restrictOnDelete();
                $table->string('opmerking')->nullable();
                $table->timestamp('datum_aangemaakt')->nullable();
                $table->unique(['klant_id', 'wens_allergie_id']);
            });
        }

        Schema::table('bestellingen', function (Blueprint $table): void {
            if (! Schema::hasColumn('bestellingen', 'klant_id')) {
                $table->foreignId('klant_id')->nullable()->after('id')->constrained('klanten')->restrictOnDelete();
            }

            if (! Schema::hasColumn('bestellingen', 'klant_naam')) {
                $table->string('klant_naam', 150)->nullable()->after('klant_id');
            }

            if (! Schema::hasColumn('bestellingen', 'orderdatum')) {
                $table->date('orderdatum')->nullable()->after('klant_naam');
            }
        });

        DB::table('klanten')->whereNull('adres')->update(['adres' => 'Onbekend adres']);

        $this->loadCustomerStoredProcedures();
        $this->seedCustomerDetails();
        $this->koppelBestellingenAanKlanten();
        $this->seedAppointmentsForCustomers();
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_customer_history');
        }
    }

    private function loadCustomerStoredProcedures(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $sql = file_get_contents(database_path('stored-procedures/customers_procedures.sql'));

        if ($sql === false) {
            return;
        }

        [$beforeDelimiter, $afterDelimiter] = explode('DELIMITER //', $sql, 2);
        [$procedures] = explode('DELIMITER ;', $afterDelimiter, 2);

        foreach (array_filter(array_map('trim', explode(';', $beforeDelimiter))) as $statement) {
            DB::unprepared($statement);
        }

        foreach (array_filter(array_map('trim', preg_split('/\s*\/\/\s*/', $procedures))) as $statement) {
            DB::unprepared($statement);
        }
    }

    private function seedCustomerDetails(): void
    {
        $customers = [
            'lisa.tiko@example.com' => [
                'adres' => 'Kappersstraat 12, Rotterdam',
                'wens' => 'Altijd dezelfde kapper indien mogelijk',
                'allergie' => 'Parfumvrije haarproducten',
            ],
            'sanne.bakker@example.com' => [
                'adres' => 'Salonplein 8, Den Haag',
                'wens' => 'Rustige plek bij het raam',
                'allergie' => 'Latex',
            ],
            'mila.jansen@example.com' => [
                'adres' => 'Kniplaan 3, Utrecht',
                'wens' => 'Geen stylinggel',
                'allergie' => 'Geen bekende allergieen',
            ],
        ];

        foreach ($customers as $email => $details) {
            DB::table('klanten')->where('email', $email)->update(['adres' => $details['adres']]);

            $customerId = DB::table('klanten')->where('email', $email)->value('id');

            if (! $customerId) {
                continue;
            }

            foreach (['wens', 'allergie'] as $type) {
                $wishId = DB::table('wens_allergies')->insertGetId([
                    'type' => $type,
                    'beschrijving' => $details[$type],
                    'is_actief' => true,
                    'datum_aangemaakt' => now(),
                    'datum_gewijzigd' => now(),
                ]);

                DB::table('klant_wensen')->updateOrInsert(
                    ['klant_id' => $customerId, 'wens_allergie_id' => $wishId],
                    ['datum_aangemaakt' => now()],
                );
            }
        }
    }

    private function koppelBestellingenAanKlanten(): void
    {
        if (! Schema::hasColumn('bestellingen', 'klant_id')) {
            return;
        }

        if (Schema::hasColumn('bestellingen', 'klant_naam')) {
            DB::table('bestellingen')
                ->join('klanten', 'bestellingen.klant_naam', '=', DB::raw("CONCAT(klanten.voornaam, ' ', klanten.achternaam)"))
                ->whereNull('bestellingen.klant_id')
                ->update(['bestellingen.klant_id' => DB::raw('klanten.id')]);

            DB::table('bestellingen')
                ->join('klanten', 'bestellingen.klant_id', '=', 'klanten.id')
                ->whereNull('bestellingen.klant_naam')
                ->update(['bestellingen.klant_naam' => DB::raw("CONCAT(klanten.voornaam, ' ', klanten.achternaam)")]);
        }

        if (Schema::hasColumn('bestellingen', 'besteldatum') && Schema::hasColumn('bestellingen', 'orderdatum')) {
            DB::table('bestellingen')
                ->whereNull('orderdatum')
                ->update(['orderdatum' => DB::raw('DATE(besteldatum)')]);
        }
    }

    private function seedAppointmentsForCustomers(): void
    {
        if (DB::table('afspraken')->exists()) {
            return;
        }

        $employeeId = DB::table('medewerkers')->value('id');

        if (! $employeeId) {
            $employeeId = DB::table('medewerkers')->insertGetId([
                'name' => 'Noor Smit',
                'voornaam' => 'Noor',
                'achternaam' => 'Smit',
                'email' => 'noor.smit@example.com',
                'role' => 'medewerker',
                'phone' => '0610101010',
                'telefoonnummer' => '0610101010',
                'functie' => 'Kapper',
                'is_active' => true,
                'is_actief' => true,
                'datum_aangemaakt' => now(),
                'datum_gewijzigd' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $treatmentId = DB::table('behandelingen')->value('id');

        if (! $treatmentId) {
            $treatmentId = DB::table('behandelingen')->insertGetId([
                'naam' => 'Knippen',
                'categorie' => 'Haar',
                'duur' => 45,
                'prijs' => 25.00,
                'omschrijving' => 'Knippen en modelleren',
                'is_actief' => true,
                'datum_aangemaakt' => now(),
                'datum_gewijzigd' => now(),
            ]);
        }

        $customerIds = DB::table('klanten')
            ->whereIn('email', ['lisa.tiko@example.com', 'sanne.bakker@example.com'])
            ->pluck('id');

        foreach ($customerIds as $index => $customerId) {
            $appointmentId = DB::table('afspraken')->insertGetId([
                'klant_id' => $customerId,
                'medewerker_id' => $employeeId,
                'datum' => now()->addDays($index + 3)->toDateString(),
                'starttijd' => sprintf('%02d:00:00', 10 + $index),
                'eindtijd' => sprintf('%02d:45:00', 10 + $index),
                'status' => 'Gepland',
                'is_actief' => true,
                'opmerking' => 'Voorbeeldafspraak voor klantbeheer.',
                'datum_aangemaakt' => now(),
                'datum_gewijzigd' => now(),
            ]);

            DB::table('afspraak_behandeling')->insert([
                'afspraak_id' => $appointmentId,
                'behandeling_id' => $treatmentId,
                'prijs_op_moment' => 25.00,
                'duur_op_moment' => 45,
            ]);
        }
    }
};
