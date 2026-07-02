<?php

use App\Models\User;
use App\Models\Medewerker;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the employee overview for authenticated users', function () {
    $owner = User::factory()->create([
        'name' => 'Lisa Jansen',
        'email' => 'owner@example.com',
        'role' => User::ROLE_OWNER,
    ]);

    Medewerker::create([
        'name' => 'Mila de Vries',
        'email' => 'mila@example.com',
        'role' => Medewerker::ROLE_EMPLOYEE,
        'phone' => '0612345678',
    ]);

    $this->actingAs($owner);

    $response = $this->get(route('medewerkers.index'));

    $response->assertOk()
        ->assertSee('Overzicht medewerkers')
        ->assertSee('Mila de Vries')
        ->assertSee('mila@example.com')
        ->assertSee('0612345678');
});
