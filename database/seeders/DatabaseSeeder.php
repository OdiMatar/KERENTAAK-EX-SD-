<?php

namespace Database\Seeders;

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

        $this->call(BestellingSeeder::class);

        foreach ($this->klanten() as $klant) {
            DB::table('klanten')->updateOrInsert(
                ['email' => $klant['email']],
                [
                    ...$klant,
                    'is_actief' => true,
                    'datum_aangemaakt' => now(),
                    'datum_gewijzigd' => now(),
                ],
            );
        }

        $this->loadAppointmentStoredProcedures();
        DB::statement('CALL sp_seed_appointment_basisdata()');
        $this->seedAppointmentOverview();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function klanten(): array
    {
        return [
            [
                'voornaam' => 'Lisa',
                'achternaam' => 'Tiko',
                'adres' => 'Kappersstraat 12, Rotterdam',
                'telefoonnummer' => '0612345678',
                'email' => 'lisa.tiko@example.com',
            ],
            [
                'voornaam' => 'Sanne',
                'achternaam' => 'Bakker',
                'adres' => 'Salonplein 8, Den Haag',
                'telefoonnummer' => '0687654321',
                'email' => 'sanne.bakker@example.com',
            ],
            [
                'voornaam' => 'Mila',
                'achternaam' => 'Jansen',
                'adres' => 'Kniplaan 3, Utrecht',
                'telefoonnummer' => '0611223344',
                'email' => 'mila.jansen@example.com',
            ],
        ];
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

    private function seedAppointmentOverview(): void
    {
        $customerUserId = User::query()
            ->where('email', 'klant@kniplokettiko.nl')
            ->value('id');

        if (! $customerUserId) {
            return;
        }

        $customerResult = DB::selectOne('CALL sp_ensure_customer_for_user(?)', [$customerUserId]);
        $customerId = (int) $customerResult->customer_id;

        $appointments = [
            [
                'employee_email' => 'yassin.attiah@kniplokettiko.nl',
                'treatment_name' => 'Knippen',
                'date' => now()->addDays(3)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '10:45:00',
            ],
            [
                'employee_email' => 'sara.bakker@kniplokettiko.nl',
                'treatment_name' => 'Kleuren',
                'date' => now()->addDays(5)->toDateString(),
                'start_time' => '11:00:00',
                'end_time' => '12:30:00',
            ],
            [
                'employee_email' => 'mohammad.abdullah@kniplokettiko.nl',
                'treatment_name' => 'Stylen',
                'date' => now()->addDays(7)->toDateString(),
                'start_time' => '13:00:00',
                'end_time' => '13:30:00',
            ],
            [
                'employee_email' => 'amina.elidrissi@kniplokettiko.nl',
                'treatment_name' => 'Extensions',
                'date' => now()->addDays(10)->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
            ],
        ];

        foreach ($appointments as $appointmentData) {
            $employeeId = DB::table('medewerkers')
                ->where('email', $appointmentData['employee_email'])
                ->value('id');
            $treatment = DB::table('behandelingen')
                ->where('naam', $appointmentData['treatment_name'])
                ->first();

            if (! $employeeId || ! $treatment) {
                continue;
            }

            $appointmentId = DB::table('afspraken')->updateOrInsert(
                [
                    'klant_id' => $customerId,
                    'datum' => $appointmentData['date'],
                    'starttijd' => $appointmentData['start_time'],
                ],
                [
                    'medewerker_id' => $employeeId,
                    'eindtijd' => $appointmentData['end_time'],
                    'status' => 'Gepland',
                    'is_actief' => true,
                    'opmerking' => 'Voorbeeldafspraak voor het afsprakenoverzicht.',
                    'datum_aangemaakt' => now(),
                    'datum_gewijzigd' => now(),
                ],
            );

            $appointmentId = DB::table('afspraken')
                ->where('klant_id', $customerId)
                ->where('datum', $appointmentData['date'])
                ->where('starttijd', $appointmentData['start_time'])
                ->value('id');

            DB::table('afspraak_behandeling')->updateOrInsert(
                [
                    'afspraak_id' => $appointmentId,
                    'behandeling_id' => $treatment->id,
                ],
                [
                    'prijs_op_moment' => $treatment->prijs,
                    'duur_op_moment' => $treatment->duur,
                ],
            );
        }
    }
}
