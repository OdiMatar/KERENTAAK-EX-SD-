<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('toont gasten homepage inloggen en registreren in de navbar', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Homepage')
        ->assertSee('Inloggen')
        ->assertSee('Registreren')
        ->assertDontSee('Profiel')
        ->assertDontSee('Uitloggen');
});

it('toont ingelogde gebruikers homepage profiel en uitloggen in de navbar', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Homepage')
        ->assertSee('Profiel')
        ->assertSee('Uitloggen')
        ->assertDontSee('Inloggen')
        ->assertDontSee('Registreren');
});

it('toont de profielpagina voor ingelogde gebruikers', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile'))
        ->assertOk()
        ->assertSee('Profiel')
        ->assertSee(e($user->name), false)
        ->assertSee($user->email);
});

it('toont meldingen als bootstrap alerts die automatisch verdwijnen', function (): void {
    $this->withSession(['status' => 'Opgeslagen.'])
        ->get(route('home'))
        ->assertOk()
        ->assertSee('alert alert-success', false)
        ->assertSee('auto-dismiss', false)
        ->assertSee('Opgeslagen.');
});
