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
        /** @var User $user */
        $user = auth()->user();
        $isStaffOverview = $user->isOwner() || $user->isEmployee();
        $appointments = $isStaffOverview
            ? $this->allPlannedAppointments()
            : collect(DB::select('CALL sp_get_customer_appointments(?)', [$this->ensureCustomerIdForAuthenticatedUser()]));

        $technicalLogger->record('appointment_index', 'Klant heeft afsprakenoverzicht geopend.', auth()->id(), [
            'appointments_count' => $appointments->count(),
            'is_staff_overview' => $isStaffOverview,
        ]);

        return view('appointments.index', [
            'appointments' => $appointments,
            'isStaffOverview' => $isStaffOverview,
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
        /** @var User $user */
        $user = auth()->user();
        $isStaffOverview = $user->isOwner() || $user->isEmployee();
        $customerId = $isStaffOverview ? null : $this->ensureCustomerIdForAuthenticatedUser();
        $appointmentDetails = $isStaffOverview
            ? $this->appointmentForStaff($appointment)
            : $this->appointmentForCustomer($appointment, (int) $customerId);

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
            'isStaffOverview' => $isStaffOverview,
        ]);
    }

    public function update(UpdateAppointmentRequest $request, int $appointment, TechnicalLogger $technicalLogger): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $isStaffOverview = $user->isOwner() || $user->isEmployee();
        $customerId = $isStaffOverview
            ? $this->customerIdForAppointment($appointment)
            : $this->ensureCustomerIdForAuthenticatedUser();

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
        /** @var User $user */
        $user = auth()->user();
        $customerId = ($user->isOwner() || $user->isEmployee())
            ? $this->customerIdForAppointment($appointment)
            : $this->ensureCustomerIdForAuthenticatedUser();

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
     * @return Collection<int, object>
     */
    private function allPlannedAppointments(): Collection
    {
        return DB::table('afspraken')
            ->join('klanten', 'klanten.id', '=', 'afspraken.klant_id')
            ->join('medewerkers', 'medewerkers.id', '=', 'afspraken.medewerker_id')
            ->join('afspraak_behandeling', 'afspraak_behandeling.afspraak_id', '=', 'afspraken.id')
            ->join('behandelingen', 'behandelingen.id', '=', 'afspraak_behandeling.behandeling_id')
            ->where('afspraken.status', 'Gepland')
            ->where('afspraken.is_actief', true)
            ->whereRaw("CAST(CONCAT(afspraken.datum, ' ', afspraken.starttijd) AS DATETIME) >= NOW()")
            ->select([
                'afspraken.id',
                DB::raw("CONCAT(klanten.voornaam, ' ', klanten.achternaam) AS customer_name"),
                DB::raw("TIME_FORMAT(afspraken.starttijd, '%H:%i') AS start_time"),
                DB::raw("TIME_FORMAT(afspraken.eindtijd, '%H:%i') AS end_time"),
                'afspraken.datum AS date',
                DB::raw("CONCAT(medewerkers.voornaam, ' ', medewerkers.achternaam) AS employee_name"),
                'behandelingen.naam AS treatment_name',
                'afspraken.status',
            ])
            ->orderBy('afspraken.datum')
            ->orderBy('afspraken.starttijd')
            ->get();
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

    private function appointmentForStaff(int $appointmentId): ?object
    {
        return DB::table('afspraken')
            ->join('afspraak_behandeling', 'afspraken.id', '=', 'afspraak_behandeling.afspraak_id')
            ->join('behandelingen', 'afspraak_behandeling.behandeling_id', '=', 'behandelingen.id')
            ->where('afspraken.id', $appointmentId)
            ->where('afspraken.status', 'Gepland')
            ->where('afspraken.is_actief', true)
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

    private function customerIdForAppointment(int $appointmentId): int
    {
        return (int) DB::table('afspraken')
            ->where('id', $appointmentId)
            ->value('klant_id');
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
