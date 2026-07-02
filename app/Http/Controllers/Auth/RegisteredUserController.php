<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreRegisteredUserRequest;
use App\Models\User;
use App\Services\TechnicalLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(StoreRegisteredUserRequest $request, TechnicalLogger $technicalLogger): RedirectResponse
    {
        try {
            $result = DB::selectOne('CALL sp_register_user(?, ?, ?)', [
                $request->string('name')->toString(),
                $request->string('email')->toString(),
                Hash::make($request->string('password')->toString()),
            ]);

            $user = User::query()->findOrFail($result->user_id);

            Auth::login($user);
            $request->session()->regenerate();

            $technicalLogger->record('register', 'Nieuwe gebruiker geregistreerd.', $user->id, [
                'email' => $user->email,
            ]);

            return redirect()
                ->route('dashboard')
                ->with('status', 'Je account is aangemaakt en je bent ingelogd.');
        } catch (Throwable $exception) {
            Log::error('Registratie mislukt.', [
                'email' => $request->string('email')->toString(),
                'exception' => $exception,
            ]);

            return back()
                ->withInput($request->safe()->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Registreren is niet gelukt. Probeer het opnieuw.']);
        }
    }
}
