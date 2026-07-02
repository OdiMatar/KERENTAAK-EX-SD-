<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = [
            [
                'name' => 'Lisa Jansen',
                'email' => 'eigenaar@kniplokettiko.nl',
                'role' => 'eigenaar',
            ],
            [
                'name' => 'Mila de Vries',
                'email' => 'medewerker@kniplokettiko.nl',
                'role' => 'medewerker',
            ],
            [
                'name' => 'Sanne Bakker',
                'email' => 'klant@kniplokettiko.nl',
                'role' => 'klant',
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('Welkom123'),
                    'role' => $user['role'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->whereIn('email', [
                'eigenaar@kniplokettiko.nl',
                'medewerker@kniplokettiko.nl',
                'klant@kniplokettiko.nl',
            ])
            ->delete();
    }
};
