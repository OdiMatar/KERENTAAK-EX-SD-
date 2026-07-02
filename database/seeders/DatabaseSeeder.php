<?php

namespace Database\Seeders;

use App\Models\Medewerker;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
    }
}
