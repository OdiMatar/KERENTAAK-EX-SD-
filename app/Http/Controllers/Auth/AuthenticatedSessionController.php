<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\TechnicalLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, TechnicalLogger $technicalLogger): RedirectResponse
    {
        try {
            if (! Auth::attempt($request->credentials(), $request->boolean('remember'))) {
                $technicalLogger->record('login_failed', 'Inlogpoging mislukt.', null, [
                    'email' => $request->string('email')->toString(),
                ]);

                return back()
                    ->withInput($request->safe()->only('email'))
                    ->withErrors(['email' => 'Deze combinatie van e-mail en wachtwoord is niet bekend.']);
            }

            $request->session()->regenerate();

            $technicalLogger->record('login', 'Gebruiker succesvol ingelogd.', Auth::id(), [
                'email' => Auth::user()?->email,
            ]);

            return redirect()
                ->intended(route('dashboard'))
                ->with('status', 'Welkom terug. Je bent ingelogd.');
        } catch (Throwable $exception) {
            Log::error('Inloggen mislukt door een technische fout.', [
                'email' => $request->string('email')->toString(),
                'exception' => $exception,
            ]);

            return back()
                ->withInput($request->safe()->only('email'))
                ->withErrors(['email' => 'Inloggen is tijdelijk niet gelukt. Probeer het opnieuw.']);
        }
    }

    public function destroy(Request $request, TechnicalLogger $technicalLogger): RedirectResponse
    {
        $userId = Auth::id();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $technicalLogger->record('logout', 'Gebruiker uitgelogd.', $userId);

        return redirect()
            ->route('home')
            ->with('status', 'Je bent veilig uitgelogd.');
    }
}
