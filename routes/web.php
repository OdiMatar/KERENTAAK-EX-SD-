<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BestellingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedewerkerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/registreren', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registreren', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/inloggen', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/inloggen', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('medewerkers', MedewerkerController::class)->except(['show'])->names('medewerkers');
    Route::get('/bestellingen', [BestellingController::class, 'index'])->name('bestellingen.index');
    Route::get('/bestellingen/toevoegen', [BestellingController::class, 'create'])->name('bestellingen.create');
    Route::post('/bestellingen', [BestellingController::class, 'store'])->name('bestellingen.store');
    Route::get('/bestellingen/{bestelling}', [BestellingController::class, 'show'])->name('bestellingen.show');
    Route::get('/bestellingen/{bestelling}/wijzigen', [BestellingController::class, 'edit'])->name('bestellingen.edit');
    Route::put('/bestellingen/{bestelling}', [BestellingController::class, 'update'])->name('bestellingen.update');
    Route::delete('/bestellingen/{bestelling}', [BestellingController::class, 'destroy'])->name('bestellingen.destroy');
    Route::post('/bestellingen/{bestelling}/regels', [BestellingController::class, 'storeRegel'])->name('bestellingen.regels.store');
    Route::put('/bestellingen/{bestelling}/regels/{bestelregel}', [BestellingController::class, 'updateRegel'])->name('bestellingen.regels.update');
    Route::delete('/bestellingen/{bestelling}/regels/{bestelregel}', [BestellingController::class, 'destroyRegel'])->name('bestellingen.regels.destroy');
    Route::get('/bestellingen/{bestelling}/producten/toevoegen', [BestellingController::class, 'createProduct'])->name('bestellingen.producten.create');
    Route::post('/bestellingen/{bestelling}/producten', [BestellingController::class, 'storeProduct'])->name('bestellingen.producten.store');
    Route::get('/bestellingen/{bestelling}/producten/{product}/wijzigen', [BestellingController::class, 'editProduct'])->name('bestellingen.producten.edit');
    Route::put('/bestellingen/{bestelling}/producten/{product}', [BestellingController::class, 'updateProduct'])->name('bestellingen.producten.update');
    Route::delete('/bestellingen/{bestelling}/producten/{product}', [BestellingController::class, 'destroyProduct'])->name('bestellingen.producten.destroy');
    Route::get('/afspraken', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/afspraken/nieuw', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/afspraken', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/afspraken/{appointment}/wijzigen', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/afspraken/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::patch('/afspraken/{appointment}/annuleren', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/profiel', function () {
        return view('profile.show');
    })->name('profile');
    Route::post('/uitloggen', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
