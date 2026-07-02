<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Behandeling;
use App\Models\Medewerker;
use App\Models\User;
use App\Services\TechnicalLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AppointmentController extends Controller
{
    public function index(TechnicalLogger $technicalLogger): View
    {
        $customerId = $this->ensureCustomerIdForAuthenticatedUser();
        $appointments = collect(DB::select('CALL sp_get_customer_appointments(?)', [$customerId]));

        $technicalLogger->record('appointment_index', 'Klant heeft afsprakenoverzicht geopend.', auth()->id(), [
            'appointments_count' => $appointments->count(),
        ]);

        return view('appointments.index', [
            'appointments' => $appointments,
        ]);
    }

    public function create(): View
    {
        return view('appointments.create', [
            'treatments' => $this->activeTreatments(),
            'employees' => $this->activeEmployees(),
            'selectedTreatmentId' => (int) request('behandeling_id'),
        ]);
    }

    public function store(StoreAppointmentRequest $request, TechnicalLogger $technicalLogger): RedirectResponse
    {
        $customerId = $this->ensureCustomerIdForAuthenticatedUser();

        try {
            DB::select('CALL sp_create_appointment(?, ?, ?, ?, ?)', [
                $customerId,
                $request->integer('medewerker_id'),
                $request->integer('behandeling_id'),
                $request->date('datum')->format('Y-m-d'),
                $request->string('starttijd')->toString(),
            ]);

            $technicalLogger->record('appointment_create', 'Afspraak aangemaakt.', auth()->id(), [
                'customer_id' => $customerId,
                'treatment_id' => $request->integer('behandeling_id'),
                'employee_id' => $request->integer('medewerker_id'),
                'date' => $request->date('datum')->format('Y-m-d'),
                'start_time' => $request->string('starttijd')->toString(),
            ]);

            return redirect()
                ->route('appointments.index')
                ->with('status', 'Je afspraak is bevestigd.');
        } catch (QueryException $exception) {
            $technicalLogger->record('appointment_create_failed', 'Afspraak aanmaken mislukt.', auth()->id(), [
                'customer_id' => $customerId,
                'treatment_id' => $request->integer('behandeling_id'),
                'employee_id' => $request->integer('medewerker_id'),
                'date' => $request->date('datum')?->format('Y-m-d'),
                'start_time' => $request->string('starttijd')->toString(),
                'error' => $this->storedProcedureErrorMessage($exception),
            ]);

            return $this->backWithStoredProcedureError($exception, 'Deze medewerker is op dit tijdstip niet beschikbaar');
        } catch (Throwable $exception) {
            Log::error('Afspraak aanmaken mislukt.', ['exception' => $exception]);

            return back()
                ->withInput()
                ->with('error', 'Afspraak aanmaken is niet gelukt. Probeer het opnieuw.');
        }
    }

    public function edit(int $appointment, TechnicalLogger $technicalLogger): View|RedirectResponse
    {
        $customerId = $this->ensureCustomerIdForAuthenticatedUser();
        $appointmentDetails = $this->appointmentForCustomer($appointment, $customerId);

        if ($appointmentDetails === null) {
            $technicalLogger->record('appointment_edit_failed', 'Afspraak wijzigen geopend voor onbekende afspraak.', auth()->id(), [
                'appointment_id' => $appointment,
                'customer_id' => $customerId,
            ]);

            return redirect()
                ->route('appointments.index')
                ->with('error', 'Afspraak niet gevonden.');
        }

        $technicalLogger->record('appointment_edit', 'Klant heeft wijzigformulier geopend.', auth()->id(), [
            'appointment_id' => $appointment,
        ]);

        return view('appointments.edit', [
            'appointment' => $appointmentDetails,
            'treatments' => $this->activeTreatments(),
            'employees' => $this->activeEmployees(),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, int $appointment, TechnicalLogger $technicalLogger): RedirectResponse
    {
        $customerId = $this->ensureCustomerIdForAuthenticatedUser();

        try {
            DB::select('CALL sp_update_appointment(?, ?, ?, ?, ?, ?)', [
                $appointment,
                $customerId,
                $request->integer('medewerker_id'),
                $request->integer('behandeling_id'),
                $request->date('datum')->format('Y-m-d'),
                $request->string('starttijd')->toString(),
            ]);

            $technicalLogger->record('appointment_update', 'Afspraak gewijzigd.', auth()->id(), [
                'appointment_id' => $appointment,
                'customer_id' => $customerId,
            ]);

            return redirect()
                ->route('appointments.index')
                ->with('status', 'Je afspraak is gewijzigd.');
        } catch (QueryException $exception) {
            $technicalLogger->record('appointment_update_failed', 'Afspraak wijzigen mislukt.', auth()->id(), [
                'appointment_id' => $appointment,
                'customer_id' => $customerId,
                'treatment_id' => $request->integer('behandeling_id'),
                'employee_id' => $request->integer('medewerker_id'),
                'date' => $request->date('datum')?->format('Y-m-d'),
                'start_time' => $request->string('starttijd')->toString(),
                'error' => $this->storedProcedureErrorMessage($exception),
            ]);

            return $this->backWithStoredProcedureError($exception, 'Dit tijdstip is niet beschikbaar');
        } catch (Throwable $exception) {
            Log::error('Afspraak wijzigen mislukt.', ['exception' => $exception]);

            return back()
                ->withInput()
                ->with('error', 'Afspraak wijzigen is niet gelukt. Probeer het opnieuw.');
        }
    }

    public function cancel(int $appointment, TechnicalLogger $technicalLogger): RedirectResponse
    {
        $customerId = $this->ensureCustomerIdForAuthenticatedUser();

        try {
            DB::select('CALL sp_cancel_appointment(?, ?)', [$appointment, $customerId]);

            $technicalLogger->record('appointment_cancel', 'Afspraak geannuleerd.', auth()->id(), [
                'appointment_id' => $appointment,
                'customer_id' => $customerId,
            ]);

            return redirect()
                ->route('appointments.index')
                ->with('status', 'Je afspraak is geannuleerd.');
        } catch (QueryException $exception) {
            $technicalLogger->record('appointment_cancel_failed', 'Afspraak annuleren mislukt.', auth()->id(), [
                'appointment_id' => $appointment,
                'customer_id' => $customerId,
                'error' => $this->storedProcedureErrorMessage($exception),
            ]);

            return $this->backWithStoredProcedureError($exception, 'Deze afspraak kan niet meer geannuleerd worden');
        }
    }

    private function ensureCustomerIdForAuthenticatedUser(): int
    {
        /** @var User $user */
        $user = auth()->user();
        $result = DB::selectOne('CALL sp_ensure_customer_for_user(?)', [$user->id]);

        return (int) $result->customer_id;
    }

    /**
     * @return Collection<int, Behandeling>
     */
    private function activeTreatments(): Collection
    {
        return Behandeling::query()
            ->where('is_actief', true)
            ->orderBy('naam')
            ->get();
    }

    /**
     * @return Collection<int, Medewerker>
     */
    private function activeEmployees(): Collection
    {
        return Medewerker::query()
            ->where('is_actief', true)
            ->orderBy('voornaam')
            ->orderBy('achternaam')
            ->get();
    }

    private function appointmentForCustomer(int $appointmentId, int $customerId): ?object
    {
        return DB::table('afspraken')
            ->join('afspraak_behandeling', 'afspraken.id', '=', 'afspraak_behandeling.afspraak_id')
            ->join('behandelingen', 'afspraak_behandeling.behandeling_id', '=', 'behandelingen.id')
            ->where('afspraken.id', $appointmentId)
            ->where('afspraken.klant_id', $customerId)
            ->where('afspraken.status', 'Gepland')
            ->select([
                'afspraken.id',
                'afspraken.medewerker_id',
                'afspraken.datum',
                DB::raw("TIME_FORMAT(afspraken.starttijd, '%H:%i') as starttijd"),
                'afspraak_behandeling.behandeling_id',
                'behandelingen.naam as behandeling_naam',
            ])
            ->first();
    }

    private function backWithStoredProcedureError(QueryException $exception, string $fallbackMessage): RedirectResponse
    {
        $message = $this->storedProcedureErrorMessage($exception) ?? $fallbackMessage;

        return back()
            ->withInput()
            ->with('error', $message ?: $fallbackMessage);
    }

    private function storedProcedureErrorMessage(QueryException $exception): ?string
    {
        return $exception->errorInfo[2] ?? null;
    }
}
