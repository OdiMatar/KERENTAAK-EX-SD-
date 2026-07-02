<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
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
